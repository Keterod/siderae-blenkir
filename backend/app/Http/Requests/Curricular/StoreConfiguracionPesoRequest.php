<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConfiguracionPesoRequest extends FormRequest
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
            'nivel' => ['nullable', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['nullable', 'string', 'max:20'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'curso_catalogo_id' => ['nullable', 'integer', 'exists:cursos_catalogo,id'],
            'peso_cuaderno' => ['required', 'numeric', 'min:0', 'max:100'],
            'peso_libro' => ['required', 'numeric', 'min:0', 'max:100'],
            'peso_tarea' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
