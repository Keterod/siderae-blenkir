# Plan AI-DLC — RF-19 Semáforo de completitud de datos

**Fase:** 3A–3C — Planificación + RBAC + backend implementados · **3D–3E — Frontend y cierre pendientes**  
**Fecha:** 2026-06-23  
**Metodología:** AI-DLC · perfiles en [`docs/metodologia/agentes-ai-dlc-siderae.md`](../agentes-ai-dlc-siderae.md)  
**Guía operativa:** [`docs/metodologia/ai-dlc-aplicado-siderae.md`](../ai-dlc-aplicado-siderae.md)

---

## 1. Propósito

Planificar **RF-19** (*Semáforo de completitud de datos*) antes de escribir código, siguiendo AI-DLC y validación humana obligatoria.

RF-19 **no calcula riesgo**. Solo indica si los datos disponibles son **suficientes**, **parciales** o **insuficientes** para interpretar el riesgo académico.

Este documento propone un **V1 mínimo por estudiante** y define las fases 3B–3E futuras.

---

## 2. Requerimiento

Resumen según DRS v2.1 §RF-19:

| Aspecto | Contenido |
|---------|-----------|
| **Qué es** | Semáforo que indica la completitud de datos para interpretar riesgo académico. |
| **Estados** | **Verde** (suficiente), **amarillo** (parcial; interpretable con advertencia), **rojo** (insuficiente). |
| **Insumos DRS** | Notas, asistencia, reportes conductuales, historial de riesgo. |
| **Actores** | Docente, administrador, coordinador académico. |
| **Relación RF-06** | Apoya la interpretación; no reemplaza el cálculo de riesgo. |
| **Relación RF-04** | Los reportes conductuales activos son un insumo de completitud. |
| **Relación RF-20** | Futura: el historial evolutivo podrá mostrar cambios de completitud por periodo. |
| **Relación RF-14** | Futura: el dashboard podrá incluir indicadores de completitud cuando RF-19 esté operativo. |

---

## 3. Estado actual

| Elemento | Estado encontrado | Evidencia |
| -------- | ----------------- | --------- |
| DRS v2.1 §RF-19 | Planificado | [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../../drs/DRS_SIDERAE_Blenkir_v2.md) |
| Matriz RF–Sprint–Test | Planificado; sin código ni prueba | [`docs/matriz-rf-sprint-test.md`](../../matriz-rf-sprint-test.md) fila RF-19 |
| `docs/limitaciones.md` | Planificado; sin UI/lógica | [`docs/limitaciones.md`](../../limitaciones.md) §5 |
| `docs/api.md` | Planificado; sin rutas | [`docs/api.md`](../../api.md) §12 |
| `docs/seguridad-roles-permisos.md` | `ver_semaforo_completitud` sugerido/planificado | [`docs/seguridad-roles-permisos.md`](../../seguridad-roles-permisos.md) §16 |
| `PermissionsSeeder.php` | `ver_semaforo_completitud` **implementado Fase 3B** | [`backend/database/seeders/PermissionsSeeder.php`](../../../backend/database/seeders/PermissionsSeeder.php) |
| Endpoint RF-19 | **Implementado Fase 3C** | [`backend/routes/api.php`](../../../backend/routes/api.php) |
| `CompletitudDatosService` | **Implementado Fase 3C** | [`backend/app/Services/CompletitudDatosService.php`](../../../backend/app/Services/CompletitudDatosService.php) |
| `SemaforoCompletitudController` | **Implementado Fase 3C** | [`backend/app/Http/Controllers/Api/SemaforoCompletitudController.php`](../../../backend/app/Http/Controllers/Api/SemaforoCompletitudController.php) |
| UI RF-19 | **Implementada Fase 3D** | `frontend/src/components/estudiantes/EstudiantePerfilSemaforoCompletitud.jsx` |
| Tests RF-19 | **Implementados Fase 3C — 11 passed, 55 assertions** | [`backend/tests/Feature/SemaforoCompletitudTest.php`](../../../backend/tests/Feature/SemaforoCompletitudTest.php) |
| Servicio relacionado | `RiesgoAcademicoService::validarDatosMinimos()` existe, pero valida VSE (inconsistente con v2.1) | [`backend/app/Services/RiesgoAcademicoService.php`](../../../backend/app/Services/RiesgoAcademicoService.php) |

**Nota:** RF-19 no corregirá `RiesgoAcademicoService::validarDatosMinimos()`. Esa brecha corresponde a RF-06 y queda documentada aquí como contexto.

---

## 4. Alcance exacto propuesto

V1 mínimo por estudiante:

