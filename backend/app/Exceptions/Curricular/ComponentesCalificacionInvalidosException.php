<?php

namespace App\Exceptions\Curricular;

use RuntimeException;

class ComponentesCalificacionInvalidosException extends RuntimeException
{
    public function __construct(string $message = 'La configuración de componentes de calificación no es válida.')
    {
        parent::__construct($message);
    }
}
