# Aula, notas y Excel — SIDERAE-Blenkir

Documento vigente (Fase 6 documental). Fecha de verificación en código: **2026-06-09**.

Describe el **módulo curricular operativo V1**: aulas/secciones, notas semanales, asistencia curricular y flujos Excel **confirmados en código**. Complementa [`docs/manual-usuario.md`](manual-usuario.md) y [`docs/analisis/modulo-curricular-academico.md`](analisis/modulo-curricular-academico.md).

**Fuentes:** [`backend/routes/api.php`](../backend/routes/api.php), [`PlantillaRegistroAuxiliarExcelService.php`](../backend/app/Services/Curricular/PlantillaRegistroAuxiliarExcelService.php), [`frontend/src/App.jsx`](../frontend/src/App.jsx), [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md).

---

## 1. Propósito

Este documento explica cómo opera en V1 el registro académico **curricular** del prototipo:

- Configuración previa (calendario, malla, criterios, bimestre, secciones, asignaciones).
- **Notas semanales** (pantalla, bulk API, plantilla Excel por curso).
- **Asistencia curricular** diaria.
- **Excel por aula** (descarga multi-hoja institucional).
- Diferencia explícita frente a **legacy** (sin menú) e **importación SIAGIE** (no implementada).

---

## 2. Alcance V1

| Criterio | Estado |
|----------|--------|
| Sede operativa | **Chilca** — payloads y filtros fijan Chilca ([`AGENTS.md`](../AGENTS.md), [`sedeOperativa.js`](../frontend/src/lib/sedeOperativa.js)) |
| Campo `sede` | Conservado en BD/API (`chilca` / `auquimarca`) |
| Auquimarca en BD local | **Histórico/local** — no operación multi-sede V1 |
| Selector multi-sede | **No visible** en UI V1 |
| Módulo curricular | **Confirmado** — menú lateral en [`App.jsx`](../frontend/src/App.jsx) |
| Importación SIAGIE (DRS RF-01) | **Fuera del alcance actual** — plantillas propias RF-32/RF-33 |
| Certificación ISO | **No aplica** |

---

## 3. Relación con RF-01 y DRS

El **RF-01** cubre carga manual e importación mediante **plantillas Excel propias** (RF-32). **SIAGIE no se implementará** en este alcance. El módulo curricular oficial está en **RF-21 a RF-35**.

**En V1 el prototipo cubre RF-01 y RF-21–RF-35 mediante:**

| Mecanismo | Qué es | RF relacionado |
|-----------|--------|----------------|
| Registro manual notas semanales | UI + `POST …/bulk` | RF-01, RF-29 — **confirmado** |
| Plantilla registro auxiliar Excel | Descarga + **importación curricular** por curso/asignación | RF-32 — **confirmado**; **no** es SIAGIE |
| Excel por aula | Descarga multi-hoja del aula | RF-33 — **confirmado**; **solo descarga** |
| Asistencia curricular | UI + bulk API | RF-02, RF-31 — **confirmado** |
| Legacy notas/asistencias lote | API sin menú | Parcial — fuera flujo visible V1 |
| Importación SIAGIE global | — | **Fuera del alcance actual** |

**No** llamar «importación SIAGIE» a la plantilla curricular ni al Excel por aula.

---

## 4. Conceptos principales

| Concepto | Significado en SIDERAE-Blenkir |
|----------|-------------------------------|
| **Periodo académico** | Bimestre dentro de un año escolar (`periodos_academicos`) |
| **Malla curricular** | Estructura año + nivel + grado → áreas y cursos (`mallas_curriculares`, `malla_cursos`) |
| **Curso / área** | Curso institucional dentro de un área del CN |
| **Competencia / capacidad** | Catálogo curricular nacional resumido |
| **Criterio de evaluación** | Criterio ligado a tema semanal (UI: **Criterios de evaluación**) |
| **Componente de calificación** | Columnas dinámicas de evaluación (p. ej. Cuaderno, Libro, Tarea o personalizados) |
| **Configuración bimestral** | Componentes, ETAs y pesos por curso/bimestre |
| **Tema semanal** | Tema único por curso + bimestre + semana (`temas_semanales`) |
| **Aula / sección** | Grado + sección + sede (`secciones_aulas`, estudiantes) |
| **Asignación docente** | Vínculo docente ↔ curso ↔ sección (`docente_curso_aulas`) |
| **Nota semanal** | Calificación por estudiante y tema (`notas_semanales`) |
| **Asistencia curricular** | Registro diario por estudiante (`asistencias_diarias`) |
| **Registro auxiliar Excel** | Plantilla `.xlsx` por **un curso** — descarga e importación |
| **Excel por aula** | Libro `.xlsx` con hoja estudiantes + una hoja por curso del grado — **solo descarga** |

