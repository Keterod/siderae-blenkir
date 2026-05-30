<?php

namespace App\Http\Requests\Curricular;

use App\Http\Requests\Curricular\Concerns\ValidatesAsistenciaDiariaContexto;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AsistenciaDiariaFormularioRequest extends FormRequest
{
    use ValidatesAsistenciaDiariaContexto;

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validarGradoContexto($validator);
        });
    }

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
        ]);
    }
}
