<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReporteConductualRequest extends FormRequest
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
            'fecha' => ['required', 'date'],
            'tipo_conducta' => ['required', 'string', 'max:255'],
            'nivel_gravedad' => ['required', 'string', 'in:leve,moderado,grave'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'accion_inmediata' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
