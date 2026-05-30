# Sprint 8.5B: API curricular, UI, asignación docente e integración con riesgo

## Objetivo

Exponer la **API REST** del módulo curricular, implementar pantallas React para coordinador/tutora y docente (separadas del flujo legacy), flujo **obligatorio** de asignación docente–aula–curso por UI, registro de notas C/L/T con CE automático, consulta de notas para coordinación y adaptación del promedio académico para riesgo **sin modificar Flask**.

## Duración estimada

2 semanas

## Dependencias de entrada

- Sprint 8.5A completado (tablas, seeders, servicios CE y equivalencias).
- [`docs/analisis/modulo-curricular-academico.md`](../docs/analisis/modulo-curricular-academico.md) vigente.

## Alcance

### Permisos nuevos (Spatie — sin roles nuevos)

| Permiso | administrador | coordinador_academico | docente | directivo | psicologo_tutor |
|---------|:---:|:---:|:---:|:---:|:---:|
| `ver_malla_curricular` | Sí | Sí | Sí | Sí | No |
| `gestionar_malla_curricular` | Sí | Sí | No | No | No |
| `gestionar_temas_semanales` | Sí | Sí | No | No | No |
| `configurar_pesos_evaluacion` | Sí | Sí | No | No | No |
| `gestionar_asignaciones_docente` | Sí | Sí | No | No | No |
| `ver_notas_academicas` | Sí | Sí | Sí | Sí | Sí (solo lectura) |
| `registrar_notas_semanales` | **No** | **No** | Sí | No | No |

**Regla:** coordinador/tutora **no** registra notas semanales en 8.5B. Solo consulta vía `ver_notas_academicas`. Un permiso futuro `registrar_notas_semanales_coordinador` quedaría explícitamente fuera de alcance hasta decisión del equipo.

### Endpoints definitivos (`/api`, `auth:sanctum` + `permission:*`)

#### Equivalencias y catálogo

| Método | Ruta | Permiso | Descripción |
|--------|------|---------|-------------|
| GET | `/catalogo/niveles-grados` | autenticado | Lista niveles y grados curriculares |
| GET | `/areas` | `ver_malla_curricular` | Filtro `?nivel=` |
| GET | `/areas/{area}/competencias` | `gestionar_temas_semanales` o `ver_malla_curricular` | Competencias por área |
| GET | `/competencias/{competencia}/capacidades` | idem | Capacidades por competencia |

#### Malla curricular

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/mallas-curriculares` | `ver_malla_curricular` |
| GET | `/mallas-curriculares/{id}` | `ver_malla_curricular` |
| POST | `/mallas-curriculares/cargar-plantilla` | `gestionar_malla_curricular` |
| PUT | `/mallas-curriculares/{id}` | `gestionar_malla_curricular` |
| POST | `/mallas-curriculares/{id}/cursos` | `gestionar_malla_curricular` |
| PATCH | `/mallas-curriculares/{id}/cursos/{mallaCurso}` | `gestionar_malla_curricular` |
| DELETE | `/mallas-curriculares/{id}/cursos/{mallaCurso}` | `gestionar_malla_curricular` |

#### Calendario

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/periodos-academicos` | `ver_malla_curricular` |
| GET | `/periodos-academicos/{id}/semanas` | `ver_malla_curricular` |
| POST | `/periodos-academicos/{id}/semanas` | `gestionar_temas_semanales` |
| PATCH | `/semanas-academicas/{id}` | `gestionar_temas_semanales` |

