<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Orden;

class ClienteOrdenController extends Controller
{
    public function index()
    {
        $cliente = Auth::guard('clientes')->user();

        $ordenes = Orden::with(['detalles.prenda', 'pago'])
            ->where('IdCliente', $cliente->idCliente)
            ->get()
            ->map(function ($orden) {
                // Obtener información del pago
                $pago = $orden->pago;
                
                // Priorizar la información de pago directamente de la orden si existe
                return [
                    'id' => $orden->IdOrden,
                    'total' => $orden->PrecioTotal,
                    'estado' => $orden->Estado,
                    'fecha' => $orden->created_at ? $orden->created_at->format('d/m/Y H:i') : null,
                    // Usar primero MetodoPago de la orden, luego el del registro de pago si existe
                    'metodo_pago' => $orden->MetodoPago ?? ($pago ? $pago->MetodoPago : null),
                    // Usar primero EstadoPago de la orden, luego el del registro de pago si existe
                    'estado_pago' => $orden->EstadoPago ?? ($pago ? $pago->Estado : 'pendiente'),
                    'detalle_orden' => $orden->detalles->map(function ($detalle) {
                        return [
                            'nombre_prenda' => $detalle->prenda->TipoPrenda ?? 'Prenda eliminada',
                            'cantidad' => $detalle->Cantidad,
                            'peso' => $detalle->Peso,
                            'subtotal' => $detalle->Cantidad > 0 ? 
                                          ($detalle->PrecioUnitario * $detalle->Cantidad) : 
                                          ($detalle->PrecioUnitario * $detalle->Peso),
                        ];
                    }),
                ];
            });

        return response()->json($ordenes);
    }
}
