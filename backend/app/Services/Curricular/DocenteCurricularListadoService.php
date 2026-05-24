<?php

namespace App\Services\Curricular;

use App\Models\Curricular\DocenteCursoAula;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DocenteCurricularListadoService
{
    /**
     * @return Collection<int, array{id: int, name: string, email: string, asignaciones_activas_count: int}>
     */
    public function listarDocentes(?string $search, ?string $anioEscolar, ?string $nivel, ?string $sede): Collection
    {
        $query = User::role('docente')
            ->select(['id', 'name', 'email'])
            ->orderBy('name');

        if ($search !== null && trim($search) !== '') {
            $termino = '%'.trim($search).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('name', 'like', $termino)
                    ->orWhere('email', 'like', $termino);
            });
        }

        $docentes = $query->get();

        if ($docentes->isEmpty()) {
            return collect();
        }

        $conteos = DocenteCursoAula::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->where('activo', true)
            ->when($anioEscolar, fn (Builder $q) => $q->where('anio_escolar', $anioEscolar))
            ->when($nivel, fn (Builder $q) => $q->where('nivel', $nivel))
            ->when($sede, fn (Builder $q) => $q->where('sede', $sede))
            ->whereIn('user_id', $docentes->pluck('id'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        return $docentes->map(fn (User $docente): array => [
            'id' => $docente->id,
            'name' => $docente->name,
            'email' => $docente->email,
            'asignaciones_activas_count' => (int) ($conteos[$docente->id] ?? 0),
        ]);
    }
}
