<?php

namespace Database\Seeders\Curricular;

/**
 * Competencias y capacidades oficiales del Currículo Nacional (nombres resumidos).
 * Fuente: Currículo Nacional de Educación Básica Regular — Perú.
 */
class CurriculoNacionalOficial
{
    /**
     * @return array<string, array<string, list<string>>>
     */
    public static function competenciasPorArea(): array
    {
        return [
            'Matemática' => [
                'Resuelve problemas de cantidad' => [
                    'Traduce cantidades a expresiones numéricas',
                    'Comunica su comprensión sobre los números y el conteo',
                    'Usa estrategias y procedimientos de estimación y cálculo',
                    'Plantea afirmaciones sobre las características y propiedades de las operaciones',
                    'Argumenta afirmaciones sobre las relaciones que se dan con las operaciones',
                ],
                'Resuelve problemas de regularidad, equivalencia y cambio' => [
                    'Traduce datos y condiciones a expresiones algebraicas y gráficas',
                    'Comunica su comprensión sobre las relaciones algebraicas',
                    'Usa estrategias y procedimientos para encontrar equivalencias y reglas generales',
                    'Plantea afirmaciones sobre relaciones entre expresiones algebraicas y gráficas',
                ],
                'Resuelve problemas de forma, movimiento y localización' => [
                    'Modela objetos con formas geométricas y sus transformaciones',
                    'Comunica su comprensión sobre las formas y relaciones geométricas',
                    'Usa estrategias y procedimientos para orientarse en el espacio',
                    'Plantea afirmaciones sobre las características y relaciones geométricas',
                ],
                'Resuelve problemas de gestión de datos e incertidumbre' => [
                    'Representa datos con gráficos y medidas de tendencia central',
                    'Comunica su comprensión sobre las ideas estadísticas',
                    'Usa estrategias y procedimientos para recopilar y procesar datos',
                    'Plantea afirmaciones sobre la posibilidad de que ocurra un evento',
                ],
            ],
            'Comunicación' => [
                'Se comunica oralmente en su lengua materna' => [
                    'Obtiene información del texto oral',
                    'Infiere e interpreta información del texto oral',
                    'Adecúa, organiza y desarrolla las ideas de forma coherente y cohesionada',
                    'Utiliza recursos no verbales y paraverbales de forma estratégica',
                    'Interactúa estratégicamente con distintos interlocutores',
                    'Reflexiona y evalúa la forma, el contenido y el contexto del texto oral',
                ],
                'Lee diversos tipos de textos escritos' => [
                    'Obtiene información del texto escrito',
                    'Infiere e interpreta información del texto escrito',
                    'Adecúa, organiza y desarrolla las ideas de forma coherente y cohesionada',
                    'Reflexiona y evalúa la forma, el contenido y el contexto del texto escrito',
                ],
                'Escribe diversos tipos de textos' => [
                    'Adecúa el texto a la situación comunicativa',
                    'Organiza y desarrolla las ideas de forma coherente y cohesionada',
                    'Utiliza convenciones del lenguaje escrito de forma pertinente',
                    'Reflexiona y evalúa la forma, el contenido y el contexto del texto escrito',
                ],
                'Crea proyectos desde los lenguajes artísticos' => [
                    'Explora y experimenta con los lenguajes artísticos',
                    'Aplica procesos de creación artística',
                    'Evalúa y comunica procesos y productos artísticos',
                ],
            ],
            'Ciencia y Tecnología' => [
                'Indaga mediante métodos científicos para construir conocimientos' => [
                    'Problematiza situaciones para hacer indagación',
                    'Diseña estrategias para hacer indagación',
                    'Genera y registra datos e información',
                    'Analiza datos e información',
                    'Evalúa y comunica el proceso y resultados',
                ],
                'Explica el mundo físico basándose en conocimientos sobre los seres vivos; materia y energía; biodiversidad, Tierra y Universo' => [
                    'Comprende y usa conocimientos sobre los seres vivos; materia y energía; biodiversidad, Tierra y Universo',
                ],
                'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno' => [
                    'Determina una alternativa de solución tecnológica',
                    'Diseña la alternativa de solución tecnológica',
                    'Implementa y valida la alternativa de solución tecnológica',
                    'Evalúa y comunica el funcionamiento y el impacto de la solución tecnológica',
                ],
            ],
            'Personal Social' => [
                'Construye su identidad' => [
                    'Se valora a sí mismo',
                    'Autorregula sus emociones',
                    'Reflexiona y argumenta éticamente',
                    'Vive su sexualidad de manera integral y responsable',
                ],
                'Convive y participa democráticamente en la búsqueda del bien común' => [
                    'Interactúa con todas las personas',
                    'Construye normas y asume acuerdos y leyes',
                    'Maneja conflictos de forma constructiva',
                    'Delibera sobre asuntos públicos',
                    'Participa en acciones que promueven el bienestar común',
                ],
                'Construye interpretaciones históricas' => [
                    'Interpreta críticamente fuentes diversas',
                    'Comprende el tiempo histórico',
                    'Elabora explicaciones sobre procesos históricos',
                ],
                'Gestiona responsablemente el espacio y el ambiente' => [
                    'Comprende la interacción entre el ser humano y el ambiente',
                    'Gestiona el ambiente de forma sostenible',
                ],
                'Gestiona responsablemente los recursos económicos' => [
                    'Comprende la relación entre las actividades económicas y el entorno',
                    'Toma decisiones económicas y financieras',
                ],
            ],
            'Ciencias Sociales' => [
                'Construye interpretaciones históricas' => [
                    'Interpreta críticamente fuentes diversas',
                    'Comprende el tiempo histórico',
                    'Elabora explicaciones sobre procesos históricos',
                ],
                'Gestiona responsablemente el espacio y el ambiente' => [
                    'Comprende la interacción entre el ser humano y el ambiente',
                    'Gestiona el ambiente de forma sostenible',
                ],
                'Gestiona responsablemente los recursos económicos' => [
                    'Comprende la relación entre las actividades económicas y el entorno',
                    'Toma decisiones económicas y financieras',
                ],
            ],
            'Desarrollo Personal, Ciudadanía y Cívica' => [
                'Construye su identidad' => [
                    'Se valora a sí mismo',
                    'Autorregula sus emociones',
                    'Reflexiona y argumenta éticamente',
                    'Vive su sexualidad de manera integral y responsable',
                ],
                'Convive y participa democráticamente en la búsqueda del bien común' => [
                    'Interactúa con todas las personas',
                    'Construye normas y asume acuerdos y leyes',
                    'Maneja conflictos de forma constructiva',
                    'Delibera sobre asuntos públicos',
                    'Participa en acciones que promueven el bienestar común',
                ],
            ],
            'Educación Física' => [
                'Se desenvuelve de manera autónoma a través de su motricidad' => [
                    'Comprende su cuerpo y su movimiento',
                    'Asume una vida activa y saludable',
                    'Interactúa a través de su motricidad',
                ],
                'Asume una vida saludable' => [
                    'Comprende las relaciones entre la actividad física, alimentación, postura y salud',
                    'Incorpora prácticas que mejoran su calidad de vida',
                ],
            ],
            'Inglés' => [
                'Se comunica oralmente en inglés como lengua extranjera' => [
                    'Obtiene información de textos orales en inglés',
                    'Infiere e interpreta información del texto oral en inglés',
                    'Adecúa, organiza y desarrolla las ideas de forma coherente y cohesionada',
                    'Utiliza recursos no verbales y paraverbales de forma estratégica',
                    'Interactúa estratégicamente con distintos interlocutores',
                    'Reflexiona y evalúa la forma, el contenido y el contexto del texto oral',
                ],
                'Lee diversos tipos de textos escritos en inglés' => [
                    'Obtiene información del texto escrito en inglés',
                    'Infiere e interpreta información del texto escrito en inglés',
                    'Reflexiona y evalúa la forma, el contenido y el contexto del texto escrito',
                ],
                'Escribe diversos tipos de textos en inglés' => [
                    'Adecúa el texto a la situación comunicativa',
                    'Organiza y desarrolla las ideas de forma coherente y cohesionada',
                    'Utiliza convenciones del lenguaje escrito de forma pertinente',
                ],
            ],
            'Educación Religiosa' => [
                'Conoce a Dios y asume su identidad religiosa y espiritual en la búsqueda de sentido' => [
                    'Conoce y valora la Palabra de Dios',
                    'Conoce y valora la fe cristiana',
                    'Conoce y valora las expresiones religiosas de su entorno',
                ],
                'Asume la experiencia del encuentro personal y comunitario con Dios en su proyecto de vida' => [
                    'Transforma su entorno desde el amor cristiano',
                    'Actúa coherentemente en función de su fe cristiana',
                ],
            ],
            'Arte y Cultura' => [
                'Aprecia de manera crítica manifestaciones artístico-culturales' => [
                    'Aprecia obras artístico-culturales de manera sensible y gozosa',
                    'Comunica lo apreciado en obras artístico-culturales',
                ],
                'Crea proyectos desde los lenguajes artísticos' => [
                    'Explora y experimenta con los lenguajes artísticos',
                    'Aplica procesos de creación artística',
                    'Evalúa y comunica procesos y productos artísticos',
                ],
            ],
            'Educación para el Trabajo' => [
                'Gestiona proyectos de emprendimiento' => [
                    'Crea propuestas de valor',
                    'Desarrolla productos o servicios',
                    'Implementa acciones que generan valor',
                ],
                'Gestiona procesos de innovación' => [
                    'Identifica oportunidades de mejora e innovación',
                    'Diseña soluciones innovadoras',
                    'Implementa y evalúa soluciones innovadoras',
                ],
            ],
            'Psicomotricidad' => [
                'Se desenvuelve de manera autónoma a través de su motricidad' => [
                    'Comprende su cuerpo y su movimiento',
                    'Asume una vida activa y saludable',
                    'Interactúa a través de su motricidad',
                ],
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function competenciasParaArea(string $nombreArea): array
    {
        return self::competenciasPorArea()[$nombreArea] ?? [];
    }

    /**
     * @param  array<string, list<string>>  $institucional
     * @return array<string, list<string>>
     */
    public static function fusionarConInstitucional(string $nombreArea, array $institucional): array
    {
        $oficial = self::competenciasParaArea($nombreArea);
        $fusion = $oficial;

        foreach ($institucional as $competencia => $capacidades) {
            if (! isset($fusion[$competencia])) {
                $fusion[$competencia] = $capacidades;

                continue;
            }

            $fusion[$competencia] = array_values(array_unique([
                ...$fusion[$competencia],
                ...$capacidades,
            ]));
        }

        return $fusion;
    }
}
