<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nivel', 'grado_curricular', 'grado_estudiante_legacy'])]
class EquivalenciaGrado extends Model
{
    protected $table = 'equivalencias_grado';
}
