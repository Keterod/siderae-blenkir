<?php

namespace Tests\Unit\Curricular\EvaluacionBimestral;

use App\Exceptions\Curricular\PesosEvaluacionBimestralInvalidosException;
use App\Services\Curricular\EvaluacionBimestral\PesosComponentesService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PesosComponentesServiceTest extends TestCase
{
    #[Test]
    public function validar_suma_acepta_cien_exactos(): void
    {
        (new PesosComponentesService)->validarPesosManuales([
            1 => 25,
            2 => 25,
            3 => 25,
            4 => 25,
        ]);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validar_suma_rechaza_pesos_invalidos(): void
    {
        $this->expectException(PesosEvaluacionBimestralInvalidosException::class);

        (new PesosComponentesService)->validarPesosManuales([
            1 => 40,
            2 => 30,
            3 => 29,
        ]);
    }
}
