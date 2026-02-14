<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
    /**
     * Crea una sesión de pago de Stripe para una orden específica
     */
    public function crearSesion(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:ordenes,IdOrden',
        ]);

        $ordenId = $request->orden_id;
        $orden = Orden::with(['detalles.prenda', 'cliente'])->findOrFail($ordenId);
        
        // Verificar que la orden pertenezca al cliente autenticado
        $cliente = Auth::guard('clientes')->user();
        if ($cliente && $orden->IdCliente != $cliente->idCliente) {
            return response()->json([
                'error' => 'No tienes permiso para pagar esta orden'
            ], 403);
        }

        // Configurar Stripe con la clave secreta
        Stripe::setApiKey(config('stripe.secret'));
        
        try {
            // Crear los items para la sesión de Stripe
            $lineItems = [];
            
            // Añadir cada detalle de la orden como un item en Stripe
            foreach ($orden->detalles as $detalle) {
                $nombrePrenda = $detalle->prenda ? $detalle->prenda->nombre : 'Prenda';
                $cantidad = $detalle->Cantidad > 0 ? $detalle->Cantidad : 1;
                
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'pen',
                        'product_data' => [
                            'name' => $nombrePrenda,
                            'description' => 'Servicio de lavandería',
                        ],
                        'unit_amount' => (int)($orden->PrecioTotal * 100 / $cantidad), // Convertir a centavos
                    ],
                    'quantity' => $cantidad,
                ];
            }
            
            // Si no hay detalles, usar el total de la orden como un solo item
            if (empty($lineItems)) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'pen',
                        'product_data' => [
                            'name' => 'Servicio de lavandería',
                            'description' => 'Orden #' . $orden->IdOrden,
                        ],
                        'unit_amount' => (int)($orden->PrecioTotal * 100), // Convertir a centavos
                    ],
                    'quantity' => 1,
                ];
            }
            
            // Crear la sesión de pago en Stripe
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => url('/cliente/pago/exito?session_id={CHECKOUT_SESSION_ID}&orden_id=' . $ordenId),
                'cancel_url' => url('/cliente/pago/cancelado?orden_id=' . $ordenId),
                'client_reference_id' => $ordenId,
                'metadata' => [
                    'orden_id' => $ordenId,
                    'cliente_id' => $orden->IdCliente,
                ],
            ]);
            
            return response()->json([
                'id' => $session->id,
                'url' => $session->url
            ]);
            
        } catch (ApiErrorException $e) {
            Log::error('Error al crear sesión de Stripe: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar el pago',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Maneja el webhook de Stripe para actualizar el estado del pago
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('stripe.webhook.secret');
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
            
            // Manejar el evento según su tipo
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->handleCompletedCheckout($session);
                    break;
                    
                default:
                    Log::info('Evento de Stripe no manejado: ' . $event->type);
            }
            
            return response()->json(['status' => 'success']);
            
        } catch (\UnexpectedValueException $e) {
            Log::error('Error en webhook de Stripe (payload inválido): ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Error en webhook de Stripe (firma inválida): ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Error en webhook de Stripe: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Maneja el evento de checkout completado
     */
    private function handleCompletedCheckout($session)
    {
        $ordenId = $session->metadata->orden_id;
        
        // Buscar la orden
        $orden = Orden::find($ordenId);
        if (!$orden) {
            Log::error('Orden no encontrada para el pago completado: ' . $ordenId);
            return;
        }
        
        // Actualizar el pago
        $pago = Pago::where('IdOrden', $ordenId)->first();
        if ($pago) {
            $pago->Estado = 'completado';
            $pago->ReferenciaPago = $session->payment_intent;
            $pago->save();
            
            Log::info('Pago actualizado a completado para la orden: ' . $ordenId);
        } else {
            Log::error('Pago no encontrado para la orden: ' . $ordenId);
        }
    }
    
    /**
     * Página de éxito después del pago
     */
    public function exitoPago(Request $request)
    {
        $sessionId = $request->session_id;
        $ordenId = $request->orden_id;
        
        if (!$sessionId || !$ordenId) {
            return redirect('/cliente/ordenes')->with('error', 'Información de pago incompleta');
        }
        
        try {
            // Verificar el estado de la sesión en Stripe
            Stripe::setApiKey(config('stripe.secret'));
            $session = Session::retrieve($sessionId);
            
            if ($session->payment_status === 'paid') {
                // Actualizar el estado del pago en la base de datos
                $pago = Pago::where('IdOrden', $ordenId)->first();
                if ($pago) {
                    $pago->Estado = 'completado';
                    $pago->ReferenciaPago = $session->payment_intent;
                    $pago->save();
                    
                    return view('pages.pagoExitoso', [
                        'orden_id' => $ordenId,
                        'monto' => $pago->MontoPagado
                    ]);
                }
            }
            
            return redirect('/cliente/ordenes')->with('warning', 'El estado del pago no pudo ser verificado');
            
        } catch (\Exception $e) {
            Log::error('Error al verificar pago: ' . $e->getMessage());
            return redirect('/cliente/ordenes')->with('error', 'Error al verificar el pago');
        }
    }
    
    /**
     * Página de cancelación de pago
     */
    public function canceladoPago(Request $request)
    {
        $ordenId = $request->orden_id;
        
        return view('pages.pagoCancelado', [
            'orden_id' => $ordenId
        ]);
    }
}
