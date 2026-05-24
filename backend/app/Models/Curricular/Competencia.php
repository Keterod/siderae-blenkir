<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['area_id', 'nombre', 'descripcion', 'codigo', 'activo'])]
class Competencia extends Model
{
    protected $table = 'competencias';

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function capacidades(): HasMany
    {
        return $this->hasMany(Capacidad::class, 'competencia_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
