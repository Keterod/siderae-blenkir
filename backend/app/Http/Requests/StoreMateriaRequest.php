<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'nivel' => ['required', Rule::in(['primaria', 'secundaria'])],
            'grado' => ['required', 'string', 'max:255'],
            'anio_escolar' => ['required', 'string', 'max:255'],
            'sede' => ['required', Rule::in(['chilca', 'auquimarca'])],
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
