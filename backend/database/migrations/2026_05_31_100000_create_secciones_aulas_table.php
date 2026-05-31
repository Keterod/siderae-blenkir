<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secciones_aulas', function (Blueprint $table) {
            $table->id();
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->string('grado', 20);
            $table->string('nombre', 120);
            $table->string('codigo', 60)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['nivel', 'grado', 'codigo'], 'sec_aulas_niv_grado_cod_uq');
            $table->index(['nivel', 'grado', 'nombre'], 'sec_aulas_niv_grado_nom_idx');
            $table->index(['nivel', 'grado', 'activo', 'orden'], 'sec_aulas_niv_grado_act_ord_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secciones_aulas');
    }
};
