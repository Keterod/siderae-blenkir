<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['area_id', 'nombre', 'es_institucional', 'activo'])]
class CursoCatalogo extends Model
{
    protected $table = 'cursos_catalogo';

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    protected function casts(): array
    {
        return [
            'es_institucional' => 'boolean',
            'activo' => 'boolean',
        ];
    }
}
