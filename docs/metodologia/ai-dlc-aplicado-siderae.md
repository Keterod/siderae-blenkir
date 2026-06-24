# AI-DLC aplicado a SIDERAE-Blenkir

**Fase:** 1B — Guía operativa de desarrollo asistido por IA  
**Fecha:** 2026-06-10  
**Base:** [`analisis-ai-dlc-siderae.md`](analisis-ai-dlc-siderae.md) · [`ai-dlc.md`](ai-dlc.md) · DRS v2.1

---

## 1. Propósito

Este documento convierte la metodología **AI-DLC** (*AI-Driven Development Life Cycle*) del proyecto en una **guía práctica** para continuar el desarrollo de SIDERAE-Blenkir después de la consolidación documental **DRS v2.1** (RF-01 a RF-35).

Queda explícito que:

- **AI-DLC** es la **metodología principal** del proceso de ingeniería: define *cómo* y *cuándo* usar IA generativa con gobierno, trazabilidad y validación humana.
- **Scrum** (adaptado) **organiza** el trabajo en sprints o fases con objetivo, alcance, evidencia y cierre incremental.
- **MLOps básico** se aplica al **componente Flask** y a su contrato con Laravel; cobra especial relevancia en **RF-18** (reentrenamiento ML), hoy **planificado**, no implementado.
- La **IA asiste** en planificación, código, revisión, análisis de errores y documentación; **no decide sola** el alcance ni declara un RF como cerrado.
- La **revisión humana es obligatoria** antes de aceptar cambios, fusionar código o actualizar trazabilidad como «implementado».

Este texto **no sustituye** el DRS v2.1 ni certifica ISO. Complementa la definición canónica en [`ai-dlc.md`](ai-dlc.md) y los perfiles de trabajo en [`agentes-ai-dlc-siderae.md`](agentes-ai-dlc-siderae.md).

---

## 2. Principios de trabajo

Los siguientes principios gobiernan todo incremento posterior a DRS v2.1:

1. **IA como apoyo, no reemplazo del equipo** — Cursor, ChatGPT u otras herramientas aceleran borradores; el desarrollador humano integra, corrige y responde académicamente.
2. **Revisión humana obligatoria** — Ningún cambio sustancial se acepta solo porque lo propuso un modelo de lenguaje.
3. **Trazabilidad entre RF, código, pruebas y documentación** — Cada RF trabajado debe poder rastrearse en [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md).
4. **Prompts controlados** — Instrucciones explícitas con criterios de aceptación y rutas de archivos; evitar solicitudes vagas de alcance amplio.
5. **Preparación explícita del contexto** — Context Engineering: DRS, limitaciones, arquitectura, sprint y fragmentos de código antes de pedir implementación.
6. **No inventar alcance fuera del DRS** — Lo retirado (SIAGIE, Fast Test, VSE en riesgo, comunicación familiar) no se reintroduce sin decisión formal en DRS v2.1.
7. **Separar análisis, implementación, pruebas y documentación** — No mezclar fases; un plan RF aprobado precede a la construcción asistida.
8. **Registrar brechas y no conformidades** — Deuda explícita en [`no-conformidades-y-mejora.md`](../calidad/no-conformidades-y-mejora.md) cuando el RF no cierre al 100 %.
9. **Mantener coherencia con V1 Chilca** — Sede operativa única en UI y consultas por defecto ([`AGENTS.md`](../../AGENTS.md)); Auquimarca = histórico/local.
10. **Mantener ISO como referencia académica, no certificación** — Alineación progresiva según [`alineacion-iso.md`](../calidad/alineacion-iso.md); sin acreditación externa.
11. **No afirmar implementación sin evidencia en código o pruebas** — Estado honesto: confirmado / parcial / planificado / retirado.

---

## 3. Fuentes de verdad

Al trabajar con IA o al cerrar un RF, usar este orden de prioridad. Documentos de menor prioridad **no sustituyen** al DRS v2.1.

