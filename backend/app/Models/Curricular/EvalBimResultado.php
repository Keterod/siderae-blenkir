<?php

namespace App\Models\Curricular;

use App\Enums\Curricular\EvalBimEstadoCalculo;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'malla_curso_id',
    'periodo_academico_id',
    'sede',
    'grado',
    'seccion',
    'promedio_criterios',
    'oral',
    'promedio_eta',
    'examen_bimestral',
    'nivel_logro_numerico',
    'nivel_logro_literal',
    'conclusion_descriptiva',
    'estado_calculo',
    'detalle_json',
    'calculado_en',
])]
class EvalBimResultado extends Model
{
    protected $table = 'eval_bim_resultados';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function mallaCurso(): BelongsTo
    {
        return $this->belongsTo(MallaCurso::class, 'malla_curso_id');
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'periodo_academico_id');
    }

    protected function casts(): array
    {
        return [
            'promedio_criterios' => 'decimal:2',
            'oral' => 'decimal:2',
            'promedio_eta' => 'decimal:2',
            'examen_bimestral' => 'decimal:2',
            'nivel_logro_numerico' => 'decimal:2',
            'estado_calculo' => EvalBimEstadoCalculo::class,
            'detalle_json' => 'array',
            'calculado_en' => 'datetime',
        ];
    }
}
