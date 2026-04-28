<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->date('semana_inicio');
            $table->enum('estado', ['presente', 'tardanza', 'falta']);
            $table->string('anio_escolar');
            $table->enum('bimestre', ['1', '2', '3', '4']);
            $table->foreignId('registrado_por')->constrained('users');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('asistencias');
    }
};
