<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nivel', 'grado', 'nombre', 'activo', 'detalle_completo'])]
class PlantillaCurricular extends Model
{
    protected $table = 'plantillas_curriculares';

    public function plantillaCursos(): HasMany
    {
        return $this->hasMany(PlantillaCurso::class, 'plantilla_curricular_id');
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'detalle_completo' => 'boolean',
        ];
    }
}
