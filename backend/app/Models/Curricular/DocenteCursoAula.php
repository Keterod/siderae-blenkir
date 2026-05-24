<?php

namespace App\Models\Curricular;

use App\Models\Curricular\Concerns\SyncActivoUniqueKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'malla_curso_id',
    'anio_escolar',
    'nivel',
    'grado',
    'seccion',
    'sede',
    'activo',
])]
class DocenteCursoAula extends Model
{
    use SyncActivoUniqueKey;

    protected $table = 'docente_curso_aulas';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mallaCurso(): BelongsTo
    {
        return $this->belongsTo(MallaCurso::class, 'malla_curso_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
