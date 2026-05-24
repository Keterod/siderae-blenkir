<?php

namespace App\Exceptions\Curricular;

use RuntimeException;

class TemaSemanalDuplicadoException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Ya existe un tema semanal activo para este curso, bimestre y semana.');
    }
}
