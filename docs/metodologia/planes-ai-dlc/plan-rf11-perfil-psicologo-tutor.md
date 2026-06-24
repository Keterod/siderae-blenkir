# Plan AI-DLC — RF-11 Perfil psicólogo/tutor V1

**Fase:** RF-11A — Planificación previa a implementación  
**Fecha:** 2026-06-24  
**Metodología:** AI-DLC · perfiles en [`docs/metodologia/agentes-ai-dlc-siderae.md`](../agentes-ai-dlc-siderae.md)  
**Guía operativa:** [`docs/metodologia/ai-dlc-aplicado-siderae.md`](../ai-dlc-aplicado-siderae.md)

---

## 1. Propósito

Planificar **RF-11** (*Atención psicológica/tutorial con perfil integral*) antes de escribir código, siguiendo AI-DLC y validación humana obligatoria.

RF-11 permitirá al psicólogo/tutor consultar y acompañar casos de estudiantes con señales de riesgo académico o conductual. La vista es de **seguimiento y apoyo institucional**, no de diagnóstico clínico, ni historia médica, ni reemplazo del criterio profesional o pedagógico del especialista.

El alcance V1 es **lectura** de datos ya existentes, con una vista/listado propia para el rol `psicologo_tutor`. No modifica la fórmula de riesgo, no recalcula riesgo, no llama a Flask y no agrega campos médicos o sensibles.

---

## 2. Estado actual

| Elemento | Estado encontrado | Evidencia |
| -------- | ----------------- | --------- |
| DRS v2.1 §RF-11 | Planificado / parcial | [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../../drs/DRS_SIDERAE_Blenkir_v2.md) §RF-11 |
| Matriz RF–Sprint–Test | Implementado parcialmente; solo **Alertas** hoy | [`docs/matriz-rf-sprint-test.md`](../../matriz-rf-sprint-test.md) fila RF-11 |
| `docs/limitaciones.md` | Planificado; hoy solo alertas | [`docs/limitaciones.md`](../../limitaciones.md) §4 y §7 |
| `docs/api.md` | Sin endpoint específico de seguimiento psicólogo/tutor | [`docs/api.md`](../../api.md) |
| `docs/seguridad-roles-permisos.md` | Permiso `ver_perfil_integral_estudiante` sugerido/planificado; **no está en seeder** | [`docs/seguridad-roles-permisos.md`](../../seguridad-roles-permisos.md) §16 |
| `PermissionsSeeder.php` | **Permiso `ver_perfil_psicologo_tutor` implementado RF-11B** | [`backend/database/seeders/PermissionsSeeder.php`](../../../backend/database/seeders/PermissionsSeeder.php) |
| Rol `psicologo_tutor` en seeder | Existe con 7 permisos (alertas + lectura académica + RF-04 + RF-11B) | [`backend/database/seeders/PermissionsSeeder.php`](../../../backend/database/seeders/PermissionsSeeder.php) L114–L121 |
| Backend de riesgo | Implementado V1 (`POST /api/estudiantes/{id}/procesar-riesgo`) | [`backend/routes/api.php`](../../../backend/routes/api.php), [`RiesgoAcademicoService`](../../../backend/app/Services/RiesgoAcademicoService.php) |
| Backend de historial RF-20 | Implementado V1 (`GET /api/estudiantes/{id}/historial-riesgo`) | [`HistorialRiesgoController.php`](../../../backend/app/Http/Controllers/Api/HistorialRiesgoController.php) |
| Backend de semáforo RF-19 | Implementado V1 (`GET /api/estudiantes/{id}/semaforo-completitud`) | [`SemaforoCompletitudController.php`](../../../backend/app/Http/Controllers/Api/SemaforoCompletitudController.php) |
| Backend de reportes conductuales RF-04 | Implementado V1 (`GET/POST /api/estudiantes/{id}/reportes-conductuales`) | [`ReporteConductualController.php`](../../../backend/app/Http/Controllers/Api/ReporteConductualController.php) |
| Backend de alertas/intervenciones | Implementado V1 | [`backend/routes/api.php`](../../../backend/routes/api.php) L142–L151 |
| Backend RF-11C | **Implementado V1** — endpoint + controller + tests | [`PsicologoTutorSeguimientoController.php`](../../../backend/app/Http/Controllers/Api/PsicologoTutorSeguimientoController.php), [`PsicologoTutorSeguimientoTest.php`](../../../backend/tests/Feature/PsicologoTutorSeguimientoTest.php) |
| Frontend de alertas | Implementado V1 (`AlertasPanel.jsx`) | [`frontend/src/components/alertas/AlertasPanel.jsx`](../../../frontend/src/components/alertas/AlertasPanel.jsx) |
| Frontend de perfil estudiante | Existente; acceso restringido por `gestionar_estudiantes` | [`frontend/src/components/estudiantes/EstudiantesPanel.jsx`](../../../frontend/src/components/estudiantes/EstudiantesPanel.jsx) |
| Frontend helpers API | Funciones existentes para historial, semáforo, reportes conductuales, alertas | [`frontend/src/lib/api.js`](../../../frontend/src/lib/api.js) |
| UI específica RF-11 | **Implementada V1** (RF-11D) | [`PerfilPsicologoTutorPanel.jsx`](../../../frontend/src/components/psicologo-tutor/PerfilPsicologoTutorPanel.jsx) |
| Tests RF-11 | **RF-11C backend tests creados** — 20 passed | [`PsicologoTutorSeguimientoTest.php`](../../../backend/tests/Feature/PsicologoTutorSeguimientoTest.php) |

