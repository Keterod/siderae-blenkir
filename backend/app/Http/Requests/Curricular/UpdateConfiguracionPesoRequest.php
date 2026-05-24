<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfiguracionPesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nivel' => ['sometimes', 'nullable', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['sometimes', 'nullable', 'string', 'max:20'],
            'area_id' => ['sometimes', 'nullable', 'integer', 'exists:areas,id'],
            'curso_catalogo_id' => ['sometimes', 'nullable', 'integer', 'exists:cursos_catalogo,id'],
            'peso_cuaderno' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'peso_libro' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'peso_tarea' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