| Prioridad | Fuente | Uso |
| --------: | ------ | --- |
| 1 | [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) | Definición formal de RF, RN y RNF; alcance v2.1. |
| 2 | [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) | Trazabilidad RF → sprint → código → prueba; estado V1. |
| 3 | [`docs/limitaciones.md`](../limitaciones.md) | Alcance real vs formal; retirados y planificados. |
| 4 | [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) | 23 permisos implementados; 8 sugeridos/planificados; RBAC. |
| 5 | [`docs/api.md`](../api.md) | Catálogo de endpoints vigentes y planificados. |
| 6 | [`docs/manual-tecnico.md`](../manual-tecnico.md) | Stack, Docker, pruebas, arranque local. |
| 7 | [`docs/manual-usuario.md`](../manual-usuario.md) | Flujos visibles por rol; coherencia UI. |
| 8 | [`docs/auditoria-documentacion.md`](../auditoria-documentacion.md) | Qué documentos están vigentes, obsoletos o históricos. |
| 9 | [`docs/calidad/no-conformidades-y-mejora.md`](../calidad/no-conformidades-y-mejora.md) | Brechas NC-01–NC-21 y acciones de mejora. |
| 10 | [`docs/metodologia/`](.) | Marco AI-DLC, Scrum complemento, MLOps básico, agentes metodológicos. |

**No sustituyen el DRS v2.1** (solo contexto o planificación):

- Documentos **históricos**: `contexto-drs-requerimientos.md`, DRS v1 PDF, matrices Sprint 8 obsoletas parcialmente.
- Archivos en **`sprints/`** — planificación por etapa; contrastar siempre con código y matriz RF.
- **Mockups UI** (`docs/ui/mockups/`) — referencia visual; pueden diferir del flujo curricular vigente.
- **Procesos institucionales** (`docs/procesos/`) — descripción de negocio; no prueba de implementación.
- **Contextos IA antiguos** (`docs/arquitectura/contexto-*.md` marcados obsoletos en auditoría) — riesgo de SIAGIE/Fast Test pendientes.

Jerarquía operativa adicional: [`.cursorrules`](../../.cursorrules) (reglas técnicas) · [`AGENTS.md`](../../AGENTS.md) (decisión Chilca).

---

## 4. Ciclo AI-DLC operativo

Las **siete etapas** provienen de [`ai-dlc.md`](ai-dlc.md) §4. Son secuenciales en lo conceptual y admiten **retrocesos controlados** (por ejemplo, volver a preparación de contexto si la IA propone endpoints inexistentes o funcionalidades retiradas del alcance).

| Etapa | Qué se hace en SIDERAE | Evidencia esperada | Responsable humano |
| ----- | ---------------------- | ------------------ | ------------------ |
| **1. Análisis del requerimiento** | Leer RF en DRS v2.1, fila en matriz RF–Sprint–Test, `limitaciones.md`; identificar riesgos (permisos, datos sensibles, contrato Laravel–Flask). | Plan RF iniciado (plantilla §5); notas de alcance incluido/excluido. | Desarrollador / líder · perfil **Analista RF** |
| **2. Preparación del contexto** | Seleccionar archivos (`api.php`, controllers, componentes React, `ml-service` solo si RF-18), `.cursorrules`, `AGENTS.md`, docs de arquitectura vigentes. | Lista de rutas adjuntas al prompt; sin datos sensibles en el prompt. | Desarrollador · **Arquitecto Técnico** |
| **3. Planificación asistida por IA** | Desglose en tareas por capa, orden de dependencias, checklist de pruebas; **recorte humano** al backlog real. | Plan RF §5 completo; criterios de aceptación redactados. | Desarrollador / líder |
| **4. Construcción asistida** | Cambios incrementales; commits lógicos; sede Chilca en código nuevo; **no** tocar Flask salvo RF-18 o corrección aprobada. | Diff revisable; archivos tocados acotados al RF. | Desarrollador · **Backend** / **Frontend** según capa |
| **5. Validación y pruebas** | PHPUnit (`memory_limit=512M` para Excel), build frontend, smoke manual por rol; **sin** Cypress (ausente). | Log de pruebas ejecutadas y **no ejecutadas**; resultado conocido en matriz. | Desarrollador · **QA/Test** |
| **6. Documentación y cierre** | Actualizar matriz, `api.md`, manuales, permisos si aplica; registrar limitaciones si RF parcial. | Docs actualizados; NC nueva o actualizada si hay brecha. | Desarrollador · **Documentación** |
| **7. Retroalimentación para el siguiente sprint** | Registrar qué funcionó en prompts/contexto; siguiente RF recomendado; lecciones para Context Engineering. | Nota breve en plan RF o NC; prioridad backlog actualizada. | Desarrollador / líder · asesoría académica |

---

## 5. Aplicación por RF

Cada **RF pendiente o parcial** se trabaja como **unidad controlada**: un plan, un diff acotado, una ronda de pruebas y una actualización documental antes de pasar al siguiente RF del backlog priorizado (§6).

