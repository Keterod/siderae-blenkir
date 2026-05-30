<?php

namespace App\Http\Requests\Concerns;

use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesEstudianteNivelGrado
{
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Estudiante|null $estudiante */
            $estudiante = $this->route('estudiante');

            $nivel = (string) ($this->input('nivel') ?? $estudiante?->nivel ?? '');
            $grado = (string) ($this->input('grado') ?? $estudiante?->grado ?? '');

            if ($nivel === '' || $grado === '') {
                return;
            }

            $debeValidar = $this->isMethod('POST')
                || $this->has('nivel')
                || $this->has('grado');

            if (! $debeValidar) {
                return;
            }

            if (! CatalogoNivelGrado::esGradoEstudianteValido($nivel, $grado)) {
                $validator->errors()->add(
                    'grado',
                    'El grado no es válido para el nivel indicado.'
                );
            }
        });
    }
}
