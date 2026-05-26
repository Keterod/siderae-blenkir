<?php

namespace App\Models\Curricular;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'estudiante_id',
    'eval_bim_eta_item_id',
    'nota',
    'docente_id',
])]
class EvalBimNotaEta extends Model
{
    protected $table = 'eval_bim_notas_eta';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function etaItem(): BelongsTo
    {
        return $this->belongsTo(EvalBimEtaItem::class, 'eval_bim_eta_item_id');
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
