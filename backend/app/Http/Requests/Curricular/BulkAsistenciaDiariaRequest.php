<?php

namespace App\Http\Requests\Curricular;

use App\Http\Requests\Curricular\Concerns\ValidatesAsistenciaDiariaContexto;
use App\Models\Curricular\AsistenciaDiaria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkAsistenciaDiariaRequest extends FormRequest
{
    use ValidatesAsistenciaDiariaContexto;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->reglasContextoAula(), [
            'fecha' => ['required', 'date'],
            'filas' => ['required', 'array', 'min:1'],
            'filas.*.estudiante_id' => ['required', 'integer', 'exists:estudiantes,id'],
            'filas.*.estado' => ['required', 'string', Rule::in(AsistenciaDiaria::ESTADOS)],
            'filas.*.observacion' => ['nullable', 'string', 'max:500'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validarGradoContexto($validator);

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $filasRaw = $this->input('filas', []);
            if (! is_array($filasRaw)) {
                return;
            }

            $ids = collect($filasRaw)->pluck('estudiante_id');
            if ($ids->count() !== $ids->unique()->count()) {
                $validator->errors()->add('filas', 'Hay estudiantes duplicados en el lote.');
            }

            $this->validarEstudiantesPertenecenAlAula($validator, $filasRaw);
        });
    }
}
