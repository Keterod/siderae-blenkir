<?php

namespace App\Exceptions\Curricular;

use RuntimeException;

class NotasCurricularesVaciasException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Debe registrar al menos una nota entre Cuaderno, Libro o Tarea.');
    }
}
