# Plan AI-DLC — RF-04 Reportes conductuales

**Fase:** 2A — Planificación previa a implementación (sin código)  
**Fecha:** 2026-06-10  
**Metodología:** AI-DLC · perfiles en [`agentes-ai-dlc-siderae.md`](../agentes-ai-dlc-siderae.md)  
**Guía operativa:** [`ai-dlc-aplicado-siderae.md`](../ai-dlc-aplicado-siderae.md)

---

## 1. Propósito

Este documento **planifica RF-04** (*Registro digital de reportes conductuales*) **antes** de modificar código, siguiendo **AI-DLC** y los **agentes metodológicos** del proyecto SIDERAE-Blenkir.

Objetivos del plan:

- Fijar alcance honesto V1 a partir del **DRS v2.1** y evidencia en repositorio.
- Definir impactos futuros en backend, frontend, permisos, pruebas y documentación.
- Proponer **fases 2B–2E** para implementación controlada con **validación humana obligatoria**.

**No implementa RF-04.** No crea rutas, controllers, UI, permisos en seeder ni modifica Flask.

---

## 2. Requerimiento

Resumen según [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../../drs/DRS_SIDERAE_Blenkir_v2.md) §RF-04:

| Aspecto | Contenido DRS v2.1 |
| ------- | ------------------ |
| **Qué registra** | Reportes conductuales **por estudiante**, con datos sugeridos: estudiante, **fecha**, **tipo**, **gravedad**, **descripción**, **usuario registrador**, **estado**. |
| **Para qué sirve** | Parte del **flujo de riesgo académico**; insumo para cálculo/interpretación de riesgo (junto con notas, asistencia e insumos institucionales disponibles). |
| **Actores** | **Docente**, **psicólogo/tutor**, **coordinador académico** (registro/consulta según permisos); **directivo** con **lectura en casos críticos**. |
| **Relación con riesgo** | Debe relacionarse con **riesgo**, **alertas** y **seguimiento**; integración futura con **RF-06** (procesamiento multivariable) y **RF-19** (semáforo de completitud). |
| **Relación alertas / tutoría** | **RF-10** menciona escalamiento por **reporte conductual grave**; **RF-11** planifica perfil integral con lectura de reportes conductuales; no sustituye **RF-12** (comunicación familiar — **eliminada**). |
| **Estado V1 documentado** | **Planificado / se implementará** — existe **migración y modelo** `reportes_conductuales`; **sin rutas API** ni **UI** en V1 actual. |
| **Prioridad** | Alta |
| **Brechas declaradas en DRS** | API, UI, tests conductuales; integración con RF-06 y RF-19 |

---

## 3. Estado actual

Verificación en documentación y código (solo lectura, 2026-06-10):

| Elemento | Estado encontrado | Evidencia |
| -------- | ----------------- | --------- |
| **Matriz RF–Sprint–Test** | **Planificado** — migración sin API; sin UI; sin prueba automatizada | [`docs/matriz-rf-sprint-test.md`](../../matriz-rf-sprint-test.md) fila RF-04; §5.3 tests planificados |
| **Limitaciones** | **Planificado** — migración sin API | [`docs/limitaciones.md`](../../limitaciones.md) §4 |
| **API catálogo** | **Parcial** — §9 `api.md` (Fase 2C) | [`docs/api.md`](../../api.md) |
| **Seguridad / permisos** | Implementados seeder + rutas API | [`docs/seguridad-roles-permisos.md`](../../seguridad-roles-permisos.md) |
| **Migración BD** | **Existe** + columna `estado` (2026_06_10) | Migraciones `reportes_conductuales` |
| **Modelo Eloquent** | **Existe** — scopes, relaciones | [`ReporteConductual.php`](../../../backend/app/Models/ReporteConductual.php) |
| **Relación Estudiante** | **Existe** | [`Estudiante.php`](../../../backend/app/Models/Estudiante.php) |
| **Rutas API** | **Existen** (3 endpoints) | [`api.php`](../../../backend/routes/api.php) |
| **Controller** | **Existe** | `ReporteConductualController.php` |
| **UI React** | **Existe** — perfil estudiante | `EstudiantePerfilReportesConductuales.jsx` (Fase 2D) |
| **Tests PHPUnit** | **Existen** | `ReporteConductualTest.php` (8 casos) |
| **Uso en riesgo** | Conteo **solo activos** | `RiesgoAcademicoService` (ajuste mínimo Fase 2C) |
| **Flask / ML** | **Sin modificar** | Contrato determinístico vigente |
| **NC registradas** | RF-04 en backlog abierto | [`docs/calidad/no-conformidades-y-mejora.md`](../../calidad/no-conformidades-y-mejora.md) NC-13, NC-16 |

