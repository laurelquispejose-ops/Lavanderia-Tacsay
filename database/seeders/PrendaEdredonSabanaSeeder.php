<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PrendaEdredonSabanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Añade los tipos de prendas Edredón y Sábana a la base de datos.
     */
    public function run(): void
    {
        // Verificar si ya existen estas prendas para evitar duplicados
        $edredonExists = DB::table('prendas')
            ->where('TipoPrenda', 'Edredón')
            ->exists();
            
        $sabanaExists = DB::table('prendas')
            ->where('TipoPrenda', 'Sábana')
            ->exists();
        
        // Obtener el último ID de prenda para continuar la secuencia
        $lastId = DB::table('prendas')->max('IdPrenda') ?? 0;
        
        $now = Carbon::now();
        
        // Añadir Edredón si no existe
        if (!$edredonExists) {
            DB::table('prendas')->insert([
                'IdPrenda' => $lastId + 1,
                'TipoPrenda' => 'Edredón',
                'ColorPrenda' => 'N/A',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            $this->command->info('Prenda Edredón añadida correctamente.');
            $lastId++;
        } else {
            $this->command->info('La prenda Edredón ya existe en la base de datos.');
        }
        
        // Añadir Sábana si no existe
        if (!$sabanaExists) {
            DB::table('prendas')->insert([
                'IdPrenda' => $lastId + 1,
                'TipoPrenda' => 'Sábana',
                'ColorPrenda' => 'N/A',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            $this->command->info('Prenda Sábana añadida correctamente.');
        } else {
            $this->command->info('La prenda Sábana ya existe en la base de datos.');
        }
    }
}
