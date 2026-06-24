# Matriz RF–Sprint–Test — SIDERAE-Blenkir

Documento vigente (Fase 9 documental — DRS v2.1). Fecha de verificación en código: **2026-06-09**. Cubre **RF-01 a RF-35**.

Referencias cruzadas: [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) · [`docs/limitaciones.md`](limitaciones.md) · [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md) · [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) · [`docs/aula-notas-excel.md`](aula-notas-excel.md) · [`docs/calidad/alineacion-iso.md`](calidad/alineacion-iso.md) · [`docs/arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md).

---

## 1. Propósito

Esta matriz relaciona los **requerimientos funcionales (RF-01–RF-35)** del DRS formal con:

- Sprints de planificación del repositorio (`sprints/`),
- Evidencia de **código y rutas** (`backend/routes/api.php`, UI en `frontend/src/App.jsx`),
- **Pruebas automatizadas** detectadas en `backend/tests/`,
- Estado honesto **V1** (prototipo académico, sede operativa Chilca).

Sirve para trazabilidad académica y como insumo para actualizar el DRS separando alcance formal de implementación real.

---

## 2. Criterios de estado

| Estado | Significado |
|--------|-------------|
| **Confirmado en código** | Rutas, UI o tests demuestran la funcionalidad principal del RF en V1. |
| **Implementado parcialmente** | Solo parte de los REQ del DRS está operativa o visible. |
| **Pendiente** | Definido en DRS/sprints; sin equivalencia suficiente en código. |
| **No confirmado** | Mencionado en documentación; sin verificación en esta revisión. |
| **Planeado/no encontrado** | Documento de prueba o sprint lo prevé; archivo o suite **no existe** en repo. |
| **Histórico** | Planificación o matriz anterior; no refleja solo el estado V1. |
| **Retirado del alcance** | Requisito histórico o DRS v1; **no** vigente en alcance V2.1. |
| **Planificado** | Definido en DRS v2.1; pendiente de implementación en código. |

**Resultado de prueba:** solo se documenta ejecución **conocida** (Fase 1). No usar «aprobado» sin evidencia de corrida completa.

---

## 3. Fuentes utilizadas

| Ruta | Uso |
|------|-----|
| `DRS_SIDERAE_Blenkir_v1.pdf` | **No presente en el repositorio** — nombres RF vía [`contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) |
| [`docs/limitaciones.md`](limitaciones.md) | Alcance V1 vs DRS |
| [`docs/pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) | Comandos y conteos Fase 1 |
| [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md) | Permisos, 401/403 |
| [`docs/manual-usuario.md`](manual-usuario.md) | Flujos visibles por rol |
| [`docs/api.md`](api.md) | Catálogo endpoints |
| [`backend/routes/api.php`](../backend/routes/api.php) | Evidencia rutas |
| [`backend/tests/`](../backend/tests/) | Pruebas PHPUnit |
| [`frontend/src/App.jsx`](../frontend/src/App.jsx) | Módulos UI |
| [`ml-service/main.py`](../ml-service/main.py) | ML determinístico |
| [`docker-compose.yml`](../docker-compose.yml) | Infra local |
| `sprints/sprint 1.md` … `sprints/sprint 10.md` (+ sub-sprints 3A/B, 6A/B, 7.x, 8.5x) | Planificación |

---

## 4. Advertencias de lectura

- **V1 Chilca:** operación documentada con sede única; sin selector multi-sede en UI.
- **Auquimarca:** registros en BD local auditada = **histórico/local**; no operación multi-sede V1.
- **BD auditada Fase 1:** conteos **no** equivalen a seed oficial limpio (`migrate:fresh --seed` no ejecutado en Fase 1).
- **Suite PHPUnit completa:** falló por **OOM** con `memory_limit=128M` en `ExcelAulaTest` (Fase 1).
- **`ExcelAulaTest` aislado:** **8 passed**, 32 assertions con `memory_limit=512M` (Fase 1).
- **Cypress:** existe infraestructura global parcial (auth/logout + smoke RF-04); no existe suite E2E completa del sistema.
- **SIAGIE:** **fuera del alcance actual** — plantillas Excel propias (RF-32).
- **Fast Test (RF-03):** **retirado del alcance vigente**.
- **VSE (RF-05):** **retiradas del flujo de riesgo**; API legacy puede existir.
- **Comunicación familiar (RF-12):** **eliminada del alcance**.
- **ML:** servicio Flask **determinístico**; RF-18 reentrenamiento **planificado**, no implementado.
- **ISO:** referencia académica orientativa únicamente; **sin certificación**.

---

## 5. Matriz principal RF–Sprint–Código–Prueba

Nombres RF según DRS (tabla §3 de [`contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md)).

