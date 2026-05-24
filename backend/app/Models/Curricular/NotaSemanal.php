<?php

namespace App\Models\Curricular;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'tema_semanal_id',
    'docente_id',
    'nota_cuaderno',
    'nota_libro',
    'nota_tarea',
    'ce_calculado',
    'pesos_usados_json',
    'fecha_registro',
])]
class NotaSemanal extends Model
{
    protected $table = 'notas_semanales';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function temaSemanal(): BelongsTo
    {
        return $this->belongsTo(TemaSemanal::class, 'tema_semanal_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    protected function casts(): array
    {
        return [
            'nota_cuaderno' => 'decimal:2',
            'nota_libro' => 'decimal:2',
            'nota_tarea' => 'decimal:2',
            'ce_calculado' => 'decimal:2',
            'pesos_usados_json' => 'array',
            'fecha_registro' => 'date',
        ];
    }
}
