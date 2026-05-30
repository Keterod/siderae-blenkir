<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComponenteCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gestionar_componentes_calificacion') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'anio_escolar' => ['required', 'string', 'max:10'],
            'nivel' => ['required', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'peso' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
