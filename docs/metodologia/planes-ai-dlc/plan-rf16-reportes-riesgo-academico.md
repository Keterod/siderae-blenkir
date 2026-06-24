# Plan AI-DLC — RF-16 Reportes de riesgo académico V1

## 1. Propósito

RF-16 permitirá consultar reportes de riesgo académico usando datos ya existentes en `indices_riesgo`, sin recalcular riesgo, sin llamar Flask y sin generar PDF en V1. El reporte se presenta como listado paginable y filtrable para que docentes, coordinadores y directivos tomen decisiones informadas.

## 2. Estado actual

| Elemento                          | Estado                            | Evidencia                                                                                               |
| --------------------------------- | --------------------------------- | ------------------------------------------------------------------------------------------------------- |
| Endpoint de reportes riesgo       | **No existe**                     | Solo `GET /api/dashboard/export` (PDF parcial, DomPDF) — `backend/routes/api.php:71`                    |
| Permiso `ver_reportes_riesgo`     | **Base RBAC implementada RF-16B** | Permiso creado en `PermissionsSeeder.php` y asignado a administrador, coordinador_academico, directivo  |
| Permiso `generar_reportes_riesgo` | **Planificado** | PDF/exportación fuera de V1; no implementado en RF-16B/C |
| Controller de reportes riesgo     | **No existe**                     | Solo `DashboardController` con método `export()` que usa `ver_dashboard`                                |
| UI de reportes riesgo             | **No existe**                     | No hay ruta ni componente en `App.jsx` para reportes. Solo botón "Exportar PDF" en `DashboardPanel.jsx` |
| Tests de reportes riesgo          | **No existen**                    | `DashboardTest` cubre export PDF; no hay test para reportes de riesgo dedicados                         |
| `indices_riesgo`                  | **Poblado**                       | Tabla con registros de riesgo procesados vía API o comando `demo:procesar-riesgos` (RF-06, RF-20)       |
| Historial riesgo (RF-20)          | **Implementado V1**               | `GET /api/estudiantes/{estudiante}/historial-riesgo`, permiso `ver_historial_riesgo`                    |
| Semáforo completitud (RF-19)      | **Implementado V1**               | `GET /api/estudiantes/{estudiante}/semaforo-completitud`, permiso `ver_semaforo_completitud`            |
| Cálculo de riesgo (RF-06)         | **Implementado V1**               | Backend completo, 61 tests, Flask validado. UI activada V1 (NC-11 cerrada V1); botón **Procesar/Actualizar riesgo** en perfil estudiante con permiso `procesar_riesgo` |

## 3. Alcance V1 propuesto

- Reporte/listado de estudiantes con riesgo ya calculado (solo consulta a `indices_riesgo`).
- Filtros básicos: año escolar, grado, sección, nivel de riesgo, bimestre.
- Solo sede Chilca (usar `SedeOperativa::defaultConsulta()`).
- Mostrar por cada registro: nombre del estudiante, índice, nivel (Bajo/Medio/Alto), fecha de cálculo, año escolar, bimestre.
- Opcional V1: mostrar de forma resumida el semáforo de completitud (RF-19) y el historial evolutivo (RF-20) en la misma fila o tooltip.
- **No recalcular riesgo** — solo consultar datos existentes.
- **No llamar a Flask**.
- **No generar PDF en V1** — el reporte es en pantalla.
- **No es dashboard RF-14** — RF-16 es zona específica de reportes de riesgo.
- Tabla paginable con ordenamiento por columna (índice, fecha, nivel).
- Estado vacío: mostrar mensaje claro si no hay registros con los filtros aplicados.
- Error aislado: capturar excepciones y devolver error 500 genérico sin exponer detalles internos.

## 4. Permiso sugerido

| Permiso               | Nivel     | Propósito                                            |
| --------------------- | --------- | ---------------------------------------------------- |
| `ver_reportes_riesgo` | Operación | Consultar el listado de reportes de riesgo académico |

Permiso ya implementado en RF-16B.

**Roles V1:**

| Rol                   | ¿Acceso? | Justificación                           |
| --------------------- | -------- | --------------------------------------- |
| administrador         | Sí       | Acceso total del sistema                |
| coordinador_academico | Sí       | Supervisión académica global            |
| directivo             | Sí       | Visión institucional (DRS lo justifica) |

Psicólogo/tutor: **No** en V1 (su alcance son alertas e intervenciones, no reportes globales de riesgo).

RF-16 permitirá consultar reportes de riesgo académico usando datos ya existentes en `indices_riesgo`, sin recalcular riesgo, sin llamar Flask y sin generar PDF en V1. El reporte se presenta como listado paginable y filtrable para usuarios autorizados de gestión académica e institucional.

## 5. Impacto backend futuro

**Endpoint propuesto:**

```
GET /api/reportes/riesgo-academico
```

**Filtros query string:**

- `anio_escolar` (string)
- `grado` (string)
- `seccion` (string)
- `nivel` (string — nivel riesgo: Alto, Medio, Bajo)
- `bimestre` (string)
- `page` / `per_page` (paginación)

