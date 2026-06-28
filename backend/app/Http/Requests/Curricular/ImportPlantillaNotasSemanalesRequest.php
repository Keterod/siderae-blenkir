<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportPlantillaNotasSemanalesRequest extends FormRequest
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
            'archivo' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
