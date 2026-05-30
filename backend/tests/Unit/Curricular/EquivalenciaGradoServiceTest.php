<?php

namespace Tests\Unit\Curricular;

use App\Services\Curricular\EquivalenciaGradoService;
use Database\Seeders\Curricular\EquivalenciasGradoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EquivalenciaGradoServiceTest extends TestCase
{
    use RefreshDatabase;

    private EquivalenciaGradoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EquivalenciasGradoSeeder::class);
        $this->service = new EquivalenciaGradoService;
    }

    #[Test]
    public function equivalencia_uno_primero(): void
    {
        $this->assertSame('1°', $this->service->aLegacy('primaria', '1ro'));
        $this->assertSame('1ro', $this->service->aCurricular('primaria', '1°'));
    }

    #[Test]
    public function equivalencia_dos_segundo(): void
    {
        $this->assertSame('2°', $this->service->aLegacy('primaria', '2do'));
        $this->assertSame('2do', $this->service->aCurricular('primaria', '2°'));
    }

    #[Test]
    public function equivalencia_inicial_tres_anos(): void
    {
        $this->assertSame('3 años', $this->service->aLegacy('inicial', '3 años'));
        $this->assertSame('3 años', $this->service->aCurricular('inicial', '3 años'));
    }

    #[Test]
    public function equivalencia_inicial_cuatro_y_cinco_anos(): void
    {
        $this->assertSame('4 años', $this->service->aLegacy('inicial', '4 años'));
        $this->assertSame('4 años', $this->service->aCurricular('inicial', '4 años'));
        $this->assertSame('5 años', $this->service->aLegacy('inicial', '5 años'));
        $this->assertSame('5 años', $this->service->aCurricular('inicial', '5 años'));
    }
}
