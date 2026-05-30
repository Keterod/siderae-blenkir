<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas_semanales', function (Blueprint $table) {
            $table->enum('modelo_calificacion', ['legacy', 'dinamico'])
                ->default('legacy')
                ->after('pesos_usados_json');
        });

        Schema::create('notas_semanales_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_semanal_id')->constrained('notas_semanales')->cascadeOnDelete();
            $table->foreignId('componente_calificacion_nivel_id')
                ->constrained('componentes_calificacion_nivel')
                ->restrictOnDelete();
            $table->decimal('nota', 4, 2)->nullable();
            $table->decimal('peso_usado', 5, 2);
            $table->string('nombre_componente_snapshot', 120);
            $table->string('codigo_componente_snapshot', 50);
            $table->unsignedSmallInteger('orden_snapshot')->default(0);
            $table->timestamps();

            $table->unique(
                ['nota_semanal_id', 'componente_calificacion_nivel_id'],
                'notas_sem_comp_nota_componente_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_semanales_componentes');

        Schema::table('notas_semanales', function (Blueprint $table) {
            $table->dropColumn('modelo_calificacion');
        });
    }
};
