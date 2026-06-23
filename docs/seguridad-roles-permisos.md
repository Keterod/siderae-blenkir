# Seguridad, roles y permisos — SIDERAE-Blenkir

Documento vigente (Fase 3 documental; actualización Fase 2B RF-04; Fase 3B–3C RF-19; Fase 4B RF-20). Fecha de verificación en código: **2026-06-23**.

**Fuentes primarias:** [`backend/routes/api.php`](../backend/routes/api.php), [`backend/routes/auth.php`](../backend/routes/auth.php), [`backend/database/seeders/PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php), [`backend/database/seeders/RolesSeeder.php`](../backend/database/seeders/RolesSeeder.php), [`frontend/src/App.jsx`](../frontend/src/App.jsx), [`frontend/src/context/AuthContext.jsx`](../frontend/src/context/AuthContext.jsx), [`backend/composer.json`](../backend/composer.json).

Referencia histórica (no usar como única fuente): [`docs/arquitectura/matriz-control-accesos-sprint8.md`](arquitectura/matriz-control-accesos-sprint8.md).

---

## 1. Propósito

Este documento describe el **estado vigente confirmado en código** de:

- Autenticación (sesión Sanctum / Breeze).
- Autorización (Spatie Permission en rutas API).
- Roles y permisos sembrados por defecto.
- Visibilidad de pantallas en el frontend React.
- Endpoints sensibles, pruebas 401/403 donde existen, auditoría parcial (`activity_log`) y brechas conocidas.

No sustituye al DRS ni constituye certificación de seguridad. Es apoyo para operación del prototipo académico V1 y trazabilidad documental.

---

## 2. Alcance V1

| Criterio | Estado documentado |
|----------|-------------------|
| Sede operativa | **Chilca** en UI y consultas por defecto ([`AGENTS.md`](../AGENTS.md)) |
| Campo `sede` en BD/API | Conservado (`chilca` / `auquimarca`) por compatibilidad y expansión futura |
| Operación multi-sede | **No activa** en V1; datos Auquimarca en BD local auditada = histórico, no alcance operativo |
| Certificación ISO | **No** — alineación orientativa académica únicamente (§14) |
| Entorno | Prototipo local Docker; datos demo/sintéticos |
| Cypress / E2E frontend | **No confirmado** en el repositorio |

---

## 3. Modelo de autenticación

| Elemento | Evidencia | Detalle |
|----------|-----------|---------|
| Laravel Sanctum | `laravel/sanctum` en [`backend/composer.json`](../backend/composer.json) | Sesión stateful SPA |
| Laravel Breeze | `laravel/breeze` en composer | Rutas en [`backend/routes/auth.php`](../backend/routes/auth.php) |
| CSRF | [`frontend/src/lib/api.js`](../frontend/src/lib/api.js) | `GET /sanctum/csrf-cookie` + header `X-XSRF-TOKEN` desde cookie |
| Cookies | `api.js` | `credentials: 'include'` en fetch |
| Login | `POST /login` | guest — [`AuthenticationTest.php`](../backend/tests/Feature/Auth/AuthenticationTest.php) |
| Logout | `POST /logout` | auth — idem |
| Registro público | `POST /register` | guest — **activo** ([`RegistrationTest.php`](../backend/tests/Feature/Auth/RegistrationTest.php)) |
| Perfil sesión | `GET /api/me` | `auth:sanctum` — devuelve `usuario`, `roles`, `permisos` ([`api.php`](../backend/routes/api.php) L41–48) |
| Usuario simple | `GET /api/user` | `auth:sanctum` — solo modelo usuario |
| Salud | `GET /api/health` | **Sin autenticación** |
| No autenticado | Tests Feature | **401** en rutas protegidas sin sesión (p. ej. [`DashboardTest.php`](../backend/tests/Feature/DashboardTest.php), [`EstudianteTest.php`](../backend/tests/Feature/EstudianteTest.php)) |

**Frontend:** [`AuthContext.jsx`](../frontend/src/context/AuthContext.jsx) llama `getMe()` al cargar; si falla con 401, deja sesión vacía y muestra login.

---

## 4. Modelo de autorización

| Capa | Mecanismo | Fuente |
|------|-----------|--------|
| Backend API | `middleware(['auth:sanctum', 'permission:…'])` | [`backend/routes/api.php`](../backend/routes/api.php) |
| Permisos OR | Sintaxis `permission:a\|b` en rutas | p. ej. materias, estudiantes GET |
| Frontend menú | `moduloPermitido()` por nombre de permiso (y excepción rol `directivo` en notas) | [`App.jsx`](../frontend/src/App.jsx) L217–261 |
| Policies Laravel | — | **No confirmado** uso de Policy classes en este análisis |

**Principio:** el frontend **oculta** módulos; el backend **rechaza** operaciones sin permiso (**403** confirmado en tests). Ocultar UI no sustituye la protección del servidor.

---

## 5. Roles confirmados

Fuente: [`RolesSeeder.php`](../backend/database/seeders/RolesSeeder.php) + asignación en [`PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php).

