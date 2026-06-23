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

**RF-05:** API legacy confirmada; **retirada del flujo funcional de riesgo** (v2.1); UI pausada.

**Import SIAGIE:** **fuera del alcance actual** — no confundir con import Excel curricular RF-32 (§10).

---

## 8. Riesgo académico (RF-06/07)

| Método | Ruta | Permiso |
|--------|------|---------|
| POST | `/api/estudiantes/{estudiante}/procesar-riesgo` | `procesar_riesgo` |

Orquesta llamada a Flask — ver [`ml-service.md`](ml-service.md).

---

## 9. Reportes conductuales (RF-04 — V1 mínimo)

Solo estudiantes con `sede = chilca` (V1 operativa). Listado devuelve reportes con `estado = activo`. Anulación lógica (`estado = anulado`); **sin** DELETE físico. UI en **perfil estudiante** (`EstudiantePerfilReportesConductuales.jsx`); **sin** menú global ni listado por grado/sección.

| Método | Ruta | Permiso | Descripción |
|--------|------|---------|-------------|
| GET | `/api/estudiantes/{estudiante}/reportes-conductuales` | `ver_reportes_conductuales` | Lista activos, orden fecha desc |
| POST | `/api/estudiantes/{estudiante}/reportes-conductuales` | `registrar_reportes_conductuales` | Crea reporte; `registrado_por` = usuario sesión |
| PATCH | `/api/reportes-conductuales/{reporteConductual}/anular` | `registrar_reportes_conductuales` | Anula lógicamente |

**Body POST (JSON):** `fecha`, `tipo_conducta`, `nivel_gravedad` (`leve`|`moderado`|`grave`), `descripcion`, `accion_inmediata` (opcional).

Tests: [`ReporteConductualTest.php`](../backend/tests/Feature/ReporteConductualTest.php) — **8 passed** (Fase 2E, 2026-06-10). Smoke UI: [`smoke-rf04-reportes-conductuales.md`](pruebas/smoke-rf04-reportes-conductuales.md).

---

## 10. Semáforo de completitud de datos (RF-19 — backend V1)

Solo estudiantes con `sede = chilca` (V1 operativa). El endpoint es **informativo**: no recalcula riesgo, no llama a Flask y no bloquea el perfil. Devuelve `verde`, `amarillo` o `rojo` según la presencia de notas curriculares, asistencia curricular, reportes conductuales activos (RF-04) e índice de riesgo existente.

| Método | Ruta | Permiso | Descripción |
|--------|------|---------|-------------|
| GET | `/api/estudiantes/{estudiante}/semaforo-completitud` | `ver_semaforo_completitud` | Semáforo por estudiante; query `anio_escolar` (default año del estudiante), `bimestre` (opcional) |

**Respuesta 200:** `estudiante_id`, `anio_escolar`, `bimestre`, `color`, `etiqueta`, `mensaje`, `razones[]`.

Tests: [`SemaforoCompletitudTest.php`](../backend/tests/Feature/SemaforoCompletitudTest.php) — **11 passed**, 55 assertions (Fase 3C/E, 2026-06-23). UI: [`EstudiantePerfilSemaforoCompletitud.jsx`](../frontend/src/components/estudiantes/EstudiantePerfilSemaforoCompletitud.jsx) integrado en perfil estudiante (Fase 3D).

---

