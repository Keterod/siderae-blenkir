<?php

namespace App\Models;

use Database\Factories\EstudianteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'codigo',
    'nombres',
    'apellidos',
    'fecha_nacimiento',
    'sexo',
    'grado',
    'seccion',
    'nivel',
    'sede',
    'anio_escolar',
    'activo',
])]
class Estudiante extends Model
{
    /** @use HasFactory<EstudianteFactory> */
    use HasFactory;

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function asistenciasDiarias(): HasMany
    {
        return $this->hasMany(\App\Models\Curricular\AsistenciaDiaria::class);
    }

    public function variablesSocioeconomicas(): HasMany
    {
        return $this->hasMany(VariableSocioeconomica::class);
    }

    public function indicesRiesgo(): HasMany
    {
        return $this->hasMany(IndiceRiesgo::class);
    }

    public function reportesConductuales(): HasMany
    {
        return $this->hasMany(ReporteConductual::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
        ];
    }
}