### Brechas esquema vs DRS

| Campo DRS | Columna migración actual | Observación |
| --------- | ------------------------ | ----------- |
| estado | `estado` enum `activo\|anulado` | **Implementado** Fase 2C |
| tipo | `tipo_conducta` | Alineado (nombre distinto) |
| gravedad | `nivel_gravedad` enum `leve\|moderado\|grave` | Alineado |
| acción | `accion_inmediata` nullable | Extra respecto al listado mínimo DRS; conservar |
| usuario registrador | `registrado_por` FK `users` | Relación `registradoPor()` implementada |

**Conclusión de estado (post Fase 2E):** RF-04 **Implementado V1 mínimo** — backend + frontend perfil + tests; smoke UI navegador pendiente; brechas documentadas.

---

## 4. Alcance exacto propuesto

Implementación futura **V1 Chilca** (sujeta a aprobación humana antes de Fase 2C):

| Ítem | Alcance propuesto |
| ---- | ----------------- |
| **Registrar** | Crear reporte conductual vinculado a `estudiante_id` con: fecha, tipo_conducta, nivel_gravedad, descripcion, accion_inmediata (opcional), registrado_por (usuario autenticado). |
| **Consultar por estudiante** | Listar reportes del estudiante en perfil (orden descendente por fecha). |
| **Consultar agregado aula/grado** | **Opcional V1 mínima:** índice filtrable por `grado`/`seccion` vía join con `estudiantes`, restringido a sede **Chilca** (`SedeOperativa` / `estudiantes.sede`). Prioridad **media** — puede diferirse post-MVP perfil si el cronograma apremia. |
| **Editar** | **Fuera del MVP inicial** salvo corrección de typo en ventana corta; DRS no exige edición explícita. |
| **Anular lógicamente** | **Sí** — campo `estado` (`activo` / `anulado`) o equivalente; **sin** borrado físico (`DELETE` HTTP no expuesto). |
| **Trazabilidad** | Registrar `registrado_por`, timestamps; valorar entrada en `activity_log` alineada a RF-17 parcial (patrón existente en otros módulos). |
| **Sede** | Solo estudiantes **Chilca** en operación V1; rechazar o filtrar Auquimarca en consultas operativas. |
| **Permisos** | Lectura (`ver_reportes_conductuales`) y registro (`registrar_reportes_conductuales`) **separados**. |
| **Integración riesgo** | Tras registrar/anular, **no** recalcular riesgo automáticamente en V1 salvo decisión explícita; el conteo ya alimenta `RiesgoAcademicoService` en próximo `procesar-riesgo`. |
| **Alertas** | **No** crear alertas automáticas por reporte en Fase RF-04 (RF-10 planificado). |

---

## 5. Fuera del alcance

Queda **explícitamente excluido** de la implementación RF-04 (fases 2B–2E):

