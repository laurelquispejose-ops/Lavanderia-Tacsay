<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrendaSeeder extends Seeder
{
    public function run(): void
    {
        $prendas = [
            ['TipoPrenda' => 'Camisa', 'ColorPrenda' => 'Blanco'],
            ['TipoPrenda' => 'Pantalón', 'ColorPrenda' => 'Negro'],
            ['TipoPrenda' => 'Vestido', 'ColorPrenda' => 'Azul'],
            ['TipoPrenda' => 'Saco', 'ColorPrenda' => 'Gris'],
            ['TipoPrenda' => 'Falda', 'ColorPrenda' => 'Rojo'],
        ];

        foreach ($prendas as $prenda) {
            DB::table('prendas')->insert([
                'TipoPrenda' => $prenda['TipoPrenda'],
                'ColorPrenda' => $prenda['ColorPrenda'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
