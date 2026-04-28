<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->string('anio_escolar');
            $table->enum('bimestre', ['1', '2', '3', '4']);
            $table->string('curso');
            $table->decimal('nota', 4, 2);
            $table->decimal('nota_conducta', 4, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('notas');
    }
};
