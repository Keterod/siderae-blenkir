<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('docente_curso_aulas', 'activo_unique_key')) {
            Schema::table('docente_curso_aulas', function (Blueprint $table) {
                $table->dropUnique('docente_curso_aula_asignacion_unique');
            });

            Schema::table('docente_curso_aulas', function (Blueprint $table) {
                $table->unsignedTinyInteger('activo_unique_key')->nullable()->after('activo');
            });

            DB::table('docente_curso_aulas')->where('activo', true)->update(['activo_unique_key' => 1]);
            DB::table('docente_curso_aulas')->where('activo', false)->update(['activo_unique_key' => null]);

            Schema::table('docente_curso_aulas', function (Blueprint $table) {
                $table->unique(
                    ['anio_escolar', 'nivel', 'grado', 'seccion', 'sede', 'malla_curso_id', 'activo_unique_key'],
                    'docente_curso_aula_activo_unique'
                );
            });
        }

        if (! Schema::hasColumn('temas_semanales', 'activo_unique_key')) {
            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->index('malla_curso_id', 'temas_semanales_malla_curso_fk_index');
            });

            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->dropUnique('temas_curso_periodo_semana_unique');
            });

            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->unsignedTinyInteger('activo_unique_key')->nullable()->after('activo');
            });

            DB::table('temas_semanales')->where('activo', true)->update(['activo_unique_key' => 1]);
            DB::table('temas_semanales')->where('activo', false)->update(['activo_unique_key' => null]);

            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->unique(
                    ['malla_curso_id', 'periodo_academico_id', 'semana_academica_id', 'activo_unique_key'],
                    'temas_curso_periodo_semana_activo_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('docente_curso_aulas', 'activo_unique_key')) {
            Schema::table('docente_curso_aulas', function (Blueprint $table) {
                $table->dropUnique('docente_curso_aula_activo_unique');
                $table->dropColumn('activo_unique_key');
            });

            Schema::table('docente_curso_aulas', function (Blueprint $table) {
                $table->unique(
                    ['anio_escolar', 'nivel', 'grado', 'seccion', 'sede', 'malla_curso_id'],
                    'docente_curso_aula_asignacion_unique'
                );
            });
        }

        if (Schema::hasColumn('temas_semanales', 'activo_unique_key')) {
            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->dropUnique('temas_curso_periodo_semana_activo_unique');
                $table->dropColumn('activo_unique_key');
            });

            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->unique(
                    ['malla_curso_id', 'periodo_academico_id', 'semana_academica_id'],
                    'temas_curso_periodo_semana_unique'
                );
                $table->dropIndex('temas_semanales_malla_curso_fk_index');
            });
        }
    }
};
