<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->string('grado');
            $table->string('seccion');
            $table->enum('nivel', ['primaria', 'secundaria']);
            $table->enum('sede', ['chilca', 'auquimarca']);
            $table->string('anio_escolar');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('estudiantes');
    }
};
