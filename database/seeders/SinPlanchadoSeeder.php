<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SinPlanchadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Añade el servicio "Sin planchado" a la base de datos.
     */
    public function run(): void
    {
        // Verificar si ya existe este servicio para evitar duplicados
        $sinPlanchadoExists = DB::table('servicios')
            ->where('Nombre', 'Sin planchado')
            ->where('TipoServicio', 'planchado')
            ->exists();
        
        if (!$sinPlanchadoExists) {
            // Obtener el último ID de servicio para continuar la secuencia
            $lastId = DB::table('servicios')->max('IdServicio') ?? 0;
            
            $now = Carbon::now();
            
            // Añadir el servicio "Sin planchado"
            DB::table('servicios')->insert([
                'IdServicio' => $lastId + 1,
                'Nombre' => 'Sin planchado',
                'TipoServicio' => 'planchado',
                'Precio' => 0, // Sin costo
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            $this->command->info('Servicio "Sin planchado" añadido correctamente con ID ' . ($lastId + 1));
        } else {
            $this->command->info('El servicio "Sin planchado" ya existe en la base de datos.');
        }
    }
}
