<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nivel',
    'grado',
    'nombre',
    'codigo',
    'orden',
    'activo',
])]
class SeccionAula extends Model
{
    public const NIVEL_INICIAL = 'inicial';

    public const NIVEL_PRIMARIA = 'primaria';

    public const NIVEL_SECUNDARIA = 'secundaria';

    protected $table = 'secciones_aulas';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }
}
