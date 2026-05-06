<?php

namespace App\Http\Requests;

use App\Models\Estudiante;
use App\Models\Materia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anio_escolar' => ['required', 'string', 'max:255'],
            'bimestre' => ['required', Rule::in(['1', '2', '3', '4'])],
            'materia_id' => ['nullable', 'integer', Rule::exists('materias', 'id')->where('activo', true)],
            'curso' => ['nullable', 'string', 'max:255', 'required_without:materia_id'],
            'nota' => ['required', 'numeric', 'between:0,20'],
            'nota_conducta' => ['nullable', 'numeric', 'between:0,20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('materia_id')) {
                return;
            }

            $estudiante = $this->route('estudiante');
            if (! $estudiante instanceof Estudiante) {
                return;
            }

            $mat = Materia::query()->whereKey((int) $this->input('materia_id'))->first();
            if ($mat === null) {
                return;
            }

            if ($mat->nivel !== $estudiante->nivel ||
                $mat->grado !== $estudiante->grado ||
                $mat->anio_escolar !== $estudiante->anio_escolar ||
                $mat->sede !== $estudiante->sede) {
                $validator->errors()->add(
                    'materia_id',
                    'La materia no coincide con nivel, grado, año escolar o sede del estudiante.'
                );
            }
        });
    }
}
