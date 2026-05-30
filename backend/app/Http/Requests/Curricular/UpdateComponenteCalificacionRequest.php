<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComponenteCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gestionar_componentes_calificacion') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:120'],
            'peso' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'orden' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
