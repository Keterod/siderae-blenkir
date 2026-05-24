<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMallaCursoRequest extends FormRequest
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
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'curso_catalogo_id' => ['nullable', 'integer', 'exists:cursos_catalogo,id'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $tieneId = $this->filled('curso_catalogo_id');
            $tieneNombre = is_string($this->input('nombre')) && trim($this->input('nombre')) !== '';

            if ($tieneId && $tieneNombre) {
                $validator->errors()->add(
                    'curso_catalogo_id',
                    'Envíe curso_catalogo_id o nombre, no ambos.',
                );

                return;
            }

            if (! $tieneId && ! $tieneNombre) {
                $validator->errors()->add(
                    'curso_catalogo_id',
                    'Debe enviar curso_catalogo_id o nombre.',
                );
            }
        });
    }
}
