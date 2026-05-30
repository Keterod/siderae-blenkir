<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nota_semanal_id',
    'componente_calificacion_nivel_id',
    'nota',
    'peso_usado',
    'nombre_componente_snapshot',
    'codigo_componente_snapshot',
    'orden_snapshot',
])]
class NotaSemanalComponente extends Model
{
    protected $table = 'notas_semanales_componentes';

    public function notaSemanal(): BelongsTo
    {
        return $this->belongsTo(NotaSemanal::class, 'nota_semanal_id');
    }

    public function componenteCalificacionNivel(): BelongsTo
    {
        return $this->belongsTo(ComponenteCalificacionNivel::class, 'componente_calificacion_nivel_id');
    }

    protected function casts(): array
    {
        return [
            'nota' => 'decimal:2',
            'peso_usado' => 'decimal:2',
        ];
    }
}
