# Plan AI-DLC — RF-20 Historial evolutivo de riesgo

**Fase:** 4A — Planificación previa a implementación  
**Fecha:** 2026-06-23  
**Metodología:** AI-DLC · perfiles en [`docs/metodologia/agentes-ai-dlc-siderae.md`](../agentes-ai-dlc-siderae.md)  
**Guía operativa:** [`docs/metodologia/ai-dlc-aplicado-siderae.md`](../ai-dlc-aplicado-siderae.md)

---

## 1. Propósito

Planificar **RF-20** (*Historial evolutivo de riesgo académico por estudiante*) antes de escribir código, siguiendo AI-DLC y validación humana obligatoria.

RF-20 **no recalcula el riesgo** ni llama a Flask. Solo consulta y presenta los registros históricos de `indices_riesgo` asociados a un estudiante, permitiendo ver su evolución por periodo/bimestre.

---

## 2. Estado actual

| Elemento | Estado | Evidencia |
| -------- | ------ | --------- |
| DRS v2.1 §RF-20 | Planificado / parcial | [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../../drs/DRS_SIDERAE_Blenkir_v2.md) §RF-20 |
| Matriz RF–Sprint–Test | **Implementado V1**; smoke manual navegador pendiente | [`docs/matriz-rf-sprint-test.md`](../../matriz-rf-sprint-test.md) fila RF-20 |
| `docs/limitaciones.md` | **Implementado V1** con limitaciones documentadas | [`docs/limitaciones.md`](../../limitaciones.md) §RF-20 |
| `docs/api.md` | Sin endpoint de historial | [`docs/api.md`](../../api.md) |
| `docs/seguridad-roles-permisos.md` | Permiso `ver_historial_riesgo` implementado Fase 4B | [`docs/seguridad-roles-permisos.md`](../../seguridad-roles-permisos.md) |
| Modelo `IndiceRiesgo` | Existe con campos `indice`, `nivel`, `anio_escolar`, `bimestre`, `variables_utilizadas`, `modelos_scores` | [`backend/app/Models/IndiceRiesgo.php`](../../../backend/app/Models/IndiceRiesgo.php) |
| Tabla `indices_riesgo` | Migración existente | [`backend/database/migrations/2026_04_23_024405_create_indices_riesgo_table.php`](../../../backend/database/migrations/2026_04_23_024405_create_indices_riesgo_table.php) |
| Procesamiento de riesgo | Backend `POST /api/estudiantes/{id}/procesar-riesgo` crea registros | [`backend/routes/api.php`](../../../backend/routes/api.php), [`RiesgoAcademicoService`](../../../backend/app/Services/RiesgoAcademicoService.php) |
| Backend RF-20 | **Implementado** — endpoint + controller + tests | [`HistorialRiesgoController.php`](../../../backend/app/Http/Controllers/Api/HistorialRiesgoController.php), [`HistorialRiesgoTest.php`](../../../backend/tests/Feature/HistorialRiesgoTest.php) |
| Frontend RF-20 | **Implementado** — componente perfil + función API + build OK | [`EstudiantePerfilHistorialRiesgo.jsx`](../../../frontend/src/components/estudiantes/EstudiantePerfilHistorialRiesgo.jsx), [`frontend/src/lib/api.js`](../../../frontend/src/lib/api.js) |
| UI perfil estudiante | `EstudiantePerfilRiesgo.jsx` pausado; historial RF-20 visible con permiso | [`EstudiantesPanel.jsx`](../../../frontend/src/components/estudiantes/EstudiantesPanel.jsx) |
| Tests relacionados | `RiesgoTest`, `ActivoUniqueKeyHistorialTest` (persistencia); `HistorialRiesgoTest` (RF-20) | [`backend/tests/`](../../../backend/tests/) |
| Permiso específico | **Implementado** — `ver_historial_riesgo` asignado a `administrador`, `docente` y `coordinador_academico` | [`PermissionsSeeder.php`](../../../backend/database/seeders/PermissionsSeeder.php) |

**Resumen:** RF-20 está **implementado V1** (backend, frontend, permisos y cierre documental). **Smoke manual en navegador pendiente; Cypress global no ejecutado**.

---

## 3. Alcance V1 propuesto

- **Historial por estudiante:** consultar y mostrar registros de `indices_riesgo` filtrados por `estudiante_id`.
- **Visible en perfil estudiante:** nuevo bloque cerca de **Riesgo académico** y **Semáforo de completitud de datos** (RF-19).
- **Datos mostrados V1:**
  - Periodo del registro (`anio_escolar`, `bimestre`).
  - Índice de riesgo (`indice`).
  - Nivel (`nivel`: Alto/Medio/Bajo).
  - `variables_utilizadas` solo como dato opcional si ya existe en `indices_riesgo`; **no** es requisito obligatorio.
- **Orden:** más reciente primero.
- **Presentación:** tabla o timeline simple. **Sin gráficos complejos.**
- **No recalcula riesgo.**
- **No llama a Flask.**
- **No dashboard global** (RF-14).
- **No reportes PDF** (RF-16).
- **No exportación.**
- **V1 Chilca:** validación de sede igual que RF-19.

---

## 4. Permiso implementado (Fase 4B)

**Permiso:** `ver_historial_riesgo`

**Roles asignados V1:**

- `administrador`
- `docente`
- `coordinador_academico`

**Roles no asignados V1:**

- `psicologo_tutor` (se evaluará con RF-11 perfil integral).
- `directivo` (se evaluará con RF-10 escalamiento / RF-16 reportes).