| RF | Nombre / funcionalidad | Sprint relacionado | Estado funcional V1 | Evidencia código/ruta | Evidencia UI/manual | Prueba automatizada | Resultado conocido | Observación |
|----|------------------------|-------------------|---------------------|----------------------|---------------------|---------------------|-------------------|-------------|
| RF-01 | Carga e importación de datos académicos | 3B, 7.6B, 8.5B | Implementado parcialmente | `/api/curricular/notas-semanales/*`, `importar-excel`, `GET /excel-aula`, legacy API | Notas/asistencia curricular; plantilla Excel propia (import); Excel aula (solo descarga) | `DatosAcademicosTest`, `PlantillaRegistroAuxiliarExcelTest`, `CurricularApiTest`, `NotasSemanales*`, `ExcelAulaTest` | Parcial — `ExcelAulaTest` 8 passed @ 512M | **SIAGIE fuera del alcance actual**; ver RF-21–RF-35 y [`aula-notas-excel.md`](aula-notas-excel.md) |
| RF-02 | Registro digital de asistencia semanal | 3B, 8.5B | Confirmado en código (curricular) | `/api/curricular/asistencias-diarias/*` | Menú **Asistencia** | `AsistenciaDiariaTest` | No ejecutado Fase 5 | Ver RF-31 |
| RF-03 | Importación resultados Fast Test | — | **Retirado del alcance** | — | No aplica | — | — | Institución no utiliza Fast Test; referencia histórica DRS v1 |
| RF-04 | Registro reportes conductuales | AI-DLC 2B–2F | **Implementado V1 mínimo** | API + permisos + `EstudiantePerfilReportesConductuales.jsx` | Perfil estudiante → **Reportes conductuales** | `ReporteConductualTest`; Cypress smoke RF-04 | PHPUnit: **8 passed**, 26 assert. (2E); Cypress verificado, spec detenido por falta de `CYPRESS_E2E_EMAIL` (2F) | Fases 2B–2F; Cypress mínimo RF-04 configurado; sin módulo global |
| RF-05 | Integración variables socioeconómicas | 3B | **Retirado del flujo de riesgo** | API legacy `/variables-socioeconomicas` | UI pausada | `DatosAcademicosTest` (API legacy) | Parcial | No insumo obligatorio de RF-06 |
| RF-06 | Procesamiento multivariable e índice de riesgo | 4, 8.5B | **RF-06E: Implementado V1 corregido y enriquecido** | `POST …/procesar-riesgo`, `MlRiskService` | Perfil riesgo **en pausa** | `RiesgoTest` (38 passed, 125 assert.) + `SemaforoCompletitudTest` (11) + `HistorialRiesgoTest` (12) = 61 tests, 210 assert. | **RF-06E cerrado V1** | ML **determinístico**; sin VSE ni Fast Test; mínimo: notas+asistencia; conducta opcional; payload enriquecido (nota_min, cursos_riesgo/desap, inasistencias, recientes, graves, gravedad, reincidencia); pesos 55/30/15; Flask validado 5 escenarios. ML real (RF-18) pendiente |
| RF-07 | Evaluación automática nivel de riesgo | 4, 5 | Confirmado en código (parcial DRS) | Umbrales en servicio riesgo | Dashboard KPIs riesgo | `RiesgoTest` | No re-ejecutado Fase 5 | REQ configurables admin: pendiente |
| RF-08 | Emisión alertas tempranas | 5 | Confirmado en código | `/api/alertas` | **Alertas** | `RiesgoTest`, `AlertaIntervencionTest` | No re-ejecutado Fase 5 | — |
| RF-09 | Intervención preventiva docente | 5 | Confirmado en código | `POST /api/alertas/{id}/intervenciones` | Alertas → detalle | `AlertaIntervencionTest` | No re-ejecutado Fase 5 | — |
| RF-10 | Escalamiento directivo casos críticos | — | **Planificado** | Sin rutas API | No visible | — | — | Directivo solo casos críticos/extremos; no actor inicial de todas las alertas |
| RF-11 | Atención psicológica perfil integral | 5, 8 | Implementado parcialmente | Alertas + permisos psicólogo | Solo **Alertas** hoy | `AlertaIntervencionTest` (parcial) | — | **Planificado:** perfil integral lectura (notas, asistencia, riesgo, conductuales) |
| RF-12 | Comunicación formal con familia | — | **Eliminado del alcance** | Esquema BD histórico | No aplica | — | — | Gestión fuera del sistema |
| RF-13 | Registro acción y cierre alerta | 5 | Implementado parcialmente | `POST …/cerrar` | Alertas | `AlertaIntervencionTest` | No re-ejecutado Fase 5 | Cierre **solo por intervención**; sin comunicación familiar |
| RF-14 | Dashboard académico e institucional | 6A, 6B, 7A, RF-14B | **Parcial / en avance** | `GET /api/dashboard` existente (riesgo subset). Permiso `ver_dashboard_institucional` implementado en seeder (RF-14B). `ver_dashboard` se mantiene | **Dashboard** (riesgo subset) | `DashboardTest` (11 tests definidos) | RF-14B ejecutado | Base RBAC institucional implementada. Pendiente: endpoint `GET /api/dashboard/institucional`, controller, UI institucional, tests extendidos, smoke manual. Sin PDF/exportación nuevos. Sin selector de sede |
| RF-15 | Gestión usuarios y control acceso | 2, 8 | Confirmado en código | `/api/usuarios`, Spatie (**23 permisos** en seeder) | **Usuarios** (admin) | `GestionUsuariosTest`, `AuthenticationTest`; Cypress auth/logout | Parcial 401; Cypress 2H: 2 auth tests passed, login/logout E2E pendiente por credenciales | [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) |
| RF-16 | Generación reportes de riesgo académico | 6B, RF-16E | **Implementado V1 con smoke manual pendiente** | Backend: `GET /api/reportes/riesgo-academico` paginado + filtros + `ReporteRiesgoAcademicoController`. Frontend: `ReporteRiesgoAcademicoPanel.jsx` + `App.jsx` menú "Reportes de riesgo". Permiso `ver_reportes_riesgo` | Menú lateral **Reportes de riesgo** | `ReporteRiesgoAcademicoTest` 13 passed (36 assertions); regresión RF-06/RF-19/RF-20 (61 tests); build frontend OK; lint preexistente 88 problemas; smoke manual navegador pendiente | RF-16E ejecutado | Backend+frontend+permiso implementados. Smoke manual pendiente. PDF/exportación (`generar_reportes_riesgo`) fuera de V1. No recalcula riesgo ni llama Flask |
| RF-17 | Log auditoría / trazabilidad | 7.5A, 8 | Implementado parcialmente | `activity_log` | Sin UI consulta | `ActivityLogTest` | No re-ejecutado Fase 5 | Apoya alineación ISO progresiva; sin certificación |
| RF-18 | Reentrenamiento modelo ML | — | **Planificado** | Sin endpoint ML/Laravel | No visible | — | — | ML real cuando exista dataset; no implementado |
| RF-19 | Semáforo completitud datos | AI-DLC 3B–3E | **Implementado V1** | `GET /api/estudiantes/{estudiante}/semaforo-completitud`, `CompletitudDatosService`, `EstudiantePerfilSemaforoCompletitud.jsx` | Perfil estudiante → **Completitud de datos** | `SemaforoCompletitudTest.php` | **11 passed**, 55 assertions (2026-06-23) | Backend Fase 3C; UI Fase 3D; build OK; **smoke manual navegador aprobado**; Cypress no ejecutado |
| RF-20 | Historial riesgo evolutivo | 4, 6A | **Implementado V1** | Tabla `indices_riesgo`; permiso `ver_historial_riesgo`; endpoint `GET /api/estudiantes/{estudiante}/historial-riesgo`; componente `EstudiantePerfilHistorialRiesgo.jsx` | Perfil estudiante → **Historial de riesgo académico** | `HistorialRiesgoTest.php` 12 passed, 30 assertions; build frontend OK | Fases 4B–4E | Backend + frontend V1 implementados; **smoke manual navegador pendiente**; Cypress global no ejecutado |

