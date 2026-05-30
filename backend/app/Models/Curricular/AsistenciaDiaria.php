<?php

namespace App\Models\Curricular;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'anio_escolar',
    'nivel',
    'grado',
    'seccion',
    'sede',
    'fecha',
    'estado',
    'observacion',
    'registrado_por',
])]
class AsistenciaDiaria extends Model
{
    public const ESTADOS = ['presente', 'tarde', 'falta', 'justificado'];

    protected $table = 'asistencias_diarias';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
