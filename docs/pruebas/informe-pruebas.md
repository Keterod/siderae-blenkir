# Informe de pruebas — SIDERAE-Blenkir

Consolidación del **estado de pruebas conocido** para cierre académico V1. Fecha de referencia principal: **2026-06-09** (Fase 1 documental).

**Importante:** este informe **no** certifica ejecución completa en la Fase 5. Solo documenta evidencias ya registradas y archivos detectados en el repositorio.

Referencias: [`docs/pruebas/hallazgos-fase1-documentacion.md`](hallazgos-fase1-documentacion.md) · [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) · [`docs/limitaciones.md`](../limitaciones.md) · [`docs/aula-notas-excel.md`](../aula-notas-excel.md) · [`docs/calidad/alineacion-iso.md`](../calidad/alineacion-iso.md).

---

## 1. Propósito

Este informe consolida:

- Qué pruebas **existen** en el repositorio (PHPUnit Feature/Unit),
- Qué se **ejecutó** en la auditoría Fase 1 y con qué resultado,
- Limitaciones de entorno (memoria PHP, Cypress mínimo solo RF-04, BD no seed limpio),
- Brechas para defensa académica honesta del prototipo V1.

No sustituye al Plan de Pruebas formal ni constituye auditoría externa.

---

## 2. Alcance del informe

| Incluido | Excluido |
|----------|----------|
| Backend **PHPUnit / Feature / Unit** en `backend/tests/` | Suite Cypress completa del sistema |
| Comandos documentados en Fase 1 | Certificación **ISO** |
| Pruebas manuales **recomendadas** (sin ejecutar en Fase 5) | Auditoría externa o pentest |
| Conteos BD local auditada (solo lectura) | Afirmación de «suite aprobada» global |

---

## 3. Ambiente de prueba conocido

Basado en [`docs/pruebas/hallazgos-fase1-documentacion.md`](hallazgos-fase1-documentacion.md):

| Elemento | Detalle |
|----------|---------|
| Orquestación | **Docker Compose** — 4 servicios Up (`docker compose ps`) |
| Backend | Laravel **^13** ([`backend/composer.json`](../backend/composer.json)) |
| Base de datos | MySQL 8 en contenedor (`localhost:3307` → `3306`) |
| Frontend | Vite/React (`localhost:5173`) — no probado E2E en Fase 1 |
| ML | Flask determinístico (`localhost:5000`) |
| BD | **Local auditada** con historial previo — **no** seed limpio oficial |
| Sede V1 | Operación documentada **Chilca**; conteos Auquimarca = histórico/local |

**Advertencia:** no se ejecutó `migrate:fresh --seed` en Fase 1 (destructivo). Los conteos no representan un entorno de referencia único del producto.

---

## 4. Comandos ejecutados conocidos

Solo comandos registrados en Fase 1 (2026-06-09) y **Fase 2E RF-04** (2026-06-10).