### 5.1 Módulo curricular — RF-21 a RF-35

| RF | Nombre | Sprint | Estado V1 | Evidencia código | UI | Tests | Observación |
|----|--------|--------|-----------|------------------|-----|-------|-------------|
| RF-21 | Gestión periodos académicos | 8.5A | Confirmado | `/api/curricular/periodos-academicos/*` | Periodos académicos | `PeriodoAcademicoTest`, `CurricularApiTest` | — |
| RF-22 | Gestión malla curricular | 8.5A/B | Confirmado | `/api/curricular/malla/*` | Malla curricular | `MallaCurricularTest` | — |
| RF-23 | Competencias y capacidades | 8.5A | Confirmado | `/api/curricular/competencias/*`, capacidades | Paneles curriculares | `CompetenciaCapacidadCrudTest` | — |
| RF-24 | Criterios/temas semanales | 8.5B | Confirmado | `/api/curricular/criterios-semanales/*` | Criterios semanales | Tests curriculares | — |
| RF-25 | Componentes calificación | 8.5B | Confirmado | `/api/curricular/componentes-calificacion/*` | Componentes | `ComponentesCalificacionNivelTest` | — |
| RF-26 | Configuración bimestral | 8.5C | Confirmado | `/api/curricular/configuracion-bimestral/*` | Config bimestral | `ConfiguracionBimestral*Test` | — |
| RF-27 | Secciones y aulas | 8.5B | Confirmado | `/api/curricular/secciones-aulas/*` | Secciones/aulas | `SeccionesAulasTest` | — |
| RF-28 | Asignación docente | 8.5B | Confirmado | `/api/curricular/asignaciones-docente/*` | Asignación docente | `AsignacionDocenteValidacionesTest` | — |
| RF-29 | Notas semanales curriculares | 8.5B | Confirmado | `/api/curricular/notas-semanales/*` | Notas semanales | `NotasSemanales*` | — |
| RF-30 | Consulta institucional notas | 8.5B | Parcial según rol | GET curriculares + permisos | Según rol | Tests 403 curricular | Directivo excepción UI |
| RF-31 | Asistencia curricular diaria | 8.5B | Confirmado | `/api/curricular/asistencias-diarias/*` | Asistencia | `AsistenciaDiariaTest` | = RF-02 flujo curricular |
| RF-32 | Plantilla Excel curricular | 8.5B | Confirmado | `plantilla-excel`, `importar-excel` | Notas semanales toolbar | `PlantillaRegistroAuxiliarExcelTest` | Sustituye SIAGIE en alcance |
| RF-33 | Excel por aula multi-hoja | 8.5B+ | Confirmado | `GET /excel-aula` | Excel por aula | `ExcelAulaTest` | Solo descarga |
| RF-34 | Evaluación bimestral | 8.5C | Confirmado | Endpoints eval bimestral | Flujo bimestral | `EvaluacionBimestral*Test` | — |
| RF-35 | Resumen académico estudiante | 8.5C | Parcial según UI | Resumen endpoints | Perfil/resumen | `ResumenAcademicoTest` | — |

