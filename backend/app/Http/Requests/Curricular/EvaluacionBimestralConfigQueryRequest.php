<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class EvaluacionBimestralConfigQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ver_notas_academicas')
            || $this->user()?->can('configurar_evaluacion_bimestral');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'malla_curso_id' => ['required', 'integer', 'exists:malla_cursos,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
        ];
    }
}
