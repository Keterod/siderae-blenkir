<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('indices_riesgo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->decimal('indice', 5, 4);
            $table->enum('nivel', ['Alto', 'Medio', 'Bajo']);
            $table->string('anio_escolar');
            $table->enum('bimestre', ['1', '2', '3', '4']);
            $table->json('variables_utilizadas')->nullable();
            $table->json('modelos_scores')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('indices_riesgo');
    }
};