| Ítem | Alcance |
| ---- | ------- |
| **Semáforo por estudiante** | Un indicador por estudiante, para un año escolar y bimestre/periodo de referencia. |
| **Colores** | Verde / amarillo / rojo. |
| **Motivo** | La respuesta incluye qué datos están presentes y cuáles faltan. |
| **Mensaje** | Texto corto y legible para docente/coordinador. |
| **UI** | Visible en el perfil del estudiante, cerca del bloque de riesgo académico. |
| **Insumos V1** | Notas curriculares, asistencia curricular, reportes conductuales activos (RF-04), índice de riesgo existente como dato complementario. |
| **Intervenciones** | **No** son insumo obligatorio de RF-19 V1. |
| **Sede** | Solo Chilca; sin selector de sede. |
| **Bloqueo** | El semáforo es informativo; no bloquea el perfil ni el procesamiento de riesgo. |

### Criterios implementados (V1)

| Color | Criterio |
| ----- | -------- |
| **Verde** | Existen notas curriculares **y** asistencia curricular para el periodo. |
| **Amarillo** | Falta notas **o** asistencia, pero existe al menos uno de los siguientes: notas, asistencia, reportes conductuales activos o índice de riesgo del periodo. |
| **Rojo** | No hay notas, asistencia, reportes conductuales ni índice de riesgo. |

> **Nota de diseño:** el índice de riesgo se considera un dato complementario; nunca determina `verde` por sí solo.

---

## 5. Fuera del alcance

Queda explícitamente excluido de RF-19 V1:

* ML real / reentrenamiento (RF-18).
* Modificación de Flask.
* Cambio de la fórmula determinística de riesgo.
* Recálculo automático de riesgo.
* Corrección de `RiesgoAcademicoService::validarDatosMinimos()`.
* Intervenciones como insumo obligatorio.
* Dashboard nuevo (RF-14).
* Historial evolutivo (RF-20) — solo relación futura documentada.
* Escalamiento directivo (RF-10).
* Perfil integral psicólogo/tutor (RF-11).
* Reportes PDF / zona RF-16.
* SIAGIE, Fast Test, comunicación familiar.
* Variables socioeconómicas como insumo obligatorio.
* Multi-sede / selector de sede.
* Cypress / E2E en esta fase.
* Certificación ISO.

---

## 6. Aplicación de agentes AI-DLC

| Perfil | Qué revisa para RF-19 | Entregable |
| ------ | --------------------- | ---------- |
| **Analista RF** | DRS §RF-19, matriz, limitaciones, NC-19 | Plan §2–5, criterios §14 |
| **Arquitecto Técnico** | Servicio simple, endpoint, sede Chilca, sin Flask | §7–8, riesgos §13 |
| **Backend Laravel** | `CompletitudDatosService`, endpoint, tests | Especificación §7 (Fase 3C) |
| **Frontend React** | Bloque de semáforo en perfil estudiante | Especificación §8 (Fase 3D) |
| **Seguridad/RBAC** | Permiso `ver_semaforo_completitud`; middleware; roles | §9 (Fase 3B) |
| **QA/Test** | 401/403, verde/amarillo/rojo, Chilca, sin Flask | §11 (Fase 3E) |
| **Documentación** | api.md, matriz, manuales, seguridad, NC | §12 (Fase 3E) |
| **ML/MLOps** | Que RF-19 no se confunda con ML real | Nota §10 |

---

## 7. Impacto backend propuesto

| Elemento backend | Estado V1 | Evidencia |
| ---------------- | ---------- | ------------------------- |
| `CompletitudDatosService` | **Implementado** | [`backend/app/Services/CompletitudDatosService.php`](../../../backend/app/Services/CompletitudDatosService.php) |
| Endpoint | **Implementado** | `GET /api/estudiantes/{estudiante}/semaforo-completitud` en [`backend/routes/api.php`](../../../backend/routes/api.php) |
| Controller | **Implementado** | [`backend/app/Http/Controllers/Api/SemaforoCompletitudController.php`](../../../backend/app/Http/Controllers/Api/SemaforoCompletitudController.php) |
| Datos consultados | **Implementado** | `NotaSemanal`, `EvalBimResultado`, `AsistenciaDiaria`, `ReporteConductual`, `IndiceRiesgo` |
| Sede | **Implementado** | Solo Chilca; Auquimarca responde 403 |
| Tests Feature | **Implementados** | [`backend/tests/Feature/SemaforoCompletitudTest.php`](../../../backend/tests/Feature/SemaforoCompletitudTest.php) — 11 passed, 55 assertions |

### Respuesta JSON orientativa

```json
{
  "estudiante_id": 123,
  "anio_escolar": "2026",
  "bimestre": "II",
  "color": "amarillo",
  "etiqueta": "Datos parciales",
  "mensaje": "Hay notas curriculares, pero falta asistencia del periodo.",
  "razones": [
    { "dato": "notas_curriculares", "presente": true },
    { "dato": "asistencia_curricular", "presente": false },
    { "dato": "reportes_conductuales", "presente": false },
    { "dato": "indice_riesgo", "presente": false }
  ]
}
```

