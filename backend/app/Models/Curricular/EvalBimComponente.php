<?php

namespace App\Models\Curricular;

use App\Enums\Curricular\EvalBimComponenteTipo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'malla_curso_id',
    'periodo_academico_id',
    'tipo',
    'codigo',
    'nombre',
    'peso',
    'orden',
    'activo',
])]
class EvalBimComponente extends Model
{
    protected $table = 'eval_bim_componentes';

    public function mallaCurso(): BelongsTo
    {
        return $this->belongsTo(MallaCurso::class, 'malla_curso_id');
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'periodo_academico_id');
    }

    public function etaItems(): HasMany
    {
        return $this->hasMany(EvalBimEtaItem::class, 'eval_bim_componente_id');
    }

    public function notasScalar(): HasMany
    {
        return $this->hasMany(EvalBimNotaScalar::class, 'eval_bim_componente_id');
    }

    protected function casts(): array
    {
        return [
            'tipo' => EvalBimComponenteTipo::class,
            'peso' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
