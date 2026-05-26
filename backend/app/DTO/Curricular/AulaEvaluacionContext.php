<?php

namespace App\DTO\Curricular;

readonly class AulaEvaluacionContext
{
    /**
     * @param  list<int>  $estudianteIds
     */
    public function __construct(
        public int $mallaCursoId,
        public int $periodoAcademicoId,
        public string $sede,
        public string $grado,
        public string $seccion,
        public array $estudianteIds = [],
    ) {}
}
