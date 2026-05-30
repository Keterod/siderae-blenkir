<?php

namespace Tests\Unit\Curricular;

use App\Exceptions\Curricular\NotaCurricularFueraDeRangoException;
use App\Exceptions\Curricular\NotasCurricularesVaciasException;
use App\Services\Curricular\CeComponentesDinamicosService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CeComponentesDinamicosServiceTest extends TestCase
{
    private CeComponentesDinamicosService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CeComponentesDinamicosService;
    }

    #[Test]
    public function calcula_promedio_simple_con_pesos_iguales(): void
    {
        $ce = $this->service->calcular([
            ['nota' => 12.0, 'peso' => 33.33],
            ['nota' => 15.0, 'peso' => 33.33],
            ['nota' => 18.0, 'peso' => 33.34],
        ]);

        $this->assertEqualsWithDelta(15.0, $ce, 0.01);
    }

    #[Test]
    public function calcula_promedio_ponderado_con_pesos_distintos(): void
    {
        $ce = $this->service->calcular([
            ['nota' => 10.0, 'peso' => 50.0],
            ['nota' => 20.0, 'peso' => 50.0],
        ]);

        $this->assertEqualsWithDelta(15.0, $ce, 0.01);
    }

    #[Test]
    public function rechaza_lista_vacia(): void
    {
        $this->expectException(NotasCurricularesVaciasException::class);
        $this->service->calcular([]);
    }

    #[Test]
    public function rechaza_nota_fuera_de_rango(): void
    {
        $this->expectException(NotaCurricularFueraDeRangoException::class);
        $this->service->calcular([
            ['nota' => 21.0, 'peso' => 100.0],
        ]);
    }
}
