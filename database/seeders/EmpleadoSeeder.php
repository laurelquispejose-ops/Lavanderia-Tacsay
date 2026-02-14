<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use Illuminate\Support\Facades\Hash;
use App\Enums\Cargo;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        Empleado::create([
            'Nombre' => 'Empleado Prueba',
            'CorreoElectronico' => 'empleado@demo.com',
            'Telefono' => '999999999',
            'Direccion' => 'Oficina Central',
            'password' => Hash::make('Empleado123!'),
            'Cargo' => Cargo::cases()[0]->value // Primer cargo disponible
        ]);
    }
}
