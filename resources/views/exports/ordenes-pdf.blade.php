<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Órdenes - TACSAY</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
        }
        .info {
            font-size: 11px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .estado-pendiente {
            color: #f6c23e;
            font-weight: bold;
        }
        .estado-lavado {
            color: #36b9cc;
            font-weight: bold;
        }
        .estado-planchado {
            color: #4e73df;
            font-weight: bold;
        }
        .estado-finalizado {
            color: #1cc88a;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE ÓRDENES - TACSAY</h1>
        <div class="info">
            <strong>Fecha de generación:</strong> {{ $fechaGeneracion }}
        </div>
        <div class="info">
            <strong>Filtros aplicados:</strong> 
            Estado: {{ $estado ? $estado : 'Todos' }} | 
            Fecha: {{ $fechaInicio ? $fechaInicio : 'Desde el inicio' }} 
            hasta {{ $fechaFin ? $fechaFin : 'la fecha actual' }}
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Total (S/.)</th>
                <th>A Cuenta (S/.)</th>
                <th>Saldo (S/.)</th>
                <th>Detalles</th>
                <th>Método de Pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenes as $orden)
                <tr>
                    <td>{{ $orden->IdOrden }}</td>
                    <td>{{ $orden->cliente ? $orden->cliente->nombre : 'N/A' }}</td>
                    <td>{{ $orden->FechaOrden }}</td>
                    <td class="estado-{{ Str::slug(explode(' ', $orden->Estado)[0]) }}">
                        {{ $orden->estadoFormateado }}
                    </td>
                    <td>{{ number_format($orden->PrecioTotal, 2) }}</td>
                    <td>{{ number_format($orden->ACuenta, 2) }}</td>
                    <td>{{ number_format($orden->Saldo, 2) }}</td>
                    <td>{{ $orden->detallesFormateados }}</td>
                    <td>{{ $orden->metodoPago }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No se encontraron órdenes con los filtros aplicados</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>© {{ date('Y') }} TACSAY - Todos los derechos reservados</p>
        <p>Este documento es generado automáticamente y no requiere firma</p>
    </div>
</body>
</html>