| Comando | Fecha / fase | Resultado | Observación |
|---------|--------------|-----------|-------------|
| `docker compose ps` | Fase 1 | Exit 0 — **4 servicios Up** | backend, frontend, ml, mysql |
| `docker compose exec app-backend php artisan test` | Fase 1 | **Exit 2 — incompleto** | OOM 128M en `ExcelAulaTest`; ~277 tests ✓ antes del fallo |
| `docker compose exec app-backend php -d memory_limit=512M artisan test --filter=ExcelAulaTest` | Fase 1 | Exit 0 — **8 passed**, 32 assertions | ~136 s |
| `php artisan tinker --execute="Estudiante::count()"` | Fase 1 | **449** total | Solo lectura |
| `php artisan tinker --execute="… sede chilca/auquimarca …"` | Fase 1 | **253** Chilca / **196** Auquimarca | Histórico/local; no multi-sede V1 |
| `php artisan tinker --execute="User::count()"` | Fase 1 | **8** usuarios | Solo lectura |
| `php artisan tinker --execute="NotaSemanal::count()"` | Fase 1 | **15** | Solo lectura |
| `php artisan tinker --execute="AsistenciaDiaria / SeccionAula"` | Fase 1 | **35** / **69** | Solo lectura |
| `docker compose exec app-backend php artisan test --filter=ReporteConductualTest` | Fase 2E RF-04 | Exit 0 — **8 passed**, **26 assertions** | ~15.5 s |
| `docker compose exec app-frontend npm run build` | Fase 2E RF-04 | Exit 0 — Vite build | ~7.7 s, 108 módulos |
| `docker compose exec app-backend php artisan db:seed --class=PermissionsSeeder` | Fase 3E RF-19 | Exit 0 — seed OK | — |
| `docker compose exec app-backend php artisan test --filter=SemaforoCompletitudTest` | Fase 3C/E RF-19 | Exit 0 — **11 passed**, **55 assertions** | ~14.2 s |
| `docker compose exec app-frontend npm run build` | Fase 3D/E RF-19 | Exit 0 — Vite build | ~5.7 s, 109 módulos |
| `docker compose exec app-backend php artisan test --filter=HistorialRiesgoTest` | Fase 4C RF-20 | Exit 0 — **12 passed**, **30 assertions** | ~17.4 s |
| `docker compose exec app-frontend npm run build` | Fase 4D RF-20 | Exit 0 — Vite build | ~8.2 s, 110 módulos |
| `docker compose exec app-backend php artisan db:seed --class=PermissionsSeeder` | Fase 4E RF-20 | Exit 0 — INFO Seeding database | — |
| `docker compose exec app-backend php artisan test --filter=HistorialRiesgoTest` | Fase 4E RF-20 | Exit 0 — **12 passed**, **30 assertions** | ~38.0 s |
| `docker compose exec app-backend php artisan route:list --path=historial-riesgo` | Fase 4E RF-20 | Ruta listada correctamente | — |
| `docker compose exec app-frontend npm run build` | Fase 4E RF-20 | Exit 0 — Vite build | ~10.6 s, 110 módulos |
| `docker compose exec app-backend php artisan test --filter=ReporteRiesgoAcademicoTest` | RF-16E | Exit 0 — **13 passed**, 36 assertions | — |
| `docker compose exec app-frontend npm run build` | RF-16E | Exit 0 — Vite build, chunk `ReporteRiesgoAcademicoPanel` generado | — |
| `docker compose exec app-frontend npm run lint` | RF-16E | Exit 1 — **88 problemas preexistentes**; `ReporteRiesgoAcademicoPanel.jsx` sin errores nuevos | Lint global no limpio; componente RF-16 limpio |
| `docker compose exec app-backend php artisan test --filter=DashboardInstitucionalTest` | RF-14C | Exit 0 — **16 passed**, 57 assertions | — |
| `docker compose exec app-backend php artisan test --filter=DashboardTest` | RF-14C regresión | Exit 0 — **12 passed**, 76 assertions | Dashboard legacy intacto |
| `docker compose exec app-frontend npm run build` | RF-14D | Exit 0 — Vite build, chunk `DashboardInstitucionalPanel` generado | — |
| `docker compose exec app-frontend npm run lint` | RF-14D | Exit 1 — **88 problemas preexistentes**; `DashboardInstitucionalPanel.jsx` sin errores nuevos | Lint global no limpio; componente RF-14 limpio |

---

## 5. Resultado resumido

