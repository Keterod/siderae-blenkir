<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('variables_socioeconomicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->enum('composicion_familiar', ['nuclear', 'monoparental', 'extendida', 'otros']);
            $table->enum('nivel_socioeconomico', ['bajo', 'medio', 'alto']);
            $table->boolean('acceso_internet')->default(false);
            $table->decimal('distancia_colegio_km', 5, 2)->nullable();
            $table->string('anio_escolar');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('variables_socioeconomicas');
    }
};