- **SIAGIE** — fuera del alcance actual.
- **Fast Test (RF-03)** — retirado.
- **Comunicación familiar (RF-12)** — eliminada; no confundir con reportes conductuales.
- **Variables socioeconómicas en flujo de riesgo (RF-05)** — retiradas como insumo obligatorio.
- **ML real / ensemble entrenado** — no implementar; mantener fórmula **determinística** en Flask.
- **RF-18 reentrenamiento** — planificado; no tocar en RF-04.
- **Multi-sede V1** — sin selector Auquimarca; sede operativa Chilca ([`AGENTS.md`](../../../AGENTS.md)).
- **Dashboard nuevo (RF-14)** — no ampliar dashboard en esta fase.
- **Reportes PDF / zona RF-16** — no export PDF de conductuales en esta fase.
- **Modificación de Flask** — contrato actual ya acepta conteo `reportes_conductuales`.
- **Modificación de `RiesgoAcademicoService`** — salvo bug crítico aprobado; el conteo existente se mantiene.
- **Borrado físico** de reportes — prohibido; solo anulación lógica.
- **Cypress / E2E** — ausente en repo; no planificar como existente.
- **Certificación ISO** — solo alineación progresiva documental.

---

## 6. Aplicación de agentes AI-DLC

| Perfil | Qué revisa para RF-04 | Entregable |
| ------ | --------------------- | ---------- |
| **Analista RF** | DRS §RF-04, matriz, limitaciones, NC-13/NC-16 | Este plan §2–5, criterios §14 |
| **Arquitecto Técnico** | Migración/modelo existentes, rutas propuestas, sede Chilca, sin Flask directo | §7–8, riesgos §13, fases §15 |
| **Backend Laravel** | Controller, FormRequest, rutas anidadas en estudiante, tests Feature | Especificación §7 (fase 2C) |
| **Frontend React** | Sección en perfil estudiante, permisos UI, estados UX | Especificación §8 (fase 2D) |
| **Seguridad/RBAC** | Permisos no existentes en seeder; asignación por rol DRS | §9, Fase 2B |
| **QA/Test** | 401/403, validación, sede, anulación lógica, smoke manual | §11, Fase 2E |
| **Documentación** | api, matriz, manuales, NC, limitaciones | §12, Fase 2E |
| **ML/MLOps** | **No interviene** en implementación RF-04 | Solo nota §10: conteo ya en payload; RF-18 y RF-19 posteriores |

---

## 7. Impacto backend propuesto

Análisis sin implementar. Patrones de referencia: [`AlertaController`](../../../backend/app/Http/Controllers/Api/AlertaController.php), rutas alertas/intervenciones en [`api.php`](../../../backend/routes/api.php).

| Elemento backend | Existe hoy | Acción futura recomendada |
| ---------------- | ---------- | ------------------------- |
| Tabla `reportes_conductuales` | **Sí** | Evaluar migración complementaria: columna `estado` (`activo`/`anulado`) si se adopta anulación lógica |
| Modelo `ReporteConductual` | **Sí** | Añadir relación `registradoPor()` → `User`; scope `activos()`; posible `SoftDeletes` **no** recomendado (preferir `estado`) |
| `ReporteConductualController` | **No** | Crear en Fase 2C |
| Rutas API | **No** | Propuesta mínima (nombres orientativos): |
| | | `GET /api/estudiantes/{estudiante}/reportes-conductuales` — middleware `auth:sanctum`, `permission:ver_reportes_conductuales` |
| | | `POST /api/estudiantes/{estudiante}/reportes-conductuales` — `permission:registrar_reportes_conductuales` |
| | | `PATCH /api/reportes-conductuales/{reporteConductual}` — anular (`estado=anulado`) — mismo permiso registro o permiso dedicado (decidir en 2B) |
| | | `GET /api/reportes-conductuales` — listado institucional opcional — filtros `grado`, `seccion`, `nivel_gravedad`; filtrar `estudiantes.sede = chilca` |
| FormRequest | **No** | `StoreReporteConductualRequest`, `UpdateReporteConductualRequest` (anulación) — validar enums gravedad, fechas, longitudes |
| Autorización estudiante | Parcial | Verificar estudiante existe y `sede` coherente con V1 Chilca antes de CRUD |
| Activity log | Parcial en proyecto | Registrar create/anulación si política RF-17 lo exige (patrón en `ActivityLogTest`) |
| Tests Feature | **No** | `ReporteConductualTest.php` en Fase 2E |
| Seeder datos demo | **No** | Opcional: factory/seed mínimo Chilca en fase posterior (no obligatorio Fase 2E) |

