# Plan AI-DLC — RF-14 Dashboard institucional V1

## 1. Propósito

RF-14 permitirá visualizar un resumen institucional del estado académico y de riesgo de la sede Chilca usando datos ya existentes en el sistema. El dashboard ampliará el alcance actual (que hoy muestra solo un subset de riesgo) con indicadores académicos, de asistencia, conductuales y de completitud, sin recalcular riesgo y sin llamar a Flask.

## 2. Estado actual

| Elemento | Estado | Evidencia |
|----------|--------|-----------|
| Endpoint dashboard | **Existe** | `GET /api/dashboard` en `backend/routes/api.php:70` |
| Endpoint export PDF | **Existe (parcial)** | `GET /api/dashboard/export` en `backend/routes/api.php:71`; DomPDF — antecedente parcial, no parte del alcance RF-14 V1 institucional |
| Controller dashboard | **Existe** | `backend/app/Http/Controllers/Api/DashboardController.php` con `index()` y `export()` |
| Permiso dashboard | **Existe** | `ver_dashboard` en `PermissionsSeeder.php`; asignado a administrador, docente, coordinador_academico, directivo |
| UI dashboard | **Existe (riesgo subset)** | `frontend/src/components/DashboardPanel.jsx` registrado en `App.jsx` como módulo `dashboard` |
| Tests backend | **Existen** | `backend/tests/Feature/DashboardTest.php` — 11 tests definidos (estructura, filtros, riesgo, alertas, export PDF) |
| Tests frontend | **No existen** | Sin Jest/Cypress confirmado |
| Indicadores académicos ampliados | **Pendiente** | DRS v2.1 §RF-14 pide notas, asistencia, reportes conductuales, avance curricular; solo hay riesgo + indicadores curriculares básicos |
| Filtros por año/bimestre | **Pendiente** | Dashboard actual filtra por nivel, grado, sección, nivel_riesgo y sede; no por año_escolar ni bimestre |
| Documentación | **Actualizada parcialmente** | `api.md`, `manual-usuario.md`, `manual-tecnico.md`, `matriz-rf-sprint-test.md` reflejan dashboard actual |

## 3. Alcance V1 propuesto

El dashboard institucional V1 ampliará `GET /api/dashboard` (o un nuevo `GET /api/dashboard/institucional`) con indicadores agregados simples:

- **Resumen general:**
  - total de estudiantes activos de Chilca;
  - total de estudiantes con al menos un índice de riesgo;
  - cantidad por nivel de riesgo (Bajo, Medio, Alto) usando el **último índice por estudiante**.
- **Distribución por grado/sección:**
  - total de estudiantes por combinación grado + sección;
  - desglose por nivel de riesgo dentro de cada combinación.
- **Últimos riesgos registrados:**
  - listado de los últimos 10–15 índices de riesgo con estudiante, grado, sección, nivel, índice y fecha.
- **Completitud de datos (RF-19) resumida:**
  - cantidad de estudiantes con semáforo verde / amarillo / rojo, si se puede integrar sin recalcular riesgo.
- **Acceso a RF-16:**
  - enlace o botón hacia **Reportes de riesgo académico** si el usuario tiene permiso `ver_reportes_riesgo`.

Restricciones V1:

- Solo sede **Chilca** (`SedeOperativa::CHILCA` / `conSedeOperativa()`).
- **Sin PDF** nuevo; el export existente del dashboard se mantiene como antecedente parcial.
- **Sin exportación** nueva.
- **Sin gráficos complejos** (solo tarjetas, tablas y barras simples).
- **No recalcular riesgo**; usar `indices_riesgo` existentes.
- **No llamar a Flask**.

## 4. Permiso sugerido

Se propone crear un permiso nuevo para distinguir el dashboard institucional del dashboard básico heredado:

| Permiso | Uso funcional | Roles V1 |
|---------|---------------|----------|
| `ver_dashboard_institucional` | Consultar dashboard institucional ampliado | administrador, coordinador_academico, directivo |

**Justificación:** el permiso legacy `ver_dashboard` está asignado también a `docente` en el seeder actual. Para que docente y psicólogo_tutor queden fuera del dashboard institucional V1, se recomienda un permiso específico. En RF-14B se decidirá si:

