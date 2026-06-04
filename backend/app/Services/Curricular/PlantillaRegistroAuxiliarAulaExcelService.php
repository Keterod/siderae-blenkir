<?php

namespace App\Services\Curricular;

class PlantillaRegistroAuxiliarAulaExcelService
{
    public function __construct(
        private readonly PlantillaRegistroAuxiliarExcelService $excelService = new PlantillaRegistroAuxiliarExcelService,
    ) {}

    /**
     * @param  array{colegio: string, sede: string, anio_escolar: string, nivel: string, grado: string, seccion: string, bimestre: string|int, modo: string}  $resumen
     * @param  list<array{titulo: string, payload: array<string, mixed>}>  $hojasCurso
     */
    public function generar(array $resumen, array $hojasCurso): string
    {
        return $this->excelService->generarExcelAula($resumen, $hojasCurso);
    }
}