| Tipo de prueba | Estado | Resultado conocido | Evidencia | Limitación |
|----------------|--------|-------------------|-----------|------------|
| PHPUnit suite completa | **Parcial / incompleta** | Fallo técnico OOM antes de fin | Fase 1 salida consola | `memory_limit=128M` insuficiente para Excel |
| `ExcelAulaTest` aislado | **Ejecutado (Fase 1)** | 8 passed @ 512M | Fase 1 | No implica suite global verde |
| Tests previos a OOM | **Parcial** | ~277 passed (conteo salida) | Fase 1 | No inventariados uno a uno en informe |
| Cypress auth/logout | **Infraestructura configurada** | `auth-login`: 2 passed, 2 fallan por falta de `CYPRESS_E2E_EMAIL`; `logout`: 2 fallan por falta de credenciales | `auth-login.cy.js`, `logout.cy.js` | No implica suite global completa |
| Cypress auth/logout 2H.1 | **Ejecutado con fallas** | Build OK; Cypress con credenciales temporales falla por sesión/layout; corrida auth aislada bloqueada | `commands.js`, `auth-login.cy.js`, `logout.cy.js` | Pendiente estabilizar sesión local/Sanctum |
| Cypress / E2E RF-04 | **Configurado mínimo** | Ejecutado; no completó por falta de `CYPRESS_E2E_EMAIL` | `frontend/cypress/e2e/rf04-reportes-conductuales.cy.js` | Solo smoke RF-04; no suite global |
| Pruebas manuales por rol | **Recomendadas** | RF-04 smoke **pendiente navegador** | [`smoke-rf04-reportes-conductuales.md`](smoke-rf04-reportes-conductuales.md) | API/build verdes Fase 2E |
| `ReporteConductualTest` aislado | **Ejecutado (Fase 2E)** | 8 passed, 26 assertions | Fase 2E | RF-04 cierre |
| `SemaforoCompletitudTest` aislado | **Ejecutado (Fase 3C)** | 11 passed, 55 assertions | Fase 3C | RF-19 backend |
| `HistorialRiesgoTest` aislado | **Ejecutado (Fases 4C–4E)** | 12 passed, 30 assertions | Fase 4E | RF-20 backend |
| Build frontend RF-20 | **Ejecutado (Fases 4D–4E)** | Vite build OK, 110 módulos | Fase 4E | RF-20 frontend |
| `ReporteRiesgoAcademicoTest` aislado | **Ejecutado (RF-16E)** | 13 passed, 36 assertions | RF-16E | RF-16 backend |
| Build frontend RF-16E | **Ejecutado** | Vite build OK, chunk `ReporteRiesgoAcademicoPanel` generado | RF-16E | RF-16 frontend |
| `DashboardInstitucionalTest` aislado | **Ejecutado (RF-14E)** | 16 passed, 57 assertions | RF-14E | RF-14 backend |
| Build frontend RF-14D/E | **Ejecutado** | Vite build OK, chunk `DashboardInstitucionalPanel` generado | RF-14E | RF-14 frontend |
| Smoke manual RF-19 navegador | **Ejecutado** | Bloque visible, estado amarillo, razones correctas, sin selector sede, perfil estable | Registro manual | RF-19 UI |
| Smoke manual RF-20 navegador | **Pendiente** | No se dispone de navegador/E2E en este entorno; validar visualmente tabla historial en perfil estudiante | — | RF-20 UI |
| Conteos BD tinker | **Ejecutado (Fase 1)** | Ver §10 | Solo lectura | BD no seed oficial |

---

## 6. Suite backend PHPUnit / Feature

**49 archivos** `.php` detectados bajo [`backend/tests/`](../backend/tests/). Listado por grupo (existencia en repo; **no** implica ejecución individual en Fase 5).

### Auth

- `Feature/Auth/AuthenticationTest.php`
- `Feature/Auth/RegistrationTest.php`
- `Feature/Auth/EmailVerificationTest.php`
- `Feature/Auth/PasswordResetTest.php`

### Estudiantes y académico legacy

- `Feature/EstudianteTest.php`
- `Feature/EstudianteInicialTest.php`
- `Feature/DatosAcademicosTest.php`
- `Feature/MateriaTest.php`

### Dashboard, riesgo, alertas, auditoría, conductuales

- `Feature/DashboardTest.php` — **12 passed** (RF-14 regresión, 76 assertions)
- `Feature/DashboardInstitucionalTest.php` — **16 passed** (RF-14, 57 assertions) — 401/403, permiso separado, sede Chilca, resumen, completitud, distribución por grado/sección, últimos riesgos, filtros, no recalcula riesgo, no llama Flask, dashboard legacy intacto.
- `Feature/RiesgoTest.php` — **38 passed** (RF-06D/E, 125 assertions)

