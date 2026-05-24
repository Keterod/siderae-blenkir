<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CargarPlantillaMallaRequest extends FormRequest
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
            'anio_escolar' => ['required', 'string', 'max:20'],
            'nivel' => ['required', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['required', 'string', 'max:20'],
        ];
    }
}
