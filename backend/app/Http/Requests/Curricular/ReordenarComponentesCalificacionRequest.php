<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReordenarComponentesCalificacionRequest extends FormRequest
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
            'ordenes' => ['required', 'array', 'min:1'],
            'ordenes.*.id' => ['required', 'integer', 'exists:componentes_calificacion_nivel,id'],
            'ordenes.*.orden' => ['sometimes', 'integer', 'min:0'],
            'ordenes.*.peso' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'ordenes.*.activo' => ['sometimes', 'boolean'],
        ];
    }
}