- `ver_dashboard` sigue vigente para un dashboard básico limitado; o
- se reemplaza por `ver_dashboard_institucional` y se retira `ver_dashboard` del seeder para docente.

No implementar permiso todavía.

## 5. Impacto backend futuro

**Endpoint propuesto:**

```
GET /api/dashboard/institucional
```

Alternativa menor: ampliar el existente `GET /api/dashboard` (requiere mantener compatibilidad con UI actual).

**Middleware:**

- `auth:sanctum`
- `permission:ver_dashboard_institucional`

**Debe:**

- Consultar `Estudiante` filtrado por `sede = 'chilca'`.
- Consultar `IndiceRiesgo` para obtener último índice por estudiante.
- Calcular conteos de riesgo bajo/medio/alto sobre el último índice de cada estudiante.
- Agrupar por `grado` y `seccion`.
- Obtener últimos riesgos ordenados por `created_at` DESC (limit 10–15).
- Opcionalmente consultar semáforo de completitud (`SemaforoCompletitudController` / `CompletitudDatosService`) para conteos verde/amarillo/rojo.
- No recalcular riesgo.
- No llamar a Flask.
- No exponer selector de sede.

**Respuesta propuesta:**

```json
{
  "resumen": {
    "total_estudiantes": 120,
    "con_riesgo": 80,
    "riesgo_bajo": 45,
    "riesgo_medio": 25,
    "riesgo_alto": 10
  },
  "completitud": {
    "verde": 60,
    "amarillo": 40,
    "rojo": 20
  },
  "por_grado_seccion": [
    {
      "grado": "5°",
      "seccion": "A",
      "total": 20,
      "riesgo_bajo": 12,
      "riesgo_medio": 5,
      "riesgo_alto": 3
    }
  ],
  "ultimos_riesgos": [
    {
      "estudiante_id": 1,
      "estudiante": "Pérez, Juan",
      "grado": "5°",
      "seccion": "A",
      "nivel": "Alto",
      "indice": 0.82,
      "fecha": "2026-06-23"
    }
  ]
}
```

**Controller propuesto:** extender o crear nuevo método en `DashboardController` (por ejemplo `institucional()`).

## 6. Impacto frontend futuro

**Componente/vista propuesto:**

- Nombre: `DashboardInstitucionalPanel.jsx`
- Ubicación: `frontend/src/components/dashboard/` o junto al `DashboardPanel.jsx` existente.

**Debe incluir:**

- Tarjetas de resumen (total estudiantes, con riesgo, bajo, medio, alto).
- Tarjeta de completitud (verde/amarillo/rojo) si se integra RF-19.
- Tabla de distribución por grado/sección.
- Tabla de últimos riesgos registrados.
- Enlace o botón hacia **Reportes de riesgo** (RF-16) si el usuario tiene `ver_reportes_riesgo`.
- Filtros simples: año escolar, bimestre, nivel, grado, sección.
- Estado de carga.
- Estado vacío (cuando no hay estudiantes o no hay riesgos).
- Estado de error aislado.

**Restricciones:**

- No usar librerías nuevas de gráficos.
- No crear selector de sede.
- No agregar botón PDF/exportar (el existente del dashboard actual se mantiene como antecedente).

**Registro en `App.jsx`:**

- Agregar módulo `dashboard_institucional` (o reutilizar `dashboard` si se decide reemplazar).
- Visible solo con `ver_dashboard_institucional`.

## 7. Pruebas futuras

### Backend

| Prueba | Descripción |
|--------|-------------|
| 401 sin sesión | Llamar sin token → 401 |
| 403 sin permiso | Llamar con token pero sin `ver_dashboard_institucional` → 403 |
| 200 con permiso | Usuario con permiso consulta → 200 + estructura esperada |
| Solo Chilca | Registros de Auquimarca no aparecen en conteos |
| Conteo riesgo | Último índice por estudiante clasifica bajo/medio/alto correctamente |
| Distribución grado/sección | Tabla por grado/sección suma correctamente |
| Últimos riesgos | Devuelve los más recientes ordenados por fecha |
| Completitud | Conteos verde/amarillo/rojo coinciden con RF-19 |
| No recalcula riesgo | No invoca `RiesgoAcademicoService` ni Flask |
| No llama Flask | Verificar que no hay llamadas HTTP a `/predict` |