---

## 5. Flujo funcional confirmado

Orden recomendado de configuración y operación (V1 Chilca):

| Paso | Acción | Rol principal | Permiso | Pantalla / módulo | Estado | Evidencia |
|------|--------|---------------|---------|-------------------|--------|-----------|
| 1 | Periodos académicos | Admin / coordinador | `gestionar_calendario_academico` | Periodos académicos | Confirmado | `PeriodosAcademicosPanel`, `/periodos-academicos/*` |
| 2 | Malla curricular | Admin / coordinador | `gestionar_malla_curricular` | Malla curricular | Confirmado | `MallaCurricularPanel`, `/mallas/*` |
| 3 | Competencias y capacidades | Admin / coordinador | `gestionar_competencias_capacidades` | Competencias y capacidades | Confirmado | `CompetenciasCapacidadesPanel` |
| 4 | Criterios de evaluación | Admin / coordinador | `gestionar_temas_semanales` | Criterios de evaluación | Confirmado | `TemasSemanalesPanel`, `/temas/*` |
| 5 | Componentes de calificación | Admin / coordinador | `gestionar_componentes_calificacion` | Componentes de calificación | Confirmado | `ComponentesCalificacionNivelPanel` |
| 6 | Configuración bimestral | Admin / coordinador | `configurar_evaluacion_bimestral` | Configuración bimestral | Confirmado | `ConfiguracionBimestralPanel` |
| 7 | Secciones / aulas | Admin / coordinador | `gestionar_secciones_aulas` | Secciones / Aulas | Confirmado | `SeccionesAulasPanel` |
| 8 | Asignación docente | Admin / coordinador | `gestionar_asignaciones_docente` | Asignación docente | Confirmado | `AsignacionDocentePanel` |
| 9 | Registrar notas semanales | Docente / admin | `registrar_notas_semanales` | Notas semanales | Confirmado | `RegistroNotasSemanalesPanel`, `/notas-semanales/bulk` |
| 10 | Consultar notas (institucional) | Coordinador / directivo | `ver_notas_academicas` (+ reglas UI) | Notas semanales | Parcial | Solo lectura coordinador/directivo |
| 11 | Asistencia curricular | Docente / admin | `registrar_asistencia_curricular` | Asistencia | Confirmado | `AsistenciaCurricularPanel`, `/asistencias-diarias/bulk` |
| 12 | Descargar Excel por aula | Admin / coordinador | `descargar_excel_aula` | Excel por aula | Confirmado | `ExcelPorAulaPanel`, `GET /excel-aula` |
| 13 | Plantilla Excel + import (curso) | Docente / admin | `registrar_notas_semanales` | Notas semanales (toolbar) | Confirmado | `plantilla-excel`, `importar-excel` |

**Oculto en menú V1:** Pesos C/L/T (`curricular_pesos`, `visible: false` en `App.jsx`); legacy materias/notas masivas.

---

## 6. Pantallas y acciones de usuario

Basado en [`manual-usuario.md`](manual-usuario.md) y [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md).

| Pantalla | Acción | Rol | Permiso | Estado V1 | Observación |
|----------|--------|-----|---------|-----------|-------------|
| Periodos académicos | Gestionar años/bimestres | Admin, coord | `gestionar_calendario_academico` | Confirmado | Prerrequisito filtros |
| Malla curricular | Ver / editar malla | Admin, coord (+ lectura docente/directivo) | `ver_*` / `gestionar_malla_curricular` | Confirmado | — |
| Criterios de evaluación | CRUD temas/criterios | Admin, coord | `gestionar_temas_semanales` | Confirmado | — |
| Componentes calificación | CRUD por nivel | Admin, coord | `gestionar_componentes_calificacion` | Confirmado | — |
| Configuración bimestral | Config ETAs/componentes | Admin, coord | `configurar_evaluacion_bimestral` | Confirmado | — |
| Secciones / Aulas | Catálogo secciones | Admin, coord | `gestionar_secciones_aulas` | Confirmado | — |
| Asignación docente | Vincular docente–aula–curso | Admin, coord | `gestionar_asignaciones_docente` | Confirmado | Coord ve Notas por este permiso |
| Notas semanales | Registrar notas (formulario) | Docente, admin | `registrar_notas_semanales` | Confirmado | — |
| Notas semanales | Consulta institucional | Coord, directivo | `ver_notas_academicas` | Parcial | Solo lectura |
| Notas semanales | Descargar plantilla Excel | Docente, admin | `registrar_notas_semanales` | Confirmado | Por asignación + periodo |
| Notas semanales | Importar plantilla Excel | Docente, admin | `registrar_notas_semanales` | Confirmado | **Import curricular**; no SIAGIE |
| Asistencia | Registrar asistencia diaria | Docente, admin, coord | `registrar_asistencia_curricular` | Confirmado | — |
| Asistencia | Consultar | Varios | `ver_asistencia_curricular` | Confirmado | Solo lectura si aplica |
| Excel por aula | Descargar libro aula | Admin, coord | `descargar_excel_aula` | Confirmado | **Sin import** en UI/API |
| Pesos evaluación | Configurar pesos C/L/T | — | `configurar_pesos_evaluacion` | Fuera menú V1 | API activa; panel oculto |

