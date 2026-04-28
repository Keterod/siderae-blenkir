<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('indice_riesgo_id')->constrained('indices_riesgo');
            $table->enum('estado', ['pendiente', 'en_atencion', 'cerrada'])->default('pendiente');
            $table->json('factores_influyentes')->nullable();
            $table->text('recomendacion')->nullable();
            $table->text('resultado_cierre')->nullable();
            $table->foreignId('cerrada_por')->nullable()->constrained('users');
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('alertas');
    }
};
