<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class ReactivarComponenteCalificacionRequest extends FormRequest
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
            'peso' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ];
    }
}
