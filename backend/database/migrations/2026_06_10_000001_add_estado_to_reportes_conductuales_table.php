<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reportes_conductuales', function (Blueprint $table) {
            $table->enum('estado', ['activo', 'anulado'])
                ->default('activo')
                ->after('accion_inmediata');
            $table->index(['estudiante_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('reportes_conductuales', function (Blueprint $table) {
            $table->dropIndex(['estudiante_id', 'estado']);
            $table->dropColumn('estado');
        });
    }
};
