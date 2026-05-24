# Sprint 8.5A: Backend curricular — esquema, catálogo, seeders y dominio CE

## Objetivo

Introducir el **modelo de datos** del módulo curricular académico para los niveles **Inicial, Primaria y Secundaria**, sin modificar el `enum nivel` de las tablas legacy `estudiantes` ni `materias`, con seeders del Currículo Nacional (solo áreas, competencias y capacidades), plantilla institucional Primaria (detalle **2do**), calendario académico con semanas configurables, tabla **obligatoria** `equivalencias_grado` y servicios de cálculo de CE.

Este sprint es **solo backend de persistencia y dominio**. No incluye controladores API públicos ni pantallas React (Sprint 8.5B).

## Duración estimada

1,5 a 2 semanas

## Dependencias de entrada

- Sprint 8 completado (RBAC Spatie operativo).
- Documentación actualizada: [`docs/analisis/modulo-curricular-academico.md`](../docs/analisis/modulo-curricular-academico.md).

## Alcance

### Reglas arquitectónicas no negociables

- **No alterar** migraciones existentes de `estudiantes`, `materias`, `notas`, `asistencias`.
- Campo `nivel` curricular en tablas nuevas: `inicial | primaria | secundaria`.
- Tablas legacy siguen con `primaria | secundaria` únicamente.
- **No** seed de estándares de aprendizaje ni desempeños del Currículo Nacional.
- **No** inventar cursos internos de Secundaria sin plantilla institucional validada.
- **No** registrar `notas_semanales` para nivel Inicial en este sprint (sin estudiantes Inicial en legacy).

### Migraciones (tablas nuevas)

Ver especificación completa en `modulo-curricular-academico.md` §20. Resumen:

| Tabla | Propósito |
|-------|-----------|
| `equivalencias_grado` | **Obligatoria.** Mapeo grado curricular ↔ grado legacy estudiantes |
| `areas` | Áreas CN por nivel curricular |
| `cursos_catalogo` | Cursos institucionales (Primaria) u homónimos por área |
| `competencias` | Competencias CN (nombre resumido) |
| `capacidades` | Capacidades CN por competencia |
| `plantillas_curriculares` | Plantilla base por nivel + grado |
| `plantilla_cursos` | Cursos de plantilla |
| `mallas_curriculares` | Malla por año + nivel + grado |
| `malla_cursos` | Cursos activos en malla |
| `periodos_academicos` | Bimestres por año (`semanas_planificadas` configurable) |
| `semanas_academicas` | Semanas por bimestre |
| `temas_semanales` | Tema por curso + bimestre + semana (sin `seccion`) |
| `tema_competencias` / `tema_capacidades` | Pivots |
| `configuracion_pesos_evaluacion` | Pesos C/L/T |
| `docente_curso_aulas` | Asignación docente (preparación para 8.5B) |
| `notas_semanales` | C, L, T, `ce_calculado` |

**Índice único obligatorio en `temas_semanales`:** un solo tema activo por `(malla_curso_id, periodo_academico_id, semana_academica_id)`.

### Tabla `equivalencias_grado` (obligatoria)

| nivel | grado_curricular | grado_estudiante_legacy |
|-------|------------------|-------------------------|
| primaria | 1ro … 6to | 1° … 6° |
| secundaria | 1ro … 5to | 1° … 5° |

Sin filas para `inicial` (no hay estudiantes legacy Inicial en 8.5).

### Seeders

| Seeder | Contenido |
|--------|-----------|
| `EquivalenciasGradoSeeder` | 11 filas primaria + 5 secundaria |
| `CurriculoNacionalBaseSeeder` | Áreas, competencias, capacidades (nombres oficiales **resumidos**) para Inicial, Primaria y Secundaria. **Sin** estándares ni desempeños. Secundaria: catálogo CN base. |
| `PlantillaPrimariaInstitucionalSeeder` | Grado **2do**: áreas + cursos institucionales (tabla análisis §7). Otros grados Primaria: registro de plantilla sin replicar automáticamente la malla de 2do. |
| `PlantillaInicialBaseSeeder` | Estructura + CN base; **sin** cursos institucionales inventados masivos |
| `PlantillaSecundariaBaseSeeder` | Estructura + CN base; **sin** cursos internos inventados |
| `PeriodosSemanasDemoSeeder` | Año 2026; 4 bimestres; **4 semanas por bimestre** (demo; ajustable después) |

### Servicios de dominio + pruebas unitarias

- `CatalogoNivelGrado` — grados: Inicial (3/4/5 años), Primaria (1ro–6to), Secundaria (1ro–5to).
- `EquivalenciaGradoService` — traducción curricular ↔ legacy (solo primaria/secundaria con estudiantes).
- `CeCalculatorService` — CE solo con notas **presentes**; al menos una de C/L/T; pesos normalizados si faltan componentes.
- `PesoEvaluacionResolver` — precedencia: curso → área → grado/nivel → global; suma exacta 100; default 33.33 / 33.33 / 33.34.
- `DocenteCursoAulaValidator` — un solo docente activo por `(anio, nivel, grado, seccion, sede, malla_curso_id)`.

### Reglas CE (Fase 2)

- C, L, T **opcionales** individualmente; **obligatorio** al menos uno.
- CE `decimal(5,2)`; no editable.
- Pesos personalizados: normalizar solo sobre pesos de notas presentes.

### Inicial (Fase 2)

- Tablas y seeders CN/malla para Inicial.
- **Sin** notas semanales demo ni integración riesgo para Inicial.

### Pruebas obligatorias (PHPUnit)

- CE: las tres notas; solo C+T; solo L; pesos normalizados (ej. C=50, T=20).
- Sin ninguna nota → excepción.
- Pesos que no suman 100% → excepción.
- Equivalencia `2do` ↔ `2°`.
- Validación docente único activo por combinación.

## Fuera de alcance

- Rutas en `api.php` y controladores HTTP.
- Pantallas React.
- `PermissionsSeeder` (8.5B).
- Import Excel / PDF.
- Flask / Docker.
- Estudiantes con `nivel = inicial` en tabla `estudiantes`.
- Coordinador registrando notas semanales.

## Criterios de aceptación

1. `php artisan migrate` crea solo tablas curriculares; **cero** cambios en columnas de `estudiantes`/`materias`.
2. `equivalencias_grado` poblada con mapeos primaria y secundaria completos.
3. Catálogo CN cargado (áreas, competencias, capacidades) para tres niveles; **no** existen tablas de desempeños.
4. Plantilla Primaria **2do** con cursos institucionales §7; otros grados sin afirmar malla idéntica.
5. Secundaria tiene CN base; **no** hay cursos inventados tipo “Aritmética” en Secundaria.
6. Periodos 2026 con 4 bimestres y 4 semanas cada uno (16 semanas demo).
7. Tests unitarios de CE, pesos y equivalencias pasan en verde.
8. Documentación de análisis y este sprint alineadas.

## Entregables

- Migraciones + modelos Eloquent + factories mínimas.
- Seeders listados.
- Servicios de dominio + tests PHPUnit.
- Sin cambios en `backend/routes/api.php` ni `frontend/`.

## Dependencias de salida

Habilita Sprint 8.5B.
