<?php

namespace App\Exceptions\Curricular;

use RuntimeException;

class AsignacionDocenteDuplicadaException extends RuntimeException
{
    public function __construct(?string $message = null)
    {
        parent::__construct(
            $message ?? 'Ya existe un docente activo asignado a este curso, sección y año escolar.',
        );
    }
}