**No modificar** `RiesgoAcademicoService` en Fase RF-04 salvo corrección aprobada: ya cuenta `ReporteConductual::query()->where('estudiante_id', …)->count()` (incluye anulados hoy — **ajustar a `estado=activo` en fase 2C** cuando exista columna).

---

## 8. Impacto frontend propuesto

| Elemento frontend | Propuesta | Justificación |
| ----------------- | --------- | ------------- |
| **Ubicación UI** | Nueva sección **«Reportes conductuales»** en **perfil del estudiante** (`EstudiantesPanel`), junto a `EstudiantePerfilRiesgo` y `EstudiantePerfilDatos` | DRS centra registro **por estudiante**; mockups no tienen módulo global RF-04 |
| **Módulo menú lateral** | **No** en V1 mínima | Evitar duplicar navegación; acceso vía estudiantes + permiso |
| **Ver reportes** | Lista + detalle en perfil si `ver_reportes_conductuales` | Separación lectura/registro |
| **Registrar** | Formulario modal o panel si `registrar_reportes_conductuales` | Campos alineados a API |
| **Docente** | Formulario registro **según DRS** | Resolver discrepancia con fichas manuales (§13) |
| **Psicólogo/tutor** | Ver + registrar (si DRS + permisos 2B) | Ya tiene `ver_notas_academicas`; ampliar perfil RF-11 futuro |
| **Coordinador** | Ver + registrar + listado grado/sección opcional | Rol con `gestionar_estudiantes`, `procesar_riesgo` |
| **Directivo** | **Solo lectura**; filtrar `nivel_gravedad=grave` o todos según decisión 2B | DRS: lectura en casos críticos — MVP: `ver_reportes_conductuales` sin registro |
| **Estados UI** | Carga, vacío («Sin reportes»), error API, validación inline | Convención UI SIDERAE |
| **Permisos UI** | Ocultar botones sin permiso; **no** sustituir middleware backend | Agente Seguridad/RBAC |
| **API client** | Funciones en [`frontend/src/lib/api.js`](../../../frontend/src/lib/api.js) | Patrón `request('/api/estudiantes/...')` |
| **Sede** | No selector sede; estudiantes listados ya filtrados Chilca | [`sedeOperativa.js`](../../../frontend/src/lib/sedeOperativa.js) |

---

## 9. Impacto permisos/RBAC

Verificación en [`PermissionsSeeder.php`](../../../backend/database/seeders/PermissionsSeeder.php) (solo lectura):

| Permiso | Existe en seeder | Confirmación |
| ------- | ---------------- | ------------ |
| `ver_reportes_conductuales` | **No** | Solo documentado §16 [`seguridad-roles-permisos.md`](../../seguridad-roles-permisos.md) |
| `registrar_reportes_conductuales` | **No** | Idem |

**Total permisos implementados hoy:** 23 (8 legacy + 15 curricular). Los 8 **planificados** incluyen estos dos para RF-04.

### Verificación permisos (Fase 2B — completada)

| Permiso | Existe en seeder | Confirmación |
| ------- | ---------------- | ------------ |
| `ver_reportes_conductuales` | **Sí** | `PermissionsSeeder.php` — Fase 2B (2026-06-10) |
| `registrar_reportes_conductuales` | **Sí** | Idem |

**Total permisos implementados:** 25 (8 legacy + 15 curricular + 2 conductuales). **Planificados restantes:** 6 (§16 `seguridad-roles-permisos.md`).

### Asignación por rol (implementada Fase 2B)

| Permiso | Existe en seeder | Roles asignados | Uso |
| ------- | ---------------- | --------------- | --- |
| `ver_reportes_conductuales` | **Sí** | `administrador`, `docente`, `coordinador_academico`, `psicologo_tutor`, `directivo` | GET listado/detalle (Fase 2C) |
| `registrar_reportes_conductuales` | **Sí** | `administrador`, `docente`, `coordinador_academico`, `psicologo_tutor` | POST crear; PATCH anular (Fase 2C) |
| — | — | **`directivo` sin registro** | Solo lectura; coherente con RF-10 futuro |

