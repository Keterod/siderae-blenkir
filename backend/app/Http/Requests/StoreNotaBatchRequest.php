<?php

namespace App\Http\Requests;

use App\Models\Estudiante;
use App\Models\Materia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreNotaBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'materia_id' => ['required', 'integer', Rule::exists('materias', 'id')->where('activo', true)],
            'anio_escolar' => ['required', 'string', 'max:255'],
            'bimestre' => ['required', Rule::in(['1', '2', '3', '4'])],
            'sede' => ['required', Rule::in(['chilca', 'auquimarca'])],
            'nivel' => ['required', Rule::in(['primaria', 'secundaria'])],
            'grado' => ['required', 'string', 'max:255'],
            'seccion' => ['required', 'string', 'max:255'],
            'filas' => ['required', 'array', 'min:1'],
            'filas.*.estudiante_id' => ['required', 'integer', 'exists:estudiantes,id'],
            'filas.*.nota' => ['required', 'numeric', 'between:0,20'],
            'filas.*.nota_conducta' => ['nullable', 'numeric', 'between:0,20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $filasRaw = $this->input('filas', []);
            if (! is_array($filasRaw)) {
                return;
            }
            $idsDup = collect($filasRaw)->pluck('estudiante_id');
            if ($idsDup->count() !== $idsDup->unique()->count()) {
                $validator->errors()->add('filas', 'Hay estudiantes duplicados en el lote.');
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Materia|null $materia */
            $materia = Materia::query()->whereKey((int) $this->input('materia_id'))->first();
            if ($materia === null) {
                return;
            }

            if ($materia->sede !== $this->input('sede') ||
                $materia->nivel !== $this->input('nivel') ||
                $materia->grado !== $this->input('grado') ||
                $materia->anio_escolar !== $this->input('anio_escolar')) {
                $validator->errors()->add(
                    'materia_id',
                    'La materia no coincide con sede, nivel, grado o año escolar seleccionados.'
                );
            }

            $filas = $this->input('filas', []);
            $estudianteIds = collect($filas)->pluck('estudiante_id')->all();
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

                if ($est->sede !== $this->input('sede') ||
                    $est->nivel !== $this->input('nivel') ||
                    $est->grado !== $this->input('grado') ||
                    $est->seccion !== $this->input('seccion') ||
                    $est->anio_escolar !== $this->input('anio_escolar')) {
                    $validator->errors()->add(
                        "filas.{$idx}.estudiante_id",
                        'El estudiante no pertenece al contexto académico seleccionado.'
                    );
                    continue;
                }

                if ($materia->nivel !== $est->nivel ||
                    $materia->grado !== $est->grado ||
                    $materia->anio_escolar !== $est->anio_escolar ||
                    $materia->sede !== $est->sede) {
                    $validator->errors()->add(
                        "filas.{$idx}.estudiante_id",
                        'La materia no corresponde al estudiante.'
                    );
                }
            }
        });
    }
}
