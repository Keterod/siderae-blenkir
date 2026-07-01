# Auditoría de Implementación - SIDERAE Blenkir

**Proyecto:** Keterod/siderae-blenkir  
**Rama final:** `main`  
**Fecha de auditoría:** 2026-06-28  
**Hash HEAD main:** `6bdfcfd`

---

## RF04 - Reportes Conductuales

| Campo | Valor |
|-------|-------|
| **Nombre** | Reportes Conductuales |
| **Descripción** | Registro, listado y anulación de reportes conductuales por estudiante |
| **Rama desarrollo** | `feature/rf04-backend-api-reportes-conductuales` |
| **Commit inicial (planning)** | `0e429f9` — 2026-06-10 — feat: agregar permisos rf04 reportes conductuales |
| **Commit implementación** | `a01dddf` — 2026-06-10 — feat: cerrar rf04 reportes conductuales v1 minimo |
| **Commit más reciente** | `a01dddf` — 2026-06-10 |
| **Permisos** | `ver_reportes_conductuales`, `registrar_reportes_conductuales` |

### Archivos

| Archivo | Tipo | Clase/Componente | Líneas | Commit inicial | Hash commit | Fecha | URL |
|---------|------|------------------|--------|---------------|-------------|-------|-----|
| `backend/app/Models/ReporteConductual.php` | Backend | `ReporteConductual` | 1-49 | `8590b2f` | `a01dddf` | 2026-06-10 | [URL](https://github.com/Keterod/siderae-blenkir/blob/a01dddf/backend/app/Models/ReporteConductual.php#L1-L49) |
| `backend/app/Http/Controllers/Api/ReporteConductualController.php` | Backend | `ReporteConductualController` | 1-103 | `a01dddf` | `a01dddf` | 2026-06-10 | [URL](https://github.com/Keterod/siderae-blenkir/blob/a01dddf/backend/app/Http/Controllers/Api/ReporteConductualController.php#L1-L103) |
| `backend/app/Http/Requests/StoreReporteConductualRequest.php` | Backend | `StoreReporteConductualRequest` | 1-27 | `a01dddf` | `a01dddf` | 2026-06-10 | [URL](https://github.com/Keterod/siderae-blenkir/blob/a01dddf/backend/app/Http/Requests/StoreReporteConductualRequest.php#L1-L27) |
| `backend/database/seeders/PermissionsSeeder.php` | Backend | Permisos RF04 (líneas 42-45) | 42-45 | `0e429f9` | `0e429f9` | 2026-06-10 | [URL](https://github.com/Keterod/siderae-blenkir/blob/0e429f9/backend/database/seeders/PermissionsSeeder.php#L42-L45) |
| `backend/tests/Feature/ReporteConductualTest.php` | Backend | `ReporteConductualTest` | 1-242 | `a01dddf` | `a01dddf` | 2026-06-10 | [URL](https://github.com/Keterod/siderae-blenkir/blob/a01dddf/backend/tests/Feature/ReporteConductualTest.php#L1-L242) |
| `frontend/src/components/estudiantes/EstudiantePerfilReportesConductuales.jsx` | Frontend | `EstudiantePerfilReportesConductuales` | 1-401 | `a01dddf` | `a01dddf` | 2026-06-10 | [URL](https://github.com/Keterod/siderae-blenkir/blob/a01dddf/frontend/src/components/estudiantes/EstudiantePerfilReportesConductuales.jsx#L1-L401) |
| `frontend/src/lib/api.js` | Frontend | `getReportesConductuales`, `postReporteConductual`, `patchAnularReporteConductual` | 307-322 | `a01dddf` | `a01dddf` | 2026-06-10 | [URL](https://github.com/Keterod/siderae-blenkir/blob/a01dddf/frontend/src/lib/api.js#L307-L322) |

---

## RF06 - Riesgo Académico

| Campo | Valor |
|-------|-------|
| **Nombre** | Riesgo Académico |
| **Descripción** | Cálculo de índice de riesgo académico combinando notas, asistencia y reportes conductuales vía ML |
| **Rama desarrollo** | `feature/rf06-diagnostico-variables-riesgo` |
| **Commit inicial (planning)** | `8ec0f01` — 2026-06-23 — docs: diagnosticar variables rf06 riesgo |
| **Commit implementación** | `c66c496` — 2026-06-23 — feat: enriquecer calculo rf06 riesgo academico |
| **Commit cierre** | `588b105` — 2026-06-23 — docs: cerrar rf06 riesgo academico v1 |
| **Commit más reciente** | `c66c496` — 2026-06-23 |
| **Permisos** | `procesar_riesgo` (Sprint 2) |

### Archivos

| Archivo | Tipo | Clase/Componente | Líneas | Commit inicial | Hash commit | Fecha | URL |
|---------|------|------------------|--------|---------------|-------------|-------|-----|
| `backend/app/Services/RiesgoAcademicoService.php` | Backend | `RiesgoAcademicoService` | 1-318 | `65090e0f` | `c66c496` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/c66c496/backend/app/Services/RiesgoAcademicoService.php#L1-L318) |
| `backend/app/Models/IndiceRiesgo.php` | Backend | `IndiceRiesgo` | 1-54 | `8590b2f` | `abdfaf5` | 2026-04-29 | [URL](https://github.com/Keterod/siderae-blenkir/blob/abdfaf5/backend/app/Models/IndiceRiesgo.php#L1-L54) |
| `backend/app/Http/Controllers/Api/ProcesarRiesgoController.php` | Backend | `ProcesarRiesgoController::store` | 1-86 | `8590b2f` | `ef38acb` | 2026-05-30 | [URL](https://github.com/Keterod/siderae-blenkir/blob/ef38acb/backend/app/Http/Controllers/Api/ProcesarRiesgoController.php#L1-L86) |
| `backend/app/Services/MlRiskService.php` | Backend | `MlRiskService::predict` | 1-52 | `8590b2f` | `8590b2f` | 2026-04-29 | [URL](https://github.com/Keterod/siderae-blenkir/blob/8590b2f/backend/app/Services/MlRiskService.php#L1-L52) |
| `ml-service/main.py` | ML | `predict()` endpoint Flask | 27-107 | `32af7be` | `c66c496` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/c66c496/ml-service/main.py#L27-L107) |
| `backend/tests/Feature/RiesgoTest.php` | Backend | `RiesgoTest` | 1-400+ | `8590b2f` | `c66c496` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/c66c496/backend/tests/Feature/RiesgoTest.php#L1-L400) |
| `frontend/src/components/estudiantes/EstudiantePerfilRiesgo.jsx` | Frontend | `EstudiantePerfilRiesgo` | 1-180 | `8590b2f` | `408d6ff` | 2026-06-24 | [URL](https://github.com/Keterod/siderae-blenkir/blob/408d6ff/frontend/src/components/estudiantes/EstudiantePerfilRiesgo.jsx#L1-L180) |
| `backend/routes/commands/demo_procesar_riesgos.php` | Backend | Comando demo procesar riesgos | 1-115 | `65090e0f` | `65090e0f` | 2026-05-06 | [URL](https://github.com/Keterod/siderae-blenkir/blob/65090e0f/backend/routes/commands/demo_procesar_riesgos.php#L1-L115) |
| `backend/tests/Support/RiesgoCurricularFixtures.php` | Backend | `RiesgoCurricularFixtures` (trait) | 1-277 | `ef38acb` | `c66c496` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/c66c496/backend/tests/Support/RiesgoCurricularFixtures.php#L1-L277) |

---

## RF11 - Seguimiento Psicólogo Tutor

| Campo | Valor |
|-------|-------|
| **Nombre** | Seguimiento Psicólogo Tutor |
| **Descripción** | Panel de seguimiento para psicólogos/tutores con métricas de estudiantes, alertas y semáforo |
| **Rama desarrollo** | `main` (sin rama feature) |
| **Commit inicial (planning)** | `275d0bc` — 2026-06-24 — docs: planificar rf11 perfil psicologo tutor |
| **Commit backend** | `8bf71a0` — 2026-06-24 — feat: implementar backend rf11 seguimiento psicologo tutor |
| **Commit frontend** | `a95b1c2` — 2026-06-24 — feat: implementar backend rf11 seguimiento psicologo tutor |
| **Commit cierre** | `577e59c` — 2026-06-24 — docs: cerrar rf11 seguimiento psicologo tutor v1 |
| **Commit más reciente** | `6bdfcfd` — 2026-06-28 (quality fixes Sonar) |
| **Permisos** | `ver_perfil_psicologo_tutor` |

### Archivos

| Archivo | Tipo | Clase/Componente | Líneas | Commit inicial | Hash commit | Fecha | URL |
|---------|------|------------------|--------|---------------|-------------|-------|-----|
| `backend/app/Http/Controllers/Api/PsicologoTutorSeguimientoController.php` | Backend | `PsicologoTutorSeguimientoController` | 1-190 | `8bf71a0` | `8bf71a0` | 2026-06-24 | [URL](https://github.com/Keterod/siderae-blenkir/blob/8bf71a0/backend/app/Http/Controllers/Api/PsicologoTutorSeguimientoController.php#L1-L190) |
| `backend/app/Models/Alerta.php` | Backend | `Alerta` | 1-78 | `abdfaf5` | `abdfaf5` | 2026-04-29 | [URL](https://github.com/Keterod/siderae-blenkir/blob/abdfaf5/backend/app/Models/Alerta.php#L1-L78) |
| `backend/app/Models/Intervencion.php` | Backend | `Intervencion` | 1-43 | `abdfaf5` | `abdfaf5` | 2026-04-29 | [URL](https://github.com/Keterod/siderae-blenkir/blob/abdfaf5/backend/app/Models/Intervencion.php#L1-L43) |
| `backend/app/Http/Controllers/Api/IntervencionController.php` | Backend | `IntervencionController::store` | 1-54 | `abdfaf5` | `ab2f530` | 2026-05-05 | [URL](https://github.com/Keterod/siderae-blenkir/blob/ab2f530/backend/app/Http/Controllers/Api/IntervencionController.php#L1-L54) |
| `backend/app/Http/Controllers/Api/AlertaCierreController.php` | Backend | `AlertaCierreController::store` | 1-52 | `abdfaf5` | `ab2f530` | 2026-05-05 | [URL](https://github.com/Keterod/siderae-blenkir/blob/ab2f530/backend/app/Http/Controllers/Api/AlertaCierreController.php#L1-L52) |
| `backend/tests/Feature/PsicologoTutorSeguimientoTest.php` | Backend | `PsicologoTutorSeguimientoTest` | 1-436 | `8bf71a0` | `8bf71a0` | 2026-06-24 | [URL](https://github.com/Keterod/siderae-blenkir/blob/8bf71a0/backend/tests/Feature/PsicologoTutorSeguimientoTest.php#L1-L436) |
| `backend/database/seeders/PermissionsSeeder.php` | Backend | Permiso `ver_perfil_psicologo_tutor` | 63-65 | `3c44856` | `3c44856` | 2026-06-24 | [URL](https://github.com/Keterod/siderae-blenkir/blob/3c44856/backend/database/seeders/PermissionsSeeder.php#L63-L65) |
| `frontend/src/components/psicologo-tutor/PerfilPsicologoTutorPanel.jsx` | Frontend | `PerfilPsicologoTutorPanel` | 1-361 | `a95b1c2` | `6bdfcfd` | 2026-06-28 | [URL](https://github.com/Keterod/siderae-blenkir/blob/6bdfcfd/frontend/src/components/psicologo-tutor/PerfilPsicologoTutorPanel.jsx#L1-L361) |
| `frontend/src/components/alertas/AlertasPanel.jsx` | Frontend | `AlertasPanel` | 1-494 | `abdfaf5` | `6bdfcfd` | 2026-06-28 | [URL](https://github.com/Keterod/siderae-blenkir/blob/6bdfcfd/frontend/src/components/alertas/AlertasPanel.jsx#L1-L494) |
| `backend/routes/api.php` | Backend | Ruta `GET /api/psicologo-tutor/seguimiento` | 143-144 | `8bf71a0` | `8bf71a0` | 2026-06-24 | [URL](https://github.com/Keterod/siderae-blenkir/blob/8bf71a0/backend/routes/api.php#L143-L144) |

---

## RF14 - Dashboard Institucional

| Campo | Valor |
|-------|-------|
| **Nombre** | Dashboard Institucional |
| **Descripción** | Dashboard con resumen institucional, completitud, distribución por grado/sección y últimos riesgos |
| **Rama desarrollo** | `feature/rf14-dashboard-institucional` |
| **Commit inicial (planning)** | `517950f` — 2026-06-23 — docs: planificar rf14 dashboard institucional |
| **Commit backend** | `3b3da9b` — 2026-06-23 — feat: implementar backend rf14 dashboard institucional |
| **Commit frontend** | `4c3d46e` — 2026-06-23 — feat: implementar frontend rf14 dashboard institucional |
| **Commit cierre** | `18019f8` — 2026-06-23 — docs: cerrar rf14 dashboard institucional v1 |
| **Commit más reciente** | `4c3d46e` — 2026-06-23 |
| **Permisos** | `ver_dashboard_institucional` |

### Archivos

| Archivo | Tipo | Clase/Componente | Líneas | Commit inicial | Hash commit | Fecha | URL |
|---------|------|------------------|--------|---------------|-------------|-------|-----|
| `backend/app/Http/Controllers/Api/DashboardInstitucionalController.php` | Backend | `DashboardInstitucionalController` | 1-244 | `3b3da9b` | `3b3da9b` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/3b3da9b/backend/app/Http/Controllers/Api/DashboardInstitucionalController.php#L1-L244) |
| `frontend/src/components/dashboard/DashboardInstitucionalPanel.jsx` | Frontend | `DashboardInstitucionalPanel` | 1-387 | `4c3d46e` | `4c3d46e` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/4c3d46e/frontend/src/components/dashboard/DashboardInstitucionalPanel.jsx#L1-L387) |
| `backend/tests/Feature/DashboardInstitucionalTest.php` | Backend | `DashboardInstitucionalTest` (15 tests) | 1-357 | `3b3da9b` | `3b3da9b` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/3b3da9b/backend/tests/Feature/DashboardInstitucionalTest.php#L1-L357) |
| `backend/database/seeders/PermissionsSeeder.php` | Backend | Permiso `ver_dashboard_institucional` | 59-61 | `3a45cbc` | `3a45cbc` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/3a45cbc/backend/database/seeders/PermissionsSeeder.php#L59-L61) |
| `backend/routes/api.php` | Backend | Ruta `GET /api/dashboard/institucional` | 78-79 | `3b3da9b` | `3b3da9b` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/3b3da9b/backend/routes/api.php#L78-L79) |
| `frontend/src/lib/api.js` | Frontend | `getDashboardInstitucional` | ~820+ | `4c3d46e` | `4c3d46e` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/4c3d46e/frontend/src/lib/api.js#L820-L823) |

---

## RF16 - Reportes de Riesgo

| Campo | Valor |
|-------|-------|
| **Nombre** | Reportes de Riesgo |
| **Descripción** | Reporte paginado de índices de riesgo académico con filtros por año, bimestre, grado, sección y nivel |
| **Rama desarrollo** | `feature/rf16-reportes-riesgo` |
| **Commit inicial (planning)** | `305ca76` — 2026-06-23 — docs: planificar rf16 reportes riesgo academico |
| **Commit backend** | `5e9200e` — 2026-06-23 — feat: implementar backend rf16 reportes riesgo |
| **Commit frontend** | `ae765f6` — 2026-06-23 — feat: implementar frontend rf16 reportes riesgo |
| **Commit cierre** | `e5f834b` — 2026-06-23 — docs: cerrar rf16 reportes riesgo v1 |
| **Commit más reciente** | `6bdfcfd` — 2026-06-28 (quality fixes) |
| **Permisos** | `ver_reportes_riesgo` |

### Archivos

| Archivo | Tipo | Clase/Componente | Líneas | Commit inicial | Hash commit | Fecha | URL |
|---------|------|------------------|--------|---------------|-------------|-------|-----|
| `backend/app/Http/Controllers/Api/ReporteRiesgoAcademicoController.php` | Backend | `ReporteRiesgoAcademicoController::index` | 1-69 | `5e9200e` | `5e9200e` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/5e9200e/backend/app/Http/Controllers/Api/ReporteRiesgoAcademicoController.php#L1-L69) |
| `frontend/src/components/reportes/ReporteRiesgoAcademicoPanel.jsx` | Frontend | `ReporteRiesgoAcademicoPanel` | 1-310 | `ae765f6` | `6bdfcfd` | 2026-06-28 | [URL](https://github.com/Keterod/siderae-blenkir/blob/6bdfcfd/frontend/src/components/reportes/ReporteRiesgoAcademicoPanel.jsx#L1-L310) |
| `backend/tests/Feature/ReporteRiesgoAcademicoTest.php` | Backend | `ReporteRiesgoAcademicoTest` (13 tests) | 1-297 | `5e9200e` | `5e9200e` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/5e9200e/backend/tests/Feature/ReporteRiesgoAcademicoTest.php#L1-L297) |
| `backend/database/seeders/PermissionsSeeder.php` | Backend | Permiso `ver_reportes_riesgo` | 55-57 | `8f5184a` | `8f5184a` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/8f5184a/backend/database/seeders/PermissionsSeeder.php#L55-L57) |
| `backend/routes/api.php` | Backend | Ruta `GET /api/reportes/riesgo-academico` | 140-141 | `5e9200e` | `5e9200e` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/5e9200e/backend/routes/api.php#L140-L141) |
| `frontend/src/lib/api.js` | Frontend | `getReportesRiesgoAcademico` | 819-822 | `ae765f6` | `ae765f6` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/ae765f6/frontend/src/lib/api.js#L819-L822) |

---

## RF19 - Semáforo de Completitud

| Campo | Valor |
|-------|-------|
| **Nombre** | Semáforo de Completitud |
| **Descripción** | Evalúa la completitud de datos del estudiante (notas, asistencia, reportes, índice) y muestra color verde/amarillo/rojo |
| **Rama desarrollo** | `feature/rf19-plan-semaforo-completitud` |
| **Commit inicial (planning)** | `8866e2f` — 2026-06-23 — docs: planificar rf19 semaforo de completitud |
| **Commit backend** | `0d245a0` — 2026-06-23 — feat: implementar backend rf19 semaforo completitud |
| **Commit frontend** | `09419df` — 2026-06-23 — feat: implementar frontend rf19 semaforo completitud |
| **Commit cierre** | `fb76f93` — 2026-06-23 — docs: cerrar rf19 semaforo completitud v1 |
| **Commit más reciente** | `408d6ff` — 2026-06-24 (auto-refresh al procesar riesgo) |
| **Permisos** | `ver_semaforo_completitud` |

### Archivos

| Archivo | Tipo | Clase/Componente | Líneas | Commit inicial | Hash commit | Fecha | URL |
|---------|------|------------------|--------|---------------|-------------|-------|-----|
| `backend/app/Services/CompletitudDatosService.php` | Backend | `CompletitudDatosService::evaluar` | 1-160 | `0d245a0` | `0d245a0` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/0d245a0/backend/app/Services/CompletitudDatosService.php#L1-L160) |
| `backend/app/Http/Controllers/Api/SemaforoCompletitudController.php` | Backend | `SemaforoCompletitudController::show` | 1-45 | `0d245a0` | `0d245a0` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/0d245a0/backend/app/Http/Controllers/Api/SemaforoCompletitudController.php#L1-L45) |
| `frontend/src/components/estudiantes/EstudiantePerfilSemaforoCompletitud.jsx` | Frontend | `EstudiantePerfilSemaforoCompletitud` | 1-136 | `09419df` | `408d6ff` | 2026-06-24 | [URL](https://github.com/Keterod/siderae-blenkir/blob/408d6ff/frontend/src/components/estudiantes/EstudiantePerfilSemaforoCompletitud.jsx#L1-L136) |
| `backend/tests/Feature/SemaforoCompletitudTest.php` | Backend | `SemaforoCompletitudTest` (11 tests) | 1-245 | `0d245a0` | `0d245a0` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/0d245a0/backend/tests/Feature/SemaforoCompletitudTest.php#L1-L245) |
| `backend/database/seeders/PermissionsSeeder.php` | Backend | Permiso `ver_semaforo_completitud` | 47-49 | `89de8c4` | `89de8c4` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/89de8c4/backend/database/seeders/PermissionsSeeder.php#L47-L49) |
| `backend/routes/api.php` | Backend | Ruta `GET /api/estudiantes/{id}/semaforo-completitud` | 134-135 | `0d245a0` | `0d245a0` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/0d245a0/backend/routes/api.php#L134-L135) |
| `frontend/src/lib/api.js` | Frontend | `getSemaforoCompletitud` | 810-812 | `09419df` | `09419df` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/09419df/frontend/src/lib/api.js#L810-L812) |

---

## RF20 - Historial de Riesgo

| Campo | Valor |
|-------|-------|
| **Nombre** | Historial de Riesgo |
| **Descripción** | Visualización del historial de índices de riesgo por período (solo lectura, no recalcula) |
| **Rama desarrollo** | `feature/rf20-historial-riesgo` |
| **Commit inicial (planning)** | `1cfdfbc` — 2026-06-23 — docs: planificar rf20 historial riesgo |
| **Commit backend** | `50a4e9c` — 2026-06-23 — feat: agregar permiso rf20 historial riesgo |
| **Commit frontend** | `891cc9a` — 2026-06-23 — feat: implementar frontend rf20 historial riesgo |
| **Commit cierre** | `a883ed9` — 2026-06-23 — docs: cerrar rf20 historial riesgo v1 |
| **Commit más reciente** | `408d6ff` — 2026-06-24 (auto-refresh) |
| **Permisos** | `ver_historial_riesgo` |

### Archivos

| Archivo | Tipo | Clase/Componente | Líneas | Commit inicial | Hash commit | Fecha | URL |
|---------|------|------------------|--------|---------------|-------------|-------|-----|
| `backend/app/Http/Controllers/Api/HistorialRiesgoController.php` | Backend | `HistorialRiesgoController::index` | 1-59 | `50a4e9c` | `50a4e9c` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/50a4e9c/backend/app/Http/Controllers/Api/HistorialRiesgoController.php#L1-L59) |
| `frontend/src/components/estudiantes/EstudiantePerfilHistorialRiesgo.jsx` | Frontend | `EstudiantePerfilHistorialRiesgo` | 1-170 | `891cc9a` | `408d6ff` | 2026-06-24 | [URL](https://github.com/Keterod/siderae-blenkir/blob/408d6ff/frontend/src/components/estudiantes/EstudiantePerfilHistorialRiesgo.jsx#L1-L170) |
| `backend/tests/Feature/HistorialRiesgoTest.php` | Backend | `HistorialRiesgoTest` (12 tests) | 1-264 | `50a4e9c` | `50a4e9c` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/50a4e9c/backend/tests/Feature/HistorialRiesgoTest.php#L1-L264) |
| `backend/database/seeders/PermissionsSeeder.php` | Backend | Permiso `ver_historial_riesgo` | 51-53 | `e333420` | `e333420` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/e333420/backend/database/seeders/PermissionsSeeder.php#L51-L53) |
| `backend/routes/api.php` | Backend | Ruta `GET /api/estudiantes/{id}/historial-riesgo` | 137-138 | `50a4e9c` | `50a4e9c` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/50a4e9c/backend/routes/api.php#L137-L138) |
| `frontend/src/lib/api.js` | Frontend | `getHistorialRiesgo` | 814-817 | `891cc9a` | `891cc9a` | 2026-06-23 | [URL](https://github.com/Keterod/siderae-blenkir/blob/891cc9a/frontend/src/lib/api.js#L814-L817) |

---

## Resumen de Commits por RF

| RF | Commit inicial | Commit implementación | Rama | Archivos creados | Tests |
|----|---------------|----------------------|------|-----------------|-------|
| RF04 | `0e429f9` / `a01dddf` | `a01dddf` | `feature/rf04-backend-api-reportes-conductuales` | 5+ | 8 tests |
| RF06 | `8ec0f01` / `c66c496` | `c66c496` | `feature/rf06-diagnostico-variables-riesgo` | 5+ (ML + Backend) | 20+ tests |
| RF11 | `275d0bc` / `8bf71a0` | `8bf71a0` / `a95b1c2` | `main` (sin rama feature) | 10+ | 34 tests |
| RF14 | `517950f` / `3b3da9b` | `3b3da9b` / `4c3d46e` | `feature/rf14-dashboard-institucional` | 6+ | 15 tests |
| RF16 | `305ca76` / `5e9200e` | `5e9200e` / `ae765f6` | `feature/rf16-reportes-riesgo` | 4+ | 13 tests |
| RF19 | `8866e2f` / `0d245a0` | `0d245a0` / `09419df` | `feature/rf19-plan-semaforo-completitud` | 6+ | 11 tests |
| RF20 | `1cfdfbc` / `50a4e9c` | `50a4e9c` / `891cc9a` | `feature/rf20-historial-riesgo` | 4+ | 12 tests |

---

## Rama base y fechas clave

- **Initial commit:** `32af7be` — 2026-04-28
- **Rama main actual:** `6bdfcfd` — 2026-06-28
- **Total commits en main:** 79 (desde initial commit)
- **Ramas feature fusionadas:** 7 (rf04, rf06, rf14, rf16, rf19, rf20, rf11 en main directo)

### Ramas locales activas (no fusionadas):
- `feature/ai-dlc-fase1-limpieza-metodologia`
- `feature/cypress-e2e-global`
- `feature/nc11-activar-riesgo-ui`
- `feature/rf04-cypress-e2e`
- `gg`
- `revision-final-docente`
- `seeders-inicial`
- `sonar-coverage-evidencias`
- `sonar-mejoras-calidad`
- `sprint-6`

---

*Auditoría generada el 2026-06-28 mediante análisis de git log, git blame y lectura de archivos.*