---

## 6. Matriz por Sprint

Incluye sub-sprints documentados en `sprints/` vinculados al sprint principal. Estado = implementación + tests **detectados**, no cierre formal de sprint.

| Sprint | Objetivo documentado | Funcionalidad asociada | Evidencia implementación | Tests asociados | Estado | Observación |
|--------|----------------------|------------------------|--------------------------|-----------------|--------|-------------|
| **1** | Docker + health checks | Compose 4 servicios, `/api/health`, ML `/` | `docker-compose.yml`, `api.php` L34–38 | `ExampleTest` | Confirmado en código | Healthcheck solo MySQL; backend/ML sin healthcheck Compose |
| **2** | Login + `/api/me` + RBAC mínimo | Sanctum, Breeze, Spatie seed | `auth.php`, `AuthContext`, seeders | `AuthenticationTest`, `RegistrationTest`, `EmailVerificationTest`, `PasswordResetTest` | Confirmado en código | `POST /register` público — brecha prototipo |
| **3A** | CRUD estudiantes + perfil | Estudiantes API/UI | `EstudianteController`, `EstudiantesPanel` | `EstudianteTest`, `EstudianteInicialTest` | Confirmado en código | V1 Chilca default |
| **3B** | Datos base + validaciones | Notas/asistencia legacy, VSE API | Rutas legacy L98–111 | `DatosAcademicosTest` | Implementado parcialmente | Legacy sin menú V1 |
| **4** | Laravel → ML + persistencia riesgo | `procesar-riesgo`, Flask | `MlRiskService`, `RiesgoTest` | `RiesgoTest`, `DemoProcesarRiesgosCommandTest` | Implementado parcialmente | UI procesar riesgo ausente |
| **5** | Alertas + intervención + cierre | Alertas API/UI | `AlertaController`, `AlertasPanel` | `AlertaIntervencionTest` | Confirmado en código | Sin derivación RF-10 |
| **6A** | Dashboard mínimo KPIs | Dashboard API/UI | `DashboardController`, `DashboardPanel` | `DashboardTest` | Implementado parcialmente | Subset RF-14 |
| **6B** | Filtros + export PDF + rol | Export dashboard | `GET /api/dashboard/export` | `DashboardTest` | Implementado parcialmente | Parcial RF-16 |
| **7A** | Rediseño UI/UX | Layout, sidebar, tokens | `AppLayout`, `Sidebar`, `App.jsx` | — | Confirmado en código | Sin tests E2E |
| **7B** | Pantallas según mockups | Paneles módulos | `frontend/src/components/*` | — | Implementado parcialmente | Mockups legacy ≠ curricular completo |
| **7.5A/B** | Correcciones P0 / visual | Fixes funcionales | Código disperso | Tests regresión existentes | Parcial | Documentado en sprints |
| **7.6A/B** | Materias admin + registro masivo | Legacy materias/lotes | API; UI sin menú | `MateriaTest`, `DatosAcademicosTest` | Implementado parcialmente | Fuera flujo visible V1 |
| **8** | Seguridad RBAC + 401/403 | Middleware `permission:*` | `api.php`, `PermissionsSeeder` | `DashboardTest`, `EstudianteTest`, `GestionUsuariosTest`, `CurricularApiTest`, etc. | Implementado parcialmente | Matriz vigente: `seguridad-roles-permisos.md`; Sprint 8 matriz: histórico |
| **8.5A** | Backend curricular + seeders | `/api/curricular/*`, migraciones | Rutas L128–282 | `CurricularSeedersTest`, `CurricularApiTest` | Confirmado en código | — |
| **8.5B** | API/UI curricular + asignación | Notas, asistencia, malla UI | Paneles `curricular/*` | `AsistenciaDiariaTest`, `AsignacionDocenteValidacionesTest`, `NotasSemanales*` | Confirmado en código | — |
| **8.5C** | Evaluación bimestral | Config bimestral, CE | `EvaluacionBimestral*` | `EvaluacionBimestralApiTest`, `EvaluacionBimestralTest` | Confirmado en código | — |
| **AI-DLC 3B** | RF-19 permisos base | `ver_semaforo_completitud` en `PermissionsSeeder.php` | Seeder actualizado | — | Completado | Asignado a `administrador`, `docente`, `coordinador_academico` |
| **AI-DLC 3C** | RF-19 backend semáforo | Endpoint + servicio + tests | `CompletitudDatosService`, `SemaforoCompletitudController`, ruta API | `SemaforoCompletitudTest` — 11 passed | Completado | Sin Flask; no recalcula riesgo; sede Chilca |
| **AI-DLC 3D** | RF-19 frontend semáforo | Componente perfil estudiante | `EstudiantePerfilSemaforoCompletitud.jsx`, `api.js` | Build frontend OK | Completado | UI bloque junto a riesgo; permiso `ver_semaforo_completitud` |
| **AI-DLC 3E** | RF-19 cierre V1 | Validaciones + documentación + smoke manual | Tests backend + build frontend + docs + smoke | `SemaforoCompletitudTest` 11 passed; build OK; smoke manual aprobado | Completado | Cypress no ejecutado |
| **AI-DLC 4B** | RF-20 permisos base | `ver_historial_riesgo` en `PermissionsSeeder.php`; documentación seguridad | Seeder actualizado; docs actualizados | — | Completado | Asignado a `administrador`, `docente`, `coordinador_academico`; sin API ni UI aún |
| **AI-DLC 4C** | RF-20 backend historial | Endpoint + controller + tests | `HistorialRiesgoController`, ruta API | `HistorialRiesgoTest.php` | Completado | Sin Flask; no recalcula riesgo; sede Chilca; filtros `anio_escolar` y `bimestre` |
| **AI-DLC 4D** | RF-20 frontend historial | Componente perfil estudiante | `EstudiantePerfilHistorialRiesgo.jsx`, `getHistorialRiesgo()` | Build frontend OK | Completado | UI tabla simple; permiso `ver_historial_riesgo`; error aislado; sin gráficos |
| **AI-DLC 4E** | RF-20 cierre V1 | Validaciones + documentación + smoke manual | Tests backend + build frontend + docs | `HistorialRiesgoTest` 12 passed; build OK; smoke manual pendiente | Completado | Cypress global no ejecutado |
| **9** | Pruebas integrales + regresión | Campaña pytest + Cypress planeado | Cypress infraestructura 2H en `frontend/cypress/` | Ejecución Fase 1 documentada; Cypress 2H ejecutado parcialmente sin credenciales | Implementado parcialmente | No hay suite Cypress global; suite PHPUnit OOM 128M |
| **10** | Documentación + cierre calidad | Manuales, matriz RF, informe | `docs/*` Fases 1–5 | Informe consolidado Fase 5 | En progreso | ISO solo referencia; sin certificación |

