<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoController extends Controller
{
    public function __construct()
    {
        // Configurar SDK de MercadoPago
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
    }

    /**
     * Verificar la conexión con Mercado Pago
     */
    public function verificarConexion()
    {
        try {
            // Verificar credenciales
            $access_token = config('mercadopago.access_token');
            $public_key = config('mercadopago.public_key');
            
            // Intentar obtener información básica de la cuenta
            $client = new \MercadoPago\Client\PaymentMethod\PaymentMethodClient();
            $payment_methods = $client->list();
            
            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa con Mercado Pago',
                'token_length' => strlen($access_token),
                'token_prefix' => substr($access_token, 0, 8) . '...',
                'public_key_prefix' => substr($public_key, 0, 8) . '...',
                'test_mode' => config('mercadopago.test_mode') ? 'true' : 'false',
                'payment_methods_available' => 'OK'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al verificar conexión con MercadoPago', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con MercadoPago: ' . $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }

    /**
     * Obtener configuración de MercadoPago para el frontend
     */
    public function getConfig()
    {
        try {
            return response()->json([
                'success' => true,
                'public_key' => config('mercadopago.public_key'),
                'test_mode' => config('mercadopago.test_mode', true)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener configuración de MercadoPago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar el pago con Mercado Pago Bricks
     */
    public function procesarPago(Request $request)
    {
        try {
            // Verificar credenciales
            $access_token = config('mercadopago.access_token');
            $public_key = config('mercadopago.public_key');
            
            // Registrar información de diagnóstico
            Log::info('Iniciando procesamiento de pago', [
                'access_token_length' => strlen($access_token),
                'access_token_starts' => substr($access_token, 0, 4) . '...',
                'public_key_length' => strlen($public_key),
                'test_mode' => config('mercadopago.test_mode') ? 'true' : 'false',
            ]);
            
            // Registrar la estructura de datos recibidos del frontend
            $paymentData = $request->input('payment_data');
            Log::info('Datos recibidos para procesamiento de pago', [
                'orden_id' => $request->input('orden_id'),
                'total' => $request->input('total'),
                'payment_data_keys' => is_array($paymentData) ? array_keys($paymentData) : 'No es un array',
                'token_present' => is_array($paymentData) && isset($paymentData['token']) ? 'Sí' : 'No',
            ]);
            
            $ordenId = $request->input('orden_id');
            $paymentData = $request->input('payment_data');
            $total = $request->input('total');
            
            // Validar que los datos sean correctos
            if (empty($ordenId)) {
                throw new \Exception('ID de orden no proporcionado');
            }
            
            if (!is_array($paymentData) || !isset($paymentData['token'])) {
                throw new \Exception('Datos de pago inválidos o token no proporcionado');
            }
            
            if (empty($total) || !is_numeric($total)) {
                throw new \Exception('Total inválido o no proporcionado');
            }
            
            // Obtener la orden
            $orden = Orden::with('cliente')->findOrFail($ordenId);
            
            // Crear el pago con MercadoPago
            $client = new PaymentClient();
            
            // Preparar datos para el pago siguiendo la estructura exacta del SDK
            $paymentDataForAPI = [
                'transaction_amount' => floatval($total),
                'token' => $paymentData['token'],
                'description' => "Pago orden #{$ordenId} - TACSAY Lavandería",
                'installments' => (int)$paymentData['installments'],
                'payment_method_id' => $paymentData['payment_method_id'],
                'payer' => [
                    'email' => $orden->cliente->Email ?? 'cliente@example.com',
                    'first_name' => $orden->cliente->Nombre ?? 'Cliente',
                    'last_name' => $orden->cliente->Apellido ?? 'TACSAY',
                    'identification' => [
                        'type' => 'DNI',
                        'number' => $orden->cliente->NumeroDocumento ?? '12345678'
                    ]
                ],
                'additional_info' => [
                    'items' => [
                        [
                            'id' => 'orden_'.$ordenId,
                            'title' => "Servicio de lavandería",
                            'description' => "Orden de lavandería #{$ordenId}",
                            'quantity' => 1,
                            'unit_price' => floatval($total)
                        ]
                    ]
                ],
                'metadata' => [
                    'orden_id' => $ordenId,
                    'cliente_id' => $orden->IdCliente
                ]
            ];
            
            // Registrar los datos que se enviarán a la API
            Log::info('Datos enviados a MercadoPago:', [
                'payment_data' => json_encode($paymentDataForAPI)
            ]);
            
            // Crear el pago
            $payment = $client->create($paymentDataForAPI);
            
            // Guardar el pago en nuestra base de datos
            $pago = Pago::updateOrCreate(
                ['IdOrden' => $orden->IdOrden],
                [
                    'MetodoPago' => 'MercadoPago',
                    'Estado' => $this->mapearEstado($payment->status),
                    'Monto' => $payment->transaction_amount,
                    'ReferenciaPago' => $payment->id,
                    'FechaPago' => now(),
                ]
            );
            
            // IMPORTANTE: Actualizar todos los campos de la orden
            if ($payment->status === 'approved') {
                $actualizacion = [
                    'Estado' => 'Pagada',
                    'MetodoPago' => 'MercadoPago',
                    'EstadoPago' => 'completado',
                    'FechaPago' => now()
                ];
                
                $orden->update($actualizacion);
                
                Log::info('Orden actualizada a estado Pagada', [
                    'orden_id' => $ordenId,
                    'payment_id' => $payment->id
                ]);
            }
            
            // Determinar la URL de redirección según el estado del pago
            $redirectUrl = route('pago.exitoso', ['orden_id' => $ordenId]);
            if ($payment->status === 'rejected') {
                $redirectUrl = route('pago.error', ['orden_id' => $ordenId]);
            } elseif ($payment->status === 'pending' || $payment->status === 'in_process') {
                $redirectUrl = route('pago.pendiente', ['orden_id' => $ordenId]);
            }
            
            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'payment_id' => $payment->id,
                'redirect_url' => $redirectUrl,
                'message' => 'Pago procesado correctamente'
            ]);
            
        } catch (MPApiException $e) {
            // Registrar detalles completos del error
            Log::error('Error al procesar pago con MercadoPago', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // En la versión actual del SDK, extraemos directamente el mensaje
            $mensaje = 'Error al procesar el pago con MercadoPago: ' . $e->getMessage();
            
            return response()->json([
                'success' => false,
                'message' => $mensaje,
                'error_code' => $e->getCode()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error general al procesar pago', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mostrar vista de pago exitoso
     */
    public function pagoExitoso(Request $request)
    {
        $ordenId = $request->input('orden_id');
        return view('pages.pagoExitoso', [
            'orden_id' => $ordenId
        ]);
    }
    
    /**
     * Mostrar vista de pago con error
     */
    public function pagoError(Request $request)
    {
        // Usar query() en lugar de input() para obtener parámetros de la URL
        $ordenId = $request->query('orden_id', 0); // Valor por defecto si no existe
        $errorMsg = $request->query('error', 'Error desconocido en el procesamiento del pago');
        
        // Registrar información para diagnóstico
        Log::info('Pago error recibido', [
            'orden_id' => $ordenId,
            'error_msg' => $errorMsg,
            'query_params' => $request->query()
        ]);
        
        return view('pages.pagoError', [
            'orden_id' => $ordenId,
            'mensaje' => $errorMsg // Cambiar 'error' por 'mensaje' para coincidir con la vista
        ]);
    }
    
    /**
     * Mostrar vista de pago pendiente
     */
    public function pagoPendiente(Request $request)
    {
        // Usar query() en lugar de input() para ser consistente
        $ordenId = $request->query('orden_id', 0);
        
        Log::info('Pago pendiente recibido', [
            'orden_id' => $ordenId
        ]);
        
        return view('pages.pagoPendiente', [
            'orden_id' => $ordenId
        ]);
    }
    
    /**
     * Mostrar los errores recientes de MercadoPago para diagnóstico
     */
    public function mostrarErrores()
    {
        try {
            // Revisar si hay errores relacionados con MercadoPago en las últimas 24 horas
            $errores = [];
            $logPath = storage_path('logs/laravel.log');
            
            if (file_exists($logPath)) {
                $log = file_get_contents($logPath);
                $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*?Error.*?MercadoPago.*?\n/s';
                
                if (preg_match_all($pattern, $log, $matches)) {
                    // Tomar los últimos 10 errores como máximo
                    $errores = array_slice($matches[0], -10);
                }
            }
            
            // Obtener la configuración actual
            $config = [
                'public_key_length' => strlen(config('mercadopago.public_key')),
                'access_token_length' => strlen(config('mercadopago.access_token')),
                'test_mode' => config('mercadopago.test_mode') ? 'true' : 'false'
            ];
            
            return response()->json([
                'success' => true,
                'errores_recientes' => $errores,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener logs: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mapear estado de MercadoPago a estado interno
     */
    private function mapearEstado($estadoMercadoPago)
    {
        switch ($estadoMercadoPago) {
            case 'approved':
                return 'completado';
            case 'pending':
                return 'pendiente';
            case 'in_process':
                return 'en_proceso';
            case 'rejected':
                return 'rechazado';
            default:
                return 'pendiente';
        }
    }
}
