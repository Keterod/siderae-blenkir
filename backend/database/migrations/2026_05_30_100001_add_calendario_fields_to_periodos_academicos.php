<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->foreignId('anio_escolar_id')
                ->nullable()
                ->after('id')
                ->constrained('anios_escolares')
                ->nullOnDelete();

            $table->enum('estado', ['activo', 'inactivo', 'cerrado'])
                ->default('activo')
                ->after('activo');

            $table->boolean('es_vigente')->default(false)->after('estado');

            $table->index(['anio_escolar', 'es_vigente'], 'periodos_anio_vigente_idx');
        });
    }

    public function down(): void
    {
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->dropIndex('periodos_anio_vigente_idx');
            $table->dropConstrainedForeignId('anio_escolar_id');
            $table->dropColumn(['estado', 'es_vigente']);
        });
    }
};
