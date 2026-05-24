<?php

namespace Tests\Unit\Curricular;

use App\Exceptions\Curricular\PesosEvaluacionInvalidosException;
use App\Services\Curricular\PesoEvaluacionResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PesoEvaluacionResolverTest extends TestCase
{
    #[Test]
    public function pesos_por_defecto_suman_cien(): void
    {
        $resolver = new PesoEvaluacionResolver;
        $pesos = $resolver->pesosPorDefecto();

        $resolver->validarSuma100($pesos);

        $this->assertSame(33.33, $pesos['cuaderno']);
        $this->assertSame(33.33, $pesos['libro']);
        $this->assertSame(33.34, $pesos['tarea']);
    }

    #[Test]
    public function pesos_invalidos_fallan_validacion(): void
    {
        $resolver = new PesoEvaluacionResolver;

        $this->expectException(PesosEvaluacionInvalidosException::class);

        $resolver->validarSuma100([
            'cuaderno' => 40,
            'libro' => 30,
            'tarea' => 29,
        ]);
    }
}
