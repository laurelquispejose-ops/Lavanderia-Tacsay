<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Agrega un empleado de prueba
        $this->call(EmpleadoSeeder::class);
        
        // Agrega tipos de documentos
        $this->call(TipoDocumentoSeeder::class);
        
        // Agrega prendas y servicios necesarios
        $this->call(PrendaSeeder::class);
        $this->call(ServicioSeeder::class);
        
        // Agrega tipos de prendas Edredón y Sábana
        $this->call(PrendaEdredonSabanaSeeder::class);
        
        // Agrega el servicio "Sin planchado"
        $this->call(SinPlanchadoSeeder::class);
    }
}
