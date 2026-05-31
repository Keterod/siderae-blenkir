<?php

namespace Database\Seeders\Demo;

use App\Models\Curricular\MallaCurricular;
use App\Models\Curricular\MallaCurso;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\Curricular\CatalogoNivelGrado;
use Illuminate\Support\Collection;

/**
 * Contexto compartido del demo curricular operativo (aula principal y referencias).
 */
final class DemoCurricularContext
{
    public const ANIO_ESCOLAR = '2026';

    public const BIMESTRE = '1';

    public const NIVEL_PRIMARIA = CatalogoNivelGrado::NIVEL_PRIMARIA;

    public const GRADO_CURRICULAR_PRIMARIA = '2do';

    public const GRADO_ESTUDIANTE_PRIMARIA = '2°';

    public const NIVEL_SECUNDARIA = CatalogoNivelGrado::NIVEL_SECUNDARIA;

    public const GRADO_CURRICULAR_SECUNDARIA = '1ro';

    public const NIVEL_INICIAL = CatalogoNivelGrado::NIVEL_INICIAL;

    public const GRADO_CURRICULAR_INICIAL = '3 años';

    public const SEDE_PRINCIPAL = 'chilca';

    public const SEDE_SECUNDARIA = 'auquimarca';

    public const SECCION_PRINCIPAL = 'AMISTAD';

    /** Aula demo secundaria (docente2) — auquimarca 1ro. */
    public const SECCION_SECUNDARIA = 'BASICO';

    /** Cantidad de cursos de malla usados para notas/asignaciones demo. */
    public const CURSOS_DEMO = 3;

    public static function periodoBimestreUno(): PeriodoAcademico
    {
        return PeriodoAcademico::query()
            ->where('anio_escolar', self::ANIO_ESCOLAR)
            ->where('bimestre', self::BIMESTRE)
            ->firstOrFail();
    }

    public static function mallaPrimaria2do(): MallaCurricular
    {
        return MallaCurricular::query()
            ->where('anio_escolar', self::ANIO_ESCOLAR)
            ->where('nivel', self::NIVEL_PRIMARIA)
            ->where('grado', self::GRADO_CURRICULAR_PRIMARIA)
            ->firstOrFail();
    }

    /**
     * @return Collection<int, MallaCurso>
     */
    public static function mallaCursosPrimaria2do(int $limite = self::CURSOS_DEMO): Collection
    {
        return MallaCurso::query()
            ->where('malla_curricular_id', self::mallaPrimaria2do()->id)
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->limit($limite)
            ->get();
    }

    /**
     * @return Collection<int, Estudiante>
     */
    public static function estudiantesAulaPrincipal(): Collection
    {
        return Estudiante::query()
            ->where('activo', true)
            ->where('anio_escolar', self::ANIO_ESCOLAR)
            ->where('nivel', self::NIVEL_PRIMARIA)
            ->where('sede', self::SEDE_PRINCIPAL)
            ->where('grado', self::GRADO_ESTUDIANTE_PRIMARIA)
            ->where('seccion', self::SECCION_PRINCIPAL)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    public static function docente(): User
    {
        return User::query()->where('email', 'docente@siderae.test')->firstOrFail();
    }

    public static function docenteSecundario(): User
    {
        return User::query()->where('email', 'docente2@siderae.test')->firstOrFail();
    }

    public static function coordinador(): User
    {
        return User::query()->where('email', 'coordinador@siderae.test')->firstOrFail();
    }
}
