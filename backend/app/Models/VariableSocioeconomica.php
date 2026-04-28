<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'composicion_familiar',
    'nivel_socioeconomico',
    'acceso_internet',
    'distancia_colegio_km',
    'anio_escolar',
])]
class VariableSocioeconomica extends Model
{
    protected $table = 'variables_socioeconomicas';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    protected function casts(): array
    {
        return [
            'acceso_internet' => 'boolean',
            'distancia_colegio_km' => 'decimal:2',
        ];
    }
}
