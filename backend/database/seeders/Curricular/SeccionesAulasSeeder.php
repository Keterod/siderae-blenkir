<?php

namespace Database\Seeders\Curricular;

use App\Models\Curricular\SeccionAula;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeccionesAulasSeeder extends Seeder
{
    /** @var list<string> */
    private const SECCIONES_PRIMARIA_COMPLETA = [
        'AMISTAD',
        'AMOR',
        'BONDAD',
        'RESPETO',
        'RESPONSABILIDAD',
    ];

    /** @var list<string> */
    private const SECCIONES_PRIMARIA_CORTA = [
        'AMISTAD',
        'AMOR',
        'BONDAD',
        'RESPETO',
    ];

    /** @var list<string> */
    private const SECCIONES_SECUNDARIA = [
        'BASICO',
        'CICLADO',
        'PRE U',
        'SELECCION',
    ];

    public function run(): void
    {
        $this->sembrarInicial('3 años', [
            'ARDILLITAS',
            'ESTRELLITAS DE MAR',
            'PULPITOS',
            'TIBURONCITOS',
        ]);

        $this->sembrarInicial('4 años', [
            'HORMIGUITAS',
            'LEONCITOS',
            'PUMITAS',
            'TIGRESITOS',
        ]);

        $this->sembrarInicial('5 años', [
            'CANGREJITOS',
            'LORITOS',
            'PALOMITAS',
            'PATITOS',
            'POLLITOS',
        ]);

        foreach (CatalogoNivelGrado::GRADOS_PRIMARIA as $grado) {
            $nombres = in_array($grado, ['5to', '6to'], true)
                ? self::SECCIONES_PRIMARIA_CORTA
                : self::SECCIONES_PRIMARIA_COMPLETA;

            $this->sembrarNivelGrado(SeccionAula::NIVEL_PRIMARIA, $grado, $nombres);
        }

        foreach (CatalogoNivelGrado::GRADOS_SECUNDARIA as $grado) {
            $this->sembrarNivelGrado(SeccionAula::NIVEL_SECUNDARIA, $grado, self::SECCIONES_SECUNDARIA);
        }
    }

    /**
     * @param  list<string>  $nombres
     */
    private function sembrarInicial(string $grado, array $nombres): void
    {
        $this->sembrarNivelGrado(SeccionAula::NIVEL_INICIAL, $grado, $nombres);
    }

    /**
     * @param  list<string>  $nombres
     */
    private function sembrarNivelGrado(string $nivel, string $grado, array $nombres): void
    {
        foreach ($nombres as $indice => $nombre) {
            $codigo = Str::slug($nombre, '_');

            SeccionAula::query()->updateOrCreate(
                [
                    'nivel' => $nivel,
                    'grado' => $grado,
                    'nombre' => $nombre,
                ],
                [
                    'codigo' => $codigo !== '' ? $codigo : 'seccion_'.($indice + 1),
                    'orden' => $indice + 1,
                    'activo' => true,
                ],
            );
        }
    }
}
