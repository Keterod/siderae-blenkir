<?php

namespace App\Exceptions\Curricular;

use RuntimeException;

class PesosEvaluacionInvalidosException extends RuntimeException
{
    public function __construct(string $message = 'Los pesos de evaluación no son válidos.')
    {
        parent::__construct($message);
    }
}