---

## 8. Impacto frontend propuesto

| Elemento frontend | Propuesta | Justificación |
| ----------------- | --------- | ------------- |
| **Ubicación** | Nuevo componente pequeño en perfil estudiante, junto al bloque de riesgo. | Contexto inmediato para interpretar riesgo. |
| **Visual** | Etiqueta de color verde/amarillo/rojo con icono. | Convención semáforo; no confundir con `Badge` de gravedad. |
| **Mensaje** | Texto breve explicativo debajo del color. | Claridad para el usuario. |
| **Detalle** | Lista corta de insumos presentes/ausentes. | Transparencia. |
| **Estados** | Carga, error aislado, sin permiso. | No romper el perfil. |
| **Permisos** | Visible solo con `ver_semaforo_completitud`. | RBAC backend primero, UI después. |
| **Sede** | Sin selector; Chilca por defecto. | V1 Chilca. |
| **API client** | Nueva función en `frontend/src/lib/api.js`. | Patrón existente. |

---

## 9. Impacto permisos/RBAC

| Permiso | Existe en seeder | Roles sugeridos V1 | Uso |
| ------- | ---------------- | ------------------ | --- |
| `ver_semaforo_completitud` | **Sí** (Fase 3B) | `administrador`, `docente`, `coordinador_academico` | Consultar semáforo por estudiante |

**Middleware futuro:** `auth:sanctum` + `permission:ver_semaforo_completitud`.

Seeder actualizado en Fase 3B. `psicologo_tutor` y `directivo` **no** reciben el permiso en V1; se evaluarán con RF-11/RF-10/RF-14.

---

## 10. Impacto ML/riesgo

* RF-19 ayuda a interpretar el riesgo, pero **no reemplaza RF-06**.
* **No modifica Flask**.
* **No modifica la fórmula determinística**.
* **No implementa reentrenamiento**.
* **No recalcula riesgo automáticamente**.
* Puede advertir si el último índice de riesgo está desactualizado o se basó en datos incompletos.
* Relación con RF-20: en el futuro el historial evolutivo podrá mostrar la completitud por periodo.

---

## 11. Pruebas necesarias

### Backend

| Tipo | Prueba | Objetivo | Prioridad |
| ---- | ------ | -------- | --------- |
| Backend | 401 sin sesión | Sanctum | Alta |
| Backend | 403 sin permiso | Spatie | Alta |
| Backend | Verde con notas + asistencia | Criterio verde | Alta |
| Backend | Amarillo con un dato faltante | Criterio amarillo | Alta |
| Backend | Rojo sin datos | Criterio rojo | Alta |
| Backend | Estudiante fuera de Chilca filtrado/rechazado | V1 Chilca | Media |
| Backend | Consulta no modifica `indices_riesgo` | Sin mutación | Alta |
| Backend | Consulta no llama Flask | Sin Flask | Alta |
| Backend | Respuesta incluye razones | Explicabilidad | Alta |

### Frontend / manual

| Tipo | Prueba | Objetivo | Prioridad |
| ---- | ------ | -------- | --------- |
| Manual | Visualización verde/amarillo/rojo | UX | Alta |
| Manual | Mensaje entendible | Claridad | Alta |
| Manual | Sin permiso no se ve el bloque | RBAC UI | Alta |
| Manual | Error API no rompe perfil | Robustez | Alta |
| Manual | No aparece selector de sede | V1 Chilca | Alta |
| Manual | No se confunde con nivel de riesgo | Usabilidad | Media |

---

## 12. Documentación a actualizar en fases futuras

| Documento | Cuándo actualizar | Motivo |
| --------- | ----------------- | ------ |
| [`docs/api.md`](../../api.md) | Fase 3C ✅ | Endpoint documentado |
| [`docs/manual-usuario.md`](../../manual-usuario.md) | Fase 3D ✅ | Flujo por rol y sección perfil |
| [`docs/manual-tecnico.md`](../../manual-tecnico.md) | Fase 3D ✅ | Servicio, UI y build |
| [`docs/seguridad-roles-permisos.md`](../../seguridad-roles-permisos.md) | Fase 3B ✅ | Permiso implementado |
| [`docs/matriz-rf-sprint-test.md`](../../matriz-rf-sprint-test.md) | Fase 3D ✅ | Estado RF-19 actualizado |
| [`docs/limitaciones.md`](../../limitaciones.md) | Fase 3D ✅ | Backend y frontend RF-19 documentados |
| [`docs/calidad/no-conformidades-y-mejora.md`](../../calidad/no-conformidades-y-mejora.md) | Fase 3E | Cerrar/matizar NC-19 |
| [`docs/pruebas/informe-pruebas.md`](../../pruebas/informe-pruebas.md) | Fase 3D ✅ | Resultados tests + build frontend |
| Este plan | Fase 3D ✅ | Fases 3B–3D marcadas completadas |

