<?php

namespace App\Http\Requests;

use App\Models\Estudiante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAsistenciaBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'semana_inicio' => ['required', 'date'],
            'anio_escolar' => ['required', 'string', 'max:255'],
            'bimestre' => ['required', Rule::in(['1', '2', '3', '4'])],
            'sede' => ['required', Rule::in(['chilca', 'auquimarca'])],
            'nivel' => ['required', Rule::in(['primaria', 'secundaria'])],
            'grado' => ['required', 'string', 'max:255'],
            'seccion' => ['required', 'string', 'max:255'],
            'filas' => ['required', 'array', 'min:1'],
            'filas.*.estudiante_id' => ['required', 'integer', 'exists:estudiantes,id'],
            'filas.*.estado' => ['required', Rule::in(['presente', 'tardanza', 'falta'])],
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
                }
            }
        });
    }
}
