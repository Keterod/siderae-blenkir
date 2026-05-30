<?php

namespace App\Http\Requests\Curricular\Concerns;

use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

trait ValidatesAsistenciaDiariaContexto
{
    /**
     * @return array<string, mixed>
     */
    protected function reglasContextoAula(): array
    {
        return [
            'anio_escolar' => ['required', 'string', 'max:255'],
            'nivel' => ['required', 'string', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'sede' => ['required', 'string', Rule::in(['chilca', 'auquimarca'])],
            'grado' => ['required', 'string', 'max:20'],
            'seccion' => ['required', 'string', 'max:10'],
        ];
    }

    protected function validarGradoContexto(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $nivel = (string) $this->input('nivel');
        $grado = (string) $this->input('grado');

        if (! CatalogoNivelGrado::esGradoEstudianteValido($nivel, $grado)) {
            $validator->errors()->add(
                'grado',
                'El grado no es válido para el nivel indicado.'
            );
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $filas
     */
    protected function validarEstudiantesPertenecenAlAula(Validator $validator, array $filas): void
    {
        if ($filas === []) {
            return;
        }

        $estudianteIds = collect($filas)->pluck('estudiante_id')->map(fn ($id) => (int) $id)->all();
        $estudiantes = Estudiante::query()->whereIn('id', $estudianteIds)->get()->keyBy('id');

        foreach ($filas as $idx => $fila) {
            $eid = (int) ($fila['estudiante_id'] ?? 0);
            $est = $estudiantes->get($eid);

            if ($est === null) {
                continue;
            }

            if (! $est->activo) {
                $validator->errors()->add(
                    "filas.{$idx}.estudiante_id",
                    'El estudiante está inactivo.'
                );

                continue;
            }

            if (
                $est->sede !== $this->input('sede')
                || $est->nivel !== $this->input('nivel')
                || $est->grado !== $this->input('grado')
                || $est->seccion !== $this->input('seccion')
                || $est->anio_escolar !== $this->input('anio_escolar')
            ) {
                $validator->errors()->add(
                    "filas.{$idx}.estudiante_id",
                    'El estudiante no pertenece al contexto académico seleccionado.'
                );
            }
        }
    }
}
