<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'nivel', 'activo'])]
class Area extends Model
{
    protected $table = 'areas';

    public function cursosCatalogo(): HasMany
    {
        return $this->hasMany(CursoCatalogo::class, 'area_id');
    }

    public function competencias(): HasMany
    {
        return $this->hasMany(Competencia::class, 'area_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