---

## 7. Endpoints API relacionados

Prefijo: `/api/curricular`. Auth: `auth:sanctum` + permiso. Detalle ampliado: [`docs/api.md`](api.md) §10.

| Método | Ruta | Propósito | Permiso | Controlador | Estado |
|--------|------|-----------|---------|-------------|--------|
| GET | `/notas-semanales/formulario` | Datos para registro | `registrar_notas_semanales` OR `ver_notas_academicas` | `NotaSemanalController` | Confirmado |
| POST | `/notas-semanales/bulk` | Guardar notas | `registrar_notas_semanales` | `NotaSemanalController` | Confirmado |
| GET | `/notas-semanales/plantilla-excel` | Descarga plantilla curso | idem (reglas asignación) | `NotaSemanalController` | Confirmado |
| POST | `/notas-semanales/importar-excel` | Import plantilla curso | `registrar_notas_semanales` | `NotaSemanalController` | Confirmado |
| GET | `/excel-aula` | Descarga Excel aula completo | `descargar_excel_aula` | `NotaSemanalController` | Confirmado |
| GET | `/asistencias-diarias/formulario` | Listado asistencia | `registrar_*` OR `ver_asistencia_curricular` | `AsistenciaDiariaController` | Confirmado |
| POST | `/asistencias-diarias/bulk` | Guardar asistencia | `registrar_asistencia_curricular` | `AsistenciaDiariaController` | Confirmado |
| GET | `/docente/aulas-cursos` | Asignaciones docente | `registrar_notas_semanales` | `DocenteAulaCurricularController` | Confirmado |
| GET | `/mallas/grado` | Materializar malla | `ver_malla_curricular` | `MallaCurricularController` | Confirmado |
| POST | `/asignaciones-docente/*` | CRUD asignaciones | `gestionar_asignaciones_docente` | `AsignacionDocenteController` | Confirmado |
| GET/POST | `/evaluacion-bimestral/*` | Config y notas bimestrales | `configurar_*` / `registrar_*` / `ver_*` | `EvaluacionBimestralController` | Confirmado |

**No existe:** endpoint de importación SIAGIE global.

---

## 8. Modelo de datos relacionado

Modelos Eloquent confirmados en [`backend/app/Models/Curricular/`](../backend/app/Models/Curricular/):

| Entidad | Modelo / tabla | Relación principal |
|---------|------------------|-------------------|
| Año escolar | `AnioEscolar` | Contiene periodos |
| Periodo / bimestre | `PeriodoAcademico` | Semanas académicas |
| Malla | `MallaCurricular`, `MallaCurso` | Cursos por grado |
| Tema semanal | `TemaSemanal` | → `MallaCurso`, periodo, semana |
| Asignación | `DocenteCursoAula` | User ↔ malla_curso ↔ sección |
| Nota semanal | `NotaSemanal`, `NotaSemanalComponente` | Estudiante ↔ tema |
| Asistencia | `AsistenciaDiaria` | Estudiante ↔ fecha ↔ estado |
| Sección | `SeccionAula` | Catálogo por nivel/grado |
| Eval. bimestral | `EvalBimComponente`, `EvalBimResultado`, etc. | Por curso y periodo |

Puente grado legacy: `EquivalenciaGrado` (`equivalencias_grado`). Detalle de columnas: ver [`modulo-curricular-academico.md`](analisis/modulo-curricular-academico.md) §16 (diseño); validar migraciones si se requiere campo a campo.

---

## 9. Registro de notas semanales

### Qué registra

Calificaciones por **estudiante**, **tema semanal** y **componentes** activos (modo legacy C/L/T/CE o componentes dinámicos según configuración bimestral).

### Por qué es curricular/semanal

Sustituye en UI V1 al flujo legacy de notas masivas; se ancla a malla, tema, periodo y asignación docente.

