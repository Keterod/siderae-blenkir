<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeccionAulaRequest extends FormRequest
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
            'nivel' => ['required', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:60'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