| Rol | Fuente | Descripción funcional | Estado |
|-----|--------|----------------------|--------|
| `administrador` | Seeder | Acceso total a permisos definidos (27) | Confirmado |
| `docente` | Seeder | Estudiantes, datos académicos legacy, alertas/intervenciones, malla lectura, notas/asistencia curricular, RF-04, RF-19 lectura, RF-20 lectura | Confirmado |
| `coordinador_academico` | Seeder | Configuración curricular, riesgo, asignaciones, calendario, Excel aula, RF-04, RF-19 lectura, RF-20 lectura | Confirmado |
| `psicologo_tutor` | Seeder | Alertas, intervenciones, lectura académica/asistencia | Confirmado |
| `directivo` | Seeder | Dashboard, alertas, lectura malla/notas/asistencia; excepción UI en «Notas semanales» | Confirmado |

Usuarios demo: [`DemoUsersSeeder.php`](../backend/database/seeders/DemoUsersSeeder.php) — **pendiente de verificar** emails exactos en README.

---

## 6. Permisos confirmados

Fuente: [`PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php) — **27 permisos implementados actualmente**, `guard_name` = `web` (8 legacy + 15 curriculares + 2 conductuales RF-04 + 1 semáforo RF-19 base + 1 historial RF-20 base).

> **Permisos adicionales sugeridos/planificados:** 4 permisos para RF-10, RF-11, RF-16 y RF-18 documentados en §16 — **no** están en `PermissionsSeeder`. Los permisos RF-04 **sí** están en seeder (Fase 2B), **rutas API** (Fase 2C) y **UI perfil** (Fase 2D); cierre pruebas Fase 2E (2026-06-10). El permiso RF-19 `ver_semaforo_completitud` **sí** está en seeder (Fase 3B, 2026-06-23); la **API backend** fue implementada en Fase 3C (`CompletitudDatosService`, `SemaforoCompletitudController`, `SemaforoCompletitudTest` 11 passed); la **UI en perfil estudiante** fue implementada en Fase 3D (`EstudiantePerfilSemaforoCompletitud.jsx`, build frontend OK).

### Legacy (8)

| Permiso | Uso funcional | Módulo / rutas | Estado |
|---------|---------------|----------------|--------|
| `ver_dashboard` | KPIs y export PDF | `GET /api/dashboard`, `/export` | Confirmado |
| `gestionar_usuarios` | CRUD usuarios | `/api/usuarios*` | Confirmado |
| `gestionar_estudiantes` | CRUD estudiantes | `/api/estudiantes` POST/PATCH | Confirmado |
| `gestionar_materias` | Catálogo materias legacy | `/api/materias` escritura | Confirmado |
| `registrar_datos_academicos` | Notas/asistencias/VSE legacy, lectura materias/estudiantes | `/api/notas/*`, `/api/asistencias/*`, VSE | Confirmado |
| `procesar_riesgo` | Disparo ML vía Laravel | `POST /api/estudiantes/{id}/procesar-riesgo` | Confirmado |
| `ver_alertas` | Listado/detalle alertas | `GET /api/alertas*` | Confirmado |
| `registrar_intervencion` | Intervenciones y cierre alerta | `POST /api/alertas/{id}/intervenciones`, `/cerrar` | Confirmado |

### Curricular (15)

| Permiso | Uso funcional | Módulo | Estado |
|---------|---------------|--------|--------|
| `ver_malla_curricular` | Lectura malla, catálogo, temas | Malla, criterios (lectura) | Confirmado |
| `gestionar_malla_curricular` | Edición malla/cursos | Malla curricular | Confirmado |
| `gestionar_temas_semanales` | Criterios de evaluación | Temas semanales | Confirmado |
| `configurar_pesos_evaluacion` | Pesos C/L/T | API `/curricular/pesos*` (UI oculta) | Confirmado |
| `gestionar_componentes_calificacion` | Componentes por nivel | Componentes calificación | Confirmado |
| `gestionar_asignaciones_docente` | Asignación docente–aula | Asignación docente | Confirmado |
| `registrar_notas_semanales` | Registro/import notas | Notas semanales, eval bim bulk | Confirmado |
| `ver_notas_academicas` | Consulta notas/resumen/eval bim | Notas, resumen académico | Confirmado |
| `configurar_evaluacion_bimestral` | Config bimestral | Configuración bimestral | Confirmado |
| `registrar_asistencia_curricular` | Registro asistencia diaria | Asistencia curricular | Confirmado |
| `ver_asistencia_curricular` | Lectura asistencia | Asistencia curricular | Confirmado |
| `gestionar_calendario_academico` | Años/periodos académicos | Periodos académicos | Confirmado |
| `gestionar_competencias_capacidades` | Competencias/capacidades | Competencias y capacidades | Confirmado |
| `gestionar_secciones_aulas` | Catálogo secciones/aulas | Secciones / Aulas | Confirmado |
| `descargar_excel_aula` | Descarga Excel aula | Excel por aula | Confirmado |

### Conductuales RF-04 (2)

| Permiso | Uso funcional | Módulo / rutas | Estado |
|---------|---------------|----------------|--------|
| `ver_reportes_conductuales` | Consulta reportes conductuales por estudiante | `GET /api/estudiantes/{id}/reportes-conductuales` | Confirmado |
| `registrar_reportes_conductuales` | Registro/anulación reportes conductuales | `POST …/reportes-conductuales`, `PATCH /api/reportes-conductuales/{id}/anular` | Confirmado |

### Semáforo completitud RF-19 (1)

| Permiso | Uso funcional | Módulo / rutas | Estado |
|---------|---------------|----------------|--------|
| `ver_semaforo_completitud` | Consultar semáforo de completitud de datos por estudiante | `GET /api/estudiantes/{id}/semaforo-completitud` | Implementado V1 — API + UI perfil estudiante |

### Historial riesgo RF-20 (1)

| Permiso | Uso funcional | Módulo / rutas | Estado |
|---------|---------------|----------------|--------|
| `ver_historial_riesgo` | Consultar historial evolutivo de riesgo por estudiante | Sin endpoint ni UI todavía | **Base RBAC implementada** — API y frontend pendientes |

---

## 7. Matriz rol–permiso

Fuente: `$rolePermissionMap` en [`PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php) (sync en seed).

| Rol | Cantidad permisos | Observación |
|-----|-------------------|-------------|
| `administrador` | **27** (todos) | Confirmado |
| `docente` | **15** | Con `ver_dashboard`; incluye RF-04 ver + registrar; RF-19 ver; RF-20 ver | Confirmado |
| `coordinador_academico` | **23** | Sin gestionar_usuarios, gestionar_materias, registrar_intervencion; incluye RF-04; RF-19 ver; RF-20 ver | Confirmado |
| `psicologo_tutor` | **6** | Alertas + lectura académica + RF-04 ver + registrar | Confirmado |
| `directivo` | **8** | Lectura dashboard/alertas/malla/notas/asistencia + intervención + **solo ver** RF-04 | Confirmado |

Detalle exacto por rol: ejecutar seed y consultar `model_has_permissions` o revisar array en seeder (`PermissionsSeeder.php`).

---

## 8. Matriz rol–pantalla–acción–endpoint

Leyenda control: **Sí** = middleware/permiso confirmado; **Parcial** = UI distinta del permiso mínimo; **Pendiente** = sin evidencia de test 401/403 o sin UI.

| Módulo / pantalla | Acción visible | Endpoint / ruta | Método | Permiso requerido | Roles con acceso (seed) | Backend | Frontend | Estado |
|-------------------|----------------|-----------------|--------|-------------------|-------------------------|---------|----------|--------|
| Login | Iniciar sesión | `/login` | POST | guest | Todos (credenciales) | Sí | Sí | Confirmado |
| Logout | Cerrar sesión | `/logout` | POST | auth | Autenticados | Sí | Sí | Confirmado |
| Sesión | Cargar perfil | `/api/me` | GET | auth:sanctum | Autenticados | Sí | Sí | Confirmado |
| Dashboard | Ver KPIs | `/api/dashboard` | GET | `ver_dashboard` | admin, docente*, coord, directivo | Sí | Sí | Confirmado |
| Dashboard | Export PDF | `/api/dashboard/export` | GET | `ver_dashboard` | idem | Sí | Sí | Confirmado |
| Estudiantes | Listar/ver | `/api/estudiantes` | GET | `gestionar_estudiantes` \| `registrar_datos_academicos` | admin, docente, coord | Sí | Sí (`gestionar_estudiantes`) | Parcial UI |
| Estudiantes | Crear/editar | `/api/estudiantes` | POST/PATCH | `gestionar_estudiantes` | admin, docente, coord | Sí | Sí | Confirmado |
| Perfil estudiante | Ver datos/riesgo | `/api/estudiantes/{id}` + procesar riesgo | GET / POST | ver est. / `procesar_riesgo` | Según permiso | Sí | Parcial | Confirmado |
| Notas legacy | Lote / individual | `/api/notas/lote`, `/estudiantes/{id}/notas` | POST/GET | `registrar_datos_academicos` | admin, docente, coord | Sí | **No menú** | API sí, UI oculta |
| Asistencia legacy | Lote / individual | `/api/asistencias/lote`, … | POST/GET | `registrar_datos_academicos` | idem | Sí | **No menú** | API sí, UI oculta |
| VSE | Variables socioeconómicas | `/api/estudiantes/{id}/variables-socioeconomicas` | GET/POST | `registrar_datos_academicos` | admin, docente, coord | Sí | **Pausada UI** | Confirmado API |
| Riesgo | Procesar índice | `/api/estudiantes/{id}/procesar-riesgo` | POST | `procesar_riesgo` | admin, coord | Sí | Parcial (perfil) | Confirmado |
| Alertas | Listar | `/api/alertas` | GET | `ver_alertas` | admin, docente, coord, psicólogo, directivo | Sí | Sí | Confirmado |
| Intervenciones | Registrar | `/api/alertas/{id}/intervenciones` | POST | `registrar_intervencion` | admin, docente, directivo | Sí | Parcial | Confirmado |
| Cierre alerta | Cerrar | `/api/alertas/{id}/cerrar` | POST | `registrar_intervencion` | idem | Sí | Parcial | Confirmado |
| Notas semanales | Registro/consulta | `/api/curricular/notas-semanales/*` | GET/POST | `registrar_notas_semanales` / `ver_notas_academicas` | Varios + **directivo (UI)** | Sí | Parcial | Confirmado |
| Asistencia curricular | Formulario/bulk | `/api/curricular/asistencias-diarias/*` | GET/POST | `registrar_asistencia_curricular` / `ver_*` | Varios | Sí | Sí | Confirmado |
| Excel aula | Descarga | `/api/curricular/excel-aula` | GET | `descargar_excel_aula` | admin, coord | Sí | Sí | Confirmado |
| Config. bimestral | Config/resultados | `/api/curricular/evaluacion-bimestral/*` | GET/POST | `configurar_evaluacion_bimestral` / `ver_notas_academicas` | coord / lectores | Sí | Sí | Confirmado |
| Malla / criterios / competencias / secciones / asignación / calendario | CRUD según módulo | `/api/curricular/*` | varios | permisos `gestionar_*` / `ver_*` | Según §7 | Sí | Sí | Confirmado |
| Usuarios | Gestión | `/api/usuarios*` | GET/POST/PATCH | `gestionar_usuarios` | admin | Sí | Sí | Confirmado |
| Reportes conductuales | Ver / registrar | `GET/POST …/reportes-conductuales`, `PATCH …/anular` | GET/POST/PATCH | `ver_reportes_conductuales` / `registrar_reportes_conductuales` | admin, docente, coord, psicólogo (ver+reg); directivo (solo ver backend) | Sí | **Sí** (perfil) | V1 mínimo Fase 2E; directivo sin menú Estudiantes |
| Materias legacy | Catálogo | `/api/materias*` | varios | `gestionar_materias` / `registrar_datos_academicos` | admin / docente+coord lectura | Sí | **No menú** | API sí |

\* Docente tiene `ver_dashboard` en seed — confirmado en seeder.

**Excepción frontend confirmada:** `curricular_notas` visible si `roles.includes('directivo')` aunque el rol no tenga `registrar_notas_semanales` ([`App.jsx`](../frontend/src/App.jsx) L250–255). El backend sigue exigiendo permisos en cada endpoint.

---

## 9. Endpoints críticos

| Endpoint | Auth | Permiso | Test 401 | Test 403 | Archivo test |
|----------|------|---------|----------|----------|--------------|
| `GET /api/dashboard` | sanctum | `ver_dashboard` | Sí | Sí | `DashboardTest.php` |
| `GET /api/dashboard/export` | sanctum | `ver_dashboard` | Sí | Sí | `DashboardTest.php` |
| `GET/POST /api/estudiantes*` | sanctum | ver §8 | Sí | Sí | `EstudianteTest.php` |
| `POST …/procesar-riesgo` | sanctum | `procesar_riesgo` | Sí | Sí | `RiesgoTest.php` |
| `GET /api/alertas` | sanctum | `ver_alertas` | Sí | Sí | `AlertaIntervencionTest.php` |
| `POST /api/alertas/{id}/cerrar` | sanctum | `registrar_intervencion` | Sí | Sí | `AlertaIntervencionTest.php` |
| `GET /api/usuarios` | sanctum | `gestionar_usuarios` | Pendiente | Sí | `GestionUsuariosTest.php` |
| `GET /api/curricular/mallas` | sanctum | `ver_malla_curricular` | Sí | Sí | `CurricularApiTest.php` |
| `GET /api/curricular/excel-aula` | sanctum | `descargar_excel_aula` | Pendiente | Sí | `ExcelAulaTest.php` |
| `GET /api/estudiantes/{id}/reportes-conductuales` | sanctum | `ver_reportes_conductuales` | Sí | Sí | `ReporteConductualTest.php` |
| `POST /api/estudiantes/{id}/reportes-conductuales` | sanctum | `registrar_reportes_conductuales` | Sí | Sí | `ReporteConductualTest.php` |
| `PATCH /api/reportes-conductuales/{id}/anular` | sanctum | `registrar_reportes_conductuales` | Sí | Sí | `ReporteConductualTest.php` |
| `POST /register` | guest | — | N/A | N/A | `RegistrationTest.php` (público) |
| `GET /api/health` | ninguno | — | N/A | N/A | Pendiente de verificar |

Catálogo completo de rutas: [`docs/api.md`](api.md).

---

## 10. Seguridad en frontend

| Elemento | Archivo | Comportamiento |
|----------|---------|----------------|
| Sesión | `AuthContext.jsx` | `getMe()` → roles/permisos; logout limpia estado |
| HTTP | `api.js` | CSRF, cookies, manejo `error.status` |
| Menú lateral | `App.jsx` | `moduloPermitido()` oculta ítems sin permiso |
| Módulo activo | `App.jsx` | Redirige a `moduloPorDefecto` si permiso insuficiente |
| Sin permisos | `App.jsx` | Mensaje «Sin módulos asignados» |
| Pesos evaluación | `App.jsx` | `visible: false` — módulo oculto, API activa |
| Legacy materias/notas masivas | — | **No importados** en `App.jsx` |

**Limitaciones:** ocultar menú no impide llamadas API directas; la protección real está en Laravel. No hay Cypress confirmado.

---

## 11. Seguridad en backend

| Aspecto | Estado |
|---------|--------|
| Middleware `auth:sanctum` | Confirmado en rutas API sensibles |
| Middleware `permission:*` (Spatie) | Confirmado en [`api.php`](../backend/routes/api.php) |
| Validación Form Request | Confirmado por módulo — **pendiente de verificar** cobertura total |
| `POST /register` público | **Brecha** — activo en prototipo ([`limitaciones.md`](limitaciones.md)) |
| Rate limiting auth | **Pendiente de verificar** en login (throttle en verify-email sí) |
| Policies | **No confirmado** |
| Sede V1 | Default `chilca` en listados — [`SedeOperativa.php`](../backend/app/Support/SedeOperativa.php); no afecta permisos |

Prefijo `/api/curricular/*`: grupo con `auth:sanctum`; subgrupos con permisos específicos (L128–281 en `api.php`).

---

## 12. Pruebas de seguridad

**Cypress:** no existe suite en el repositorio.

| Test (archivo) | Qué valida | 401 | 403 | Estado |
|----------------|------------|-----|-----|--------|
| `AuthenticationTest` | login/logout | Parcial | — | Confirmado |
| `RegistrationTest` | registro público | — | — | Confirmado |
| `DashboardTest` | dashboard/export | Sí | Sí | Confirmado |
| `EstudianteTest` | CRUD estudiantes | Sí | Sí | Confirmado |
| `GestionUsuariosTest` | usuarios | Pendiente | Sí | Parcial |
| `MateriaTest` | materias | Sí | Sí | Confirmado |
| `DatosAcademicosTest` | notas/asistencias/VSE legacy | Sí | Sí | Confirmado |
| `RiesgoTest` | procesar riesgo | Sí | Sí | Confirmado |
| `AlertaIntervencionTest` | alertas/intervenciones/cierre | Sí | Sí | Confirmado |
| `CurricularApiTest` | múltiples rutas curriculares | Sí | Sí | Confirmado |
| `AsistenciaDiariaTest` | asistencia curricular | Parcial | Sí | Confirmado |
| `EvaluacionBimestralApiTest` | eval bimestral | Parcial | Sí | Confirmado |
| `ExcelAulaTest` | excel aula | Pendiente | Sí | Parcial |
| `SeccionesAulasTest`, `CalendarioAcademicoTest`, etc. | módulos curriculares | Parcial | Sí | Confirmado |

**Cobertura 401/403:** amplia en módulos principales; **no exhaustiva** en todas las rutas `/api/curricular/*`.

---

## 13. Activity log / auditoría

| Elemento | Evidencia | Estado |
|----------|-----------|--------|
| Dependencia | `spatie/laravel-activitylog` en composer | Confirmado |
| Tabla | `activity_log` en tests | Confirmado |
| Uso en controladores | Estudiantes, dashboard export, materias, datos académicos, usuarios, riesgo | **Parcial** — [`ActivityLogTest.php`](../backend/tests/Feature/ActivityLogTest.php) |
| UI consulta logs | — | **Pendiente de desarrollo** |
| Auditoría completa REQ-17 DRS | — | **No confirmado** |

No afirmar trazabilidad total del sistema; solo acciones donde `activity()` está implementado y probada. **RF-17** se mantiene por trazabilidad y apoyo a **alineación progresiva** con ISO/IEC 27000 e ISO 9001 — **no** porque ISO lo exija directamente ni implica certificación.

---

## 14. ISO y seguridad

La documentación de seguridad de este prototipo aporta evidencias (roles, permisos, tests 401/403, registro parcial en `activity_log`) útiles para una **alineación progresiva** con buenas prácticas de la familia **ISO/IEC 27000** (gestión de seguridad de la información) en contexto académico.

- **No** constituye certificación ISO.
- **No** constituye auditoría externa ni cumplimiento normativo certificado (p. ej. Ley 29733).
- Referencia orientativa también en [`docs/limitaciones.md`](limitaciones.md) §9 y [`sprints/sprint 10.md`](../sprints/sprint%2010.md).

---

## 15. Riesgos y brechas

| Riesgo / brecha | Impacto | Evidencia | Recomendación | Prioridad |
|-----------------|---------|-----------|---------------|-----------|
| `POST /register` público | Alto en producción | `auth.php`, `RegistrationTest` | Deshabilitar o restringir en despliegue real | Alta |
| UI oculta vs API legacy activa | Medio | Materias/notas legacy sin menú | Documentar; restringir permisos en prod si no se usa | Media |
| Directivo ve «Notas» sin permiso registro | Bajo | `App.jsx` excepción rol | Lectura institucional documentada | Media |
| Directivo actor inicial de alertas | Medio | DRS v2.1 RF-10 | **Planificado:** solo escalamiento casos críticos | Media |
| Psicólogo sin perfil integral | Medio | RF-11 planificado | **Planificado:** lectura académica completa | Media |
| Cobertura 401/403 incompleta | Medio | §12 | Ampliar Feature Tests por ruta crítica | Media |
| Activity log parcial | Medio | `ActivityLogTest` | Extender logging + UI consulta si RF-17 exige | Media |
| Datos Auquimarca en BD local | Bajo (V1) | Fase 1 audit | No confundir con multi-sede operativa; Chilca en UI | Baja |
| Datos demo / credenciales README | Medio | README usuarios demo | Rotar secretos; no usar en producción | Alta |
| Sin Cypress | Medio | ausencia `cypress/` | E2E manual o futura suite | Media |
| Sin healthcheck backend/ML | Bajo | docker-compose | Mejora operativa | Baja |

---

## 16. Recomendaciones para Sprint / Fase siguiente

1. **Fase 4 — Manual de usuario por rol:** usar este documento como fuente de permisos y pantallas visibles.
2. Completar pruebas **401/403** en rutas curriculares aún sin test explícito.
3. Decidir política de **`POST /register`** antes de cualquier despliegue fuera de prototipo.
4. Crear **`docs/matriz-rf-sprint-test.md`** enlazando RF de seguridad (RF-15, RF-17) con tests existentes — **disponible** en [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md).
5. Plantilla registro auxiliar (descarga + import por curso): ver [`docs/aula-notas-excel.md`](aula-notas-excel.md) §11.
6. Alimentar **DRS v2.1**; RF vigentes RF-01 a RF-35.

### Permisos sugeridos (planificados — no confirmados en `PermissionsSeeder`)

Los siguientes permisos **no existen** aún en código; se documentan como objetivo de RF planificados:

| Permiso sugerido | RF | Estado |
|------------------|-----|--------|
| `ver_perfil_integral_estudiante` | RF-11 | Planificado |
| `escalar_alerta_directivo` | RF-10 | Planificado |
| `ver_reportes_riesgo` | RF-16 | Planificado |
| `generar_reportes_riesgo` | RF-16 | Planificado |
| `gestionar_reentrenamiento_ml` | RF-18 | Planificado |

### Permisos RF-04 implementados en seeder (Fase 2B — API + UI V1 mínimo)

| Permiso | RF | Estado |
|---------|-----|--------|
| `ver_reportes_conductuales` | RF-04 | **Implementado** — seeder + API GET + UI perfil |
| `registrar_reportes_conductuales` | RF-04 | **Implementado** — seeder + API POST/PATCH anular + UI; **no** asignado a `directivo` |

### Permisos RF-19 implementados en seeder (Fase 3B — API backend Fase 3C; UI Fase 3D; cierre Fase 3E)

| Permiso | RF | Estado |
|---------|-----|--------|
| `ver_semaforo_completitud` | RF-19 | **Implementado V1** — seeder + asignación roles + endpoint + tests 11 passed + UI perfil estudiante + build frontend OK |

---

## Anexo: versión Laravel

Verificado en [`backend/composer.json`](../backend/composer.json):

```json
"laravel/framework": "^13.0"
```

Documentación técnica del repo cita **Laravel ^13** / PHP 8.3 — **confirmado**. El Plan de Pruebas histórico fue matizado post-Fase 8 (banner + tabla ^13); vigencia de pruebas: [`informe-pruebas.md`](pruebas/informe-pruebas.md).

---

*Documento generado en Fase 3 del plan de actualización documental SIDERAE-Blenkir. Actualizado Fases 2B–2E RF-04 — 2026-06-10. Actualizado Fases 3B–3E RF-19 — 2026-06-23.*