## 11. Alertas e intervenciones (RF-08/09/13)

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/alertas` | `ver_alertas` |
| GET | `/api/alertas/{alerta}` | `ver_alertas` |
| POST | `/api/alertas/{alerta}/intervenciones` | `registrar_intervencion` |
| POST | `/api/alertas/{alerta}/cerrar` | `registrar_intervencion` |

---

## 12. Historial evolutivo de riesgo (RF-20 — V1)

Solo estudiantes con `sede = chilca` (V1 operativa). El endpoint es **informativo**: consulta registros existentes de `indices_riesgo`, no recalcula el riesgo, no modifica datos y no llama a Flask. `variables_utilizadas` se devuelve solo si existe en el registro.

| Método | Ruta | Permiso | Descripción |
|--------|------|---------|-------------|
| GET | `/api/estudiantes/{estudiante}/historial-riesgo` | `ver_historial_riesgo` | Historial de riesgo por estudiante; query `anio_escolar` y `bimestre` opcionales; ordenado del más reciente al más antiguo |

**Respuesta 200:** `estudiante_id`, `historial[]` con `id`, `indice`, `nivel`, `anio_escolar`, `bimestre`, `fecha`, `variables_utilizadas`.

Tests: [`HistorialRiesgoTest.php`](../backend/tests/Feature/HistorialRiesgoTest.php) — **12 passed**, 30 assertions. UI: [`EstudiantePerfilHistorialRiesgo.jsx`](../frontend/src/components/estudiantes/EstudiantePerfilHistorialRiesgo.jsx) integrado en perfil estudiante.

---

## 14. Módulo curricular (`/api/curricular/*`)

Todas requieren `auth:sanctum` + permiso indicado. Prefijo base: `/api/curricular`.

### 12.1 Catálogo y calendario

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/catalogo/niveles-grados` | autenticado |
| GET | `/anios-escolares/activo` | varios permisos curriculares |
| GET/POST/PATCH | `/anios-escolares/*` | `gestionar_calendario_academico` |
| PATCH/POST | `/periodos-academicos/{id}/*` | `gestionar_calendario_academico` |

### 12.2 Malla, áreas, temas

| Método | Ruta | Permiso lectura | Permiso escritura |
|--------|------|-----------------|-------------------|
| GET | `/areas`, `/areas/{area}/competencias`, `/competencias/{id}/capacidades` | `ver_malla_curricular` | — |
| GET | `/periodos`, `/periodos/{id}/semanas` | `ver_malla_curricular` | — |
| GET | `/mallas`, `/mallas/grado`, `/mallas/{id}` | `ver_malla_curricular` | — |
| GET | `/temas`, `/temas/{id}` | `ver_malla_curricular` | — |
| POST/PATCH | `/mallas/cargar-plantilla`, `/mallas/{id}/cursos/*` | — | `gestionar_malla_curricular` |
| POST/PATCH | `/temas`, `/temas/{id}/*` | — | `gestionar_temas_semanales` |

### 12.3 Competencias y capacidades

Permiso: `gestionar_competencias_capacidades` — CRUD bajo `/areas/{area}/competencias`, `/competencias/{id}/*`, `/capacidades/{id}/*`.

### 12.4 Pesos C/L/T (legacy resolver)

Permiso: `configurar_pesos_evaluacion` — `/pesos`, `/pesos/resolver`, etc.

### 12.5 Componentes de calificación

Permiso: `gestionar_componentes_calificacion` — `/componentes-calificacion/*`.

### 12.6 Secciones y aulas

| Permiso | Rutas |
|---------|-------|
| Lectura (varios) | GET `/secciones-aulas` |
| `gestionar_secciones_aulas` | POST/PATCH `/secciones-aulas/*` |

### 12.7 Asignación docente

Permiso: `gestionar_asignaciones_docente` — `/docentes`, `/asignaciones-docente/*`, bulk, desactivar.

### 12.8 Notas semanales e import Excel

| Método | Ruta | Permiso | Notas |
|--------|------|---------|-------|
| GET | `/docente/aulas-cursos` | `registrar_notas_semanales` | |
| GET | `/notas-semanales/formulario` | `registrar_notas_semanales` OR `ver_notas_academicas` | |
| GET | `/notas-semanales/plantilla-excel` | idem | Descarga plantilla |
| GET | `/notas-semanales/contextos-aula` | idem | |
| POST | `/notas-semanales/bulk` | `registrar_notas_semanales` | |
| POST | `/notas-semanales/importar-excel` | `registrar_notas_semanales` | **Import curricular confirmado** |

### 12.9 Excel por aula y plantilla registro auxiliar

| Método | Ruta | Permiso | Descarga | Import | Notas |
|--------|------|---------|----------|--------|-------|
| GET | `/excel-aula` | `descargar_excel_aula` | Sí | **No** | Libro multi-hoja (estudiantes + cursos). Modo sin datos. UI: **Excel por aula** |
| GET | `/notas-semanales/plantilla-excel` | `registrar_notas_semanales` OR `ver_notas_academicas` | Sí | — | Por curso/asignación |
| POST | `/notas-semanales/importar-excel` | `registrar_notas_semanales` | — | Sí | **Import curricular confirmado** — UI toolbar Notas semanales |

Tests: `ExcelAulaTest`, `PlantillaRegistroAuxiliarExcelTest`. Documentación: [`aula-notas-excel.md`](aula-notas-excel.md).

**No confundir** con importación **SIAGIE** — **fuera del alcance actual** (RF-32 sustituye en alcance vigente).

### 12.10 Evaluación bimestral

| Permiso | Rutas |
|---------|-------|
| `ver_notas_academicas` / `configurar_evaluacion_bimestral` | GET `/evaluacion-bimestral/config`, `/resultados` |
| `configurar_evaluacion_bimestral` | POST/PATCH componentes, ETAs, aplicar grado |
| `registrar_notas_semanales` / `ver_notas_academicas` | GET `/evaluacion-bimestral/formulario` |
| `registrar_notas_semanales` | POST `/evaluacion-bimestral/bulk` |

### 12.11 Asistencia diaria curricular

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/asistencias-diarias/formulario` | `registrar_asistencia_curricular` OR `ver_asistencia_curricular` |
| GET | `/asistencias-diarias/resumen` | idem |
| POST | `/asistencias-diarias/bulk` | `registrar_asistencia_curricular` |

### 12.12 Resumen académico estudiante

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/estudiantes/{estudiante}/resumen-academico` | `ver_notas_academicas` |

---

## 15. Endpoints no expuestos (planificados o fuera de alcance)

| RF | Estado | Notas |
|----|--------|-------|
| RF-10 Escalamiento directivo | **Planificado** | Sin rutas |
| RF-12 Comunicación familiar | **Eliminado del alcance** | Esquema BD histórico |
| RF-18 Reentrenamiento ML | **Planificado** | Sin endpoints |
| RF-19 Semáforo completitud | **Implementado V1** | `GET /api/estudiantes/{estudiante}/semaforo-completitud`; UI en perfil estudiante |
| RF-20 Historial evolutivo riesgo | **Backend V1** | `GET /api/estudiantes/{estudiante}/historial-riesgo`; UI pendiente |
| RF-16 Reportes de riesgo (zona dedicada) | **Planificado** | PDF dashboard = parcial |
| RF-03 Fast Test | **Retirado del alcance** | — |
| SIAGIE import global | **Fuera del alcance** | Plantilla RF-32 en su lugar |

---

## 16. Permisos Spatie (referencia)

Definidos en [`PermissionsSeeder.php`](../backend/database/seeders/PermissionsSeeder.php):

**Legacy (8):** `ver_dashboard`, `gestionar_usuarios`, `gestionar_estudiantes`, `gestionar_materias`, `registrar_datos_academicos`, `procesar_riesgo`, `ver_alertas`, `registrar_intervencion`.

**Curricular (15):** `ver_malla_curricular`, `gestionar_malla_curricular`, `gestionar_temas_semanales`, `configurar_pesos_evaluacion`, `gestionar_componentes_calificacion`, `gestionar_asignaciones_docente`, `registrar_notas_semanales`, `ver_notas_academicas`, `configurar_evaluacion_bimestral`, `registrar_asistencia_curricular`, `ver_asistencia_curricular`, `gestionar_calendario_academico`, `gestionar_competencias_capacidades`, `gestionar_secciones_aulas`, `descargar_excel_aula`.

**Conductuales RF-04 (2):** `ver_reportes_conductuales`, `registrar_reportes_conductuales`.

**Historial riesgo RF-20 (1):** `ver_historial_riesgo`.

---

## 17. Documentos relacionados

- [`manual-tecnico.md`](manual-tecnico.md)
- [`aula-notas-excel.md`](aula-notas-excel.md)
- [`arquitectura/contexto-backend-laravel.md`](arquitectura/contexto-backend-laravel.md)
- [`ml-service.md`](ml-service.md)
- [`limitaciones.md`](limitaciones.md)

---

*Fase 2 documental — 2026-06-09. RF-04 API+UI V1 mínimo — Fases 2B–2E (2026-06-10). RF-19 implementado V1 — Fases 3B–3E (2026-06-23). RF-20 backend — Fase 4C (2026-06-23).*