### Contexto

- **Año escolar**, **periodo (bimestre)**, **nivel**, **grado**, **sección** (sede Chilca).
- **Curso** vía asignación docente o consulta global (coordinación/admin).
- **Tema semanal** único por curso + bimestre + semana.

### Roles

| Rol | Registro | Consulta |
|-----|----------|----------|
| Docente | Sí (sus asignaciones) | Sí |
| Administrador | Sí (incl. consulta global) | Sí |
| Coordinador | **No** (`registrar_notas_semanales` ausente en seed) | Sí (solo lectura) |
| Directivo | **No** | Sí (visualización institucional) |

### Validaciones confirmadas

- Permiso y asignación activa en backend (`bulk`, `importarExcel`).
- Rangos y componentes según formulario API (tests en `NotasSemanales*`, `PlantillaRegistroAuxiliarExcelTest`).
- Coordinador/directivo: `modoConsultaGlobalSoloLectura` en [`RegistroNotasSemanalesPanel.jsx`](../frontend/src/components/curricular/RegistroNotasSemanalesPanel.jsx).

### Limitaciones

- Sin import SIAGIE.
- Pesos C/L/T ocultos en menú (resolver backend puede seguir activo).
- Evaluación bimestral y componentes dinámicos añaden complejidad — ver tests `EvaluacionBimestral*`.

---

## 10. Registro de asistencia curricular

### Qué registra

Estado de asistencia **por estudiante y fecha** para un contexto de aula (nivel, grado, sección, sede Chilca).

### Estados confirmados (frontend)

`presente`, `tarde`, `falta`, `justificado` — [`asistenciaUtils.js`](../frontend/src/components/curricular/asistencia/asistenciaUtils.js); el formulario API puede devolver `estados_permitidos`.

### Roles

- **Registro:** docente (aulas asignadas), administrador, coordinador (modo global).
- **Consulta:** roles con `ver_asistencia_curricular` (p. ej. psicólogo — solo lectura).

### Limitaciones

- No equivale al RF-02 «asistencia semanal» legacy por lote (API legacy sin menú).
- Requiere calendario activo y contexto completo (año, nivel, grado, sección, fecha).

---

## 11. Excel aula / registro auxiliar

### Dos flujos distintos (no confundir)

| Flujo | API | UI | Descarga | Importación | Alcance |
|-------|-----|-----|----------|-------------|---------|
| **Excel por aula** | `GET /excel-aula` | **Excel por aula** | Sí | **No** | Todo el grado/sección/bimestre — hoja «Estudiantes» + una hoja por curso activo |
| **Plantilla registro auxiliar** | `GET /plantilla-excel`, `POST /importar-excel` | **Notas semanales** (toolbar) | Sí | **Sí** (curricular) | **Un curso** / asignación + periodo |

### Excel por aula — evidencia

- Servicio: `PlantillaRegistroAuxiliarExcelService::generarExcelAula()`.
- Controlador: `NotaSemanalController::excelAula()` → `generarSinDatos`.
- UI: [`ExcelPorAulaPanel.jsx`](../frontend/src/components/curricular/ExcelPorAulaPanel.jsx) — texto: *«Modo: plantilla sin datos (40 filas…)»*, sede Chilca.
- Contenido (tests): hoja `Estudiantes`, hojas por curso, fórmulas hacia estudiantes, columnas de componentes bimestrales activos — [`ExcelAulaTest.php`](../backend/tests/Feature/Curricular/ExcelAulaTest.php).

### Plantilla registro auxiliar — evidencia

- Servicio generación: `PlantillaRegistroAuxiliarExcelService::generar()`.
- Import: `ImportPlantillaRegistroAuxiliarService` vía `importarExcel`.
- UI: botones **Descargar plantilla Excel** e **Importar** en [`RegistroNotasToolbar.jsx`](../frontend/src/components/curricular/notas/RegistroNotasToolbar.jsx).
- Modos (tests): legacy C/L/T/CE o columnas dinámicas por componentes — [`PlantillaRegistroAuxiliarExcelTest.php`](../backend/tests/Feature/Curricular/PlantillaRegistroAuxiliarExcelTest.php).

### Pruebas y memoria

| Test | Resultado conocido (Fase 1) | Limitación |
|------|----------------------------|------------|
| `ExcelAulaTest` | **8 passed**, 32 assertions con `memory_limit=512M` | Suite global **OOM @ 128M** en misma clase |
| `PlantillaRegistroAuxiliarExcelTest` | Detectado en repo | Posible demanda alta de memoria; no re-ejecutado Fase 6 |

### Qué no es

