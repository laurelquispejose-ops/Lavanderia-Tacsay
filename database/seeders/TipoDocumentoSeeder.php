<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar tipos de documentos comunes
        $tiposDocumentos = [
            ['Nombre' => 'Boleta'],
            ['Nombre' => 'Factura'],
            ['Nombre' => 'Recibo'],
            ['Nombre' => 'Ticket'],
            ['Nombre' => 'Comprobante']
        ];

        foreach ($tiposDocumentos as $tipo) {
            DB::table('tipos_documentos')->insert([
                'Nombre' => $tipo['Nombre'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
