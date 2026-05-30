<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anios_escolares', function (Blueprint $table) {
            $table->id();
            $table->string('anio', 20)->unique();
            $table->string('nombre');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'cerrado'])->default('inactivo');
            $table->boolean('es_activo')->default(false);
            $table->timestamps();

            $table->index('es_activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anios_escolares');
    }
};
