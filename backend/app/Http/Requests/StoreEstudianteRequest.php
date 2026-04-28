<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:255', 'unique:estudiantes,codigo'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', Rule::in(['M', 'F'])],
            'grado' => ['required', 'string', 'max:255'],
            'seccion' => ['required', 'string', 'max:255'],
            'nivel' => ['required', Rule::in(['primaria', 'secundaria'])],
            'sede' => ['required', Rule::in(['chilca', 'auquimarca'])],
            'anio_escolar' => ['required', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
