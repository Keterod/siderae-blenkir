<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_diarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->string('anio_escolar');
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->string('grado', 20);
            $table->string('seccion', 10);
            $table->enum('sede', ['chilca', 'auquimarca']);
            $table->date('fecha');
            $table->enum('estado', ['presente', 'tarde', 'falta', 'justificado']);
            $table->text('observacion')->nullable();
            $table->foreignId('registrado_por')->constrained('users');
            $table->timestamps();

            $table->unique(
                ['estudiante_id', 'anio_escolar', 'nivel', 'grado', 'seccion', 'sede', 'fecha'],
                'asistencia_diaria_estudiante_contexto_fecha_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_diarias');
    }
};
