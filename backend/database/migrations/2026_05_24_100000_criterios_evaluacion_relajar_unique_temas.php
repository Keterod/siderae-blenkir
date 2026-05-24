<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('temas_semanales', 'activo_unique_key')) {
            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->dropUnique('temas_curso_periodo_semana_activo_unique');
                $table->dropColumn('activo_unique_key');
            });
        }

        Schema::table('temas_semanales', function (Blueprint $table) {
            $table->dropForeign(['semana_academica_id']);
        });

        Schema::table('temas_semanales', function (Blueprint $table) {
            $table->unsignedBigInteger('semana_academica_id')->nullable()->change();
        });

        Schema::table('temas_semanales', function (Blueprint $table) {
            $table->foreign('semana_academica_id')
                ->references('id')
                ->on('semanas_academicas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('temas_semanales', function (Blueprint $table) {
            $table->dropForeign(['semana_academica_id']);
        });

        Schema::table('temas_semanales', function (Blueprint $table) {
            $table->unsignedBigInteger('semana_academica_id')->nullable(false)->change();
        });

        Schema::table('temas_semanales', function (Blueprint $table) {
            $table->foreign('semana_academica_id')
                ->references('id')
                ->on('semanas_academicas');
        });

        if (! Schema::hasColumn('temas_semanales', 'activo_unique_key')) {
            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->unsignedTinyInteger('activo_unique_key')->nullable()->after('activo');
            });

            Schema::table('temas_semanales', function (Blueprint $table) {
                $table->unique(
                    ['malla_curso_id', 'periodo_academico_id', 'semana_academica_id', 'activo_unique_key'],
                    'temas_curso_periodo_semana_activo_unique'
                );
            });
        }
    }
};
