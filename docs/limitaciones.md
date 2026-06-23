# Limitaciones y alcance real de SIDERAE-Blenkir

## 1. Propósito

Este documento registra las **diferencias verificables** entre:

- **Alcance formal/documentado** — [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) (v2.1, estado V1 real) y resumen histórico v1 en [`docs/arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) a partir del PDF (`DRS_SIDERAE_Blenkir_v1.pdf`, **fuente formal externa al repositorio**).
- **Implementación real del prototipo** — lo confirmado en código, rutas, UI y pruebas del repositorio.
- **Pendientes para versiones futuras** — funcionalidades definidas en el DRS o en sprints pero no equivalentes en código.

No sustituye al DRS v2 ([`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md)). Para uso por rol, ver [`manual-usuario.md`](manual-usuario.md); para aspectos técnicos, [`manual-tecnico.md`](manual-tecnico.md).

---

## 2. Criterio de lectura

| Estado | Significado |
|--------|-------------|
| **Confirmado en código** | Evidencia en rutas, modelos, UI o tests del repo. |
| **Implementado parcialmente** | Solo parte del requerimiento formal está operativa. |
| **Pendiente** | Definido en DRS/sprints; no hay equivalencia suficiente en código. |
| **No confirmado** | Mencionado en documentación pero sin verificación en esta revisión. |
| **Histórico** | Documento o flujo de planificación/diseño; no refleja necesariamente la UI vigente. |
| **Fuente externa** | Alcance formal fuera del repo (DRS PDF, Formato 06). |

---

## 3. Alcance confirmado en el prototipo

Cada ítem incluye evidencia por ruta.

### Autenticación y sesión — **Confirmado en código**

- Login/logout Sanctum: [`backend/routes/auth.php`](../backend/routes/auth.php), [`frontend/src/context/AuthContext.jsx`](../frontend/src/context/AuthContext.jsx).
- Perfil de sesión: `GET /api/me` en [`backend/routes/api.php`](../backend/routes/api.php) (L41–48).
- Tests: [`backend/tests/Feature/Auth/AuthenticationTest.php`](../backend/tests/Feature/Auth/AuthenticationTest.php).

### Roles y permisos (backend) — **Confirmado en código**

