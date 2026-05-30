<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes_calificacion_nivel', function (Blueprint $table) {
            $table->id();
            $table->string('anio_escolar', 10);
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->string('codigo', 50);
            $table->string('nombre', 120);
            $table->decimal('peso', 5, 2)->default(0);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->boolean('es_predefinido')->default(false);
            $table->timestamps();

            $table->unique(
                ['anio_escolar', 'nivel', 'codigo'],
                'comp_calif_nivel_anio_nivel_codigo_unique'
            );
            $table->index(
                ['anio_escolar', 'nivel', 'activo', 'orden'],
                'comp_calif_nivel_anio_nivel_act_ord_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes_calificacion_nivel');
    }
};