**Decisión Fase 2B:** Directivo ve todos los reportes con permiso de lectura (MVP); filtro UI por gravedad opcional en Fase 2D.

**Middleware futuro:** `auth:sanctum` + `permission:ver_reportes_conductuales` / `permission:registrar_reportes_conductuales` (Spatie), igual que alertas e intervenciones — **pendiente Fase 2C**.

---

## 10. Impacto ML/riesgo

| Tema | Decisión para RF-04 |
| ---- | ------------------- |
| **Señal futura** | Cada reporte **activo** incrementa conteo enviado a Flask como `reportes_conductuales` en payload de riesgo. |
| **Flask** | **No modificar** en Fase RF-04; fórmula determinística vigente. |
| **`RiesgoAcademicoService`** | **No modificar** en plan base; en 2C valorar filtrar solo reportes `activos` al contar. |
| **Recálculo automático** | **No** disparar `procesar-riesgo` al guardar reporte en V1 (evita efectos colaterales; usuario con `procesar_riesgo` recalcula cuando corresponda). |
| **RF-19 semáforo** | Reportes conductuales serán insumo de completitud — **implementar después** (Fase RF-19). |
| **RF-20 historial** | Timeline puede incluir reportes — **después** de RF-04 operativo. |
| **RF-18** | **No interviene** en esta fase. |

---

## 11. Pruebas necesarias

Propuestas para Fase 2E. **Ninguna ejecutada en Fase 2A.**

### Backend (PHPUnit)

| Tipo | Prueba | Objetivo | Prioridad |
| ---- | ------ | -------- | --------- |
| Backend | `test_listar_reportes_sin_sesion_retorna_401` | Sanctum | Alta |
| Backend | `test_crear_reporte_sin_permiso_retorna_403` | Spatie | Alta |
| Backend | `test_docente_con_permiso_crea_reporte_201` | Flujo feliz registro | Alta |
| Backend | `test_campos_requeridos_invalidos_retorna_422` | Validación FormRequest | Alta |
| Backend | `test_listar_por_estudiante_retorna_solo_del_estudiante` | Aislamiento datos | Alta |
| Backend | `test_estudiante_auquimarca_rechazado_o_filtrado` | V1 Chilca | Media |
| Backend | `test_anular_reporte_cambia_estado_sin_delete_fisico` | Anulación lógica | Alta |
| Backend | `test_directivo_no_puede_crear_sin_permiso_registro` | RBAC directivo | Alta |
| Backend | `test_psicologo_puede_ver_y_registrar_según_seeder_2b` | Rol psicólogo | Media |

Ejecución recomendada: `php artisan test --filter=ReporteConductualTest` con `memory_limit=512M` si suite completa OOM @ 128M (NC-06).

### Frontend / manual (sin Cypress)

| Tipo | Prueba | Objetivo | Prioridad |
| ---- | ------ | -------- | --------- |
| Manual | Docente con permiso registra reporte en perfil | UX registro | Alta |
| Manual | Psicólogo consulta lista en perfil | Lectura RF-11 parcial | Alta |
| Manual | Coordinador consulta y registra | Rol institucional | Media |
| Manual | Directivo solo ve acciones lectura | RBAC UI | Alta |
| Manual | Usuario sin permiso no ve formulario ni botones | UI + API 403 | Alta |
| Manual | Anular reporte — desaparece de lista activa | Anulación lógica | Media |

Actualizar fichas manuales/automatizadas **obsoletas** que asumen docente **no** puede registrar (contradicen DRS v2.1) en Fase 2E.

---

## 12. Documentación a actualizar en fases futuras

