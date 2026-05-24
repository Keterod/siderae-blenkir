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
}
