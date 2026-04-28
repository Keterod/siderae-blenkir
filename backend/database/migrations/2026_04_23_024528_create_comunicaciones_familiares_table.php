<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comunicaciones_familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('alerta_id')->nullable()->constrained('alertas');
            $table->foreignId('registrado_por')->constrained('users');
            $table->enum('tipo', ['presencial', 'virtual', 'telefonica']);
            $table->date('fecha');
            $table->string('participantes');
            $table->text('resumen_acuerdos');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('comunicaciones_familiares');
    }
};