| Documento | Cuándo actualizar | Motivo |
| --------- | ----------------- | ------ |
| [`docs/api.md`](../../api.md) | Fase 2C (backend) | Nuevos endpoints RF-04 |
| [`docs/manual-usuario.md`](../../manual-usuario.md) | Fase 2D–2E | Flujos por rol en perfil estudiante |
| [`docs/manual-tecnico.md`](../../manual-tecnico.md) | Fase 2E si hay migración `estado` | Esquema BD / pruebas |
| [`docs/seguridad-roles-permisos.md`](../../seguridad-roles-permisos.md) | Fase 2B | Permisos pasan de planificados a implementados |
| [`docs/matriz-rf-sprint-test.md`](../../matriz-rf-sprint-test.md) | Fase 2E | Estado RF-04, rutas, tests, resultado conocido |
| [`docs/limitaciones.md`](../../limitaciones.md) | Fase 2E | Mover RF-04 de planificado a confirmado/parcial |
| [`docs/calidad/no-conformidades-y-mejora.md`](../../calidad/no-conformidades-y-mejora.md) | Fase 2E | Cerrar o matizar NC-16; NC-13 parcial |
| [`docs/metodologia/planes-ai-dlc/plan-rf-04-reportes-conductuales.md`](plan-rf-04-reportes-conductuales.md) | Fase 2E | Marcar fases 2B–2E completadas; enlazar evidencias |
| [`docs/pruebas/Fichas_Pruebas_*`](../../pruebas/) | Fase 2E | Alinear CP-04 con DRS (docente puede registrar) |
| [`docs/ml-service.md`](../../ml-service.md) | Solo si cambia conteo (activos) | Nota RF-04 operativo |

---

## 13. Riesgos

| Riesgo | Mitigación propuesta |
| ------ | -------------------- |
| Confundir reportes conductuales con **comunicación familiar (RF-12)** | Naming UI «Reporte conductual»; no reutilizar pantallas RF-12; citar DRS v2.1 |
| Usar RF-04 como **ML real** antes de RF-18 | Documentar que conteo alimenta fórmula **determinística** existente |
| **Permisos a roles incorrectos** | Fase 2B con tabla §9; revisión Agente Seguridad; tests 403 |
| **UI visible sin backend autorizado** | Ocultar acciones + tests API 403 |
| **Mezclar Auquimarca / multi-sede** | Filtro `sede=chilca`; helpers sede operativa |
| **Borrado físico** de reportes | API sin DELETE; anulación lógica + tests |
| **Sin trazabilidad** | `registrado_por`, timestamps, activity_log opcional |
| **Reintroducir funciones retiradas** | Checklist fuera de alcance §5 en cada prompt |
| **Fichas prueba vs DRS** (docente denegado en CP-04-02) | Actualizar fichas en 2E; fuente autoritativa = DRS v2.1 |
| **Conteo anulados en riesgo** | Filtrar `estado=activo` al contar en 2C |
| **Afirmar RF-04 implementado sin evidencia** | Matriz + limitaciones solo tras tests y rutas verificables |

---

## 14. Criterios de aceptación para futura implementación

Cierre RF-04 (Fase 2E) cuando se cumpla **todo** lo verificable:

1. Permisos `ver_reportes_conductuales` y `registrar_reportes_conductuales` **creados en seeder**, asignados a roles §9, **documentados** en `seguridad-roles-permisos.md`.
2. Rutas protegidas con **Sanctum + Spatie**; sin acceso anónimo.
3. Flujo mínimo operativo: **crear**, **listar por estudiante**, **anular lógicamente** (sin DELETE físico).
4. Validación server-side de campos requeridos y enums de gravedad.
5. Tests Feature: **401**, **403**, creación feliz, validación **422**, anulación — **ejecutados** con resultado archivado o descrito en matriz.
6. UI en perfil estudiante **respeta permisos** (sin botones huérfanos).
7. Operación acotada a **V1 Chilca** (estudiantes sede operativa).
8. **Documentación** §12 actualizada; NC-16 matizada o cerrada.
9. **Flask no modificado**; **notas/asistencia/excel/riesgo** existentes sin regresión conocida (smoke o tests existentes pasan).
10. **No** se afirma certificación ISO ni ML real entrenado.

---

## 15. División sugerida en fases futuras

