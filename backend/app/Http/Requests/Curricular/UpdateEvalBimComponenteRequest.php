<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvalBimComponenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('configurar_evaluacion_bimestral') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'peso' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'activo' => ['sometimes', 'boolean'],
            'orden' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