**Middleware futuro:** `auth:sanctum` + `permission:ver_historial_riesgo`.

---

## 5. Impacto backend futuro

Endpoint propuesto (solo planificación, no implementar aún):

```text
GET /api/estudiantes/{estudiante}/historial-riesgo
```

Características futuras:

- Protegido por `auth:sanctum` + `permission:ver_historial_riesgo`.
- Validar que el estudiante pertenezca a la sede operativa V1 (`chilca`).
- **Controller con consulta simple a `IndiceRiesgo`.**
- **Crear servicio separado solo si el código lo justifica.**
- Ordenar por `created_at` o `(anio_escolar, bimestre)` descendente.
- Filtro opcional por `anio_escolar` y/o `bimestre`.
- Respuesta JSON simple orientativa:

```json
{
  "estudiante_id": 1,
  "anio_escolar": "2026",
  "historial": [
    {
      "id": 10,
      "indice": 0.75,
      "nivel": "Alto",
      "anio_escolar": "2026",
      "bimestre": "II",
      "fecha": "2026-06-15"
    }
  ]
}
```

No modificar `RiesgoAcademicoService`. No recalcular índices.

---

## 6. Impacto frontend futuro

Componente propuesto (solo planificación):

```text
frontend/src/components/estudiantes/EstudiantePerfilHistorialRiesgo.jsx
```

Comportamiento futuro:

- Recibir `estudianteId`.
- Llamar a función futura `getHistorialRiesgo(estudianteId)` en `frontend/src/lib/api.js`.
- Mostrar carga, error aislado y estado vacío.
- Renderizar tabla simple o timeline simple con:
  - Periodo (año/bimestre).
  - Índice.
  - Nivel (con color sutil).
- Mostrarse solo si el usuario tiene permiso `ver_historial_riesgo`.
- Ubicarse en `EstudiantesPanel.jsx`, cerca de `EstudiantePerfilRiesgo` y `EstudiantePerfilSemaforoCompletitud`.

No gráfico complejo en V1.

---

## 7. Pruebas futuras

Backend (PHPUnit):

1. 401 sin sesión.
2. 403 sin permiso.
3. Usuario con permiso puede consultar historial.
4. Estudiante Auquimarca rechazado (sede V1 Chilca).
5. Historial vacío devuelve array vacío.
6. Historial con múltiples registros.
7. Orden descendente por fecha/periodo.
8. Filtro por `anio_escolar`.
9. Filtro por `bimestre`.
10. Consulta no modifica `indices_riesgo`.
11. Consulta no llama a Flask.

Frontend:

1. Build frontend exitoso.
2. Componente no se muestra sin permiso.
3. Componente muestra carga, error aislado, vacío.
4. Componente renderiza tabla/timeline con datos.

Manual:

1. Perfil estudiante Chilca muestra historial.
2. Datos coinciden con `indices_riesgo`.
3. Sin selector de sede.
4. Error aislado no rompe perfil.

---

## 8. Fuera del alcance

Queda explícitamente excluido de RF-20 V1:

- ML real / reentrenamiento (RF-18).
- Modificación de Flask.
- Cambio de la fórmula determinística de riesgo.
- Recálculo automático de riesgo.
- Modificación de `RiesgoAcademicoService`.
- Dashboard de riesgo (RF-14).
- Reportes PDF / zona RF-16.
- Escalamiento directivo (RF-10).
- Perfil integral psicólogo/tutor (RF-11).
- Variables socioeconómicas como insumo obligatorio.
- Multi-sede / selector de sede.
- Gráficos complejos (líneas, tendencias avanzadas).
- Cypress / E2E en esta fase.
- Certificación ISO.

---

## 9. Fases futuras

| Fase | Contenido | Entregables | Estado |
| ---- | --------- | ----------- | ------ |
| **Fase 4A** | Planificación RF-20 | Este plan | **Completada** |
| **Fase 4B** | Permisos y base RBAC RF-20 | `ver_historial_riesgo` en `PermissionsSeeder.php`; documentación seguridad | **Completada** |
| **Fase 4C** | Backend historial RF-20 | `HistorialRiesgoController`; ruta API; `HistorialRiesgoTest` | **Completada** |
| **Fase 4D** | Frontend historial RF-20 | `EstudiantePerfilHistorialRiesgo.jsx`, `getHistorialRiesgo()`, integración en perfil, build OK | **Completada** |
| **Fase 4E** | Pruebas y cierre RF-20 | Validaciones backend/frontend, docs actualizadas, build OK, tests 12 passed, NC-20 cerrada V1 | **Completada** |

---

## 10. Conclusión

RF-20 está **implementado V1**: persistencia (`indices_riesgo`), permiso RBAC (`ver_historial_riesgo`), backend (`HistorialRiesgoController` + ruta + tests 12 passed), frontend (`EstudiantePerfilHistorialRiesgo.jsx` + `getHistorialRiesgo()` + build OK) y cierre documental (Fase 4E).

**Restricciones V1 honestas:** smoke manual en navegador pendiente; Cypress global no ejecutado. No se afirma ML real, multi-sede, ISO certificado ni funcionalidades fuera de alcance (RF-10/RF-11/RF-14/RF-16/RF-18).

La implementación es mínima: consulta de registros existentes, presentación simple en perfil estudiante. No toca Flask ni `RiesgoAcademicoService`; no recalcula ni predice riesgo.

---

*Plan AI-DLC Fases 4A–4B — 2026-06-23.*