| Fase | Contenido | Entregables | Estado |
| ---- | --------- | ----------- | ------ |
| **Fase 2B** | Permisos y base RBAC RF-04 | Seeder: 2 permisos + asignación roles; `seguridad-roles-permisos.md` | **Completada** (2026-06-10) |
| **Fase 2C** | Backend API RF-04 | Migración `estado`; Controller, FormRequests, rutas, tests Feature | **Completada** (2026-06-10) |
| **Fase 2D** | Frontend RF-04 | Componente perfil, `api.js`, visibilidad permisos | **Completada** (2026-06-10) |
| **Fase 2E** | Pruebas, documentación y cierre | `ReporteConductualTest`, smoke manual, matriz, limitaciones, NC, actualización de este plan | **Completada** (2026-06-10) |

**Orden:** 2B → 2C → 2D → 2E (permisos antes de rutas; backend antes de UI).

---

## 16. Prompt futuro recomendado

Borrador para **Fase 2B** (no ejecutar en 2A):

```text
Contexto: SIDERAE-Blenkir V1 Chilca. RF-04 planificado. Plan: docs/metodologia/planes-ai-dlc/plan-rf-04-reportes-conductuales.md

Tarea Fase 2B únicamente:
1. Agregar permisos ver_reportes_conductuales y registrar_reportes_conductuales a PermissionsSeeder.php
2. Asignar roles según plan RF-04 §9 (directivo solo ver)
3. Actualizar docs/seguridad-roles-permisos.md §16 (marcar implementados)
4. NO crear rutas API, controllers, UI ni migraciones
5. NO tocar Flask, Docker, RiesgoAcademicoService
6. Ejecutar revisión humana del diff antes de cerrar

Fuentes: DRS v2.1 RF-04, AGENTS.md (Chilca), .cursorrules
```

---

## 17. Conclusión

**Fases 2B–2E completadas (2026-06-10):** permisos RBAC, API backend, UI perfil estudiante, pruebas PHPUnit RF-04 (8 passed), build frontend verde, documentación de cierre. **Sin** Flask modificado. **Sin** menú global RF-04.

**Estado final RF-04:** **Implementado V1 mínimo** — ver sección **Cierre Fase 2E** abajo.

Decisiones cerradas en 2B:

1. **Docente** con `registrar_reportes_conductuales` (DRS v2.1).
2. **Listado por grado/sección** — sigue opcional; MVP perfil en 2D.
3. **Anulación** — mecanismo `estado` implementado (2C); **directivo** solo lectura en backend; en UI V1 no accede al perfil (sin menú Estudiantes).

---

## Cierre Fase 2E

| Área | Resultado |
|------|-----------|
| **Backend** | API RF-04 operativa; `ReporteConductualTest`: **8 passed**, 26 assertions (~15.5 s) — 2026-06-10 |
| **Frontend** | Bloque perfil + `api.js`; `npm run build` exitoso (~7.7 s) |
| **Pruebas** | PHPUnit RF-04 verde; smoke UI navegador **no ejecutado** (ficha [`smoke-rf04-reportes-conductuales.md`](../../pruebas/smoke-rf04-reportes-conductuales.md)) |
| **Documentación** | Matriz, limitaciones, api, manual, seguridad, NC, informe-pruebas, este plan |
| **Brechas restantes** | Sin módulo global/grado-sección; sin PDF RF-16; sin alertas RF-10; directivo sin UI Estudiantes; smoke browser pendiente; integración avanzada riesgo/semáforo RF-19/historial RF-20 |
| **Estado final RF-04** | **Implementado V1 mínimo** |

**Próxima fase recomendada (post RF-04):** **Fase 3A — Plan AI-DLC RF-19 Semáforo de completitud** (prioridad DRS para calidad de datos de riesgo).

---

*Plan AI-DLC Fase 2A — 2026-06-10. Fase 2B permisos RBAC — 2026-06-10. Fase 2C backend API — 2026-06-10. Fase 2D frontend perfil — 2026-06-10. Fase 2E cierre — 2026-06-10.*
