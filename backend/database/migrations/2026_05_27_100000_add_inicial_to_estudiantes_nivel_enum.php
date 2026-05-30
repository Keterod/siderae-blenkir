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
            DB::statement("ALTER TABLE estudiantes MODIFY COLUMN nivel ENUM('inicial', 'primaria', 'secundaria') NOT NULL");

            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::drop('estudiantes');

        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->string('grado');
            $table->string('seccion');
            $table->enum('nivel', ['inicial', 'primaria', 'secundaria']);
            $table->enum('sede', ['chilca', 'auquimarca']);
            $table->string('anio_escolar');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE estudiantes MODIFY COLUMN nivel ENUM('primaria', 'secundaria') NOT NULL");

            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::drop('estudiantes');

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

        Schema::enableForeignKeyConstraints();
    }
};