---

## 13. Riesgos

* Confundir semáforo de completitud con nivel de riesgo.
* Hacer que el semáforo bloquee el procesamiento de riesgo.
* Modificar Flask o `RiesgoAcademicoService` innecesariamente.
* Reintroducir variables socioeconómicas retiradas.
* Incluir intervenciones como insumo obligatorio en V1.
* Mezclar Auquimarca/multi-sede.
* Dar permiso a roles incorrectos.
* Mostrar el semáforo sin explicar por qué está amarillo o rojo.
* Afirmar ML real o certificación ISO.
* Romper el perfil de estudiante si falla el endpoint.

---

## 14. Criterios de aceptación para futura implementación

1. Permiso `ver_semaforo_completitud` creado en seeder y asignado a roles §9.
2. Endpoint protegido por Sanctum + Spatie.
3. Cálculo determinístico y explicable; respuesta incluye color, mensaje y razones.
4. Estados verde/amarillo/rojo devueltos según datos reales.
5. UI visible solo con permiso; no bloquea el perfil si falla.
6. No se modifica Flask.
7. No se modifica `RiesgoAcademicoService`.
8. No se recalcula riesgo automáticamente.
9. Tests backend pasan; build frontend pasa.
10. Documentación actualizada.
11. No se afirma ML real ni ISO certificado.

---

## 15. División sugerida en fases futuras

| Fase | Contenido | Entregables | Estado |
| ---- | --------- | ----------- | ------ |
| **Fase 3B** | Permisos y base RBAC RF-19 | `ver_semaforo_completitud` en seeder; asignación de roles; `seguridad-roles-permisos.md` actualizado | **Completada** (2026-06-23) |
| **Fase 3C** | Backend semáforo RF-19 | `CompletitudDatosService`; endpoint; `SemaforoCompletitudController`; tests iniciales | **Completada** (2026-06-23) |
| **Fase 3D** | Frontend semáforo en perfil estudiante | `EstudiantePerfilSemaforoCompletitud.jsx`; `getSemaforoCompletitud()` en `api.js`; visibilidad por permiso; build frontend OK | **Completada** (2026-06-23) |
| **Fase 3E** | Pruebas finales, smoke manual y cierre RF-19 | Smoke manual UI; docs actualizados; NC-19 matizada; posible Cypress/Playwright | Pendiente |

Orden: 3B → 3C → 3D → 3E.

---

## 16. Prompt futuro recomendado

Borrador para **Fase 3C** (no ejecutar en 3B):

```text
Contexto: SIDERAE-Blenkir V1 Chilca. RF-19 base RBAC lista.
Plan: docs/metodologia/planes-ai-dlc/plan-rf-19-semaforo-completitud.md

Tarea Fase 3C únicamente:
1. Crear CompletitudDatosService con método evaluar(Estudiante, anio, bimestre)
2. Crear endpoint GET /api/estudiantes/{estudiante}/semaforo-completitud
3. Proteger con auth:sanctum + permission:ver_semaforo_completitud
4. Criterios: notas curriculares, asistencia curricular, reportes conductuales activos, índice riesgo complementario
5. No tocar RiesgoAcademicoService ni Flask
6. No recalcular riesgo automáticamente
7. Tests Feature iniciales: 401, 403, verde/amarillo/rojo, Chilca
8. Revisión humana del diff antes de cerrar

Fuentes: DRS v2.1 RF-19, AGENTS.md, .cursorrules
```

---

## 17. Conclusión

RF-19 tiene **backend y frontend V1 implementados** (Fases 3B–3D). El permiso `ver_semaforo_completitud` está en el seeder asignado a `administrador`, `docente` y `coordinador_academico`; el servicio `CompletitudDatosService`, el endpoint `GET /api/estudiantes/{estudiante}/semaforo-completitud`, los tests `SemaforoCompletitudTest` (11 passed, 55 assertions) y el componente `EstudiantePerfilSemaforoCompletitud.jsx` están en el repositorio. El build frontend fue exitoso. El cierre documental final, smoke manual UI y posibles pruebas E2E corresponden a la **Fase 3E**.

**Brecha técnica documentada:** `RiesgoAcademicoService::validarDatosMinimos()` sigue exigiendo variables socioeconómicas, lo cual es inconsistente con DRS v2.1. Esta corrección no forma parte de RF-19 y deberá tratarse como corrección aprobada de RF-06 si el equipo lo decide.

**Próxima fase recomendada:** **Fase 3E — pruebas, documentación y cierre RF-19**.

---

*Plan AI-DLC — Fases 3A–3D completadas; 3E pendiente — 2026-06-23.*
