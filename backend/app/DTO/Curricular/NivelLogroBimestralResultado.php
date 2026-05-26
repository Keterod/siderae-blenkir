<?php

namespace App\DTO\Curricular;

use App\Enums\Curricular\EvalBimEstadoCalculo;

readonly class NivelLogroBimestralResultado
{
    /**
     * @param  list<string>  $pendientes
     * @param  array<string, mixed>  $detalle
     */
    public function __construct(
        public EvalBimEstadoCalculo $estadoCalculo,
        public ?float $nivelLogroNumerico,
        public ?string $nivelLogroLiteral,
        public ?float $promedioCriterios,
        public ?float $oral,
        public ?float $promedioEta,
        public ?float $examenBimestral,
        public array $detalle = [],
        public array $pendientes = [],
    ) {}
}
