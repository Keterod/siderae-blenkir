<?php

namespace App\Models\Curricular\Concerns;

/**
 * Sincroniza activo_unique_key con activo para índices únicos compatibles con MySQL 8.
 *
 * - activo=true  → activo_unique_key=1 (solo una fila activa por combinación en BD)
 * - activo=false → activo_unique_key=null (historial: varias filas inactivas permitidas)
 *
 * No incluir activo_unique_key en fillable; se asigna en saving().
 */
trait SyncActivoUniqueKey
{
    public static function bootSyncActivoUniqueKey(): void
    {
        static::saving(function (self $model): void {
            $model->activo_unique_key = $model->activo ? 1 : null;
        });
    }
}