- **No** es importación SIAGIE institucional.
- **No** reemplaza oficialmente SIAGIE en documentación V1.
- Excel por aula **no** tiene endpoint de importación.

---

## 12. Relación con riesgo académico

- Las **notas semanales** y **asistencia curricular** pueden alimentar agregados académicos usados por servicios de riesgo en backend ([`RiesgoAcademicoService`](../backend/app/Services/RiesgoAcademicoService.php)) — **sin modificar Flask** en esta fase documental.
- **No** afirmar procesamiento automático continuo: el disparo explícito `POST …/procesar-riesgo` existe en API; la **UI de perfil de riesgo está en pausa** ([`EstudiantePerfilRiesgo.jsx`](../frontend/src/components/estudiantes/EstudiantePerfilRiesgo.jsx)).
- Procesamiento masivo post-seed es operación técnica (`demo:procesar-riesgos`), no flujo de usuario de aula/notas.

---

## 13. Reglas de sede en este módulo

| Regla | Detalle |
|-------|---------|
| V1 Chilca | Filtros y payloads usan `SEDE_OPERATIVA = 'chilca'` en frontend; backend `SedeOperativa::defaultConsulta()` |
| Sin selector | No hay combo de sede visible para el usuario |
| Campo `sede` | Persistido en estudiantes, asignaciones y consultas |
| Auquimarca | Puede existir en BD local auditada; **no** operación V1 documentada |

---

## 14. Pruebas asociadas

| Test | Qué valida | Resultado conocido | Limitación |
|------|------------|-------------------|------------|
| `ExcelAulaTest` | Descarga `/excel-aula`, 403 roles, estructura hojas, componentes | 8 passed @ **512M** (Fase 1) | OOM @ 128M en suite completa |
| `PlantillaRegistroAuxiliarExcelTest` | Plantilla legacy/dinámica, import | Detectado — no re-ejecutado Fase 6 | Archivo extenso |
| `AsistenciaDiariaTest` | Asistencia curricular API | Detectado | — |
| `NotasSemanalesInicialTest` | Notas nivel inicial | Detectado | — |
| `NotasSemanalesComponentesDinamicosTest` | Componentes dinámicos | Detectado | — |
| `CurricularApiTest` | Rutas curriculares 401/403 | Detectado | Cobertura parcial |
| `AsignacionDocenteValidacionesTest` | Asignaciones | Detectado | — |
| `EvaluacionBimestralApiTest` | Eval bimestral | Detectado | — |

Referencia: [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md), [`docs/matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md).

---

## 15. Limitaciones y pendientes

| Tema | Estado |
|------|--------|
| Importación **SIAGIE** | **Fuera del alcance actual** |
| Excel por aula — import | **No existe** |
| Import plantilla curso | Confirmado — **no** equivale a SIAGIE |
| Cypress / E2E | No existe |
| Seed oficial de referencia | Pendiente — conteos BD varían |
| Suite PHPUnit global | OOM **128M**; usar **512M** para Excel |
| UI riesgo en perfil | Pausada |
| Multi-sede operativa | Fuera V1 |
| Pesos C/L/T en menú | Ocultos (`visible: false`) |
| Legacy materias/notas lote | API sí; menú no |
| Variables socioeconómicas | API legacy; **retiradas del flujo de riesgo** (RF-05 v2.1) |

---

## 16. Criterios para documentación futura / DRS

Al actualizar el DRS:

1. **RF-01:** implementado parcialmente — curricular RF-21–RF-35 + plantilla Excel RF-32; **SIAGIE fuera del alcance**.
2. **RF-02 / RF-31:** vincular a **asistencia curricular** (`/asistencias-diarias/*`).
3. **RF-16:** distinguir PDF dashboard (parcial) de zona **reportes de riesgo** planificada y de **Excel por aula** RF-33.
4. **RF-20:** historial evolutivo por periodo — planificado; persistencia parcial.
5. Nombrar: **«Plantilla curricular propia»** vs **«SIAGIE fuera del alcance actual»**.
6. Documentar sede única **Chilca** en operación V1.

---

## Documentación relacionada

| Documento | Uso |
|-----------|-----|
| [`docs/manual-usuario.md`](manual-usuario.md) | Uso por rol |
| [`docs/api.md`](api.md) | Catálogo endpoints |
| [`docs/analisis/modulo-curricular-academico.md`](analisis/modulo-curricular-academico.md) | Diseño detallado y modelo BD |
| [`docs/matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) | Trazabilidad RF |
| [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) | Estado de pruebas |

---

*Documento generado en Fase 6 del plan de actualización documental SIDERAE-Blenkir.*