**Debe:**

- Usar `auth:sanctum`.
- Usar permiso `ver_reportes_riesgo`.
- Consultar `IndiceRiesgo` con join a `estudiantes` para datos básicos (nombres, grado, sección).
- Aplicar `SedeOperativa::defaultConsulta()` para filtrar solo Chilca.
- Aplicar filtros opcionales por año, grado, sección, nivel, bimestre.
- No incluir estudiantes de Inicial (el riesgo no se procesa para Inicial).
- No llamar a `MlRiskService` ni a Flask.
- No recalcular riesgo.
- Orden descendente por `created_at`.
- Paginar resultados (ej. 20 por página).

**Controller propuesto:** `ReporteRiesgoAcademicoController`

**Ruta propuesta:**

```php
Route::middleware(['auth:sanctum', 'permission:ver_reportes_riesgo'])
    ->get('reportes/riesgo-academico', [ReporteRiesgoAcademicoController::class, 'index']);
```

**Modelo:** Usar `IndiceRiesgo` existente (no requiere nuevo modelo ni migración).

## 6. Impacto frontend futuro

**Componente/vista propuesto:**

- Nombre: `ReporteRiesgoAcademicoPanel.jsx`
- Ubicación: `frontend/src/components/reportes/` (nueva carpeta) o junto a dashboard si se agrupa por módulo.

**Debe incluir:**

- Filtros simples (año escolar, grado, sección, nivel riesgo, bimestre) con valores obtenidos desde los datos existentes.
- Tabla de resultados con columnas: Estudiante, Grado, Sección, Índice, Nivel, Bimestre, Fecha.
- Paginación.
- Botón/acciones por fila:
    - Ver perfil del estudiante (navegar a `estudiante.show` con pestaña de riesgo).
    - Opcional V1: tooltip o columna adicional con indicador resumido de completitud RF-19 e historial RF-20.
- Estado vacío ("No se encontraron registros de riesgo con los filtros seleccionados").
- Error aislado (mostrar mensaje amigable si la API falla, sin pantalla en blanco).
- Estado de carga (spinner o skeleton).
- **Sin selector de sede** (solo Chilca, usar `SEDE_OPERATIVA`).
- **Sin PDF**.
- **Sin gráficos complejos** (solo tabla).

**API helper propuesto en `frontend/src/lib/api.js`:**

```js
getReportesRiesgo(filtros = {}) {
  return this.get('/reportes/riesgo-academico', filtros);
}
```

## 7. Pruebas futuras

### Backend

| Prueba              | Descripción                                                   |
| ------------------- | ------------------------------------------------------------- |
| 401 sin sesión      | Llamar endpoint sin token → 401                               |
| 403 sin permiso     | Llamar con token pero sin permiso `ver_reportes_riesgo` → 403 |
| 200 con permiso     | Usuario con permiso consulta → 200 + lista paginada           |
| Filtro año escolar  | `?anio_escolar=2025` → solo resultados de ese año             |
| Filtro grado        | `?grado=3` → solo estudiantes de 3er grado                    |
| Filtro sección      | `?seccion=A` → solo sección A                                 |
| Filtro nivel riesgo | `?nivel=Alto` → solo riesgo alto                              |
| Filtro bimestre     | `?bimestre=I` → solo bimestre I                               |
| Filtros combinados  | Múltiples filtros simultáneos                                 |
| Solo Chilca         | Registros de Auquimarca no aparecen                           |
| No recalcula riesgo | Verificar que no se invoca Flask ni `RiesgoAcademicoService`  |
| Paginación          | `?page=2&per_page=5` → página 2 con 5 registros               |
| Orden descendente   | Resultados ordenados por `created_at` DESC                    |
| Sin registros       | No hay índices de riesgo → lista vacía                        |

### Frontend

| Prueba               | Descripción                                       |
| -------------------- | ------------------------------------------------- |
| Build OK             | `npm run build` sin errores                       |
| Filtros renderizan   | Todos los filtros aparecen en la vista            |
| Tabla muestra datos  | Datos de API se renderizan correctamente          |
| Paginación funcional | Navegar entre páginas                             |
| Estado vacío         | Sin datos → mensaje "No se encontraron registros" |
| Estado error         | Error 500 → mensaje amigable                      |
| Sin selector sede    | No aparece combobox de sede                       |

## 8. Fuera del alcance RF-16 V1

- **No PDF** — ni DomPDF, ni otra librería. El reporte es solo pantalla.
- **No dashboard RF-14** — RF-16 es zona específica de reportes de riesgo, no reemplaza ni amplía el dashboard.
- **No ML real** — RF-18 queda fuera.
- **No reentrenamiento** — RF-18.
- **No RF-10** (escalamiento alertas).
- **No RF-11** (perfil integral).
- **No SIAGIE**.
- **No comunicación familiar**.
- **No variables socioeconómicas** — no se muestran ni se consultan.
- **No Fast Test**.
- **No Cypress en esta fase** — sigue pendiente global.
- **No multi-sede** — solo Chilca.
- **No selector de sede** en UI.
- **No recalcular riesgo** — solo consulta a `indices_riesgo`.
- **No llamar a Flask**.
- **No migraciones ni seeders nuevos**.
- **No modificar Docker**.
- **No modificar modelos existentes** (IndiceRiesgo no se toca).