- 5 roles y 26 permisos en [`backend/database/seeders/PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php) (incluye base RBAC RF-19 `ver_semaforo_completitud`).
- Middleware `auth:sanctum` + `permission:*` en [`backend/routes/api.php`](../backend/routes/api.php).
- Visibilidad de menú por permiso en [`frontend/src/App.jsx`](../frontend/src/App.jsx) (`moduloPermitido`).

### Gestión de estudiantes — **Confirmado en código**

- API CRUD parcial: [`backend/routes/api.php`](../backend/routes/api.php) (L86–96), [`backend/app/Http/Controllers/Api/EstudianteController.php`](../backend/app/Http/Controllers/Api/EstudianteController.php).
- UI: [`frontend/src/components/estudiantes/EstudiantesPanel.jsx`](../frontend/src/components/estudiantes/EstudiantesPanel.jsx).
- Tests: [`backend/tests/Feature/EstudianteTest.php`](../backend/tests/Feature/EstudianteTest.php).

### Gestión de usuarios (RF-15) — **Confirmado en código**

- API: rutas `/api/usuarios/*` en [`backend/routes/api.php`](../backend/routes/api.php) (L55–64).
- UI: [`frontend/src/components/usuarios/UsuariosPanel.jsx`](../frontend/src/components/usuarios/UsuariosPanel.jsx), módulo `usuarios` en [`frontend/src/App.jsx`](../frontend/src/App.jsx).

### Registro académico curricular — **Confirmado en código**

- Prefijo `/api/curricular/*`: [`backend/routes/api.php`](../backend/routes/api.php) (L128–282).
- UI (menú lateral): malla, criterios, componentes calificación, configuración bimestral, secciones/aulas, asignación docente, periodos académicos, notas semanales, Excel por aula, asistencia curricular — [`frontend/src/App.jsx`](../frontend/src/App.jsx).
- Tests extensos: [`backend/tests/Feature/Curricular/`](../backend/tests/Feature/Curricular/).

### Importación / exportación Excel curricular — **Confirmado en código**

- Plantilla e import: `GET /api/curricular/notas-semanales/plantilla-excel`, `POST /api/curricular/notas-semanales/importar-excel` — [`backend/routes/api.php`](../backend/routes/api.php).
- Servicio: [`backend/app/Services/Curricular/PlantillaRegistroAuxiliarExcelService.php`](../backend/app/Services/Curricular/PlantillaRegistroAuxiliarExcelService.php).
- Excel por aula: `GET /api/curricular/excel-aula` — tests [`backend/tests/Feature/Curricular/ExcelAulaTest.php`](../backend/tests/Feature/Curricular/ExcelAulaTest.php), [`backend/tests/Feature/Curricular/PlantillaRegistroAuxiliarExcelTest.php`](../backend/tests/Feature/Curricular/PlantillaRegistroAuxiliarExcelTest.php).

### Riesgo académico — **Confirmado en código** (modelo ML simplificado; ver §5)

- Endpoint: `POST /api/estudiantes/{estudiante}/procesar-riesgo` — [`backend/routes/api.php`](../backend/routes/api.php) (L113–114).
- Orquestación Laravel → Flask: [`backend/app/Services/MlRiskService.php`](../backend/app/Services/MlRiskService.php), [`backend/app/Services/RiesgoAcademicoService.php`](../backend/app/Services/RiesgoAcademicoService.php).
- UI en perfil: [`frontend/src/components/estudiantes/EstudiantePerfilRiesgo.jsx`](../frontend/src/components/estudiantes/EstudiantePerfilRiesgo.jsx).
- Tests: [`backend/tests/Feature/RiesgoTest.php`](../backend/tests/Feature/RiesgoTest.php).

### Alertas, intervenciones y cierre — **Confirmado en código**

- API: [`backend/routes/api.php`](../backend/routes/api.php) (L116–126).
- UI: [`frontend/src/components/alertas/AlertasPanel.jsx`](../frontend/src/components/alertas/AlertasPanel.jsx).
- Tests: [`backend/tests/Feature/AlertaIntervencionTest.php`](../backend/tests/Feature/AlertaIntervencionTest.php).

### RF-04 — Reportes conductuales — **Implementado V1 mínimo**

- API: `GET/POST /api/estudiantes/{id}/reportes-conductuales`, `PATCH /api/reportes-conductuales/{id}/anular` — [`ReporteConductualController.php`](../backend/app/Http/Controllers/Api/ReporteConductualController.php).
- Permisos: `ver_reportes_conductuales`, `registrar_reportes_conductuales` — [`PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php).
- UI: bloque en perfil — [`EstudiantePerfilReportesConductuales.jsx`](../frontend/src/components/estudiantes/EstudiantePerfilReportesConductuales.jsx).
- Tests: [`ReporteConductualTest.php`](../backend/tests/Feature/ReporteConductualTest.php) — **8 passed** (Fase 2E, 2026-06-10).
- Cypress mínimo: [`cypress-rf04.md`](pruebas/cypress-rf04.md), `frontend/cypress/e2e/rf04-reportes-conductuales.cy.js` — configurado Fase 2F; Cypress verificado, spec detenido por falta de `CYPRESS_E2E_EMAIL`.
- Smoke UI: [`smoke-rf04-reportes-conductuales.md`](pruebas/smoke-rf04-reportes-conductuales.md) — **pendiente ejecución humana en navegador**.
- **Brechas V1:** sin módulo global por grado/sección; sin PDF RF-16; sin alertas automáticas RF-10; directivo solo lectura backend y **sin menú Estudiantes** en UI habitual; no es comunicación familiar (RF-12 eliminado).

### Dashboard — **Confirmado en código** (alcance funcional parcial frente al DRF RF-14; ver §4)

- API: `GET /api/dashboard`, `GET /api/dashboard/export` — [`backend/routes/api.php`](../backend/routes/api.php) (L66–70).
- UI: [`frontend/src/components/DashboardPanel.jsx`](../frontend/src/components/DashboardPanel.jsx).
- Tests: [`backend/tests/Feature/DashboardTest.php`](../backend/tests/Feature/DashboardTest.php).

### RF-19 — Semáforo de completitud de datos — **Implementado V1**

- Servicio: [`backend/app/Services/CompletitudDatosService.php`](../backend/app/Services/CompletitudDatosService.php).
- API: `GET /api/estudiantes/{estudiante}/semaforo-completitud` — [`backend/routes/api.php`](../backend/routes/api.php).
- Permiso: `ver_semaforo_completitud` — [`backend/database/seeders/PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php).
- Tests backend: [`backend/tests/Feature/SemaforoCompletitudTest.php`](../backend/tests/Feature/SemaforoCompletitudTest.php) — **11 passed**, 55 assertions (Fase 3C/E, 2026-06-23).
- UI: [`frontend/src/components/estudiantes/EstudiantePerfilSemaforoCompletitud.jsx`](../frontend/src/components/estudiantes/EstudiantePerfilSemaforoCompletitud.jsx) integrado en `EstudiantesPanel.jsx` (Fase 3D, 2026-06-23).
- Build frontend verde (Fases 3D/E).
  - **Pendientes menores:** pruebas E2E globales (Cypress) no ejecutadas.

### Infraestructura Docker local — **Confirmado en código**

- Cuatro servicios: [`docker-compose.yml`](../docker-compose.yml) (`db-mysql`, `app-backend`, `app-frontend`, `ml-engine`).
- Puertos host: 3307, 8000, 5173, 5000.

### ML Service determinístico — **Confirmado en código**

- Endpoints `GET /`, `POST /predict`: [`ml-service/main.py`](../ml-service/main.py).
- Sin acceso a MySQL: [`ml-service/requirements.txt`](../ml-service/requirements.txt) (solo `flask`).

---

## 4. Alcance parcial

### RF-01 — Carga e importación de datos académicos — **Implementado parcialmente**

| Aspecto | Estado | Evidencia |
|---------|--------|-----------|
| Carga manual (notas/asistencia legacy por estudiante y lote) | Confirmado en código | [`backend/routes/api.php`](../backend/routes/api.php) L98–111; [`backend/tests/Feature/DatosAcademicosTest.php`](../backend/tests/Feature/DatosAcademicosTest.php) |
| Flujo curricular (notas semanales, bimestre, bulk) | Confirmado en código | Rutas `/api/curricular/*`; tests `Feature/Curricular/*` |
| Importación Excel **plantilla curricular** | Confirmado en código | `importar-excel`, `PlantillaRegistroAuxiliarExcelTest` — ver [`aula-notas-excel.md`](aula-notas-excel.md) |
| Excel por aula (descarga) | Confirmado en código | `GET /excel-aula`, `ExcelAulaTest` — **solo descarga**; no import |
| Importación masiva **SIAGIE** | **Fuera del alcance actual** | Decisión de alcance v2.1; plantillas Excel propias RF-32/RF-33 |

### RF-05 — Variables socioeconómicas — **Retirado del flujo funcional de riesgo**

- **API legacy:** puede existir bajo estudiante — [`backend/routes/api.php`](../backend/routes/api.php), `VariableSocioeconomicaController`.
- **No** es insumo obligatorio del cálculo de riesgo (RF-06) en alcance vigente v2.1.
- UI pausada en perfil estudiante.

### RF-14 / RF-16 — Dashboard académico-institucional y reportes de riesgo — **Implementado parcialmente**

- Dashboard con filtros y KPIs (subset académico/riesgo): confirmado (`DashboardPanel`, `DashboardTest`).
- Export PDF del dashboard vía DomPDF: confirmado — **antecedente parcial** de RF-16, no zona completa de reportes de riesgo.
- **Planificado:** RF-16 zona reportes por estudiante/aula/grado/periodo; RF-14 indicadores académicos ampliados.

### RF-17 — Auditoría (`activity_log`) — **Implementado parcialmente**

- Dependencia: `spatie/laravel-activitylog` en [`backend/composer.json`](../backend/composer.json).
- Registro en acciones críticas API: varios controladores (p. ej. `EstudianteController`, `DashboardController`, curricular).
- Tests parciales: [`backend/tests/Feature/ActivityLogTest.php`](../backend/tests/Feature/ActivityLogTest.php).
- **No confirmado:** cobertura total REQ-17.x ni UI de consulta de logs.

### RF-20 — Historial de riesgo — **Parcial / en avance**

- Persistencia en `indices_riesgo`: migración [`backend/database/migrations/2026_04_23_024405_create_indices_riesgo_table.php`](../backend/database/migrations/2026_04_23_024405_create_indices_riesgo_table.php).
- Base RBAC implementada (Fase 4B): permiso `ver_historial_riesgo` en [`backend/database/seeders/PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php).
- Backend (Fase 4C): endpoint `GET /api/estudiantes/{estudiante}/historial-riesgo`, [`HistorialRiesgoController.php`](../backend/app/Http/Controllers/Api/HistorialRiesgoController.php), tests [`HistorialRiesgoTest.php`](../backend/tests/Feature/HistorialRiesgoTest.php).
- Frontend (Fase 4D): componente [`EstudiantePerfilHistorialRiesgo.jsx`](../frontend/src/components/estudiantes/EstudiantePerfilHistorialRiesgo.jsx) integrado en perfil estudiante, función `getHistorialRiesgo()` en [`frontend/src/lib/api.js`](../frontend/src/lib/api.js), build frontend OK.
- Visualización del último índice en perfil: `EstudiantePerfilRiesgo`.
- **Pendientes:** smoke manual en navegador y cierre final RF-20.

### Registro académico legacy (materias, lotes) — **Implementado parcialmente**

- API materias y lotes legacy: [`backend/routes/api.php`](../backend/routes/api.php) (L72–84, L100–101).
- Componentes UI legacy **sin entrada en menú:** [`frontend/src/components/materias/MateriasPanel.jsx`](../frontend/src/components/materias/MateriasPanel.jsx), [`frontend/src/components/academico/NotasMasivasPanel.jsx`](../frontend/src/components/academico/NotasMasivasPanel.jsx), [`frontend/src/components/academico/AsistenciaMasivaPanel.jsx`](../frontend/src/components/academico/AsistenciaMasivaPanel.jsx) (no importados en [`App.jsx`](../frontend/src/App.jsx)).
- **Flujo operativo en UI:** curricular (notas semanales, asistencia diaria).

### Transición evaluación C/L/T por nivel — **Implementado parcialmente**

- Backend pesos C/L/T activo: `/api/curricular/pesos*` — [`backend/routes/api.php`](../backend/routes/api.php) (L188–194).
- Menú «Pesos evaluación» **oculto** en UI: [`frontend/src/App.jsx`](../frontend/src/App.jsx) (`visible: false` en `curricular_pesos`).
- README describe pendientes de transición: [`README.md`](../README.md) (§ Próximos desarrollos).

---

## 5. Alcance retirado, planificado o no confirmado

### Retirado del alcance vigente (v2.1)

| Tema | Estado | Notas |
|------|--------|-------|
| **RF-03** — Fast Test | **Retirado** | Institución no utiliza Fast Test |
| **RF-05** — VSE en flujo de riesgo | **Retirado del flujo** | API legacy puede existir; no insumo obligatorio RF-06 |
| **RF-12** — Comunicación familiar | **Eliminado** | Gestión fuera del sistema |
| **Importación SIAGIE (RF-01)** | **Fuera del alcance** | Plantillas Excel propias RF-32/RF-33 |

### Parcial / en avance

| Tema | Estado | Notas |
|------|--------|-------|
| **RF-19** — Semáforo completitud | **Implementado V1** | Backend Fase 3C (`CompletitudDatosService`, endpoint, tests 11 passed) + frontend Fase 3D (`EstudiantePerfilSemaforoCompletitud.jsx`, build OK). Smoke manual navegador aprobado; Cypress global no ejecutado. |

### Planificado (DRS v2.1 — por implementar en código)

| Tema | Estado | Notas |
|------|--------|-------|
| **RF-10** — Escalamiento directivo crítico | **Planificado** | Solo casos críticos/extremos |
| **RF-11** — Perfil integral psicólogo (lectura) | **Planificado** | Alertas operativas hoy |
| **RF-16** — Zona reportes de riesgo | **Planificado** | PDF dashboard = parcial |
| **RF-18** — Reentrenamiento ML | **Planificado** | Requiere dataset histórico; no implementado |
| **RF-20** — Historial evolutivo | **Parcial / en avance** | Fase 4B: base RBAC implementada; pendiente endpoint, UI, tests y smoke manual |

### Otros pendientes técnicos

| Tema | Estado | Notas |
|------|--------|-------|
| **Cypress / E2E** | Parcial | Infraestructura auth/logout + smoke RF-04 configurada; sin suite E2E global |
| **Random Forest / SVM / XGBoost** | No implementado | ML determinístico en V1 |
| **Certificación ISO** | No aplica | Alineación progresiva únicamente |
| **Despliegue productivo** | Pendiente | Solo Docker local |
| **DRS PDF / Formato 06** | Fuente externa | Markdown v2.1 vigente en repo |

---

## 6. Limitaciones técnicas

| Limitación | Detalle | Evidencia |
|------------|---------|-----------|
| Entorno local Docker | Prototipo pensado para `docker compose` local | [`docker-compose.yml`](../docker-compose.yml) |
| **Seed manual** | `docker compose up` ejecuta `migrate --force`, **no** `--seed` | Comando en [`docker-compose.yml`](../docker-compose.yml) L29–33 |
| Base de datos local persistente | Datos en `docker/mysql_data/` | [`docker-compose.yml`](../docker-compose.yml) volumen |
| Datos demo/sintéticos | Usuarios y estudiantes ficticios; conteos varían según seed/ejecuciones | [`README.md`](../README.md); hallazgos Fase 1 en [`docs/pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) |
| ML prototipo | Flask determinístico; no pipelines entrenados | [`ml-service/main.py`](../ml-service/main.py) |
| Dependencias en contenedor | `composer install` / `npm install` / `pip install` en arranque | [`docker-compose.yml`](../docker-compose.yml) |
| Healthchecks incompletos | Solo `db-mysql` tiene healthcheck | [`docker-compose.yml`](../docker-compose.yml); [`docs/arquitectura/contexto-docker-infraestructura.md`](arquitectura/contexto-docker-infraestructura.md) |
| Límite memoria PHP en tests Excel | Suite completa puede fallar por `memory_limit` 128M en tests de descarga Excel | Ver [`docs/pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) |
| `POST /register` público | Ruta Breeze activa; no hardening producción | [`backend/tests/Feature/Auth/RegistrationTest.php`](../backend/tests/Feature/Auth/RegistrationTest.php); [`README.md`](../README.md) |

> **Nota sobre conteos Fase 1 (BD local auditada):** Estos conteos pertenecen al entorno local auditado. La presencia de registros con sede Auquimarca no implica operación multi-sede en V1; la decisión vigente de V1 es sede operativa **Chilca** ([`AGENTS.md`](../AGENTS.md)).

---

## 7. Limitaciones funcionales

| Limitación | Detalle |
|------------|---------|
| Visibilidad por rol | Cada módulo del sidebar depende de permisos Spatie; ver `moduloPermitido` en [`frontend/src/App.jsx`](../frontend/src/App.jsx). |
| Legacy sin menú | Materias, notas masivas y asistencia masiva legacy existen en código pero **no** están en el menú principal. |
| VSE retiradas del flujo de riesgo | API legacy puede existir; pestaña UI oculta; **no** insumo obligatorio RF-06 (v2.1) |
| Directivo y alertas | Directivo **no** es actor inicial de todas las alertas; escalamiento RF-10 planificado solo casos críticos |
| Psicólogo perfil integral | **Planificado** RF-11: lectura académica completa; hoy solo alertas |
| Comunicación familiar | **Fuera del sistema** — RF-12 eliminado |
| Fast Test / VSE en riesgo | **No** aparecen como flujos de usuario vigentes |
| Pesos C/L/T ocultos | Panel `PesosEvaluacionPanel` con `visible: false` en sidebar. |
| Sede única en UI (V1) | Sin selectores de sede; payloads fijan Chilca — [`AGENTS.md`](../AGENTS.md), [`frontend/src/lib/sedeOperativa.js`](../frontend/src/lib/sedeOperativa.js). La BD puede contener registros en otras sedes (p. ej. Auquimarca) por datos históricos o seeds previos. |
| Mockups vs UI real | Mockups `docs/ui/mockups/01–12` reflejan flujo legacy; módulos curriculares **no** tienen mockups equivalentes. |
| Directivo y notas | Acceso a «Notas semanales» por excepción de rol en `moduloPermitido` (no solo por permiso `registrar_notas_semanales`). |

---

## 8. Limitaciones de pruebas

| Aspecto | Estado |
|---------|--------|
| PHPUnit / Feature Tests | **Existen** — ~49 archivos en [`backend/tests/`](../backend/tests/) |
| Ejecución suite completa (Fase 1) | **Parcial** — ver [`docs/pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md): fallo por memoria en `ExcelAulaTest` con límite 128M; misma clase pasa con 512M |
| Cypress | **Parcial** — infraestructura auth/logout y smoke RF-04 configurados; Fase 2H.1 ejecutada con credenciales temporales, pendiente por fallas de sesión/layout |
| Fichas automatizadas | [`docs/pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) referencia `ImportarDatosTest` **inexistente** |
| Informe de ejecución | [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) — consolidado Fase 5 |
| Matriz RF–Sprint–Test | [`docs/matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) |
| Evidencias (capturas, reportes) | **Pendiente** para cierre académico Sprint 10 |

---

## 9. Uso de normas ISO

- **ISO/IEC 25010**, **ISO/IEC 27000** e **ISO 9001** se usan **solo como referencia orientativa académica** (véase [`sprints/sprint 10.md`](../sprints/sprint%2010.md)).
- Matrices de alineación progresiva: [`docs/calidad/alineacion-iso.md`](calidad/alineacion-iso.md) y documentos en [`docs/calidad/`](calidad/).
- **No se afirma certificación ISO** ni auditoría externa obtenida.
- **No se afirma cumplimiento normativo certificado** (p. ej. Ley 29733); el DRS menciona privacidad como requisito formal — implementación parcial vía roles y Sanctum, no equivalente a certificación.

---

## 10. Decisiones vigentes V1

| Decisión | Evidencia |
|----------|-----------|
| Sede operativa **Chilca** en UI y consultas por defecto | [`AGENTS.md`](../AGENTS.md), [`frontend/src/lib/sedeOperativa.js`](../frontend/src/lib/sedeOperativa.js), [`backend/app/Support/SedeOperativa.php`](../backend/app/Support/SedeOperativa.php) |
| Conteos Fase 1 con Auquimarca en BD | **No** son alcance operativo V1 — datos históricos del entorno auditado; ver nota en [`docs/pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) §4 |
| Frontend **no** llama a Flask directamente | Riesgo vía `POST /api/estudiantes/{id}/procesar-riesgo`; [`frontend/src/lib/api.js`](../frontend/src/lib/api.js) |
| Laravel orquesta ML | [`backend/app/Services/MlRiskService.php`](../backend/app/Services/MlRiskService.php) → `ML_SERVICE_URL` en [`backend/.env.example`](../backend/.env.example) |
| MySQL vía Docker | Servicio `db-mysql`, host `3307` |
| Autorización real en backend | Spatie + middleware; frontend solo oculta UI |
| Alcance formal DRS vía resumen en repo | [`docs/arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) — PDF externo |
| No eliminar estudiantes | Política README — [`README.md`](../README.md) |

---

## 11. Pendientes post-consolidación (Fase 9)

Documentación principal **completada** (Fases 1–9). Paquete **Markdown** v2.1 listo para revisión humana.

Pendientes menores:

- Corregir [`docs/pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) (`ImportarDatosTest` inexistente).
- Implementar en código RF planificados: RF-04 (V1 mínimo cerrado Fase 2E; Cypress mínimo agregado en Fase 2F — ver §3); RF-10, RF-16, RF-18. **RF-19** implementado V1 (backend + frontend + smoke manual aprobado). **RF-20** parcial / en avance (Fases 4B–4D: RBAC, backend y frontend V1; pendiente smoke manual y cierre).

**Etapa posterior (opcional):** conversión formal DRS v2.1 y anexos a PDF/Word tras revisión humana.

Índice maestro: [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) · entrada `docs/`: [`README.md`](README.md).

---

*Documento generado en Fase 1 del plan de actualización documental. Fecha de verificación técnica: 2026-06-09. RF-19 cerrado V1 Fases 3B–3E — 2026-06-23.*