### Dashboard institucional RF-14

- `Feature/DashboardInstitucionalTest.php` — **16 passed**, 57 assertions.
- `Feature/DashboardTest.php` regresión — **12 passed**, 76 assertions.
- Build frontend RF-14D — **OK** (chunk `DashboardInstitucionalPanel` generado).
- Lint frontend RF-14D — **88 problemas preexistentes**; `DashboardInstitucionalPanel.jsx` sin errores nuevos.
- Smoke manual RF-14 navegador — **pendiente** por falta de navegador en el entorno.
- `Feature/SemaforoCompletitudTest.php` — **11 passed** (RF-06E regresión)
- `Feature/HistorialRiesgoTest.php` — **12 passed** (RF-06E regresión)
- **Total RF-06E:** 61 tests, 210 assertions. Flask `/predict` validado 5 escenarios manuales (bajo/medio/alto/sin reportes/backward compat).
- `Feature/ReporteRiesgoAcademicoTest.php` — **13 passed** (RF-16E, 36 assertions) — 401/403, consulta, filtros, paginación, orden, sede Chilca, no recalcula riesgo, no llama Flask.
- Regresión RF-16E: `RiesgoTest` 38 passed + `SemaforoCompletitudTest` 11 passed + `HistorialRiesgoTest` 12 passed = **61 tests, 210 assertions**.
- Build frontend RF-16E — **OK** (`npm run build` exitoso, chunk `ReporteRiesgoAcademicoPanel` generado).
- Lint frontend RF-16E — ejecutado; **88 problemas preexistentes** en otros componentes; `ReporteRiesgoAcademicoPanel.jsx` sin errores nuevos.
- Smoke manual RF-16E navegador — **pendiente** por falta de navegador en el entorno.
- Build frontend RF-14D — **OK** (`npm run build` exitoso, chunk `DashboardInstitucionalPanel` generado).
- Lint frontend RF-14D — ejecutado; **88 problemas preexistentes** en otros componentes; `DashboardInstitucionalPanel.jsx` sin errores nuevos.
- Smoke manual RF-14D navegador — **pendiente** por falta de navegador en el entorno.
- `Feature/AlertaIntervencionTest.php`
- `Feature/ReporteConductualTest.php` — **8 passed** Fase 2E
- `Feature/DemoProcesarRiesgosCommandTest.php`
- `Feature/ActivityLogTest.php`

### Usuarios

- `Feature/GestionUsuariosTest.php`

### Curricular

- `Feature/Curricular/ActivoUniqueKeyHistorialTest.php`
- `Feature/Curricular/AsignacionDocenteValidacionesTest.php`
- `Feature/Curricular/AsistenciaDiariaTest.php`
- `Feature/Curricular/CalendarioAcademicoTest.php`
- `Feature/Curricular/CompetenciaCapacidadCrudTest.php`
- `Feature/Curricular/ComponentesCalificacionNivelTest.php`
- `Feature/Curricular/ConfiguracionBimestralDefaultsTest.php`
- `Feature/Curricular/ConfiguracionBimestralGradoTest.php`
- `Feature/Curricular/ConfiguracionPesoEvaluacionTest.php`
- `Feature/Curricular/CurricularApiTest.php`
- `Feature/Curricular/CurricularSeedersTest.php`
- `Feature/Curricular/EvaluacionBimestralApiTest.php`
- `Feature/Curricular/EvaluacionBimestralTest.php`
- `Feature/Curricular/ExcelAulaTest.php`
- `Feature/Curricular/NotasSemanalesComponentesDinamicosTest.php`
- `Feature/Curricular/NotasSemanalesInicialTest.php`
- `Feature/Curricular/PlantillaRegistroAuxiliarExcelTest.php`
- `Feature/Curricular/ResumenAcademicoTest.php`
- `Feature/Curricular/SeccionesAulasTest.php`
- `Feature/Curricular/CurricularApiTestCase.php`, `EvaluacionBimestralTestCase.php`, `Concerns/PreparaFlujoNotasSemanalesDinamicas.php` (soporte)

