<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'nivel' => ['sometimes', 'required', Rule::in(['primaria', 'secundaria'])],
            'grado' => ['sometimes', 'required', 'string', 'max:255'],
            'anio_escolar' => ['sometimes', 'required', 'string', 'max:255'],
            'sede' => ['sometimes', 'required', Rule::in(['chilca', 'auquimarca'])],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('activo') && ! is_bool($this->input('activo'))) {
            $this->merge([
                'activo' => filter_var($this->input('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }
}
