<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class BulkEvaluacionBimestralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registrar_notas_semanales') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'asignacion_docente_id' => ['required', 'integer', 'exists:docente_curso_aulas,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'registros_por_estudiante' => ['required', 'array', 'min:1'],
            'registros_por_estudiante.*.estudiante_id' => ['required', 'integer', 'exists:estudiantes,id'],
            'registros_por_estudiante.*.oral' => ['nullable', 'numeric'],
            'registros_por_estudiante.*.examen_bimestral' => ['nullable', 'numeric'],
            'registros_por_estudiante.*.componentes_personalizados' => ['nullable', 'array'],
            'registros_por_estudiante.*.componentes_personalizados.*.componente_id' => ['required', 'integer'],
            'registros_por_estudiante.*.componentes_personalizados.*.nota' => ['nullable', 'numeric'],
            'registros_por_estudiante.*.etas' => ['nullable', 'array'],
            'registros_por_estudiante.*.etas.*.eta_item_id' => ['required', 'integer'],
            'registros_por_estudiante.*.etas.*.nota' => ['nullable', 'numeric'],
            'registros_por_estudiante.*.conclusion_descriptiva' => ['nullable', 'string'],
        ];
    }
}