## 9. Fases futuras

| Fase   | Nombre                          | Descripción                                                                                                                                                    | Estado         |
| ------ | ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------- |
| RF-16B | Permiso y base RBAC             | Crear permiso `ver_reportes_riesgo`, asignarlo a roles (admin, coord. académico, directivo). Actualizar seeder de permisos                                     | **Completada** |
| RF-16C | Backend reportes de riesgo | Crear `ReporteRiesgoAcademicoController`, endpoint `GET /api/reportes/riesgo-academico`, ruta, validación de filtros, paginación, sede Chilca | **Completada** |
| RF-16D | Frontend vista de reportes | Crear `ReporteRiesgoAcademicoPanel.jsx`, registrar en `App.jsx` como nuevo módulo, agregar `getReportesRiesgoAcademico` a `api.js`, filtros, tabla, paginación, estados | **Completada** |
| RF-16E | Pruebas, documentación y cierre | Tests backend (13 passed), build frontend OK, smoke manual, documentar en api.md, manual-usuario.md, matriz, informe-pruebas, cierre | **Completada** |

## 10. Conclusión

RF-16 está **implementado V1 con smoke manual pendiente**. El alcance V1 es mínimo: un endpoint de consulta a `indices_riesgo` con filtros y paginación, sin PDF, sin Flask, sin multi-sede. La base RBAC (RF-16B), el backend (RF-16C), el frontend (RF-16D) y el cierre documental (RF-16E) están completos. Smoke manual en navegador queda pendiente por falta de navegador en el entorno. No hay dependencias bloqueantes: RF-06, RF-19 y RF-20 ya están implementados y poblados.

---

## RF-16A completada — Plan reportes de riesgo académico

### Archivo creado

`docs/metodologia/planes-ai-dlc/plan-rf16-reportes-riesgo-academico.md`

### Estado encontrado

RF-16 está en estado **"Implementado V1 con smoke manual pendiente"** en la matriz. El backend `GET /api/reportes/riesgo-academico` está implementado con `ReporteRiesgoAcademicoController`, filtros, paginación, sede Chilca y 13 tests passed (RF-16C). El frontend `ReporteRiesgoAcademicoPanel.jsx` está registrado en `App.jsx` con menú lateral **Reportes de riesgo**, build OK (RF-16D) y lint propio limpio (RF-16D.1). El cierre documental, regresión de pruebas y validaciones técnicas se completaron en RF-16E. Smoke manual en navegador queda pendiente por falta de navegador en el entorno. El permiso `ver_reportes_riesgo` fue implementado en RF-16B. El permiso `generar_reportes_riesgo` (PDF/exportación) sigue planificado para fases futuras.

### Alcance V1 propuesto

- Listado paginable de estudiantes con riesgo desde `indices_riesgo`.
- Filtros: año escolar, grado, sección, nivel de riesgo, bimestre.
- Solo sede Chilca.
- Muestra: índice, nivel, fecha, año, bimestre, datos del estudiante.
- Opcional V1: indicador resumido de completitud RF-19 e historial RF-20.
- **No recalcular riesgo, no Flask, no PDF, no dashboard RF-14.**

### Permiso sugerido

`ver_reportes_riesgo` para roles: administrador, coordinador_academico, directivo.

### Fases futuras

| Fase   | Qué incluye                                                                 |
| ------ | --------------------------------------------------------------------------- |
| RF-16B | Permiso `ver_reportes_riesgo` + asignación a roles + seeder |
| RF-16C | `ReporteRiesgoAcademicoController` + endpoint + ruta + filtros + paginación + 13 tests backend |
| RF-16D | `ReporteRiesgoAcademicoPanel.jsx` + registro en App.jsx + api.js + build OK |
| RF-16E | Smoke manual + documentación final + cierre | **Completada** |

### Validaciones RF-16E

- `ReporteRiesgoAcademicoTest`: 13 passed, 36 assertions.
- Regresión RF-06/RF-19/RF-20: 61 tests, 210 assertions.
- Ruta `GET /api/reportes/riesgo-academico` con middleware `auth:sanctum` + `permission:ver_reportes_riesgo` confirmada.
- Build frontend: OK.
- Lint frontend: 88 problemas preexistentes; `ReporteRiesgoAcademicoPanel.jsx` sin errores nuevos.
- Smoke manual navegador: **pendiente** por falta de navegador en el entorno.

### Alcance V1 confirmado

- Usar datos existentes (`indices_riesgo`), no recalcular.
- No llamar a Flask.
- No incluir Auquimarca.
- No PDF.
- No selector de sede.
- No migraciones.
- Docente fuera de RF-16 V1.

### Próxima fase recomendada

**Commit de cierre RF-16.**

> **Nota:** `generar_reportes_riesgo` queda planificado para fases futuras (PDF/exportación fuera de V1).
