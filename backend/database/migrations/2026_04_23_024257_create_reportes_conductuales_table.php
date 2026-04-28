<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reportes_conductuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('registrado_por')->constrained('users');
            $table->date('fecha');
            $table->string('tipo_conducta');
            $table->text('descripcion');
            $table->enum('nivel_gravedad', ['leve', 'moderado', 'grave']);
            $table->text('accion_inmediata')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('reportes_conductuales');
    }
};