### Frontend

| Prueba | Descripción |
|--------|-------------|
| Build OK | `npm run build` sin errores |
| Renderiza tarjetas | Resumen visible |
| Renderiza tablas | Distribución y últimos riesgos visibles |
| Estado vacío | Sin datos → mensaje amigable |
| Estado error | Error 500 → mensaje amigable |
| Sin selector sede | No aparece combobox de sede |
| Sin PDF/exportar nuevo | No hay botón de exportar en dashboard institucional |
| Menú por permiso | Solo roles con permiso ven el módulo |

## 8. Fuera del alcance RF-14 V1

- **No PDF nuevo** — el export existente del dashboard actual es antecedente parcial.
- **No exportación** nueva (Excel/CSV).
- **No gráficos complejos** — solo tarjetas, tablas y barras simples.
- **No ML real** — RF-18 queda fuera.
- **No RF-10** (escalamiento alertas).
- **No RF-11** (perfil integral).
- **No RF-16** — solo enlace hacia él; no reimplementar reportes.
- **No Cypress global** en esta fase.
- **No multi-sede** — solo Chilca.
- **No selector de sede** en UI.
- **No variables socioeconómicas**.
- **No recalcular riesgo** — solo consultar `indices_riesgo`.
- **No llamar a Flask**.
- **No modificar Docker, migraciones ni seeders** en RF-14A.

## 9. Fases futuras

| Fase | Nombre | Descripción | Estado |
|------|--------|-------------|--------|
| RF-14A | Plan dashboard institucional V1 | Crear plan, revisar estado, definir alcance, permiso, backend/frontend futuros | **Completada** |
| RF-14B | Permiso/base RBAC | Crear `ver_dashboard_institucional`, asignar a administrador, coordinador_academico, directivo; actualizar seeder si aplica | **Completada** |
| RF-14C | Backend dashboard institucional | Crear/ampliar endpoint `GET /api/dashboard/institucional`, controller, consultas agregadas, sede Chilca, tests | **Completada** |
| RF-14D | Frontend dashboard institucional | Crear `DashboardInstitucionalPanel.jsx`, registrar en `App.jsx`, tarjetas, tablas, filtros, estados | **Completada** |
| RF-14E | Pruebas, smoke manual y cierre | Tests backend extendidos, build frontend, smoke manual, documentación final | **Completada** |

## 10. Conclusión

RF-14 V1 está **implementado y cerrado documentalmente**. Se cuenta con una base funcional (`DashboardController`, `DashboardPanel`, `ver_dashboard`, `DashboardTest`), la base RBAC institucional (`ver_dashboard_institucional`), el backend institucional (`GET /api/dashboard/institucional`, `DashboardInstitucionalController`, 16 tests passed) y el frontend institucional (`DashboardInstitucionalPanel.jsx`, build OK, lint propio limpio). El permiso legacy `ver_dashboard` se mantiene sin cambios.

**Validaciones RF-14E:**

- `DashboardInstitucionalTest`: 16 passed, 57 assertions.
- `DashboardTest` legacy: 12 passed, 76 assertions.
- Regresión RF-06/RF-16/RF-19/RF-20: 74 tests, 246 assertions.
- Build frontend OK.
- Lint: 88 problemas preexistentes; `DashboardInstitucionalPanel.jsx` sin errores nuevos.
- Ruta `GET /api/dashboard/institucional` verificada.

**Pendiente honesto:** smoke manual navegador por falta de navegador en el entorno.

Todo el alcance V1 respeta sede única Chilca, no recalcula riesgo, no llama a Flask, no implementa PDF/exportación ni gráficos complejos.

---

## RF-14A completada — Plan dashboard institucional V1

### Archivo creado

`docs/metodologia/planes-ai-dlc/plan-rf14-dashboard-institucional.md`

### Estado encontrado

- `GET /api/dashboard` existe con `DashboardController` y permiso `ver_dashboard`.
- `DashboardPanel.jsx` existe y muestra KPIs de riesgo + filtros básicos.
- `DashboardTest.php` existe con 11 tests.
- Faltan indicadores académicos, asistencia, conductuales, avance curricular y filtros por año/bimestre.
- El permiso `ver_dashboard` está asignado a docente; se propone permiso nuevo `ver_dashboard_institucional` para separar alcance.

