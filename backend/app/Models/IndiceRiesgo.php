<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'indice',
    'nivel',
    'anio_escolar',
    'bimestre',
    'variables_utilizadas',
    'modelos_scores',
])]
class IndiceRiesgo extends Model
{
    protected $table = 'indices_riesgo';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    protected function casts(): array
    {
        return [
            'indice' => 'decimal:4',
            'variables_utilizadas' => 'array',
            'modelos_scores' => 'array',
        ];
    }

    public static function clasificarNivelDesdeIndice(float $indice): string
    {
        if ($indice >= 0.70) {
            return 'Alto';
        }

        if ($indice >= 0.40) {
            return 'Medio';
        }

        return 'Bajo';
    }
}
