<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsistenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'semana_inicio' => ['required', 'date'],
            'estado' => ['required', Rule::in(['presente', 'tardanza', 'falta'])],
            'anio_escolar' => ['required', 'string', 'max:255'],
            'bimestre' => ['required', Rule::in(['1', '2', '3', '4'])],
        ];
    }
}
