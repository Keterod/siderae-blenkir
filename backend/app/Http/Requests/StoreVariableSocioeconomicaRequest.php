<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariableSocioeconomicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'composicion_familiar' => ['required', Rule::in(['nuclear', 'monoparental', 'extendida', 'otros'])],
            'nivel_socioeconomico' => ['required', Rule::in(['bajo', 'medio', 'alto'])],
            'acceso_internet' => ['required', 'boolean'],
            'distancia_colegio_km' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'anio_escolar' => ['required', 'string', 'max:255'],
        ];
    }
}
