<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Exportar órdenes a Excel (CSV)
     */
    public function exportarOrdenesExcel(Request $request)
    {
        $estado = $request->query('estado');
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        
        // Consultar órdenes con filtros
        $ordenes = $this->getOrdenesFiltradas($estado, $fechaInicio, $fechaFin);
        
        // Crear un archivo CSV temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        $file = fopen($tempFile, 'w');
        
        // Establecer encabezados UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
        
        // Escribir encabezados
        fputcsv($file, [
            'ID Orden',
            'Cliente',
            'Empleado',
            'Fecha',
            'Estado',
            'Total (S/.)',
            'A Cuenta (S/.)',
            'Saldo (S/.)',
            'Detalles',
            'Método de Pago'
        ]);
        
        // Escribir datos
        foreach ($ordenes as $orden) {
            // Formatear detalles
            $detalles = $orden->detalles->map(function ($detalle) {
                $prenda = $detalle->prenda ? $detalle->prenda->TipoPrenda : 'N/A';
                return "{$prenda} x{$detalle->Cantidad}";
            })->join(', ');
            
            // Método de pago
            $metodoPago = $orden->pagos->first() ? $orden->pagos->first()->MetodoPago : 'N/A';
            
            // Escribir fila
            fputcsv($file, [
                $orden->IdOrden,
                $orden->cliente ? $orden->cliente->nombre : 'N/A',
                $orden->empleado ? $orden->empleado->nombre : 'N/A',
                $orden->FechaOrden,
                $this->formatearEstado($orden->Estado),
                number_format($orden->PrecioTotal, 2),
                number_format($orden->ACuenta, 2),
                number_format($orden->Saldo, 2),
                $detalles,
                $metodoPago
            ]);
        }
        
        // Cerrar el archivo
        fclose($file);
        
        // Generar nombre de archivo con timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $estadoTexto = $estado ? Str::slug($estado) : 'todos';
        $filename = "ordenes_{$estadoTexto}_{$timestamp}.csv";
        
        // Devolver respuesta
        return Response::download($tempFile, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Exportar órdenes a PDF
     */
    public function exportarOrdenesPDF(Request $request)
    {
        $estado = $request->query('estado');
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        
        // Consultar órdenes con filtros
        $ordenes = $this->getOrdenesFiltradas($estado, $fechaInicio, $fechaFin);
        
        // Formatear datos para la vista
        $ordenes = $ordenes->map(function ($orden) {
            // Formatear detalles
            $detalles = $orden->detalles->map(function ($detalle) {
                $prenda = $detalle->prenda ? $detalle->prenda->TipoPrenda : 'N/A';
                return "{$prenda} x{$detalle->Cantidad}";
            })->join(', ');
            
            // Formatear estado
            $estadoFormateado = $this->formatearEstado($orden->Estado);
            
            // Método de pago
            $metodoPago = $orden->pagos->first() ? $orden->pagos->first()->MetodoPago : 'N/A';
            
            // Añadir propiedades formateadas
            $orden->detallesFormateados = $detalles;
            $orden->estadoFormateado = $estadoFormateado;
            $orden->metodoPago = $metodoPago;
            
            return $orden;
        });
        
        // Generar nombre de archivo con timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $estadoTexto = $estado ? Str::slug($estado) : 'todos';
        $filename = "ordenes_{$estadoTexto}_{$timestamp}.pdf";
        
        // Generar PDF con vista
        $pdf = PDF::loadView('exports.ordenes-pdf', [
            'ordenes' => $ordenes,
            'estado' => $estado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s')
        ]);
        
        // Configurar PDF
        $pdf->setPaper('a4', 'landscape');
        
        // Descargar PDF
        return $pdf->download($filename);
    }
    
    /**
     * Obtener órdenes filtradas por estado y fechas
     */
    private function getOrdenesFiltradas($estado, $fechaInicio, $fechaFin)
    {
        $query = Orden::with(['cliente', 'empleado', 'detalles.prenda', 'pagos']);
        
        // Filtrar por estado si se especifica
        if ($estado && $estado !== 'todos') {
            $query->where('Estado', $estado);
        }
        
        // Filtrar por rango de fechas si se especifica
        if ($fechaInicio) {
            $query->whereDate('FechaOrden', '>=', $fechaInicio);
        }
        
        if ($fechaFin) {
            $query->whereDate('FechaOrden', '<=', $fechaFin);
        }
        
        return $query->get();
    }
    
    /**
     * Formatear el estado para mejor legibilidad
     */
    private function formatearEstado($estado)
    {
        switch ($estado) {
            case 'pendiente':
                return 'Pendiente';
            case 'en proceso lavado':
                return 'En Proceso de Lavado';
            case 'en proceso planchado':
                return 'En Proceso de Planchado';
            case 'finalizado':
                return 'Finalizado';
            default:
                return $estado;
        }
    }
}
