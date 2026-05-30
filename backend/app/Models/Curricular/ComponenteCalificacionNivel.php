<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'anio_escolar',
    'nivel',
    'codigo',
    'nombre',
    'peso',
    'orden',
    'activo',
    'es_predefinido',
])]
class ComponenteCalificacionNivel extends Model
{
    public const NIVEL_INICIAL = 'inicial';

    public const NIVEL_PRIMARIA = 'primaria';

    public const NIVEL_SECUNDARIA = 'secundaria';

    protected $table = 'componentes_calificacion_nivel';

    protected function casts(): array
    {
        return [
            'peso' => 'decimal:2',
            'activo' => 'boolean',
            'es_predefinido' => 'boolean',
        ];
    }
}
