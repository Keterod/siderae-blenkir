<?php

namespace App\Exceptions\Curricular;

use RuntimeException;

class PesosEvaluacionBimestralInvalidosException extends RuntimeException
{
    public function __construct(string $message = 'Los pesos de evaluación bimestral no son válidos.')
    {
        parent::__construct($message);
    }
}
