<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('prendas', function (Blueprint $table) {
            $table->id('IdPrenda');
            $table->string('TipoPrenda');
            $table->string('ColorPrenda');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prendas');
    }
};
