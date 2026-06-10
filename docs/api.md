# Referencia API — SIDERAE-Blenkir

Catálogo de endpoints **confirmados** en [`backend/routes/api.php`](../backend/routes/api.php) y [`backend/routes/auth.php`](../backend/routes/auth.php).

**Convenciones:**

- Base URL local: `http://localhost:8000`
- Prefijo API: `/api` (salvo auth en raíz)
- Autenticación: Laravel Sanctum (cookies SPA + CSRF)
- Autorización: middleware `permission:*` (Spatie)
- Respuestas no autorizadas: **401** sin sesión, **403** sin permiso
- **Sede (V1):** la UI opera solo con **Chilca**; varios GET aceptan query `sede` y aplican `chilca` por defecto si se omite ([`AGENTS.md`](../AGENTS.md)). El esquema conserva `auquimarca` para compatibilidad; no implica operación multi-sede en V1.

Cliente frontend: [`frontend/src/lib/api.js`](../frontend/src/lib/api.js). Matriz permisos detallada: [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md).

---

## 1. Autenticación (sin prefijo `/api`)

| Método | Ruta | Auth | Notas |
|--------|------|------|-------|
| GET | `/sanctum/csrf-cookie` | — | Framework Sanctum |
| POST | `/login` | guest | email, password |
| POST | `/logout` | auth | |
| POST | `/register` | guest | Breeze — **público** |
| POST | `/forgot-password` | guest | |
| POST | `/reset-password` | guest | |

---

## 2. Salud y sesión

| Método | Ruta | Permiso | Descripción |
|--------|------|---------|-------------|
| GET | `/api/health` | — | `{ status, service }` |
| GET | `/api/me` | `auth:sanctum` | usuario, roles, permisos |
| GET | `/api/user` | `auth:sanctum` | usuario autenticado |

---

## 3. Usuarios (RF-15)

Permiso: `gestionar_usuarios`

| Método | Ruta |
|--------|------|
| GET | `/api/usuarios` |
| GET | `/api/usuarios/{user}` |
| POST | `/api/usuarios` |
| PATCH | `/api/usuarios/{user}` |
| PATCH | `/api/usuarios/{user}/activar` |
| PATCH | `/api/usuarios/{user}/desactivar` |
| POST | `/api/usuarios/{user}/restablecer-contrasena` |

---

## 4. Dashboard (RF-14 parcial)

Permiso: `ver_dashboard`

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/dashboard` | KPIs y filtros |
| GET | `/api/dashboard/export` | PDF (DomPDF) |

---

## 5. Materias (legacy)

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/materias` | `gestionar_materias` OR `registrar_datos_academicos` |
| GET | `/api/materias/{materia}` | idem |
| POST | `/api/materias` | `gestionar_materias` |
| PUT/PATCH | `/api/materias/{materia}` | `gestionar_materias` |
| PATCH | `/api/materias/{materia}/desactivar` | `gestionar_materias` |
| PATCH | `/api/materias/{materia}/activar` | `gestionar_materias` |

UI menú: **no expuesta** (legacy API).

---

## 6. Estudiantes

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/estudiantes` | `gestionar_estudiantes` OR `registrar_datos_academicos` |
| GET | `/api/estudiantes/{estudiante}` | idem |
| POST | `/api/estudiantes` | `gestionar_estudiantes` |
| PUT/PATCH | `/api/estudiantes/{estudiante}` | `gestionar_estudiantes` |

---

## 7. Datos académicos legacy (RF-01 parcial)

Permiso: `registrar_datos_academicos`

| Método | Ruta |
|--------|------|
| POST | `/api/notas/lote` |
| POST | `/api/asistencias/lote` |
| GET | `/api/estudiantes/{estudiante}/notas` |
| POST | `/api/estudiantes/{estudiante}/notas` |
| GET | `/api/estudiantes/{estudiante}/asistencias` |
| POST | `/api/estudiantes/{estudiante}/asistencias` |
| GET | `/api/estudiantes/{estudiante}/variables-socioeconomicas` |
| POST | `/api/estudiantes/{estudiante}/variables-socioeconomicas` |

**RF-05:** API confirmada; UI pestaña VSE pausada.

**Import SIAGIE:** pendiente — no confundir con import Excel curricular (§9).

---

## 8. Riesgo académico (RF-06/07)

| Método | Ruta | Permiso |
|--------|------|---------|
| POST | `/api/estudiantes/{estudiante}/procesar-riesgo` | `procesar_riesgo` |

Orquesta llamada a Flask — ver [`ml-service.md`](ml-service.md).

---

## 9. Alertas e intervenciones (RF-08/09/13)

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/alertas` | `ver_alertas` |
| GET | `/api/alertas/{alerta}` | `ver_alertas` |
| POST | `/api/alertas/{alerta}/intervenciones` | `registrar_intervencion` |
| POST | `/api/alertas/{alerta}/cerrar` | `registrar_intervencion` |

---

## 10. Módulo curricular (`/api/curricular/*`)

Todas requieren `auth:sanctum` + permiso indicado. Prefijo base: `/api/curricular`.

