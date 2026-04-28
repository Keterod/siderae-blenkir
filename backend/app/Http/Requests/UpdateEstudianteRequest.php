<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $estudiante = $this->route('estudiante');

        return [
            'codigo' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('estudiantes', 'codigo')->ignore($estudiante->getKey()),
            ],
            'nombres' => ['sometimes', 'required', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'required', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', Rule::in(['M', 'F'])],
            'grado' => ['sometimes', 'required', 'string', 'max:255'],
            'seccion' => ['sometimes', 'required', 'string', 'max:255'],
            'nivel' => ['sometimes', 'required', Rule::in(['primaria', 'secundaria'])],
            'sede' => ['sometimes', 'required', Rule::in(['chilca', 'auquimarca'])],
            'anio_escolar' => ['sometimes', 'required', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
