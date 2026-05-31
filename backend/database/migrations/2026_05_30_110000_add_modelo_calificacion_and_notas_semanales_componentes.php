<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('notas_semanales', 'modelo_calificacion')) {
            Schema::table('notas_semanales', function (Blueprint $table) {
                $table->enum('modelo_calificacion', ['legacy', 'dinamico'])
                    ->default('legacy')
                    ->after('pesos_usados_json');
            });
        }

        if (! Schema::hasTable('notas_semanales_componentes')) {
            Schema::create('notas_semanales_componentes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('nota_semanal_id');
                $table->unsignedBigInteger('componente_calificacion_nivel_id');
                $table->decimal('nota', 4, 2)->nullable();
                $table->decimal('peso_usado', 5, 2);
                $table->string('nombre_componente_snapshot', 120);
                $table->string('codigo_componente_snapshot', 50);
                $table->unsignedSmallInteger('orden_snapshot')->default(0);
                $table->timestamps();

                $table->foreign('nota_semanal_id', 'nsc_nota_semanal_fk')
                    ->references('id')
                    ->on('notas_semanales')
                    ->cascadeOnDelete();

                $table->foreign('componente_calificacion_nivel_id', 'nsc_comp_calif_fk')
                    ->references('id')
                    ->on('componentes_calificacion_nivel')
                    ->restrictOnDelete();

                $table->unique(
                    ['nota_semanal_id', 'componente_calificacion_nivel_id'],
                    'nsc_nota_comp_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notas_semanales_componentes')) {
            Schema::drop('notas_semanales_componentes');
        }

        if (Schema::hasColumn('notas_semanales', 'modelo_calificacion')) {
            Schema::table('notas_semanales', function (Blueprint $table) {
                $table->dropColumn('modelo_calificacion');
            });
        }
    }
};
