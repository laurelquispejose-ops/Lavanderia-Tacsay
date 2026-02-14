<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('IdEmpleado');
            $table->string('Nombre');
            $table->string('CorreoElectronico')->unique();
            $table->string('password');
            $table->text('Direccion');
            $table->enum('Cargo', ['administrador', 'empleado']);
            $table->string('Telefono');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('empleados');
    }
};
