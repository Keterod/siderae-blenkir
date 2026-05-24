<?php

namespace Tests\Unit\Curricular;

use App\Exceptions\Curricular\NotaCurricularFueraDeRangoException;
use App\Exceptions\Curricular\NotasCurricularesVaciasException;
use App\Services\Curricular\CeCalculatorService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CeCalculatorServiceTest extends TestCase
{
    private CeCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CeCalculatorService;
    }

    #[Test]
    public function calcula_ce_con_cuaderno_libro_y_tarea(): void
    {
        $ce = $this->service->calcular(14.0, 15.0, 16.0);

        $this->assertSame(15.0, $ce);
    }

    #[Test]
    public function calcula_ce_con_cuaderno_y_tarea(): void
    {
        $ce = $this->service->calcular(14.0, null, 16.0);

        $this->assertSame(15.0, $ce);
    }

    #[Test]
    public function calcula_ce_solo_con_libro(): void
    {
        $ce = $this->service->calcular(null, 12.5, null);

        $this->assertSame(12.5, $ce);
    }

    #[Test]
    public function calcula_ce_con_pesos_personalizados_y_normalizacion(): void
    {
        $pesos = ['cuaderno' => 50.0, 'libro' => 30.0, 'tarea' => 20.0];
        $ce = $this->service->calcular(14.0, null, 16.0, $pesos);

        $this->assertSame(14.57, $ce);
    }

    #[Test]
    public function rechaza_calculo_si_todas_las_notas_estan_vacias(): void
    {
        $this->expectException(NotasCurricularesVaciasException::class);

        $this->service->calcular(null, null, null);
    }

    #[Test]
    public function rechaza_nota_fuera_de_rango(): void
    {
        $this->expectException(NotaCurricularFueraDeRangoException::class);

        $this->service->calcular(21.0, 10.0, null);
    }
}
