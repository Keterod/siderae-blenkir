<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eval_bim_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('malla_curso_id')->constrained('malla_cursos')->cascadeOnDelete();
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->enum('tipo', [
                'promedio_criterios',
                'oral',
                'promedio_eta',
                'examen_bimestral',
                'personalizado',
            ]);
            $table->string('codigo', 50);
            $table->string('nombre');
            $table->decimal('peso', 5, 2);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(
                ['malla_curso_id', 'periodo_academico_id', 'codigo'],
                'eval_bim_componentes_curso_periodo_codigo_unique'
            );
            $table->index(['malla_curso_id', 'periodo_academico_id', 'activo'], 'eval_bim_comp_curso_periodo_act_idx');
        });

        Schema::create('eval_bim_eta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eval_bim_componente_id')
                ->constrained('eval_bim_componentes')
                ->cascadeOnDelete();
            $table->string('nombre');
            $table->decimal('peso_interno', 5, 2);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['eval_bim_componente_id', 'activo'], 'eval_bim_eta_comp_activo_idx');
        });

        Schema::create('eval_bim_notas_scalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('eval_bim_componente_id')
                ->constrained('eval_bim_componentes')
                ->cascadeOnDelete();
            $table->decimal('nota', 4, 2)->nullable();
            $table->foreignId('docente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['estudiante_id', 'eval_bim_componente_id'],
                'eval_bim_notas_scalar_estudiante_componente_unique'
            );
        });

        Schema::create('eval_bim_notas_eta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('eval_bim_eta_item_id')
                ->constrained('eval_bim_eta_items')
                ->cascadeOnDelete();
            $table->decimal('nota', 4, 2)->nullable();
            $table->foreignId('docente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['estudiante_id', 'eval_bim_eta_item_id'],
                'eval_bim_notas_eta_estudiante_eta_unique'
            );
        });

        Schema::create('eval_bim_resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('malla_curso_id')->constrained('malla_cursos')->cascadeOnDelete();
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->enum('sede', ['chilca', 'auquimarca']);
            $table->string('grado', 20);
            $table->string('seccion', 10);
            $table->decimal('promedio_criterios', 5, 2)->nullable();
            $table->decimal('oral', 5, 2)->nullable();
            $table->decimal('promedio_eta', 5, 2)->nullable();
            $table->decimal('examen_bimestral', 5, 2)->nullable();
            $table->decimal('nivel_logro_numerico', 5, 2)->nullable();
            $table->string('nivel_logro_literal', 5)->nullable();
            $table->text('conclusion_descriptiva')->nullable();
            $table->enum('estado_calculo', ['completo', 'pendiente']);
            $table->json('detalle_json')->nullable();
            $table->timestamp('calculado_en')->nullable();
            $table->timestamps();

            $table->unique(
                ['estudiante_id', 'malla_curso_id', 'periodo_academico_id', 'sede', 'grado', 'seccion'],
                'eval_bim_resultados_aula_unique'
            );
        });

        Schema::create('eval_bim_escala_logro', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_literal', 5);
            $table->string('etiqueta')->nullable();
            $table->unsignedTinyInteger('orden');
            $table->decimal('nota_min', 5, 2);
            $table->decimal('nota_max', 5, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('codigo_literal', 'eval_bim_escala_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eval_bim_escala_logro');
        Schema::dropIfExists('eval_bim_resultados');
        Schema::dropIfExists('eval_bim_notas_eta');
        Schema::dropIfExists('eval_bim_notas_scalar');
        Schema::dropIfExists('eval_bim_eta_items');
        Schema::dropIfExists('eval_bim_componentes');
    }
};
