<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeccionAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gestionar_secciones_aulas') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:60'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
