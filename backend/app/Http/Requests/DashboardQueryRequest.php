<?php

namespace App\Http\Requests;

use App\Support\SedeOperativa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Query params válidos para GET /api/dashboard y GET /api/dashboard/export.
     * Campos coinciden con columnas existentes del modelo Estudiante (sede, nivel educativo,
     * grado, seccion) más nivel_riesgo (último índice: alto|medio|bajo).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sede' => ['sometimes', 'nullable', Rule::in(['chilca', 'auquimarca'])],
            'nivel' => ['sometimes', 'nullable', Rule::in(['primaria', 'secundaria'])],
            'grado' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seccion' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nivel_riesgo' => ['sometimes', 'nullable', Rule::in(['alto', 'medio', 'bajo'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        foreach (['grado', 'seccion'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $trim = trim((string) $this->input($key));
                $merged[$key] = $trim === '' ? null : $trim;
            }
        }

        $this->merge($merged);
    }

    /**
     * @return array{sede:?string,nivel:?string,grado:?string,seccion:?string,nivel_riesgo:?string}
     */
    public function filtrosAplicados(): array
    {
        $validated = $this->validated();

        return [
            'sede' => SedeOperativa::defaultConsulta($validated['sede'] ?? null),
            'nivel' => $validated['nivel'] ?? null,
            'grado' => $validated['grado'] ?? null,
            'seccion' => $validated['seccion'] ?? null,
            'nivel_riesgo' => $validated['nivel_riesgo'] ?? null,
        ];
    }
}
