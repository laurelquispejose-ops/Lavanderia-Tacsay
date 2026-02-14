<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            // Servicios de lavado (IDs 1-3)
            ['Nombre' => 'Lavado Normal', 'TipoServicio' => 'lavado', 'Precio' => 3.00],
            ['Nombre' => 'Lavado Delicado', 'TipoServicio' => 'lavado', 'Precio' => 4.00],
            ['Nombre' => 'Lavado Premium', 'TipoServicio' => 'lavado', 'Precio' => 5.00],
            
            // Servicios de planchado (IDs 4-6)
            ['Nombre' => 'Planchado Simple', 'TipoServicio' => 'planchado', 'Precio' => 3.00],
            ['Nombre' => 'Planchado Especial', 'TipoServicio' => 'planchado', 'Precio' => 4.00],
            ['Nombre' => 'Planchado Premium', 'TipoServicio' => 'planchado', 'Precio' => 5.00],
        ];

        foreach ($servicios as $servicio) {
            DB::table('servicios')->insert([
                'Nombre' => $servicio['Nombre'],
                'TipoServicio' => $servicio['TipoServicio'],
                'Precio' => $servicio['Precio'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
