<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->index(
                ['anio_escolar', 'nivel', 'sede', 'grado', 'seccion', 'activo'],
                'estudiantes_filtro_aula_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropIndex('estudiantes_filtro_aula_idx');
        });
    }
};