### Alcance V1 propuesto

Dashboard institucional con: resumen de estudiantes y riesgo, distribución por grado/sección, últimos riesgos, completitud resumida (RF-19) y enlace a RF-16. Sin PDF/exportación nuevos, sin gráficos complejos, solo Chilca.

### Permiso sugerido

`ver_dashboard_institucional` para administrador, coordinador_academico y directivo. Docente y psicólogo/tutor quedan fuera del dashboard institucional V1.

### Backend futuro

- Endpoint: `GET /api/dashboard/institucional`
- Middleware: `auth:sanctum` + `permission:ver_dashboard_institucional`
- Datos: `Estudiante`, `IndiceRiesgo`, semáforo completitud
- Respuesta: resumen, completitud, por_grado_seccion, ultimos_riesgos

### Frontend futuro

- Componente: `DashboardInstitucionalPanel.jsx`
- Registro: módulo en `App.jsx`
- UI: tarjetas, tablas, filtros simples, estados carga/vacío/error, enlace a RF-16

### Fases futuras

| Fase | Qué incluye | Estado |
|------|-------------|--------|
| RF-14B | Permiso `ver_dashboard_institucional` + asignación a roles + seeder | **Completada** |
| RF-14C | Backend `GET /api/dashboard/institucional` + controller + tests | **Completada** |
| RF-14D | Frontend `DashboardInstitucionalPanel.jsx` + App.jsx + build OK | Pendiente |
| RF-14E | Tests extendidos + smoke manual + documentación final + cierre | Pendiente |

### Validaciones

- Revisar DRS v2.1 §RF-14.
- Revisar rutas, controller, permisos, UI y tests existentes.
- Confirmar datos disponibles en modelos.
- Confirmar sede única Chilca.
- Confirmar que no se recalcula riesgo ni llama Flask.

### Próxima fase recomendada

**RF-14D — Frontend dashboard institucional.**

---

## RF-14C completada — Backend dashboard institucional V1

### Archivos creados

- `backend/app/Http/Controllers/Api/DashboardInstitucionalController.php`
- `backend/tests/Feature/DashboardInstitucionalTest.php`

### Archivos modificados

- `backend/routes/api.php` — ruta `GET /api/dashboard/institucional` con middleware `auth:sanctum` + `permission:ver_dashboard_institucional`

### Endpoint implementado

| Método | Ruta | Middleware | Permiso |
|--------|------|------------|---------|
| GET | `/api/dashboard/institucional` | `auth:sanctum` | `ver_dashboard_institucional` |

### Métricas implementadas

- `resumen`: `total_estudiantes`, `con_riesgo`, `riesgo_bajo`, `riesgo_medio`, `riesgo_alto`
- `completitud`: `con_riesgo`, `sin_riesgo`, `porcentaje_con_riesgo`
- `por_grado_seccion`: distribución por grado/sección con totales y riesgos por nivel
- `ultimos_riesgos`: últimos 10 índices con datos del estudiante

### Filtros implementados

- `anio_escolar`
- `bimestre`
- `grado`
- `seccion`

### Restricciones respetadas

- Sede fija **Chilca** (sin selector de sede).
- No recalcula riesgo.
- No llama a Flask.
- No PDF/exportación nueva.
- No modifica `GET /api/dashboard` ni `ver_dashboard`.

### Pruebas ejecutadas

```bash
docker compose exec app-backend php artisan test --filter=DashboardInstitucionalTest
```

**Resultado:** 16 passed, 57 assertions.

Regresión dashboard legacy:

```bash
docker compose exec app-backend php artisan test --filter=DashboardTest
```

**Resultado:** 12 passed, 76 assertions.

### Estado RF-14

**Implementado V1 con smoke manual pendiente; frontend institucional implementado, cierre RF-14E en progreso.**

---

## RF-14D completada — Frontend dashboard institucional V1

### Archivos creados

- `frontend/src/components/dashboard/DashboardInstitucionalPanel.jsx`

### Archivos modificados

- `frontend/src/lib/api.js` — función `getDashboardInstitucional(params)`
- `frontend/src/App.jsx` — módulo `dashboard_institucional`, menú lateral **Dashboard institucional**, `PanelModulo`, `moduloPorDefecto`, `tituloModulo`, listener para navegación a reportes de riesgo
- `frontend/src/components/layout/navIcons.jsx` — icono `dashboard_institucional`