### 10.1 Catálogo y calendario

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/catalogo/niveles-grados` | autenticado |
| GET | `/anios-escolares/activo` | varios permisos curriculares |
| GET/POST/PATCH | `/anios-escolares/*` | `gestionar_calendario_academico` |
| PATCH/POST | `/periodos-academicos/{id}/*` | `gestionar_calendario_academico` |

### 10.2 Malla, áreas, temas

| Método | Ruta | Permiso lectura | Permiso escritura |
|--------|------|-----------------|-------------------|
| GET | `/areas`, `/areas/{area}/competencias`, `/competencias/{id}/capacidades` | `ver_malla_curricular` | — |
| GET | `/periodos`, `/periodos/{id}/semanas` | `ver_malla_curricular` | — |
| GET | `/mallas`, `/mallas/grado`, `/mallas/{id}` | `ver_malla_curricular` | — |
| GET | `/temas`, `/temas/{id}` | `ver_malla_curricular` | — |
| POST/PATCH | `/mallas/cargar-plantilla`, `/mallas/{id}/cursos/*` | — | `gestionar_malla_curricular` |
| POST/PATCH | `/temas`, `/temas/{id}/*` | — | `gestionar_temas_semanales` |

### 10.3 Competencias y capacidades

Permiso: `gestionar_competencias_capacidades` — CRUD bajo `/areas/{area}/competencias`, `/competencias/{id}/*`, `/capacidades/{id}/*`.

### 10.4 Pesos C/L/T (legacy resolver)

Permiso: `configurar_pesos_evaluacion` — `/pesos`, `/pesos/resolver`, etc.

### 10.5 Componentes de calificación

Permiso: `gestionar_componentes_calificacion` — `/componentes-calificacion/*`.

### 10.6 Secciones y aulas

| Permiso | Rutas |
|---------|-------|
| Lectura (varios) | GET `/secciones-aulas` |
| `gestionar_secciones_aulas` | POST/PATCH `/secciones-aulas/*` |

### 10.7 Asignación docente

Permiso: `gestionar_asignaciones_docente` — `/docentes`, `/asignaciones-docente/*`, bulk, desactivar.

### 10.8 Notas semanales e import Excel

| Método | Ruta | Permiso | Notas |
|--------|------|---------|-------|
| GET | `/docente/aulas-cursos` | `registrar_notas_semanales` | |
| GET | `/notas-semanales/formulario` | `registrar_notas_semanales` OR `ver_notas_academicas` | |
| GET | `/notas-semanales/plantilla-excel` | idem | Descarga plantilla |
| GET | `/notas-semanales/contextos-aula` | idem | |
| POST | `/notas-semanales/bulk` | `registrar_notas_semanales` | |
| POST | `/notas-semanales/importar-excel` | `registrar_notas_semanales` | **Import curricular confirmado** |

### 10.9 Excel por aula y plantilla registro auxiliar

| Método | Ruta | Permiso | Descarga | Import | Notas |
|--------|------|---------|----------|--------|-------|
| GET | `/excel-aula` | `descargar_excel_aula` | Sí | **No** | Libro multi-hoja (estudiantes + cursos). Modo sin datos. UI: **Excel por aula** |
| GET | `/notas-semanales/plantilla-excel` | `registrar_notas_semanales` OR `ver_notas_academicas` | Sí | — | Por curso/asignación |
| POST | `/notas-semanales/importar-excel` | `registrar_notas_semanales` | — | Sí | **Import curricular confirmado** — UI toolbar Notas semanales |

Tests: `ExcelAulaTest`, `PlantillaRegistroAuxiliarExcelTest`. Documentación: [`aula-notas-excel.md`](aula-notas-excel.md).

**No confundir** con importación **SIAGIE** (RF-01 DRS) — **pendiente**.

### 10.10 Evaluación bimestral

| Permiso | Rutas |
|---------|-------|
| `ver_notas_academicas` / `configurar_evaluacion_bimestral` | GET `/evaluacion-bimestral/config`, `/resultados` |
| `configurar_evaluacion_bimestral` | POST/PATCH componentes, ETAs, aplicar grado |
| `registrar_notas_semanales` / `ver_notas_academicas` | GET `/evaluacion-bimestral/formulario` |
| `registrar_notas_semanales` | POST `/evaluacion-bimestral/bulk` |

### 10.11 Asistencia diaria curricular

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/asistencias-diarias/formulario` | `registrar_asistencia_curricular` OR `ver_asistencia_curricular` |
| GET | `/asistencias-diarias/resumen` | idem |
| POST | `/asistencias-diarias/bulk` | `registrar_asistencia_curricular` |

### 10.12 Resumen académico estudiante

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/estudiantes/{estudiante}/resumen-academico` | `ver_notas_academicas` |

---

## 11. Endpoints no expuestos (pendientes)

Tablas/migraciones existen; **sin rutas** en `api.php`:

- Reportes conductuales (RF-04)
- Derivación directivo (RF-10)
- Comunicación familiar (RF-12)
- Reentrenamiento ML (RF-18)

---

## 12. Permisos Spatie (referencia)

Definidos en [`PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php):

**Legacy (8):** `ver_dashboard`, `gestionar_usuarios`, `gestionar_estudiantes`, `gestionar_materias`, `registrar_datos_academicos`, `procesar_riesgo`, `ver_alertas`, `registrar_intervencion`.

**Curricular (15):** `ver_malla_curricular`, `gestionar_malla_curricular`, `gestionar_temas_semanales`, `configurar_pesos_evaluacion`, `gestionar_componentes_calificacion`, `gestionar_asignaciones_docente`, `registrar_notas_semanales`, `ver_notas_academicas`, `configurar_evaluacion_bimestral`, `registrar_asistencia_curricular`, `ver_asistencia_curricular`, `gestionar_calendario_academico`, `gestionar_competencias_capacidades`, `gestionar_secciones_aulas`, `descargar_excel_aula`.

---

## 13. Documentos relacionados

- [`manual-tecnico.md`](manual-tecnico.md)
- [`aula-notas-excel.md`](aula-notas-excel.md)
- [`arquitectura/contexto-backend-laravel.md`](arquitectura/contexto-backend-laravel.md)
- [`ml-service.md`](ml-service.md)
- [`limitaciones.md`](limitaciones.md)

---

*Fase 2 documental — 2026-06-09. Fuente de verdad: rutas en repositorio; regenerar catálogo si cambia `api.php`.*