---

## 7. Matriz de pruebas backend detectadas

Archivos en [`backend/tests/`](../backend/tests/) (49 archivos `.php` detectados). **Resultado conocido** solo si Fase 1 lo registró.

### Auth

| Archivo test | Casos relevantes | RF | Módulo | Estado | Observación |
|--------------|------------------|-----|--------|--------|-------------|
| `Feature/Auth/AuthenticationTest.php` | login, logout | RF-15 | Auth | Detectado | No re-ejecutado Fase 5 |
| `Feature/Auth/RegistrationTest.php` | registro público | RF-15 | Auth | Detectado | Brecha producción |
| `Feature/Auth/EmailVerificationTest.php` | verificación email | RF-15 | Auth | Detectado | — |
| `Feature/Auth/PasswordResetTest.php` | reset password | RF-15 | Auth | Detectado | UI recuperación pendiente |

### Estudiantes y académico legacy

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `Feature/EstudianteTest.php` | RF-01, RF-15 | Estudiantes | Detectado | 401/403 confirmados en doc seguridad |
| `Feature/EstudianteInicialTest.php` | RF-01 | Estudiantes inicial | Detectado | — |
| `Feature/DatosAcademicosTest.php` | RF-01, RF-05 | Legacy notas/asist/VSE | Detectado | API legacy |
| `Feature/MateriaTest.php` | RF-01 | Materias legacy | Detectado | Sin menú V1 |

