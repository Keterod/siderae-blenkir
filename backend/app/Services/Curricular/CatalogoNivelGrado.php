<?php

namespace App\Services\Curricular;

class CatalogoNivelGrado
{
    public const NIVEL_INICIAL = 'inicial';

    public const NIVEL_PRIMARIA = 'primaria';

    public const NIVEL_SECUNDARIA = 'secundaria';

  /** @var list<string> */
    public const GRADOS_INICIAL = ['3 años', '4 años', '5 años'];

  /** @var list<string> */
    public const GRADOS_PRIMARIA = ['1ro', '2do', '3ro', '4to', '5to', '6to'];

  /** @var list<string> */
    public const GRADOS_SECUNDARIA = ['1ro', '2do', '3ro', '4to', '5to'];

    /** Grados legacy almacenados en estudiantes.grado (formato con °). */
    /** @var list<string> */
    public const GRADOS_ESTUDIANTE_PRIMARIA = ['1°', '2°', '3°', '4°', '5°', '6°'];

    /** @var list<string> */
    public const GRADOS_ESTUDIANTE_SECUNDARIA = ['1°', '2°', '3°', '4°', '5°'];

    public static function gradosPorNivel(string $nivel): array
    {
        return match ($nivel) {
            self::NIVEL_INICIAL => self::GRADOS_INICIAL,
            self::NIVEL_PRIMARIA => self::GRADOS_PRIMARIA,
            self::NIVEL_SECUNDARIA => self::GRADOS_SECUNDARIA,
            default => [],
        };
    }

    public static function esGradoValido(string $nivel, string $grado): bool
    {
        return in_array($grado, self::gradosPorNivel($nivel), true);
    }

    /**
     * Grados válidos para el campo estudiantes.grado según nivel.
     *
     * @return list<string>
     */
    public static function gradosEstudiantePorNivel(string $nivel): array
    {
        return match ($nivel) {
            self::NIVEL_INICIAL => self::GRADOS_INICIAL,
            self::NIVEL_PRIMARIA => self::GRADOS_ESTUDIANTE_PRIMARIA,
            self::NIVEL_SECUNDARIA => self::GRADOS_ESTUDIANTE_SECUNDARIA,
            default => [],
        };
    }

    public static function esGradoEstudianteValido(string $nivel, string $grado): bool
    {
        return in_array($grado, self::gradosEstudiantePorNivel($nivel), true);
    }

    public static function nivelesCurriculares(): array
    {
        return [
            self::NIVEL_INICIAL,
            self::NIVEL_PRIMARIA,
            self::NIVEL_SECUNDARIA,
        ];
    }
}
