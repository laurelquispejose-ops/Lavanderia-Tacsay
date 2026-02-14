<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('detalle_orden', function (Blueprint $table) {
            $table->id('IdDetalle');
            $table->integer('Cantidad');
            $table->integer('Peso');
            $table->foreignId('IdOrden')->references('IdOrden')->on('ordenes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('IdPrenda')->references('IdPrenda')->on('prendas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('IdServicioLavado')->references('IdServicio')->on('servicios')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('IdServicioPlanchado')->references('IdServicio')->on('servicios')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_orden');
    }
};