### Seeders

- `Feature/Seeders/CriteriosEvaluacionInicialSeederTest.php`
- `Feature/Seeders/DemoCurricularOperativoSeederTest.php`
- `Feature/Seeders/DemoEstudiantesCurricularesSeederTest.php`
- `Feature/Seeders/InicialIIBimestre2026SeederTest.php`

### Unit

- `Unit/Curricular/CeCalculatorServiceTest.php`
- `Unit/Curricular/CeComponentesDinamicosServiceTest.php`
- `Unit/Curricular/EquivalenciaGradoServiceTest.php`
- `Unit/Curricular/PesoEvaluacionResolverTest.php`
- `Unit/Curricular/EvaluacionBimestral/PesosComponentesServiceTest.php`
- `Unit/ExampleTest.php`

### Otros

- `Feature/ExampleTest.php`
- `TestCase.php`, `Support/RiesgoCurricularFixtures.php`

Mapeo RF ↔ test: [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) §7.

---

## 7. Pruebas de seguridad 401/403

Resumen desde [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) §12:

| Área | Tests con 401 | Tests con 403 | Estado |
|------|---------------|---------------|--------|
| Dashboard + export | Sí (`DashboardTest`) | Sí | Confirmado en código |
| Estudiantes | Sí | Sí | Confirmado |
| Alertas / intervenciones | Sí | Sí | Confirmado |
| Riesgo | Sí | Sí | Confirmado |
| Usuarios | Pendiente | Sí (`GestionUsuariosTest`) | Parcial |
| Curricular (múltiples) | Sí (`CurricularApiTest`, etc.) | Sí | Parcial — no exhaustivo |
| Excel aula | Pendiente | Sí (`ExcelAulaTest`) | Parcial |
| Legacy materias/datos | Sí | Sí | Confirmado |

**Cypress:** no aplica. Cobertura 401/403 **no exhaustiva** en todas las rutas `/api/curricular/*`.

---

## 8. Pruebas Excel Aula

| Hecho | Detalle |
|-------|---------|
| Falla suite completa | Fatal error: `Allowed memory size of 134217728 bytes exhausted` en `ExcelAulaTest::administrador_puede_descargar_excel_aula` (Fase 1) |
| Causa | Generación ZIP/Excel vía Maatwebsite + ZipStream con límite PHP **128M** del contenedor |
| Reintento | `php -d memory_limit=512M artisan test --filter=ExcelAulaTest` → **8 passed**, 32 assertions |
| Interpretación | Fallo de **infraestructura de prueba**, no necesariamente funcional en la clase aislada |
| Recomendación | Documentar en CI/local: `php -d memory_limit=512M artisan test` o subir `memory_limit` en imagen PHP de tests |

Tests relacionados: `ExcelAulaTest.php`, `PlantillaRegistroAuxiliarExcelTest.php` (import plantilla — posible demanda similar de memoria).

Documentación funcional del módulo: [`docs/aula-notas-excel.md`](../aula-notas-excel.md).

### 8.1 Módulo aula / notas / Excel (curricular)

| Flujo | Test principal | Descarga | Import | Resultado conocido |
|-------|----------------|----------|--------|-------------------|
| Excel por aula | `ExcelAulaTest` | `GET /excel-aula` | **No** | 8 passed @ **512M** (Fase 1); OOM @ 128M en suite |
| Plantilla registro auxiliar | `PlantillaRegistroAuxiliarExcelTest` | `GET /plantilla-excel` | `POST /importar-excel` | Detectado — import **curricular**, no SIAGIE |
| Notas semanales bulk | `NotasSemanales*`, `CurricularApiTest` | — | — | Detectado |
| Asistencia diaria | `AsistenciaDiariaTest` | — | — | Detectado — RF-02 curricular |

