<?php

namespace App\Exports;

use App\Models\Orden;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class OrdenesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $estado;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($estado = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->estado = $estado;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Orden::with(['cliente', 'empleado', 'detalles.prenda', 'pagos']);

        // Filtrar por estado si se especifica
        if ($this->estado && $this->estado !== 'todos') {
            $query->where('Estado', $this->estado);
        }

        // Filtrar por rango de fechas si se especifica
        if ($this->fechaInicio) {
            $query->whereDate('FechaOrden', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query->whereDate('FechaOrden', '<=', $this->fechaFin);
        }

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
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
        ];
    }

    /**
     * @param mixed $orden
     * @return array
     */
    public function map($orden): array
    {
        // Formatear detalles de la orden
        $detalles = $orden->detalles->map(function ($detalle) {
            $prenda = $detalle->prenda ? $detalle->prenda->TipoPrenda : 'N/A';
            return "{$prenda} x{$detalle->Cantidad}";
        })->join(', ');

        // Obtener método de pago
        $metodoPago = $orden->pagos->first() ? $orden->pagos->first()->MetodoPago : 'N/A';

        return [
            $orden->IdOrden,
            $orden->cliente ? $orden->cliente->Nombre : 'Cliente no disponible',
            $orden->empleado ? $orden->empleado->Nombre : 'Empleado no disponible',
            $orden->FechaOrden,
            $this->formatearEstado($orden->Estado),
            number_format($orden->PrecioTotal, 2),
            number_format($orden->ACuenta, 2),
            number_format($orden->Saldo, 2),
            $detalles,
            $metodoPago
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
            // Estilo para todas las celdas
            'A1:J1000' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
                ]
            ]
        ];
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
