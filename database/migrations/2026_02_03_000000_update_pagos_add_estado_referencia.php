<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'Estado')) {
                $table->string('Estado')->default('pendiente');
            }
            if (!Schema::hasColumn('pagos', 'ReferenciaPago')) {
                $table->string('ReferenciaPago')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'ReferenciaPago')) {
                $table->dropColumn('ReferenciaPago');
            }
            if (Schema::hasColumn('pagos', 'Estado')) {
                $table->dropColumn('Estado');
            }
        });
    }
};