**Resumen:** RF-11B (base RBAC), RF-11C (backend) y RF-11D (frontend) están **implementadas V1**. El permiso `ver_perfil_psicologo_tutor`, el endpoint `GET /api/psicologo-tutor/seguimiento`, el controlador, los tests backend y el componente React existen. Pendiente: smoke manual navegador y cierre documental final (RF-11E).

---

## 3. Alcance V1 propuesto

Vista/listado de seguimiento psicólogo/tutor, **solo lectura**, construida sobre datos existentes.

| Ítem | Alcance V1 |
| ---- | ---------- |
| **Panel propio** | `Seguimiento psicólogo/tutor` accesible desde el menú lateral solo para el permiso RF-11. |
| **Listado de estudiantes** | Estudiantes de la sede operativa V1 (Chilca) que tengan al menos una señal de seguimiento: riesgo alto/medio, reportes conductuales activos o alertas activas. |
| **Filtros** | Año escolar, nivel, grado, sección, nivel de riesgo (bajo/medio/alto), presencia de reportes conductuales activos, presencia de alertas activas. |
| **Detalle resumido por estudiante** | Nombre completo, grado, sección, último nivel de riesgo, último índice, fecha del último riesgo, cantidad de reportes conductuales activos, semáforo de completitud, cantidad de alertas activas. |
| **Acceso a perfil del estudiante** | Enlace al perfil del estudiante (reutilizando `EstudiantesPanel` si el permiso lo permite, o vista propia RF-11 de solo lectura). |
| **Acceso a historial RF-20** | Enlace/consulta al historial de riesgo del estudiante. |
| **Acceso a reportes conductuales RF-04** | Enlace/consulta a reportes conductuales del estudiante. |
| **Registro de intervenciones** | Reutilizar flujo existente de alertas/intervenciones; **no** crear nuevo módulo de intervenciones psicológicas separado en V1. |
| **Sede** | Solo Chilca; sin selector de sede. |
| **Presentación** | Tabla simple con badges de riesgo e indicadores de reportes/alertas; sin gráficos complejos. |
| **Estados UI** | Cargando, vacío, error, 403 amigable. |

### Criterios para aparecer en el listado (V1)

Un estudiante aparece si pertenece a Chilca y cumple **al menos una** de estas condiciones:

- Tiene un índice de riesgo registrado en el año escolar filtrado (cualquier nivel).
- Tiene reportes conductuales activos.
- Tiene alertas activas (`pendiente` o `en_atencion`).

Esto evita mostrar todo el padrón y enfoca la vista en casos de seguimiento.

---

## 4. Permiso sugerido

### Permiso propuesto

```text
ver_perfil_psicologo_tutor
```

### Roles asignados V1 (propuesto)

