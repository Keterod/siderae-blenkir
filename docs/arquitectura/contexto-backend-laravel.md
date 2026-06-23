# Contexto backend Laravel (v2 — Fase 2)

## Rol del backend

Capa central de negocio: autenticación Sanctum, autorización Spatie (23 permisos), API REST, persistencia MySQL, módulo curricular, orquestación ML, alertas/intervenciones, gestión de usuarios y auditoría parcial.

Referencia cruzada: [`docs/limitaciones.md`](../limitaciones.md) · [`docs/api.md`](../api.md) · [`docs/manual-tecnico.md`](../manual-tecnico.md)

---

## Relación con el DRS

- Alcance formal: `DRS_SIDERAE_Blenkir_v1.pdf` (**externo al repo**).
- Resumen en repo: [`contexto-drs-requerimientos.md`](contexto-drs-requerimientos.md).
- El backend confirma gran parte del flujo operativo; varios RF del DRS siguen **pendientes o parciales**.

---

## Stack verificado (`backend/composer.json`)

| Paquete | Uso |
|---------|-----|
| Laravel ^13, PHP ^8.3 | Framework API |
| laravel/sanctum | Sesión SPA |
| spatie/laravel-permission | RBAC |
| spatie/laravel-activitylog | Auditoría parcial |
| maatwebsite/excel | Import/export Excel curricular |
| barryvdh/laravel-dompdf | Export PDF dashboard |
| laravel/breeze | Auth (`routes/auth.php`) |

---

## Rutas — autenticación (`backend/routes/auth.php`)

| Método | Ruta | Notas |
|--------|------|-------|
| POST | `/login` | Guest |
| POST | `/logout` | Auth |
| POST | `/register` | Guest — **público**; restringir en producción |
| POST | `/forgot-password`, `/reset-password` | Guest |
| GET | `/verify-email/{id}/{hash}` | Auth + signed |

Sanctum CSRF: `GET /sanctum/csrf-cookie` (ruta framework).

---

## Rutas — API core (`backend/routes/api.php`)

Prefijo `/api` salvo `/api/me` y health.

| Grupo | Permiso(s) | Endpoints principales |
|-------|------------|----------------------|
| Health | — | `GET /health` |
| Sesión | `auth:sanctum` | `GET /me`, `GET /user` |
| Usuarios | `gestionar_usuarios` | CRUD + activar/desactivar/restablecer contraseña |
| Dashboard | `ver_dashboard` | `GET /dashboard`, `GET /dashboard/export` |
| Materias (legacy) | `gestionar_materias` / lectura OR `registrar_datos_academicos` | CRUD + activar/desactivar |
| Estudiantes | `gestionar_estudiantes` / lectura OR `registrar_datos_academicos` | listado, detalle, alta, edición |
| Datos académicos legacy | `registrar_datos_academicos` | notas/asistencias/VSE anidadas; lotes `/notas/lote`, `/asistencias/lote` |
| Riesgo | `procesar_riesgo` | `POST /estudiantes/{id}/procesar-riesgo` |
| Alertas | `ver_alertas` | listado, detalle |
| Intervención | `registrar_intervencion` | intervenciones, cierre |

Catálogo completo curricular: [`docs/api.md`](../api.md) § Curricular.

---

## Módulo curricular (`/api/curricular/*`)

Confirmado en código. Grupos principales (todos `auth:sanctum` + `permission:*`):

| Área | Permisos ejemplo | Rutas ejemplo |
|------|------------------|---------------|
| Catálogo | autenticado / `ver_malla_curricular` | `/catalogo/niveles-grados`, `/areas`, `/periodos` |
| Años y periodos | `gestionar_calendario_academico` | `/anios-escolares/*`, `/periodos-academicos/*` |
| Malla | `ver_malla_curricular`, `gestionar_malla_curricular` | `/mallas/*`, `/temas/*` |
| Competencias | `gestionar_competencias_capacidades` | CRUD competencias/capacidades |
| Pesos C/L/T | `configurar_pesos_evaluacion` | `/pesos/*` |
| Componentes calificación | `gestionar_componentes_calificacion` | `/componentes-calificacion/*` |
| Secciones/aulas | `gestionar_secciones_aulas` | `/secciones-aulas/*` |
| Asignación docente | `gestionar_asignaciones_docente` | `/asignaciones-docente/*`, `/docentes` |
| Notas semanales | `registrar_notas_semanales`, `ver_notas_academicas` | `/notas-semanales/*`, **import Excel** |
| Excel aula | `descargar_excel_aula` | `GET /excel-aula` |
| Evaluación bimestral | `configurar_evaluacion_bimestral`, notas | `/evaluacion-bimestral/*` |
| Asistencia diaria | `registrar_asistencia_curricular`, `ver_asistencia_curricular` | `/asistencias-diarias/*` |
| Resumen académico | `ver_notas_academicas` | `GET /estudiantes/{id}/resumen-academico` |

**Import Excel curricular confirmado:** `POST /curricular/notas-semanales/importar-excel` — **no** es importación SIAGIE global (RF-01 pendiente).

