<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Culqi\Culqi;

class CulqiController extends Controller
{
    /**
     * Crea un cargo con Culqi para una orden específica
     */
    public function crearCargo(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:ordenes,IdOrden',
            'token' => 'required|string',
        ]);

        $ordenId = $request->orden_id;
        $token = $request->token;
        
        $orden = Orden::with(['detalles.prenda', 'cliente'])->findOrFail($ordenId);
        
        // Verificar que la orden pertenezca al cliente autenticado
        $cliente = Auth::guard('clientes')->user();
        if ($cliente && $orden->IdCliente != $cliente->idCliente) {
            return response()->json([
                'error' => 'No tienes permiso para pagar esta orden'
            ], 403);
        }

        // Configurar Culqi con la clave privada
        $culqi = new Culqi(['api_key' => config('culqi.private_key')]);
        
        try {
            // Crear el cargo en Culqi
            $monto = intval($orden->PrecioTotal * 100); // Convertir a centavos
            
            $cargo = $culqi->Charges->create([
                'amount' => $monto,
                'currency_code' => 'PEN',
                'description' => 'Orden de lavandería #' . $orden->IdOrden,
                'email' => $cliente->Email,
                'source_id' => $token
            ]);
            
            // Si llegamos aquí, el cargo fue exitoso
            // Actualizar el pago en la base de datos
            $pago = Pago::where('IdOrden', $ordenId)->first();
            if ($pago) {
                $pago->Estado = 'completado';
                $pago->ReferenciaPago = $cargo->id;
                $pago->save();
                
                Log::info('Pago actualizado a completado para la orden: ' . $ordenId);
            } else {
                Log::error('Pago no encontrado para la orden: ' . $ordenId);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Pago procesado correctamente',
                'cargo_id' => $cargo->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al procesar pago con Culqi: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar el pago',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Página de éxito después del pago
     */
    public function exitoPago(Request $request)
    {
        $ordenId = $request->orden_id;
        $cargoId = $request->cargo_id;
        
        if (!$ordenId || !$cargoId) {
            return redirect('/cliente/ordenes')->with('error', 'Información de pago incompleta');
        }
        
        // Buscar el pago
        $pago = Pago::where('IdOrden', $ordenId)->first();
        if (!$pago || $pago->Estado !== 'completado') {
            return redirect('/cliente/ordenes')->with('warning', 'El estado del pago no pudo ser verificado');
        }
        
        return view('pages.pagoExitoso', [
            'orden_id' => $ordenId,
            'monto' => $pago->MontoPagado
        ]);
    }
    
    /**
     * Página de error de pago
     */
    public function errorPago(Request $request)
    {
        $ordenId = $request->orden_id;
        
        return view('pages.pagoError', [
            'orden_id' => $ordenId,
            'mensaje' => $request->mensaje ?? 'Hubo un problema al procesar tu pago'
        ]);
    }
}
