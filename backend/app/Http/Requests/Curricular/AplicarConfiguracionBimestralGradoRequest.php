<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AplicarConfiguracionBimestralGradoRequest extends FormRequest
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
            'anio_escolar' => ['required', 'string', 'max:20'],
            'nivel' => ['required', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['required', 'string', 'max:20'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'plantilla' => ['required', 'array'],
            'plantilla.componentes' => ['required', 'array', 'min:1'],
            'plantilla.componentes.*.codigo' => ['required', 'string', 'max:50'],
            'plantilla.componentes.*.nombre' => ['required', 'string', 'max:255'],
            'plantilla.componentes.*.peso' => ['required', 'numeric', 'min:0', 'max:100'],
            'plantilla.componentes.*.activo' => ['required', 'boolean'],
            'plantilla.componentes.*.orden' => ['required', 'integer', 'min:0'],
            'plantilla.etas' => ['required', 'array'],
            'plantilla.etas.*.nombre' => ['required', 'string', 'max:255'],
            'plantilla.etas.*.peso_interno' => ['required', 'numeric', 'min:0', 'max:100'],
            'plantilla.etas.*.activo' => ['required', 'boolean'],
            'plantilla.etas.*.orden' => ['required', 'integer', 'min:0'],
        ];
    }
}