| Rol | ¿Recibe permiso? | Justificación |
|-----|------------------|---------------|
| `administrador` | Sí | Acceso total a funcionalidades de seguimiento. |
| `psicologo_tutor` | Sí | Actor principal de RF-11. |
| `coordinador_academico` | Sí | Puede apoyar seguimiento institucional; DRS lo menciona como actor de configuración y seguimiento. |
| `docente` | **No** | Su foco es registro académico; ya ve riesgo/historial en el perfil del estudiante según permisos actuales. |
| `directivo` | **No** en V1 tutorial | Puede usar RF-14/RF-16 para vista institucional; RF-11 es seguimiento psicólogo/tutor. Si más adelante se justifica lectura institucional, se evalúa aparte. |

### Notas

- No se implementa el permiso en esta fase (RF-11A).
- La asignación debe hacerse en `PermissionsSeeder.php` como se hizo con `ver_semaforo_completitud` (RF-19) y `ver_historial_riesgo` (RF-20).
- El menú lateral debe ocultar el módulo si el usuario no tiene el permiso (`moduloPermitido` en `App.jsx`).

---

## 5. Impacto backend futuro

### Endpoint propuesto (Fase RF-11C)

```text
GET /api/psicologo-tutor/seguimiento
```

### Middleware

```text
auth:sanctum
permission:ver_perfil_psicologo_tutor
```

### Parámetros de consulta (propuestos)

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `anio_escolar` | string | Filtra por año escolar del estudiante. |
| `nivel` | string | `primaria` / `secundaria` / `inicial`. |
| `grado` | string | Grado (1, 2, 3, …). |
| `seccion` | string | Sección (A, B, …). |
| `nivel_riesgo` | string | `Bajo` / `Medio` / `Alto`. |
| `reportes_activos` | bool | `true` para estudiantes con reportes conductuales activos. |
| `alertas_activas` | bool | `true` para estudiantes con alertas activas. |
| `page` | int | Paginación (patrón `EstudianteController`). |
| `per_page` | int | Máximo 100, por defecto 25. |

### Comportamiento del backend (propuesto)

1. Fijar sede `chilca` usando `SedeOperativa::defaultConsulta()` o filtro directo (`estudiantes.sede = 'chilca'`).
2. Consultar estudiantes activos.
3. Unir/left-join con:
   - `indices_riesgo` (último registro por estudiante y año escolar).
   - `reportes_conductuales` (activos).
   - `alertas` (activas: `pendiente` o `en_atencion`).
4. Aplicar filtros recibidos.
5. No recalcular riesgo.
6. No llamar a Flask.
7. No usar VSE ni Fast Test.
8. Devolver paginación simple, compatible con el patrón de `EstudianteController`.

### Respuesta JSON orientativa

```json
{
  "data": [
    {
      "estudiante_id": 1,
      "estudiante": "Pérez García, Juan",
      "grado": "5",
      "seccion": "A",
      "nivel": "primaria",
      "anio_escolar": "2026",
      "ultimo_indice": 0.82,
      "ultimo_nivel": "Alto",
      "fecha_ultimo_riesgo": "2026-06-23",
      "reportes_conductuales_activos": 2,
      "alertas_activas": 1,
      "semaforo_completitud": "amarillo"
    }
  ],
  "current_page": 1,
  "per_page": 25,
  "total": 1,
  "last_page": 1
}
```

### Servicio/controller propuesto

- `PsicologoTutorSeguimientoController` en `backend/app/Http/Controllers/Api/`.
- Opcionalmente un `PsicologoTutorSeguimientoService` si la consulta crece en complejidad; en V1 puede bastar un controller con consulta clara.
- Reutilizar `SedeOperativa::defaultConsulta()` para mantener consistencia con RF-14/RF-16/RF-19/RF-20.

### Relación con endpoints existentes

| Funcionalidad | Endpoint existente | Uso desde RF-11 |
|---------------|--------------------|-----------------|
| Perfil/datos del estudiante | `GET /api/estudiantes/{id}` | Navegación al perfil. |
| Historial de riesgo | `GET /api/estudiantes/{id}/historial-riesgo` | Vista detalle/historial. |
| Semáforo de completitud | `GET /api/estudiantes/{id}/semaforo-completitud` | Indicador en listado (llamada por fila o precargado). |
| Reportes conductuales | `GET /api/estudiantes/{id}/reportes-conductuales` | Vista detalle de conducta. |
| Alertas/intervenciones | `GET /api/alertas`, `POST /api/alertas/{id}/intervenciones` | Acciones de seguimiento. |

---

## 6. Impacto frontend futuro

### Componente/vista propuesto (Fase RF-11D)

