<?php

namespace App\Http\Requests\Curricular;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkNotasSemanalesRequest extends FormRequest
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
            'asignacion_docente_id' => ['required', 'integer', 'exists:docente_curso_aulas,id'],
            'tema_semanal_id' => ['nullable', 'integer', 'exists:temas_semanales,id'],
            'estudiante_id' => ['nullable', 'integer', 'exists:estudiantes,id'],
            'notas' => ['nullable', 'array', 'min:1'],
            'notas.*.estudiante_id' => ['required', 'integer', 'exists:estudiantes,id'],
            'notas.*.nota_cuaderno' => ['nullable', 'numeric'],
            'notas.*.nota_libro' => ['nullable', 'numeric'],
            'notas.*.nota_tarea' => ['nullable', 'numeric'],
            'registros' => ['nullable', 'array'],
            'registros.*.tema_semanal_id' => ['required', 'integer', 'exists:temas_semanales,id'],
            'registros.*.nota_cuaderno' => ['nullable', 'numeric'],
            'registros.*.nota_libro' => ['nullable', 'numeric'],
            'registros.*.nota_tarea' => ['nullable', 'numeric'],
            'registros_por_estudiante' => ['nullable', 'array', 'min:1'],
            'registros_por_estudiante.*.estudiante_id' => ['required', 'integer', 'exists:estudiantes,id'],
            'registros_por_estudiante.*.registros' => ['required', 'array'],
            'registros_por_estudiante.*.registros.*.tema_semanal_id' => ['required', 'integer', 'exists:temas_semanales,id'],
            'registros_por_estudiante.*.registros.*.nota_cuaderno' => ['nullable', 'numeric'],
            'registros_por_estudiante.*.registros.*.nota_libro' => ['nullable', 'numeric'],
            'registros_por_estudiante.*.registros.*.nota_tarea' => ['nullable', 'numeric'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $modoTema = $this->filled('tema_semanal_id');
            $modoEstudiante = $this->filled('estudiante_id') && ! $this->filled('registros_por_estudiante');
            $modoVariosEstudiantes = $this->filled('registros_por_estudiante');

            $modosActivos = array_filter([$modoTema, $modoEstudiante, $modoVariosEstudiantes]);

            if (count($modosActivos) === 0) {
                $validator->errors()->add(
                    'asignacion_docente_id',
                    'Indique tema_semanal_id con notas[], estudiante_id con registros[] o registros_por_estudiante[].',
                );

                return;
            }

            if (count($modosActivos) > 1) {
                $validator->errors()->add(
                    'asignacion_docente_id',
                    'Use un solo modo de registro: por tema, por estudiante o por varios estudiantes.',
                );

                return;
            }

            if ($modoTema && ! $this->filled('notas')) {
                $validator->errors()->add('notas', 'Debe enviar al menos una nota por estudiante.');
            }

            if ($modoEstudiante && ! $this->has('registros')) {
                $validator->errors()->add('registros', 'Debe enviar los registros del estudiante.');
            }
        });
    }
}
