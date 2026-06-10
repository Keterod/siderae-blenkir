# Matriz de control de accesos — Sprint 8 (FASE 1)

> **Estado del documento:** Histórico.  
> Este archivo corresponde a la matriz trabajada durante Sprint 8. La referencia vigente de seguridad, roles y permisos para la documentación V1 se encuentra en [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md). Este documento se conserva para trazabilidad y no debe usarse como fuente única del estado actual.

## 1. Alcance del documento

Este documento describe el estado **vigente en código** del control de accesos basado en roles y permisos (Spatie Permission) para el prototipo SIDERAE-Blenkir, tras la **FASE 1 de Sprint 8**. Complementa la auditoría FASE 0 y la planificación en `sprints/sprint 8.md`.

- **Fuente de autorización real:** rutas en `backend/routes/api.php` con `auth:sanctum` y middleware `permission:*`.
- **Fuente de roles/permisos por defecto:** `backend/database/seeders/PermissionsSeeder.php` y `RolesSeeder.php`.
- **Frontend:** visibilidad de menú y acciones según `GET /api/me` (`permisos`); el servidor sigue siendo quien concede o niega la operación.

No sustituye al DRS ni a la certificación de seguridad; es apoyo para defensa académica y operación del equipo.

---

## 2. Roles vigentes

Definidos en `RolesSeeder` y usados en `PermissionsSeeder`:

| Rol |
|-----|
| `administrador` |
| `docente` |
| `coordinador_academico` |
| `psicologo_tutor` |
| `directivo` |

No se eliminan ni renombran roles en Sprint 8 FASE 1.

---

## 3. Permisos vigentes

Definidos en `PermissionsSeeder` (lista `$permissions`):

| Permiso |
|---------|
| `ver_dashboard` |
| `gestionar_estudiantes` |
| `gestionar_materias` |
| `registrar_datos_academicos` |
| `procesar_riesgo` |
| `ver_alertas` |
| `registrar_intervencion` |

No se introducen permisos nuevos en Sprint 8 FASE 1.

---

## 4. Matriz rol → permiso → pantalla → endpoint

**Pantallas** según entrada de menú en `frontend/src/App.jsx` (`moduloPermitido`). **Endpoints** según `backend/routes/api.php`.

| Rol | Permisos asignados (seeder) | Pantalla / módulo UI | Endpoint(s) principal(es) |
|-----|-----------------------------|----------------------|-----------------------------|
| `administrador` | todos | Dashboard | `GET /api/dashboard`, `GET /api/dashboard/export` |
| | | Estudiantes | `GET/POST /api/estudiantes`, `GET/PUT /api/estudiantes/{id}` |
| | | Notas / Asistencia | `POST /api/notas/lote`, `POST /api/asistencias/lote`, rutas anidadas bajo estudiante |
| | | Materias | `GET/POST/PATCH … /api/materias` |
| | | Alertas | `GET /api/alertas`, `GET /api/alertas/{id}`, `POST …/intervenciones`, `POST …/cerrar` |
| | | Perfil estudiante — riesgo | `POST /api/estudiantes/{id}/procesar-riesgo` |
| `docente` | `ver_dashboard`, `gestionar_estudiantes`, `registrar_datos_academicos`, `ver_alertas`, `registrar_intervencion` | Dashboard, Estudiantes, Notas, Asistencia, Alertas | Mismos grupos según permiso de cada ruta (sin `gestionar_materias` ni `procesar_riesgo`) |
| | | Lista materias (desde datos académicos/catalogación) | `GET /api/materias` (requiere `registrar_datos_academicos` **o** `gestionar_materias`) |
| `coordinador_academico` | `ver_dashboard`, `gestionar_estudiantes`, `registrar_datos_academicos`, `procesar_riesgo`, `ver_alertas` | Dashboard, Estudiantes, Notas, Asistencia, Alertas (lectura/intervención según permisos) | Incluye `POST …/procesar-riesgo`; **no** incluye mutación materias ni `registrar_intervencion` en seeder |
| `psicologo_tutor` | `ver_alertas`, `registrar_intervencion` | Alertas | `GET` alertas; `POST` intervenciones y cierre |
| `directivo` | `ver_dashboard`, `ver_alertas`, `registrar_intervencion` | Dashboard, Alertas | Dashboard y alertas como arriba; desde FASE 1 también intervención/cierre |

**Notas:**

- Alta/edición de estudiantes (`POST`/`PUT` estudiante) exige **`gestionar_estudiantes`** exclusivamente en rutas API.
- Listado/detalle estudiantes permite **`gestionar_estudiantes` | `registrar_datos_academicos`** en API; el menú “Estudiantes” en `App.jsx` exige **`gestionar_estudiantes`** para mostrar el módulo.
- Materias CRUD mutable requiere `gestionar_materias`; lectura permite OR con `registrar_datos_academicos`.

---

## 5. Decisión sobre `directivo`

- El rol **`directivo`** mantiene **`ver_dashboard`** y **`ver_alertas`**.
- En **Sprint 8 FASE 1** se agrega **`registrar_intervencion`** al `directivo` en `PermissionsSeeder`, permitiendo en el prototipo **registrar intervenciones** y **cerrar alertas** conforme a la intención de RF-13 / RN-04 documentada en el DRS (sin disponer aún de un permiso más fino para “derivación”).
- Se **reutiliza** el permiso existente `registrar_intervencion`; **no** se crean permisos nuevos.

---

## 6. Notas de seguridad

1. **Backend:** La autorización efectiva es la negada o concedida por Laravel (Sanctum + Spatie). Un cliente que llame a la API directamente debe recibir **401** si no está autenticado y **403** si no tiene el permiso requerido (convención verificada en pruebas Feature donde aplica).
2. **Frontend:** Ocultar o no mostrar botones/menús evita errores de usuario y refleja la política, pero **no** sustituye la validación en servidor.
3. **Respuestas esperadas:** no autenticado → **401**; autenticado sin permiso → **403** (en rutas protegidas con el middleware estándar del proyecto).
4. **Auditoría (`activity_log`):** se mantiene solo donde ya está instrumentado en controladores (p. ej. acciones críticas ya registradas); Sprint 8 FASE 1 no amplía la superficie de logging.

---

## 7. Nota operativa sobre procesamiento masivo de riesgo

- El comando `demo:procesar-riesgos` es una herramienta **operativa excepcional** para escenarios post-importación, post-seed o carga inicial masiva de datos.
- **No** forma parte del flujo normal diario del sistema.
- El uso normal de riesgo debe mantenerse acotado a procesamiento por lote desde operaciones académicas y/o procesamiento manual por estudiante.
- No debe ejecutarse automáticamente por carga de página, dashboard ni tareas implícitas de UI.

---

## Referencias en el repositorio

- `backend/routes/api.php`
- `backend/database/seeders/PermissionsSeeder.php`
- `backend/database/seeders/RolesSeeder.php`
- `frontend/src/App.jsx`
- `frontend/src/context/AuthContext.jsx`

---

*Última actualización coherentemente con Sprint 8 — FASE 1 (permisos directivo, pruebas 401/403 y ajustes de UI por permiso).*
