<?php

namespace App\Exceptions\Curricular;

use RuntimeException;

class NotaCurricularFueraDeRangoException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Las notas deben estar entre 0 y 20.');
    }
}