```text
frontend/src/components/psicologo-tutor/PerfilPsicologoTutorPanel.jsx
```

### Comportamiento UI (propuesto)

- **Título:** "Seguimiento psicólogo/tutor".
- **Mensaje de alcance:** breve aclaración de que la vista es de apoyo institucional, **no diagnóstico clínico**.
- **Filtros:** año escolar, nivel, grado, sección, nivel de riesgo, checkbox "solo con reportes activos", checkbox "solo con alertas activas".
- **Tabla/listado:** columnas: estudiante, grado/sección, riesgo (badge), fecha riesgo, reportes activos, alertas activas, semáforo, acciones.
- **Acciones por fila:**
  - "Ver perfil" → abre `EstudiantesPanel` en modo lectura o vista propia RF-11.
  - "Ver historial" → abre/consulta RF-20.
  - "Ver reportes" → abre/consulta RF-04.
  - "Ver alertas" → filtra `AlertasPanel` por estudiante (si se decide agregar filtro cruzado).
- **Estados:**
  - Cargando (`LoadingState`).
  - Vacío ("No hay estudiantes con señales de seguimiento para los filtros seleccionados").
  - Error (mensaje amigable + reintentar).
  - 403 ("No tienes permiso para ver este módulo").

### Menú lateral

Agregar en `App.jsx`:

```text
key: 'psicologo_tutor_seguimiento',
label: 'Seguimiento psicólogo/tutor',
visible: moduloPermitido('psicologo_tutor_seguimiento', permissions, roles),
```

Y en `moduloPermitido`:

```javascript
case 'psicologo_tutor_seguimiento':
  return permissions.includes('ver_perfil_psicologo_tutor');
```

### Helper API

Agregar en `frontend/src/lib/api.js`:

```javascript
export function getSeguimientoPsicologoTutor(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `/api/psicologo-tutor/seguimiento?${qs}` : '/api/psicologo-tutor/seguimiento');
}
```

### Restricciones UI V1

- Sin gráficos complejos.
- Sin PDF/exportación.
- Sin selector de sede.
- Sin formularios de diagnóstico clínico.
- Sin campos médicos sensibles.

---

## 7. Relación con módulos existentes

| RF | Función en RF-11 |
|----|------------------|
| **RF-06 Riesgo académico** | Provee el último índice y nivel del estudiante. RF-11 no recalcula; solo lee de `indices_riesgo`. |
| **RF-19 Semáforo de completitud** | Indicador visual en el listado; ayuda al psicólogo/tutor a interpretar si el estudiante tiene datos suficientes. |
| **RF-20 Historial de riesgo** | Permite ver la evolución del estudiante seleccionado. |
| **RF-04 Reportes conductuales** | Fuente del conteo de reportes activos y del detalle conductual. |
| **RF-08/09 Alertas e intervenciones** | El psicólogo/tutor ya puede atender alertas; RF-11 le da una vista de casos priorizados para derivar a intervenciones. |
| **RF-10 Escalamiento directivo** | Fuera de V1; RF-11 no implementa derivación, pero puede recibir casos que en el futuro escalen por RF-10. |
| **RF-14 Dashboard institucional** | Vista institucional separada; RF-11 es una vista operativa de seguimiento tutorial. |
| **RF-16 Reportes de riesgo** | Listado institucional de riesgo; RF-11 es operativo/tutorial y no lo sustituye. |

---

## 8. Pruebas futuras

### Backend (Fase RF-11E)

| Caso | Descripción |
|------|-------------|
| 401 sin sesión | `GET /api/psicologo-tutor/seguimiento` sin autenticación devuelve 401. |
| 403 sin permiso | Usuario autenticado sin `ver_perfil_psicologo_tutor` recibe 403. |
| Acceso con permiso | Rol `psicologo_tutor` consulta el listado. |
| Docente sin acceso | Docente autenticado no puede consultar (403). |
| Filtro por año escolar | Solo estudiantes del año solicitado. |
| Filtro por grado/sección | Restringe resultados correctamente. |
| Filtro por nivel de riesgo | Solo estudiantes con último riesgo del nivel indicado. |
| Filtro por reportes activos | Solo estudiantes con reportes conductuales activos. |
| Filtro por alertas activas | Solo estudiantes con alertas activas. |
| Sede Chilca | Estudiantes Auquimarca no aparecen. |
| No recalcula riesgo | Verificar que no se llama a `RiesgoAcademicoService` ni a Flask. |
| No usa VSE ni Fast Test | Confirmar que la consulta no depende de variables socioeconómicas ni Fast Test. |
| Paginación | Respuesta paginada con `data`, `current_page`, `per_page`, `total`, `last_page`. |

