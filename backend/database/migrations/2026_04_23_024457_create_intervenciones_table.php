<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('intervenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alerta_id')->constrained('alertas')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('registrado_por')->constrained('users');
            $table->enum('tipo', ['academica', 'emocional', 'familiar']);
            $table->text('descripcion');
            $table->date('fecha');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('intervenciones');
    }
};