---

## 9. Pruebas frontend / E2E

| Aspecto | Estado |
|---------|--------|
| **Cypress** | **Infraestructura global parcial** — config, comandos comunes, `auth-login.cy.js`, `logout.cy.js` y spec RF-04 |
| Jest / Vitest UI | **No confirmado** como suite de aceptación |
| E2E automatizado | **Parcial** — auth/logout + smoke RF-04; no cubre todos los módulos |
| Pruebas manuales recomendadas | Flujos por rol en [`docs/manual-usuario.md`](../manual-usuario.md): login, dashboard, estudiantes, notas (lectura/registro según rol), asistencia, alertas, export PDF |

Sprint 9 planea E2E más amplio; estado actual = **infraestructura Cypress global parcial (Fase 2H)**, no suite completa del sistema. Ver [`cypress-rf04.md`](cypress-rf04.md) y [`plan-cypress-e2e-global.md`](plan-cypress-e2e-global.md).

---

## 10. Conteos de BD local auditada

Fuente: Fase 1 — **solo lectura**, entorno Docker local con historial de datos.

| Métrica | Valor | Nota |
|---------|-------|------|
| Estudiantes total | **449** | Entorno auditado |
| Sede Chilca | **253** | Sede operativa V1 |
| Sede Auquimarca | **196** | **Histórico/local** — no operación multi-sede V1 |
| Usuarios | **8** | Demo + admin |
| Notas semanales curriculares | **15** | — |
| Asistencias diarias curriculares | **35** | — |
| Secciones/aulas | **69** | — |
| Materias/notas/asistencias legacy | **0** | Flujo UI = curricular |
| Alertas / índices riesgo (Fase 1) | **0** | Pueden generarse tras procesar riesgo |

**Aclaraciones:**

- Conteos **no** son constantes universales del producto.
- **No** se afirma seed limpio oficial.
- Tras `migrate:fresh --seed` los números pueden diferir (no ejecutado en Fase 1).

Distribución roles (Fase 1): 2 administrador, 3 docente, 1 coordinador, 1 psicólogo, 1 directivo.

---

## 11. Defectos / riesgos conocidos

