<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equivalencias_grado', function (Blueprint $table) {
            $table->id();
            $table->enum('nivel', ['primaria', 'secundaria']);
            $table->string('grado_curricular', 20);
            $table->string('grado_estudiante_legacy', 20);
            $table->timestamps();

            $table->unique(['nivel', 'grado_curricular'], 'equivalencias_grado_nivel_grado_unique');
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['nivel', 'activo']);
        });

        Schema::create('cursos_catalogo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('nombre');
            $table->boolean('es_institucional')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('descripcion', 500)->nullable();
            $table->string('codigo', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('capacidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competencia_id')->constrained('competencias')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('descripcion', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('plantillas_curriculares', function (Blueprint $table) {
            $table->id();
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->string('grado', 20);
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->boolean('detalle_completo')->default(false);
            $table->timestamps();

            $table->unique(['nivel', 'grado'], 'plantillas_nivel_grado_unique');
        });

        Schema::create('plantilla_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_curricular_id')->constrained('plantillas_curriculares')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('curso_catalogo_id')->constrained('cursos_catalogo');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('mallas_curriculares', function (Blueprint $table) {
            $table->id();
            $table->string('anio_escolar');
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->string('grado', 20);
            $table->enum('estado', ['borrador', 'activa'])->default('borrador');
            $table->foreignId('plantilla_curricular_id')->nullable()->constrained('plantillas_curriculares')->nullOnDelete();
            $table->timestamps();

            $table->unique(['anio_escolar', 'nivel', 'grado'], 'mallas_anio_nivel_grado_unique');
        });

        Schema::create('malla_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('malla_curricular_id')->constrained('mallas_curriculares')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('curso_catalogo_id')->constrained('cursos_catalogo');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('anio_escolar');
            $table->enum('bimestre', ['1', '2', '3', '4']);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->unsignedTinyInteger('semanas_planificadas')->default(4);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['anio_escolar', 'bimestre'], 'periodos_anio_bimestre_unique');
        });

        Schema::create('semanas_academicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_semana');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['periodo_academico_id', 'numero_semana'], 'semanas_periodo_numero_unique');
        });

        Schema::create('temas_semanales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('malla_curso_id')->constrained('malla_cursos');
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos');
            $table->foreignId('semana_academica_id')->constrained('semanas_academicas');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(
                ['malla_curso_id', 'periodo_academico_id', 'semana_academica_id'],
                'temas_curso_periodo_semana_unique'
            );
        });

        Schema::create('tema_competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_semanal_id')->constrained('temas_semanales')->cascadeOnDelete();
            $table->foreignId('competencia_id')->constrained('competencias');
            $table->timestamps();

            $table->unique(['tema_semanal_id', 'competencia_id'], 'tema_competencia_unique');
        });

        Schema::create('tema_capacidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_semanal_id')->constrained('temas_semanales')->cascadeOnDelete();
            $table->foreignId('competencia_id')->constrained('competencias');
            $table->foreignId('capacidad_id')->constrained('capacidades');
            $table->timestamps();

            $table->unique(
                ['tema_semanal_id', 'competencia_id', 'capacidad_id'],
                'tema_capacidad_unique'
            );
        });

        Schema::create('configuracion_pesos_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria'])->nullable();
            $table->string('grado', 20)->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('curso_catalogo_id')->nullable()->constrained('cursos_catalogo')->nullOnDelete();
            $table->decimal('peso_cuaderno', 5, 2);
            $table->decimal('peso_libro', 5, 2);
            $table->decimal('peso_tarea', 5, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('docente_curso_aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('malla_curso_id')->constrained('malla_cursos');
            $table->string('anio_escolar');
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->string('grado', 20);
            $table->string('seccion', 10);
            $table->enum('sede', ['chilca', 'auquimarca']);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(
                ['anio_escolar', 'nivel', 'grado', 'seccion', 'sede', 'malla_curso_id'],
                'docente_curso_aula_asignacion_unique'
            );
        });

        Schema::create('notas_semanales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('tema_semanal_id')->constrained('temas_semanales');
            $table->foreignId('docente_id')->constrained('users');
            $table->decimal('nota_cuaderno', 4, 2)->nullable();
            $table->decimal('nota_libro', 4, 2)->nullable();
            $table->decimal('nota_tarea', 4, 2)->nullable();
            $table->decimal('ce_calculado', 5, 2);
            $table->json('pesos_usados_json')->nullable();
            $table->date('fecha_registro');
            $table->timestamps();

            $table->unique(['estudiante_id', 'tema_semanal_id'], 'notas_semanales_estudiante_tema_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_semanales');
        Schema::dropIfExists('docente_curso_aulas');
        Schema::dropIfExists('configuracion_pesos_evaluacion');
        Schema::dropIfExists('tema_capacidades');
        Schema::dropIfExists('tema_competencias');
        Schema::dropIfExists('temas_semanales');
        Schema::dropIfExists('semanas_academicas');
        Schema::dropIfExists('periodos_academicos');
        Schema::dropIfExists('malla_cursos');
        Schema::dropIfExists('mallas_curriculares');
        Schema::dropIfExists('plantilla_cursos');
        Schema::dropIfExists('plantillas_curriculares');
        Schema::dropIfExists('capacidades');
        Schema::dropIfExists('competencias');
        Schema::dropIfExists('cursos_catalogo');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('equivalencias_grado');
    }
};
