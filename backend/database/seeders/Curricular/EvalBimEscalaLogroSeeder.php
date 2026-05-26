<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\EvalBimEscalaLogro;
use Illuminate\Database\Seeder;

class EvalBimEscalaLogroSeeder extends Seeder
{
    public function run(): void
    {
        $rangos = [
            ['codigo_literal' => 'AD', 'etiqueta' => 'Logro destacado', 'orden' => 4, 'nota_min' => 18.00, 'nota_max' => 20.00],
            ['codigo_literal' => 'A', 'etiqueta' => 'Logro esperado', 'orden' => 3, 'nota_min' => 14.00, 'nota_max' => 17.99],
            ['codigo_literal' => 'B', 'etiqueta' => 'En proceso', 'orden' => 2, 'nota_min' => 11.00, 'nota_max' => 13.99],
            ['codigo_literal' => 'C', 'etiqueta' => 'En inicio', 'orden' => 1, 'nota_min' => 0.00, 'nota_max' => 10.99],
        ];

        foreach ($rangos as $rango) {
            EvalBimEscalaLogro::query()->updateOrCreate(
                ['codigo_literal' => $rango['codigo_literal']],
                array_merge($rango, ['activo' => true]),
            );
        }
    }
}
