<?php

namespace App\Models\Curricular;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'eval_bim_componente_id',
    'nombre',
    'peso_interno',
    'orden',
    'activo',
])]
class EvalBimEtaItem extends Model
{
    protected $table = 'eval_bim_eta_items';

    public function componente(): BelongsTo
    {
        return $this->belongsTo(EvalBimComponente::class, 'eval_bim_componente_id');
    }

    public function notasEta(): HasMany
    {
        return $this->hasMany(EvalBimNotaEta::class, 'eval_bim_eta_item_id');
    }

    protected function casts(): array
    {
        return [
            'peso_interno' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