### Vista implementada

- Título: **Dashboard institucional**.
- Descripción del alcance V1 (Chilca, datos existentes, sin recalcular riesgo).
- Filtros: año escolar, bimestre, grado, sección.
- Botones **Buscar** y **Limpiar filtros**.
- Tarjetas resumen: total estudiantes, con riesgo, riesgo bajo, riesgo medio, riesgo alto.
- Bloque completitud: con riesgo, sin riesgo, porcentaje con riesgo.
- Tabla por grado/sección.
- Tabla últimos riesgos.
- Botón **Ir a Reportes de riesgo** solo si el usuario tiene `ver_reportes_riesgo`.
- Estados de carga, vacío y error.
- Mensaje 403 amigable si falta permiso.

### Permisos frontend

- Menú visible solo con `ver_dashboard_institucional`.
- Asignado en V1 a: administrador, coordinador_academico, directivo.
- Docente y psicólogo/tutor no ven el menú ni acceden al panel.

### Validaciones ejecutadas

```bash
docker compose exec app-frontend npm run build
```

**Resultado:** build OK, chunk `DashboardInstitucionalPanel` generado.

```bash
docker compose exec app-frontend npm run lint
```

**Resultado:** 88 problemas preexistentes en otros componentes; `DashboardInstitucionalPanel.jsx` sin errores nuevos.

### Smoke manual

Pendiente por falta de navegador en el entorno.

### Estado RF-14

**Implementado V1 con smoke manual pendiente; frontend institucional implementado, cierre RF-14E completado.**

---

## RF-14E completada — Cierre dashboard institucional V1

### Validaciones ejecutadas

Backend RF-14:

```bash
docker compose exec app-backend php artisan test --filter=DashboardInstitucionalTest
```

**Resultado:** 16 passed, 57 assertions.

```bash
docker compose exec app-backend php artisan test --filter=DashboardTest
```

**Resultado:** 12 passed, 76 assertions (dashboard legacy intacto).

Regresión:

```bash
docker compose exec app-backend php artisan test --filter=ReporteRiesgoAcademicoTest
```

**Resultado:** 13 passed, 36 assertions.

```bash
docker compose exec app-backend php artisan test --filter=RiesgoTest
```

**Resultado:** 38 passed, 125 assertions.

```bash
docker compose exec app-backend php artisan test --filter=SemaforoCompletitudTest
```

**Resultado:** 11 passed, 55 assertions.

```bash
docker compose exec app-backend php artisan test --filter=HistorialRiesgoTest
```

**Resultado:** 12 passed, 30 assertions.

Ruta:

```bash
docker compose exec app-backend php artisan route:list --path=dashboard/institucional
```

**Resultado:** `GET /api/dashboard/institucional` listada correctamente.

Frontend RF-14:

```bash
docker compose exec app-frontend npm run build
```

**Resultado:** build OK.

```bash
docker compose exec app-frontend npm run lint
```

**Resultado:** 88 problemas preexistentes (71 errores, 17 warnings); `DashboardInstitucionalPanel.jsx` sin errores nuevos.

### Smoke manual

Pendiente por falta de navegador en el entorno.

### Documentación actualizada

- `docs/matriz-rf-sprint-test.md`
- `docs/limitaciones.md`
- `docs/pruebas/informe-pruebas.md`
- `docs/manual-usuario.md`
- `docs/manual-tecnico.md`
- `docs/api.md`
- `docs/metodologia/planes-ai-dlc/plan-rf14-dashboard-institucional.md`
- `docs/calidad/no-conformidades-y-mejora.md` (NC-22)

### Alcance V1 confirmado

- No se implementó PDF nuevo.
- No se implementó exportación.
- No se implementaron gráficos complejos.
- No se recalculó riesgo.
- No se llamó a Flask.
- No se usaron variables socioeconómicas.
- No se creó selector de sede.
- Sede operativa V1: Chilca.
- Docente y psicólogo/tutor quedan fuera del dashboard institucional V1.
- Dashboard legacy `GET /api/dashboard` se mantiene sin cambios.

### Estado final RF-14

**RF-14 implementado V1 con smoke manual pendiente.**