---

## Controladores principales

### API legacy + riesgo + alertas

`EstudianteController`, `NotaController`, `AsistenciaController`, `VariableSocioeconomicaController`, `NotaBatchController`, `AsistenciaBatchController`, `MateriaController`, `ProcesarRiesgoController`, `AlertaController`, `IntervencionController`, `AlertaCierreController`, `DashboardController`, `UsuarioController`.

### API curricular (`App\Http\Controllers\Api\Curricular\`)

`CatalogoCurricularController`, `MallaCurricularController`, `TemaSemanalController`, `CompetenciaCapacidadController`, `ConfiguracionPesoEvaluacionController`, `ComponenteCalificacionController`, `SeccionAulaController`, `AsignacionDocenteController`, `NotaSemanalController`, `EvaluacionBimestralController`, `AsistenciaDiariaController`, `AnioEscolarController`, `PeriodoAcademicoAdminController`, `ResumenAcademicoController`, `DocenteAulaCurricularController`.

---

## Servicios relevantes

| Servicio | Archivo | Función |
|----------|---------|---------|
| ML | `App\Services\MlRiskService` | HTTP → Flask `/predict` |
| Riesgo | `App\Services\RiesgoAcademicoService` | Payload + persistencia |
| Excel plantilla | `App\Services\Curricular\PlantillaRegistroAuxiliarExcelService` | Generación/import Excel |
| CE / bimestre | `App\Services\Curricular\*` | Cálculos curriculares |

Config ML: [`backend/config/services.php`](../../backend/config/services.php) → `services.ml.url` ← `ML_SERVICE_URL`.

---

## Seeders (`backend/database/seeders/`)

| Seeder | Rol |
|--------|-----|
| `RolesSeeder`, `PermissionsSeeder` | 5 roles, 23 permisos |
| `DemoUsersSeeder` | Usuarios demo por rol |
| `CurricularModuleSeeder` | Catálogo curricular base |
| `DemoEstudiantesCurricularesSeeder` | Estudiantes demo |
| `DemoCurricularOperativoSeeder` | Aula operativa, notas, asistencia demo |
| `DemoAcademicDataSeeder` | Legacy materias/notas (**no** en seed por defecto) |

Orquestación: `DatabaseSeeder.php`. **Docker no ejecuta seed** al arrancar — solo migrate.

---

## Modelos — pendientes sin API

| Modelo / tabla | Estado |
|----------------|--------|
| `ReporteConductual` | Migración sí; **sin rutas API** (RF-04 pendiente) |
| Comunicaciones familiares | Migración sí; **sin rutas API** (RF-12 pendiente) |

---

## Integración ML

```text
ProcesarRiesgoController → RiesgoAcademicoService → MlRiskService
  → POST {ML_SERVICE_URL}/predict
  → persistencia IndiceRiesgo, posible Alerta
```

Ver [`docs/ml-service.md`](../ml-service.md).

---

## Auditoría (`activity_log`)

- Registro manual en controladores API (estudiante, notas, asistencia, VSE, riesgo, alerta, intervención, cierre, export PDF dashboard, acciones curriculares seleccionadas).
- Tests: `ActivityLogTest.php`.
- **Parcial** frente RF-17: sin UI consulta logs; cobertura no total.

---

## Pruebas Feature

~49 archivos en `backend/tests/`. Destacados:

- Core: `EstudianteTest`, `DatosAcademicosTest`, `RiesgoTest`, `AlertaIntervencionTest`, `DashboardTest`, `GestionUsuariosTest`, `ActivityLogTest`
- Curricular: `tests/Feature/Curricular/*` (incl. `PlantillaRegistroAuxiliarExcelTest`, `ExcelAulaTest`)
- Auth: `AuthenticationTest`, `RegistrationTest`, etc.

**Nota Fase 1:** suite completa con `memory_limit` 128M falló por OOM en `ExcelAulaTest`; pasó con 512M. Ver [`docs/pruebas/hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md).

---

## Estado backend por RF (resumen)

| RF | Estado |
|----|--------|
| RF-01 | **Parcial** — manual + Excel curricular; SIAGIE pendiente |
| RF-02 | **Confirmado** — legacy + asistencia curricular |
| RF-05 | **Parcial** — API sí; UI VSE pausada |
| RF-06/07 | **Confirmado** — integración ML determinística |
| RF-08–09, RF-13 | **Confirmado** |
| RF-15 | **Confirmado** — incl. API usuarios |
| RF-14/16 | **Parcial** — dashboard + PDF dashboard |
| RF-17 | **Parcial** — activitylog |
| RF-04 | **Confirmado** — V1 mínimo perfil estudiante |
| RF-10–12, RF-18 | **Pendiente** |
| RF-19 | **Backend implementado** — `CompletitudDatosService`, endpoint y tests; UI pendiente |

---

*Actualizado: Fase 2 documental (2026-06-09). RF-19 backend actualizado Fase 3C (2026-06-23).*

