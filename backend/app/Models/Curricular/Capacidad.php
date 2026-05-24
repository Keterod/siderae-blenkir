<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['competencia_id', 'nombre', 'descripcion', 'activo'])]
class Capacidad extends Model
{
    protected $table = 'capacidades';

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'competencia_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
