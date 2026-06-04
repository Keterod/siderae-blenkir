<?php

namespace App\Http\Requests\Curricular;

use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\PlantillaExcelAulaLayout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ExcelAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('descargar_excel_aula') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $nivel = (string) $this->input('nivel', '');

        return [
            'anio_escolar' => ['required', 'string', 'max:20'],
            'nivel' => ['required', 'string', Rule::in(CatalogoNivelGrado::nivelesCurriculares())],
            'grado' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, \Closure $fail) use ($nivel): void {
                if (! CatalogoNivelGrado::esGradoValido($nivel, (string) $value)) {
                    $fail('El grado no es válido para el nivel indicado.');
                }
            }],
            'seccion' => ['required', 'string', 'max:20'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'modo' => ['nullable', 'string', Rule::in([PlantillaExcelAulaLayout::MODO_SIN_DATOS])],
            'sede' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('modo') || $this->input('modo') === null || $this->input('modo') === '') {
            $this->merge(['modo' => PlantillaExcelAulaLayout::MODO_SIN_DATOS]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