Flujo mínimo por RF:

1. Analista RF completa secciones 1–4 y 12 del plan.
2. Arquitecto Técnico completa impactos 5–8 y riesgos 11.
3. Implementación backend/frontend con prompt controlado (§13).
4. Seguridad/RBAC + QA + Documentación.
5. Revisión humana final (§14) antes de declarar cierre de fase.

### Plantilla reutilizable — Plan AI-DLC por RF

Copiar y completar para cada RF:

```md
# Plan AI-DLC — RF-XX Nombre

## 1. Requerimiento
<!-- Extracto DRS v2.1 §RF-XX -->

## 2. Estado actual
<!-- matriz-rf-sprint-test + limitaciones: confirmado / parcial / planificado -->

## 3. Alcance exacto
<!-- Qué se implementará en esta fase -->

## 4. Fuera del alcance
<!-- Retirados v2.1, deuda diferida, otras sedes, ML real si no es RF-18 -->

## 5. Impacto backend
<!-- Rutas, controllers, services, modelos, migraciones si aplica -->

## 6. Impacto frontend
<!-- Componentes, menú, permisos visibilidad -->

## 7. Impacto permisos/RBAC
<!-- Permisos existentes vs nuevos; middleware -->

## 8. Impacto ML
<!-- N/A salvo RF-06/07/18/20; Flask solo RF-18 -->

## 9. Pruebas necesarias
<!-- PHPUnit, smoke manual; pruebas NO ejecutadas explícitas -->

## 10. Documentación a actualizar
<!-- matriz, api, manuales, NC -->

## 11. Riesgos
<!-- Regresiones, permisos, datos sensibles -->

## 12. Criterios de aceptación
<!-- Verificables; alineados DoD Scrum adaptado -->

## 13. Prompt controlado
<!-- Instrucción para IA: archivos, límites, criterios -->

## 14. Revisión humana
<!-- Checklist diff, pruebas, docs; aprobador y fecha -->
```

---

## 6. RF priorizados

Orden recomendado para desarrollo posterior a Fase 1B. Estados según [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) y DRS v2.1.

| Orden | RF | Nombre | Motivo | Tipo de trabajo |
| ----: | -- | ------ | ------ | --------------- |
| 1 | **RF-04** | Registro reportes conductuales | Núcleo del flujo de riesgo; migración `reportes_conductuales` existe; sin API/UI | Backend API + permisos + tests + UI |
| 2 | **RF-19** | Semáforo de completitud de datos | Apoya RF-06 con datos parciales; planificado; sin UI/lógica hoy | Lógica backend + componente UI + tests |
| 3 | **RF-20** | Historial evolutivo de riesgo | **Implementado V1**: backend consulta + UI perfil tabla simple + tests; smoke manual navegador pendiente | Cierre documental y backlog RF-10/RF-16/RF-18 |
| 4 | **RF-10** | Escalamiento directivo casos críticos | Depende de alertas y datos de riesgo más completos; sin API hoy | API + permisos + UI + tests |
| 5 | **RF-11** | Perfil integral psicólogo/tutor | **Implementado V1** — panel de seguimiento psicólogo/tutor con riesgo, reportes, alertas y semáforo; smoke manual pendiente | Backend agregación + UI perfil + tests |
| 6 | **RF-16** | Reportes de riesgo académico | PDF dashboard parcial; zona reportes dedicada **planificada** | Backend reportes + UI + tests |
| 7 | **RF-14** | Dashboard académico-institucional | Subset operativo; ampliar indicadores académicos | Backend KPIs + UI dashboard + tests |
| 8 | **RF-18** | ML real / reentrenamiento | ML **determinístico** hoy; requiere dataset histórico, métricas, versionado; **último** en la cola | MLOps básico + Flask + Laravel + docs ML |

**Aclaraciones de dependencia:**

- **RF-04, RF-19 y RF-20** forman el **siguiente núcleo de riesgo** — conviene abordarlos antes de escalamiento y perfiles integrales.
- **RF-10 y RF-11** se benefician de tener reportes conductuales, semáforo e historial operativos o al menos especificados.
- **RF-16 y RF-14** deben avanzar cuando existan **datos consolidados** de riesgo y reportes; evitar dashboard/reportes vacíos o incoherentes.
- **RF-18** va **al final** porque el ML actual es **determinístico** y el reentrenamiento exige dataset, variable objetivo, entrenamiento, métricas y modelo versionado no presentes hoy ([`ml-service.md`](../ml-service.md), NC-03, NC-04).

