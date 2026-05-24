<?php

namespace Database\Seeders\Curricular;

/**
 * Catálogo institucional Blenkir (boletas DOCX).
 * Fuente: docs/referencias/cursos/boletas blenkir*.docx
 */
class CatalogoInstitucionalBlenkir
{
    /**
     * @return array<string, array{cn: array<string, list<string>>, cursos: list<string>}>
     */
    public static function definicionInicial(): array
    {
        return [
            'Matemática' => [
                'cn' => ['Resuelve problemas de cantidad' => ['Usa estrategias y procedimientos.']],
                'cursos' => ['Aritmética', 'Geometría', 'Raz. Matemático'],
            ],
            'Comunicación' => [
                'cn' => ['Se comunica oralmente' => ['Obtiene información del entorno.']],
                'cursos' => ['Comunicación', 'Raz. Verbal'],
            ],
            'Ciencia y Tecnología' => [
                'cn' => ['Indaga mediante métodos científicos' => ['Problematiza situaciones.']],
                'cursos' => ['Ciencia y Tecnología'],
            ],
            'Personal Social' => [
                'cn' => ['Construye su identidad' => ['Se valora a sí mismo.']],
                'cursos' => ['Personal Social'],
            ],
            'Psicomotricidad' => [
                'cn' => ['Se desenvuelve de manera autónoma' => ['Comprende su cuerpo.']],
                'cursos' => ['Educación Física'],
            ],
            'Inglés' => [
                'cn' => ['Se comunica oralmente en inglés' => ['Obtiene información de textos orales.']],
                'cursos' => ['Inglés'],
            ],
        ];
    }

    /**
     * @return array<string, array{cn: array<string, list<string>>, cursos: list<string>}>
     */
    public static function definicionPrimaria(): array
    {
        return [
            'Matemática' => [
                'cn' => [
                    'Resuelve problemas de cantidad' => [
                        'Traduce cantidades a expresiones numéricas.',
                        'Usa estrategias y procedimientos de estimación y cálculo.',
                    ],
                ],
                'cursos' => ['Aritmética', 'Álgebra', 'Raz. Matemático', 'Trigonometría'],
            ],
            'Comunicación' => [
                'cn' => [
                    'Se comunica oralmente en su lengua materna' => ['Obtiene información de textos orales.'],
                    'Lee diversos tipos de textos escritos' => ['Obtiene información del texto escrito.'],
                ],
                'cursos' => ['Comprensión y Producción de Textos', 'Gramática', 'Raz. Verbal'],
            ],
            'Ciencia y Tecnología' => [
                'cn' => ['Indaga mediante métodos científicos' => ['Problematiza situaciones.']],
                'cursos' => ['Mundo Físico', 'Cuerpo Humano'],
            ],
            'Personal Social' => [
                'cn' => ['Construye interpretaciones históricas' => ['Interpreta críticamente fuentes.']],
                'cursos' => ['Historia', 'Geografía', 'Ciudadanía'],
            ],
            'Educación Física' => [
                'cn' => ['Se desenvuelve de manera autónoma' => ['Comprende su cuerpo.']],
                'cursos' => ['Educ. Física'],
            ],
            'Inglés' => [
                'cn' => ['Se comunica oralmente en inglés' => ['Obtiene información de textos orales.']],
                'cursos' => ['Inglés'],
            ],
            'Educación Religiosa' => [
                'cn' => ['Construye su identidad como persona' => ['Conoce y valora la Palabra de Dios.']],
                'cursos' => ['Educación Religiosa'],
            ],
            'Arte y Cultura' => [
                'cn' => ['Aprecia de manera crítica manifestaciones artísticas' => ['Aprecia obras artístico-culturales.']],
                'cursos' => ['Taller'],
            ],
        ];
    }

    /**
     * @return array<string, array{cn: array<string, list<string>>, cursos: list<string>}>
     */
    public static function definicionSecundaria(): array
    {
        return [
            'Matemática' => [
                'cn' => ['Resuelve problemas de cantidad' => ['Traduce cantidades a expresiones numéricas.']],
                'cursos' => ['Aritmética', 'Álgebra', 'Raz. Matemático', 'Trigonometría'],
            ],
            'Comunicación' => [
                'cn' => [
                    'Lee diversos tipos de textos escritos' => ['Obtiene información del texto escrito.'],
                    'Se comunica oralmente en su lengua materna' => ['Obtiene información de textos orales.'],
                ],
                'cursos' => ['Lenguaje', 'Literatura', 'Raz. Verbal'],
            ],
            'Ciencia y Tecnología' => [
                'cn' => ['Indaga mediante métodos científicos' => ['Problematiza situaciones.']],
                'cursos' => ['Biología', 'Física', 'Química'],
            ],
            'Desarrollo Personal, Ciudadanía y Cívica' => [
                'cn' => ['Construye su identidad' => ['Se valora a sí mismo.']],
                'cursos' => ['Cívica', 'Psicología'],
            ],
            'Ciencias Sociales' => [
                'cn' => ['Construye interpretaciones históricas' => ['Interpreta críticamente fuentes.']],
                'cursos' => ['Historia del Perú', 'Historia Universal', 'Geografía'],
            ],
            'Educación Física' => [
                'cn' => ['Se desenvuelve de manera autónoma' => ['Comprende su cuerpo.']],
                'cursos' => ['Educ. Física'],
            ],
            'Inglés' => [
                'cn' => ['Se comunica oralmente en inglés' => ['Obtiene información de textos orales.']],
                'cursos' => ['Inglés'],
            ],
            'Educación para el Trabajo' => [
                'cn' => ['Gestiona proyectos de emprendimiento' => ['Crea propuestas de valor.']],
                'cursos' => ['Educación para el Trabajo'],
            ],
            'Educación Religiosa' => [
                'cn' => ['Construye su identidad como persona' => ['Conoce y valora la Palabra de Dios.']],
                'cursos' => ['Educación Religiosa'],
            ],
            'Arte y Cultura' => [
                'cn' => ['Aprecia de manera crítica manifestaciones artísticas' => ['Aprecia obras artístico-culturales.']],
                'cursos' => ['Taller'],
            ],
        ];
    }
}