### Dashboard, riesgo, alertas, completitud

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `Feature/DashboardTest.php` | RF-14, RF-16 | Dashboard | Detectado | Export PDF |
| `Feature/RiesgoTest.php` | RF-06, RF-07, RF-08, RF-20 | Riesgo | Detectado | — |
| `Feature/AlertaIntervencionTest.php` | RF-08, RF-09, RF-13 | Alertas | Detectado | — |
| `Feature/ReporteConductualTest.php` | RF-04 | Conductuales | Detectado | **8 passed**, 26 assertions (Fase 2E) |
| `Feature/SemaforoCompletitudTest.php` | RF-19 | Completitud datos | Detectado | **11 passed**, 55 assertions (Fase 3C) |
| `Feature/DemoProcesarRiesgosCommandTest.php` | RF-06 | Comando batch | Detectado | Operación técnica, no UI |
| `Feature/ActivityLogTest.php` | RF-17 | Auditoría | Detectado | Parcial |

### Usuarios y seguridad

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `Feature/GestionUsuariosTest.php` | RF-15 | Usuarios | Detectado | 403 sí; 401 pendiente |

### Curricular (`Feature/Curricular/`)

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `CurricularApiTest.php` | RF-01, RF-15 | API curricular | Detectado | 401/403 |
| `AsistenciaDiariaTest.php` | RF-02 | Asistencia | Detectado | — |
| `ExcelAulaTest.php` | RF-01, RF-16 | Excel aula | Detectado | **8 passed @ 512M** Fase 1; OOM @ 128M en suite |
| `PlantillaRegistroAuxiliarExcelTest.php` | RF-01 | Import plantilla | Detectado | Memoria elevada posible |
| `EvaluacionBimestralApiTest.php` | RF-01 | Eval bimestral | Detectado | — |
| `EvaluacionBimestralTest.php` | RF-01 | Eval bimestral | Detectado | — |
| `NotasSemanalesInicialTest.php` | RF-01 | Notas | Detectado | — |
| `NotasSemanalesComponentesDinamicosTest.php` | RF-01 | Notas dinámicas | Detectado | — |
| `AsignacionDocenteValidacionesTest.php` | RF-01 | Asignación | Detectado | — |
| `SeccionesAulasTest.php` | RF-01 | Secciones | Detectado | — |
| `CalendarioAcademicoTest.php` | RF-01 | Calendario | Detectado | — |
| `CompetenciaCapacidadCrudTest.php` | RF-01 | Competencias | Detectado | — |
| `ComponentesCalificacionNivelTest.php` | RF-01 | Componentes | Detectado | — |
| `ConfiguracionPesoEvaluacionTest.php` | RF-01 | Pesos (UI oculta) | Detectado | — |
| `ConfiguracionBimestralGradoTest.php` | RF-01 | Config bimestral | Detectado | — |
| `ConfiguracionBimestralDefaultsTest.php` | RF-01 | Config bimestral | Detectado | — |
| `ResumenAcademicoTest.php` | RF-01, RF-20 | Resumen | Detectado | — |
| `CurricularSeedersTest.php` | — | Seeders | Detectado | — |
| `ActivoUniqueKeyHistorialTest.php` | RF-20 | Historial | Detectado | — |

