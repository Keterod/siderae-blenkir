<?php

namespace App\Http\Requests\Curricular;

use App\Models\Curricular\CursoCatalogo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMallaCursoRequest extends FormRequest
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
            'curso_catalogo_id' => ['required', 'integer', 'exists:cursos_catalogo,id'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var \App\Models\Curricular\MallaCurso $mallaCurso */
            $mallaCurso = $this->route('mallaCurso');
            $cursoCatalogoId = (int) $this->input('curso_catalogo_id');

            $perteneceAlArea = CursoCatalogo::query()
                ->where('id', $cursoCatalogoId)
                ->where('area_id', $mallaCurso->area_id)
                ->exists();

            if (! $perteneceAlArea) {
                $validator->errors()->add('curso_catalogo_id', 'El curso debe pertenecer al área del registro.');
            }
        });
    }
}
