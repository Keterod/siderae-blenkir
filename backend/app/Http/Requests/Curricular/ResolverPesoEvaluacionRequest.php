<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class ResolverPesoEvaluacionRequest extends FormRequest
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
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->query('malla_curso_id') !== null) {
            $this->merge([
                'malla_curso_id' => $this->query('malla_curso_id'),
            ]);
        }
    }
}