#### Temas semanales

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/temas-semanales` | `ver_malla_curricular` |
| POST | `/temas-semanales` | `gestionar_temas_semanales` |
| GET | `/temas-semanales/{id}` | `ver_malla_curricular` |
| PUT | `/temas-semanales/{id}` | `gestionar_temas_semanales` |
| DELETE | `/temas-semanales/{id}` | `gestionar_temas_semanales` |

Validaciones: ≥1 competencia, ≥1 capacidad; competencia ∈ área del curso; **unicidad** curso+bimestre+semana; **sin** `seccion` en tema.

#### Pesos, asignaciones, notas

| Método | Ruta | Permiso |
|--------|------|---------|
| GET/POST | `/configuracion-pesos-evaluacion` | `configurar_pesos_evaluacion` / lectura |
| PUT | `/configuracion-pesos-evaluacion/{id}` | `configurar_pesos_evaluacion` |
| GET/POST/PATCH | `/asignaciones-docente` | `gestionar_asignaciones_docente` |
| GET | `/docente/aulas-cursos` | `registrar_notas_semanales` |
| GET | `/notas-semanales/formulario` | `registrar_notas_semanales` o `ver_notas_academicas` |
| POST | `/notas-semanales/bulk` | `registrar_notas_semanales` |
| GET | `/estudiantes/{estudiante}/resumen-academico` | `ver_notas_academicas` |

**Endpoints legacy — sin cambiar contratos:** `/api/materias/*`, `/api/notas/*`, `/api/notas/lote`, `/api/estudiantes/{id}/procesar-riesgo`.

### Reglas de negocio en API

- **Docente:** solo `POST notas-semanales/bulk` en asignaciones propias; estudiantes filtrados por `seccion` asignada; grado traducido vía `equivalencias_grado`.
- **Coordinador:** CRUD malla, temas, pesos, asignaciones; **GET** notas/resumen; **no POST** bulk notas.
- **Inicial:** mismo flujo curricular que Primaria/Secundaria (malla, temas, notas C/L/T/CE, eval bim, Excel). Riesgo académico excluido en fase posterior.
- **Temas:** 2do A y 2do B comparten el mismo tema; cada docente registra notas solo de su sección.
- **Secundaria:** no ofrecer “cargar plantilla” con cursos inventados; solo malla manual a partir de CN o plantilla vacía.

### Frontend (patrón panel en `App.jsx`)

| Panel | Rol principal | Permiso |
|-------|---------------|---------|
| `MallaCurricularPanel` | coordinador, admin | `gestionar_malla_curricular` |
| `TemasSemanalesPanel` | coordinador | `gestionar_temas_semanales` |
| `PesosEvaluacionPanel` | coordinador | `configurar_pesos_evaluacion` |
| `AsignacionDocentePanel` | coordinador, admin | `gestionar_asignaciones_docente` |
| `RegistroNotasSemanalesPanel` | docente | `registrar_notas_semanales` |
| `ConsultaNotasAcademicasPanel` (o sección en temas) | coordinador | `ver_notas_academicas` |
| Resumen en perfil estudiante | varios | `ver_notas_academicas` |

**Menú coordinador:** Malla curricular → Temas semanales → Pesos → Asignación docentes → Consulta notas. **Sin** Materias ni Notas masivas.

**Menú administrador:** bloque curricular anterior + grupo **«Datos académicos (legacy)»** al final (Materias, Notas/Asistencia masivas).

**Menú docente:** solo **Notas semanales**.

Archivo auxiliar: `frontend/src/lib/academicoCurricular.js`.

### Integración riesgo (solo Laravel)

- `RiesgoAcademicoService::construirPayload()`: si existen `notas_semanales` con CE en el año del estudiante → `promedio_notas` = promedio bimestral agregado; si no → `Nota::avg('nota')` (legacy).
- Validación datos mínimos: aceptar notas semanales **o** notas legacy.
- **No** cambiar `ml-service`.

### Pruebas obligatorias (Feature / PHPUnit)

| ID | Caso |
|----|------|
| T1 | Usuario sin permiso → 403 en malla, temas, asignaciones |
| T2 | Cargar plantilla Primaria 2do → malla con cursos institucionales |
| T3 | Crear segundo tema mismo curso+bimestre+semana → 422 |
| T4 | Tema sin competencia → 422 |
| T5 | Docente sin asignación → 403 en bulk notas |
| T6 | Bulk notas válidas → CE calculado; CE no editable; al menos una de C/L/T |
| T6b | Bulk sin C/L/T → 422; CE solo C+T con pesos 50/20 → normalización |
| T7 | Nota fuera de 0–20 → 422 |
| T8 | Coordinador POST bulk notas → 403 |
| T9 | Estudiante grado `2°` + malla `2do` + equivalencia → registro OK |
| T10 | `procesar-riesgo` con CE → payload `promedio_notas` desde agregador |
| T11 | `procesar-riesgo` sin CE → fallback legacy |
| T12 | Registro notas nivel Inicial: formulario 200, bulk CE, eval bim y plantilla Excel (`NotasSemanalesInicialTest`) |

### Activity log

Registrar en acciones críticas: cargar plantilla, crear tema, asignar docente, bulk notas semanales (patrón Sprint 7.5A).

## Fuera de alcance

- Cypress (Sprint 9).
- Temas distintos por sección (backlog).
- Dos temas activos mismo curso+bimestre+semana (backlog explícito).
- Permiso coordinador para registrar notas.
- Import Excel registro auxiliar.
- Migrar `estudiantes.nivel` a Inicial.

## Criterios de aceptación finales (módulo 8.5)

1. Coordinador carga malla Primaria 2do desde plantilla institucional.
2. Coordinador crea tema con competencias/capacidades; 2do A y 2do B ven el mismo tema.
3. No puede existir dos temas activos para el mismo curso + bimestre + semana.
4. Coordinador asigna docente a curso + sección por UI.
5. Docente registra C/L/T solo en asignaciones; CE automático; 0–20.
6. Coordinador consulta notas/resumen y **no** puede registrar bulk notas.
7. Docente no crea cursos, temas, competencias ni capacidades.
8. Menú coordinador sin mezcla visual con legacy.
9. Secundaria: CN consultable; sin cursos internos seed inventados.
10. Inicial: malla configurable y registro de notas semanales con el mismo flujo que Primaria/Secundaria (sin riesgo académico).
11. Semanas demo 4/bimestre listables; estructura permite cambiar cantidad después.
12. Riesgo operativo con CE o fallback legacy.
13. Suite Feature T1–T12 en verde (o excepción documentada).
14. Matriz de accesos actualizada en `docs/arquitectura/`.

## Entregables

- Controladores, Form Requests, rutas `api.php`, `PermissionsSeeder` actualizado.
- Paneles React y menú segregado.
- Tests Feature.
- Ajuste mínimo `RiesgoAcademicoService` + `EstudiantePerfil` (lectura resumen).

## Dependencias de salida

Habilita **Sprint 9** (pruebas integrales incluyendo flujos curriculares).