| ID | Descripción | Severidad | Evidencia |
|----|-------------|-----------|-----------|
| D-01 | OOM en suite completa @ 128M | Media (infra) | Fase 1 |
| D-02 | `ImportarDatosTest` referenciado en fichas pero **inexistente** | Baja (doc) | [`Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) |
| D-03 | Cypress solo cubre smoke RF-04 | Media (cobertura UI) | `frontend/cypress/` |
| D-04 | 401/403 no exhaustivos | Media | `seguridad-roles-permisos.md` |
| D-05 | Activity log parcial | Media | `ActivityLogTest.php` |
| D-06 | Botón procesar riesgo ausente en UI perfil | Media (trazabilidad RF-06) | `EstudiantePerfilRiesgo.jsx`, manual usuario |
| D-07 | Recuperación contraseña UI pendiente | Baja | `LoginForm.jsx` |
| D-08 | `POST /register` público | Alta (producción) | `RegistrationTest.php` |
| D-09 | Conteos demo README vs BD desalineados históricamente | Baja | Fase 1, README actualizado parcialmente |
| D-10 | RF-10–12, RF-18, RF-20 sin tests; RF-04 con `ReporteConductualTest`; RF-19 con `SemaforoCompletitudTest` | Media | Matriz §8; Fases 2E, 3C |

---

## 12. Recomendaciones

1. **Ejecutar suite** con `php -d memory_limit=512M artisan test` y registrar salida completa en anexo académico.
2. **Definir entorno de referencia** con un único `migrate:fresh --seed` documentado y conteos esperados.
3. **Corregir fichas** que citan `ImportarDatosTest`; enlazar tests reales.
4. **Completar pruebas 401** en rutas curriculares pendientes.
5. **Smoke manual por rol** siguiendo [`manual-usuario.md`](../manual-usuario.md) con registro escrito.
6. **Cypress:** ejecutar y registrar `npm run cy:run` para RF-04; mantener explícito que no es suite E2E global.
7. **Completar pruebas de seguridad** y activity log si RF-17 exige cierre formal.
8. Usar este informe + [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) como entrada para **DRS actualizado**.

Comando sugerido (no ejecutado en Fase 5):

```bash
docker compose exec app-backend php -d memory_limit=512M artisan test
```

Filtros útiles (documentados en README):

```bash
docker compose exec app-backend php artisan test --filter=Curricular
docker compose exec app-backend php artisan test --filter=Riesgo
docker compose exec app-backend php artisan test --filter=ReporteConductualTest
```

---

## 14. RF-04 — Reportes conductuales (Fase 2E)

| Evidencia | Archivo / comando | Resultado 2026-06-10 |
|-----------|-------------------|----------------------|
| Tests Feature | `ReporteConductualTest.php` | **8 passed**, 26 assertions |
| Build frontend | `npm run build` en `app-frontend` | Exit 0 |
| Cypress RF-04 | [`cypress-rf04.md`](cypress-rf04.md) / `npm run cy:run` | Cypress verificado; spec detenido por falta de `CYPRESS_E2E_EMAIL` |
| Smoke manual UI | [`smoke-rf04-reportes-conductuales.md`](smoke-rf04-reportes-conductuales.md) | **No ejecutado en navegador**; casos API cubiertos por PHPUnit |
| Cierre documental | Matriz, limitaciones, NC-16, plan AI-DLC | Fase 2E completada |

Estado funcional: **Implementado V1 mínimo** (perfil estudiante Chilca; sin módulo global).

---

## 13. RF-19 — Semáforo de completitud de datos (Fase 3C)

| Evidencia | Archivo / comando | Resultado 2026-06-23 |
|-----------|-------------------|----------------------|
| Tests Feature | `SemaforoCompletitudTest.php` | **11 passed**, 55 assertions |
| Servicio | `backend/app/Services/CompletitudDatosService.php` | Cálculo determinístico; sin Flask |
| Endpoint | `GET /api/estudiantes/{estudiante}/semaforo-completitud` | Protegido por Sanctum + `ver_semaforo_completitud` |
| Sede V1 | `SemaforoCompletitudController.php` | Solo Chilca; Auquimarca rechazada 403 |
| Frontend | Componente en perfil estudiante | **Implementado Fase 3D** |
| Smoke manual navegador | Perfil estudiante Chilca | **Aprobado** — bloque visible, estado amarillo, mensaje y razones correctas, sin selector de sede, perfil estable |

Estado funcional: **Implementado V1 con smoke manual aprobado**. Cypress global no ejecutado.

---

## 14. Conclusión

SIDERAE-Blenkir V1 dispone de una **biblioteca considerable de pruebas backend PHPUnit** (auth, estudiantes, dashboard, riesgo, alertas, módulo curricular, Excel, seeders y servicios unitarios), con evidencia de **401/403** en módulos principales.

Sin embargo:

- La **suite completa no finalizó** en Fase 1 por límite de memoria PHP (128M).
- **`ExcelAulaTest` pasó aisladamente** con 512M (8 tests).
- Cypress existe solo como smoke mínimo RF-04; no hay suite E2E completa confirmada.
- Varios **RF del DRS** permanecen **pendientes o parciales** (SIAGIE, derivación, comunicación familia, semáforo, reentrenamiento ML, UI riesgo).
- La BD auditada refleja **entorno local** con datos históricos (incl. Auquimarca), **no** un seed oficial único.

El conjunto es **evidencia académica válida para prototipo V1** si se presentan estas limitaciones de forma explícita ante tribunal o asesoría. **No** constituye certificación de calidad ISO ni aprobación formal de producto.

---

*Documento generado en Fase 5 del plan de actualización documental SIDERAE-Blenkir. RF-19 cerrado V1 Fase 3E — 2026-06-23.*