### Seeders y Unit

| Archivo test | RF | Estado | Observación |
|--------------|-----|--------|-------------|
| `Feature/Seeders/DemoEstudiantesCurricularesSeederTest.php` | RF-01 | Detectado | Solo Chilca V1 |
| `Feature/Seeders/DemoCurricularOperativoSeederTest.php` | RF-01 | Detectado | — |
| `Feature/Seeders/InicialIIBimestre2026SeederTest.php` | RF-01 | Detectado | — |
| `Feature/Seeders/CriteriosEvaluacionInicialSeederTest.php` | RF-01 | Detectado | — |
| `Unit/Curricular/*` | RF-01, RF-06 | Detectado | Servicios CE, pesos, equivalencias |
| `Feature/ExampleTest.php`, `Unit/ExampleTest.php` | — | Detectado | Smoke |

---

## 8. Pruebas planeadas pero no encontradas

| Caso / documento | RF | Archivo esperado | Estado | Observación |
|------------------|-----|------------------|--------|-------------|
| [`Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) | RF-01 | `ImportarDatosTest` | **Planeado/no encontrado** | Usar `PlantillaRegistroAuxiliarExcelTest`, `DatosAcademicosTest` |
| [`sprint 9.md`](../sprints/sprint%209.md) | Varios | Suite **Cypress** | **Parcial** | Existe Cypress mínimo RF-04; suite global planeada/no encontrada |
| Plan de pruebas / Sprint 9 | RF-01 | Importación **SIAGIE** | **Fuera del alcance** | Decisión alcance v2.1; plantilla RF-32 |
| Sprint 8 / seguridad | RF-15 | 401 en **todas** rutas `/api/curricular/*` | **Parcial** | Ver [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) §12 |
| DRS RF-03 | RF-03 | `FastTestImportTest` | **Retirado del alcance** | Referencia histórica DRS v1 |
| DRS RF-04 | RF-04 | Tests reportes conductuales | **Cerrado V1 mínimo** | `ReporteConductualTest.php` 8 passed; UI perfil; Fase 2E |
| DRS RF-10 | RF-10 | Tests escalamiento directivo | **Planificado** | — |
| DRS RF-12 | RF-12 | Tests comunicación familiar | **Eliminado del alcance** | — |
| DRS RF-18 | RF-18 | Tests reentrenamiento ML | **Planificado** | Requiere ML real |
| DRS RF-19 | RF-19 | Tests semáforo completitud | **Cerrado V1** | `SemaforoCompletitudTest.php` 11 passed; UI en perfil estudiante build OK; smoke manual aprobado |
| RNF-05 Jest frontend | — | Tests Jest/React | **No confirmado** | Sin suite frontend detectada |

---

## 9. Brechas de trazabilidad

| Brecha | RF afectado | Impacto | Recomendación | Prioridad |
|--------|-------------|---------|---------------|-----------|
| DRS PDF fuera del repo | Todos | Tribunal no contrasta desde repo | Mantener `contexto-drs-requerimientos.md` actualizado | Media |
| Brecha SIAGIE vs plantilla curricular | RF-01, RF-32 | Confusión documental | SIAGIE **fuera de alcance**; plantillas propias | Alta |
| RF-10, RF-16, RF-20 planificados; RF-04 V1 mínimo; RF-19 implementado V1 | RF-10–20 | Flujo riesgo incompleto (RF-04 cerrado perfil; RF-19 V1) | Backlog DRS v2.1 | Alta |
| RF-10–12 alcance | RF-10–13 | Cierre alerta vs DRS v1 | RF-12 eliminado; RF-10 planificado | Alta |
| Excel aula solo descarga; import solo plantilla curso | RF-01, RF-16 | Usuario espera import aula | Documentar en manual y DRS | Media |
| Cypress limitado a RF-04 | Varios UI | Sin E2E automatizado global | Ejecutar smoke RF-04 y mantener smoke manual por rol ([`manual-usuario.md`](manual-usuario.md)) | Media |
| Suite OOM 128M | RF-01, RF-16 | CI/local falla antes de terminar | `memory_limit=512M` o ajuste php.ini tests | Alta |
| RF-06–07 UI pausada | RF-06, RF-07, RF-20 | Usuario no procesa riesgo desde perfil | Alinear UI o documentar comando técnico | Media |
| RF-10–12 sin API | RF-10–13 | Escalamiento/cierre incompleto | RF-10/19 planificados; RF-04 V1 mínimo; RF-12 eliminado | Alta |
| Activity log parcial | RF-17 | Trazabilidad incompleta | Extender logging + tests | Media |
| Seed oficial no definido | RF-01 | Conteos demo inconsistentes | Entorno referencia `migrate:fresh --seed` | Media |
| 401/403 incompletos | RF-15 | Riesgo seguridad no medido | Ampliar Feature tests | Media |

---

## 10. Uso para actualización del DRS

El DRS v2.1 consolidado está en [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md). Esta matriz cubre **RF-01 a RF-35**.

2. Marcar como **retirado / fuera de alcance**: SIAGIE, Fast Test (RF-03), VSE en riesgo (RF-05), comunicación familiar (RF-12).
3. Marcar como **planificado**: RF-10, RF-16 (zona reportes), RF-18, RF-11 (perfil integral). **RF-04:** implementado V1 mínimo (Fase 2E). **RF-19:** implementado V1 (Fases 3B–3E); smoke manual navegador pendiente; Cypress no ejecutado. **RF-20:** **implementado V1** (Fases 4B–4E: RBAC, backend, frontend y cierre documental); **smoke manual navegador pendiente**; Cypress global no ejecutado.
4. **Consolidar** RF parciales (RF-14 dashboard académico-institucional; RF-16 PDF dashboard parcial).
5. RF-21–RF-35: módulo curricular confirmado en código según §5.1.

---

*Documento actualizado en Fase 9 — reestructuración RF V2.1 (DRS v2.1). RF-19 cerrado V1 Fase 3E — 2026-06-23.*
