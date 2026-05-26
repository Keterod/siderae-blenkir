<?php

namespace App\Models\Curricular;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'eval_bim_componente_id',
    'nota',
    'docente_id',
])]
class EvalBimNotaScalar extends Model
{
    protected $table = 'eval_bim_notas_scalar';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(EvalBimComponente::class, 'eval_bim_componente_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    protected function casts(): array
    {
        return [
            'nota' => 'decimal:2',
        ];
    }
}