---

## 7. Relación con Scrum

**Scrum** (marco complementario, [`scrum-complemento.md`](scrum-complemento.md)) **organiza** el trabajo por **sprints o fases** documentados en `sprints/`: cada bloque tiene objetivo, alcance, entregables y criterios de aceptación.

**AI-DLC** define **cómo** se trabaja **cada RF** dentro de esa fase:

- Durante un sprint (o sub-fase dedicada a un RF), se ejecuta el ciclo §4 completo.
- El **incremento** del sprint es código + pruebas + documentación revisables, no solo texto generado por IA.
- El **cierre de fase** debe producir, cuando corresponda:
  - código mergeable,
  - pruebas ejecutadas (y lista de no ejecutadas),
  - documentación actualizada (matriz, API, manuales),
  - brechas registradas en NC si el RF queda parcial.

La **Definition of Done** adaptada (`scrum-complemento.md` §8) aplica al cierre de cada RF: alcance del plan, sin regresiones intencionales, diff revisado, pendientes explícitos.

---

## 8. Relación con MLOps básico

**MLOps básico** ([`mlops-basico.md`](mlops-basico.md)) aplica al **microservicio Flask** y al contrato **Laravel → `POST /predict`**: validación de payload, persistencia de índice/nivel, manejo de errores, documentación de limitaciones.

Reglas operativas para el desarrollo posterior:

| Regla | Detalle |
| ----- | ------- |
| **RF-18 primero para Flask** | No modificar `ml-service/` con lógica de entrenamiento o modelos reales **antes** de un plan RF-18 aprobado con dataset y métricas. |
| **Corrección técnica excepcional** | Cambios en Flask fuera de RF-18 solo si son **corrección aprobada** (bug, contrato roto) y documentados; no ampliar alcance ML. |
| **No afirmar ML real** | Random Forest, SVM, XGBoost del DRS v1 **no** están confirmados en código; el cálculo vigente es **prototipo determinístico**. |
| **Evidencia RF-18** | Dataset, variable objetivo, pipeline de entrenamiento, métricas offline, artefacto versionado y trazabilidad Laravel → Flask antes de declarar reentrenamiento implementado. |
| **Estado actual** | Integración RF-06/07 operativa con ML determinístico; reentrenamiento = **planificado** (NC-04). |

---

## 9. Criterios de cierre de una fase AI-DLC

Al cerrar la fase de trabajo de un RF (o subconjunto acordado), el responsable humano debe registrar:

| Elemento | Contenido esperado |
| -------- | ------------------ |
| **Archivos modificados** | Lista en plan RF §14 o mensaje de commit; diff revisado. |
| **Funcionalidades implementadas** | Qué REQ del RF quedó operativo; qué quedó parcial. |
| **Pruebas ejecutadas** | Comando, fecha, resultado (p. ej. `php artisan test --filter=…` @ 512M). |
| **Pruebas no ejecutadas** | Motivo (OOM, Cypress ausente, smoke pendiente). |
| **Documentación actualizada** | Matriz RF–Sprint–Test, `api.md`, manuales, `seguridad-roles-permisos.md` si aplica. |
| **Brechas registradas** | NC nueva o actualización en `no-conformidades-y-mejora.md`. |
| **Riesgos** | Regresiones conocidas, deuda técnica, brechas pre-producción (`POST /register`, etc.). |
| **Siguiente fase recomendada** | Siguiente RF del §6 o retroceso a contexto si la IA desvió alcance. |

Sin estos elementos, la fase **no** se considera cerrada metodológicamente, aunque exista código en el working tree.

---

## 10. Conclusión

**AI-DLC aplicado** es la **guía operativa** para continuar el desarrollo de SIDERAE-Blenkir con **IA asistida** y **validación humana obligatoria**, en coherencia con DRS v2.1, trazabilidad RF–Sprint–Test y limitaciones V1 (Chilca, ML determinístico, sin agentes autónomos end-to-end).

Para perfiles de instrucción al usar IA en cada etapa, ver [`agentes-ai-dlc-siderae.md`](agentes-ai-dlc-siderae.md). Para el marco teórico completo, ver [`ai-dlc.md`](ai-dlc.md) y [`analisis-ai-dlc-siderae.md`](analisis-ai-dlc-siderae.md).

---

*Guía operativa Fase 1B — 2026-06-10.*
