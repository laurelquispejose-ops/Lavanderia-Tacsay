<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->foreignId('IdOrden')->references('IdOrden')->on('ordenes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('IdTipoDocumento')->references('IdTipoDocumento')->on('tipos_documentos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('NumDocumento');
            $table->date('FechaPago');
            $table->decimal('MontoPagado', 10, 2);
            $table->string('MetodoPago');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos');
    }
};