### Frontend (Fase RF-11E)

| Caso | Descripción |
|------|-------------|
| Build OK | `npm run build` sin errores nuevos. |
| Menú visible solo con permiso | `psicologo_tutor` y `administrador` ven el ítem; docente/directivo no (según decisión V1). |
| Tabla renderiza | Listado muestra estudiantes con señales de seguimiento. |
| Filtros funcionan | Año, nivel, grado, sección, nivel riesgo, reportes activos, alertas activas. |
| Estado vacío | Mensaje amigable cuando no hay casos. |
| Estado error | Mensaje de error con opción de reintentar. |
| Estado 403 | Mensaje amigable si se accede sin permiso. |
| Sin selector de sede | Todos los filtros/payloads usan Chilca. |
| Sin PDF/exportar | No hay botones de exportación. |
| Enlace a perfil/historial/reportes | Navega correctamente a vistas existentes. |

---

## 9. Fuera del alcance

Queda explícitamente excluido de RF-11 V1:

- Diagnóstico psicológico clínico.
- Historia clínica o información médica sensible.
- Fichas psicológicas detalladas (el DRS/procesos institucionales las mencionan, pero no se implementan en el software V1).
- PDF/exportación.
- Dashboard complejo con gráficos.
- ML real / reentrenamiento (RF-18).
- Escalamiento directivo (RF-10).
- Comunicación con familias (RF-12 — eliminada del alcance).
- Variables socioeconómicas como insumo.
- Fast Test.
- Recálculo de riesgo.
- Llamadas a Flask.
- Multi-sede / selector de sede.
- Cypress / E2E en esta fase.
- Certificación ISO.

---

## 10. Fases futuras

| Fase | Objetivo | Entregables |
| ---- | -------- | ----------- |
| **RF-11B** | ✅ **Completada V1** — Permiso y base RBAC | `ver_perfil_psicologo_tutor` agregado en `PermissionsSeeder.php`; asignado a `administrador`, `coordinador_academico` y `psicologo_tutor`; documentación actualizada. |
| **RF-11C** | ✅ **Completada V1** — Backend seguimiento psicólogo/tutor | `PsicologoTutorSeguimientoController`, ruta `GET /api/psicologo-tutor/seguimiento`, `PsicologoTutorSeguimientoTest` 20 passed. |
| **RF-11D** | ✅ **Completada V1** — Frontend seguimiento psicólogo/tutor | `PerfilPsicologoTutorPanel.jsx`, helper `getSeguimientoPsicologoTutor()` en `api.js`, entrada en `App.jsx`, icono en `navIcons.jsx`, build OK, lint sin errores nuevos. |
| **RF-11E** | ✅ **Cierre documental completado V1** — Smoke manual pendiente | Tests backend 20 passed; regresión RF-06/RF-14/RF-16/RF-19/RF-20 OK; build/lint OK; ruta y middleware verificados; manuales, API, matriz, limitaciones, informe de pruebas, seguridad y no-conformidades actualizados. Smoke manual navegador pendiente por falta de navegador en el entorno. |

---

## 11. Conclusión

RF-11 está **listo para implementación controlada** en cuatro fases (B–E). La base de datos, los modelos (`Estudiante`, `IndiceRiesgo`, `ReporteConductual`, `Alerta`, `Intervencion`), los endpoints relacionados (RF-06, RF-19, RF-20, RF-04, RF-08/09) y el frontend de alertas ya existen, por lo que el esfuerzo de RF-11 V1 se reduce a:

1. Un nuevo permiso RBAC.
2. Un nuevo endpoint de agregación/listado.
3. Un nuevo componente React de tabla/filtros.
4. Tests y cierre documental.

No requiere modificaciones a Flask, fórmula de riesgo, migraciones, VSE, Fast Test ni datos médicos sensibles.

**Próxima fase recomendada:** **Smoke manual RF-11 en navegador** cuando haya un entorno con navegador disponible; luego commit de cierre RF-11 V1.
