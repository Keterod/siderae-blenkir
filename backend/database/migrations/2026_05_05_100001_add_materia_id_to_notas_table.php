<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->foreignId('materia_id')
                ->nullable()
                ->after('estudiante_id')
                ->constrained('materias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('materia_id');
        });
    }
};
