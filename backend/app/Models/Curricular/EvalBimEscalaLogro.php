<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'codigo_literal',
    'etiqueta',
    'orden',
    'nota_min',
    'nota_max',
    'activo',
])]
class EvalBimEscalaLogro extends Model
{
    protected $table = 'eval_bim_escala_logro';

    protected function casts(): array
    {
        return [
            'nota_min' => 'decimal:2',
            'nota_max' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
