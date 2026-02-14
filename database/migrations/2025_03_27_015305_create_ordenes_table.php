<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id('IdOrden');
            $table->foreignId('IdCliente')->references('IdCliente')->on('clientes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('IdEmpleado')->references('IdEmpleado')->on('empleados')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->date('FechaOrden');
            $table->string('Estado');
            $table->decimal('PrecioTotal', 10, 2);
            $table->decimal('ACuenta', 10, 2);
            $table->decimal('Saldo', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ordenes');
    }
};
