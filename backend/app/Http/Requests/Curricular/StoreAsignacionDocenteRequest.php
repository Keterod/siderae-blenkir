<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsignacionDocenteRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'malla_curso_id' => ['required', 'integer', 'exists:malla_cursos,id'],
            'anio_escolar' => ['required', 'string', 'max:20'],
            'nivel' => ['required', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['required', 'string', 'max:20'],
            'seccion' => ['required', 'string', 'max:10'],
            'sede' => ['required', Rule::in(['chilca', 'auquimarca'])],
        ];
    }
}
