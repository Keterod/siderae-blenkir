<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'curso' => ['required', 'string', 'max:255'],
            'nota' => ['required', 'numeric', 'between:0,20'],
            'nota_conducta' => ['nullable', 'numeric', 'between:0,20'],
        ];
    }
}
