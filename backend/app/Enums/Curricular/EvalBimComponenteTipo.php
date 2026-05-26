<?php

namespace App\Enums\Curricular;

enum EvalBimComponenteTipo: string
{
    case PromedioCriterios = 'promedio_criterios';
    case Oral = 'oral';
    case PromedioEta = 'promedio_eta';
    case ExamenBimestral = 'examen_bimestral';
    case Personalizado = 'personalizado';

    public function esEscalar(): bool
    {
        return match ($this) {
            self::Oral, self::ExamenBimestral, self::Personalizado => true,
            default => false,
        };
    }
}
