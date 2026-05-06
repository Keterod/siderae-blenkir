<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('nivel', ['primaria', 'secundaria']);
            $table->string('grado');
            $table->string('anio_escolar');
            $table->enum('sede', ['chilca', 'auquimarca']);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['nombre', 'nivel', 'grado', 'anio_escolar', 'sede'], 'materias_unicidad_catalogo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
