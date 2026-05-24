<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemaSemanalRequest extends FormRequest
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
            'malla_curso_id' => ['required', 'integer', 'exists:malla_cursos,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'semana_academica_id' => ['nullable', 'integer', 'exists:semanas_academicas,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'competencia_ids' => ['required', 'array', 'min:1'],
            'competencia_ids.*' => ['integer', 'exists:competencias,id'],
            'capacidad_ids' => ['required', 'array', 'min:1'],
            'capacidad_ids.*' => ['integer', 'exists:capacidades,id'],
        ];
    }
}
