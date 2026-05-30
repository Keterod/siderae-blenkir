<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE equivalencias_grado MODIFY COLUMN nivel ENUM('inicial', 'primaria', 'secundaria') NOT NULL");

            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::drop('equivalencias_grado');

        Schema::create('equivalencias_grado', function (Blueprint $table) {
            $table->id();
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->string('grado_curricular', 20);
            $table->string('grado_estudiante_legacy', 20);
            $table->timestamps();

            $table->unique(['nivel', 'grado_curricular'], 'equivalencias_grado_nivel_grado_unique');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE equivalencias_grado MODIFY COLUMN nivel ENUM('primaria', 'secundaria') NOT NULL");

            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::drop('equivalencias_grado');

        Schema::create('equivalencias_grado', function (Blueprint $table) {
            $table->id();
            $table->enum('nivel', ['primaria', 'secundaria']);
            $table->string('grado_curricular', 20);
            $table->string('grado_estudiante_legacy', 20);
            $table->timestamps();

            $table->unique(['nivel', 'grado_curricular'], 'equivalencias_grado_nivel_grado_unique');
        });

        Schema::enableForeignKeyConstraints();
    }
};
