<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes', 'FechaEntrada')) {
                $table->date('FechaEntrada')->nullable()->after('IdEmpleado');
            }
            if (!Schema::hasColumn('ordenes', 'FechaEntrega')) {
                $table->date('FechaEntrega')->nullable()->after('FechaEntrada');
            }
        });
    }

    public function down()
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes', 'FechaEntrada')) {
                $table->dropColumn('FechaEntrada');
            }
            if (Schema::hasColumn('ordenes', 'FechaEntrega')) {
                $table->dropColumn('FechaEntrega');
            }
        });
    }
};
