<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemaSemanalRequest extends FormRequest
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
            'titulo' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
            'competencia_ids' => ['sometimes', 'array', 'min:1'],
            'competencia_ids.*' => ['integer', 'exists:competencias,id'],
            'capacidad_ids' => ['sometimes', 'array', 'min:1'],
            'capacidad_ids.*' => ['integer', 'exists:capacidades,id'],
        ];
    }
}
