# Análisis de metodología AI-DLC — SIDERAE-Blenkir

**Fase:** 1A — Comprensión de la metodología AI-DLC existente  
**Fecha:** 2026-06-10  
**Alcance:** lectura documental; **sin** modificación de código, AGENTS.md ni otros artefactos operativos.

---

## 1. Propósito

Este documento analiza la metodología **AI-DLC** (*AI-Driven Development Life Cycle*) tal como está definida en el repositorio del proyecto **SIDERAE-Blenkir**, con el fin de comprender cómo debe aplicarse al desarrollo futuro asistido por IA **antes** de crear agentes, modificar `AGENTS.md` o planificar implementaciones de RF.

El análisis se basa **exclusivamente** en la documentación interna de `docs/metodologia/` y en documentos de contexto del proyecto (DRS v2.1, trazabilidad, limitaciones, auditoría). No sustituye esos documentos ni introduce una definición externa de AI-DLC.

---

## 2. Documentos metodológicos revisados

| Documento | Propósito | Estado | Observación |
| --------- | --------- | ------ | ----------- |
| [`README.md`](README.md) | Índice de la carpeta metodológica; orden de lectura; jerarquía AI-DLC + Scrum + MLOps + apoyos. | Redactado | Punto de entrada; declara carpeta autocontenida hasta citas APA finales. |
| [`metodologia-siderae.md`](metodologia-siderae.md) | Visión integrada del marco metodológico del proyecto (AI-DLC principal, Scrum, MLOps básico, DevOps ligero, Context Engineering). | Redactado | Incluye roles, flujo por sprint, relación con sprints 1–10 y fuentes F-AIDLC/F-MLOPS/F-SCRUM. |
| [`ai-dlc.md`](ai-dlc.md) | Núcleo AI-DLC: definición operativa, principios, fases, controles, diferencia vs SDLC tradicional, riesgos. | Redactado | Fuente **autoritativa** para el significado de AI-DLC en SIDERAE. |
| [`scrum-complemento.md`](scrum-complemento.md) | Scrum adaptado como marco complementario: sprints, artefactos, DoD, tabla de sprints. | Redactado | Explicita que Scrum **no** sustituye AI-DLC; roles clásicos fusionados en equipo pequeño. |
| [`mlops-basico.md`](mlops-basico.md) | Prácticas MLOps básicas sobre Flask y contrato Laravel → Flask; límites frente al DRS. | Redactado | Complemento técnico, no metodología principal; ML actual = determinístico. |
| [`aplicacion-en-siderae.md`](aplicacion-en-siderae.md) | Aplicación práctica diaria: flujo de trabajo, evidencias esperadas, relación con fuentes bibliográficas. | Redactado | Traduce la metodología a hábitos concretos (prompt, diff, pruebas, documentación). |
| [`matriz-metodologia-sprints.md`](matriz-metodologia-sprints.md) | Matriz sprint ↔ componente metodológico ↔ evidencias. | Borrador operativo | Estado «pendiente de contrastar con evidencia» en la mayoría de filas. |
| [`referencias-metodologia.md`](referencias-metodologia.md) | Inventario de fuentes F-AIDLC-01/02, F-IASE-01/02, F-MLOPS-01/02, F-SCRUM-01. | Preliminar | Citas APA pendientes; ninguna fuente certifica la etiqueta «AI-DLC» del proyecto. |

### Documentos de contexto consultados (fuera de `docs/metodologia/`)

| Documento | Uso en este análisis |
| --------- | -------------------- |
| [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) | Fuente formal de requerimientos (v2.1, RF-01–RF-35). |
| [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) | Trazabilidad RF → sprint → código → prueba. |
| [`docs/limitaciones.md`](../limitaciones.md) | Control de alcance V1 vs DRS. |
| [`docs/auditoria-documentacion.md`](../auditoria-documentacion.md) | Control de fuentes vigentes/obsoletas. |
| [`docs/calidad/no-conformidades-y-mejora.md`](../calidad/no-conformidades-y-mejora.md) | Registro de brechas y mejora continua. |
| [`docs/INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md) | Mapa del paquete documental. |
| [`docs/README.md`](../README.md) | Entrada a `docs/`. |
| [`README.md`](../../README.md) | Resumen operativo del repositorio. |
| [`AGENTS.md`](../../AGENTS.md) | Decisión operativa vigente (sede Chilca); **no modificado** en esta fase. |

---

## 3. Qué significa AI-DLC según la documentación del proyecto

### Qué significa AI-DLC

En SIDERAE-Blenkir, **AI-DLC** designa un **ciclo de vida del desarrollo de software** en el que la **inteligencia artificial generativa** participa de forma **sistemática** en actividades tradicionalmente humanas: comprensión de requerimientos, descomposición del trabajo, redacción o refactorización de código, revisión, análisis de fallos y documentación técnica.

No es un único instrumento (chat o plugin), sino una **postura metodológica**: la IA se incorpora al flujo con reglas explícitas de **gobierno**, **trazabilidad** y **validación humana**. La etiqueta **AI-DLC** es **propia del proyecto**; en fuentes bibliográficas internas aparecen términos afines (**AI-SDLC**, **GenAI-augmented** SE, **AI4SE**) con **alineación parcial**, no equivalencia literal.

### Qué problema intenta resolver

AI-DLC busca resolver tres necesidades del equipo:

1. **Productividad cognitiva** en exploración, redacción y borradores, sin confundir velocidad con corrección.
2. **Homogeneización del discurso técnico** (arquitectura, sprints, cambios) exigiendo que toda salida de IA sea **contrastable** con el repositorio y criterios de aceptación.
3. **Reducción de dispersión** en un stack heterogéneo (React, Laravel, MySQL, Flask, Docker) mediante un ciclo recurrente: requerimiento → contexto → plan → construcción → validación → documentación → lecciones.

### Cómo se diferencia de un SDLC tradicional

En un SDLC centrado solo en humanos, planificación detallada, borradores largos, búsqueda de patrones y primera versión de documentación consumen mucho tiempo calendario, especialmente en equipos reducidos y multilenguaje.

**AI-DLC** desplaza parte de ese esfuerzo hacia un **bucle humano–máquina**: la máquina produce candidatos en segundos; el humano **filtra, corrige y valida**. El desarrollador deja de ser únicamente ejecutor manual de cada línea y pasa a ser **supervisor, integrador y validador técnico**. Esto **no** implica menor competencia: exige mayor **lectura crítica**, **prueba** y **gobernanza del alcance**.

### Qué papel tiene la IA

La IA actúa como **copiloto** o **asistente técnico** (Cursor, ChatGPT u otras herramientas intercambiables):

- **Planificación asistida:** desglose de tareas, orden de dependencias, checklist de pruebas.
- **Implementación asistida:** propuestas de código y refactors sugeridos.
- **Revisión:** detección de inconsistencias, lectura de diffs, riesgos obvios.
- **Análisis de errores:** interpretación de logs y trazas (siempre verificada en ejecución real).
- **Documentación:** síntesis de arquitectura, endpoints, decisiones de seguridad o integración ML.
- **Apoyo en pruebas:** borradores de casos o aserciones.

La IA **no** fija el alcance frente al DRS, **no** decide qué está «terminado» y **no** sustituye pruebas ni revisión de diff. La documentación **no** describe agentes autónomos que modifiquen el repositorio de extremo a extremo sin intervención humana continua.

### Qué papel tiene el humano

El **desarrollador humano** (o líder del proyecto en equipo pequeño):

- Recorta, reordena y descarta propuestas de la IA.
- Revisa semántica, seguridad, convenciones y compatibilidad con pruebas.
- Integra cambios, ejecuta pruebas y asume responsabilidad académica y técnica.
- Toma decisiones de alcance respecto al DRS y al estado real del código.
- Registra decisiones en commits, revisión o documentación actualizada.

Adicionalmente, la documentación menciona un **asesor o revisor académico** (supervisión de rigor y limitaciones del prototipo) y **usuarios del sistema** (validación funcional con perfiles demo).

### Qué evidencia se genera

La metodología espera evidencias **auditables**:

| Tipo | Ejemplos en el repositorio |
| ---- | --------------------------- |
| Planificación | Archivos en `sprints/`, objetivo y criterios por iteración. |
| Código | Commits con mensajes trazables; `git diff` revisado antes del cierre. |
| Pruebas | `php artisan test`, build frontend, pruebas manuales de flujos críticos; matriz RF–Sprint–Test. |
| Documentación | `docs/arquitectura/`, manuales, `limitaciones.md`, actualización post-incremento. |
| Trazabilidad | Matriz RF–Sprint–Test; no conformidades (NC-01–NC-21). |
| Retroalimentación | Registro de qué funcionó o no en el uso de IA (prompts amplios, contexto insuficiente). |

La ausencia de evidencia indica **deuda documental**, no necesariamente ausencia de trabajo.

### Cómo se relaciona con Scrum

**Jerarquía explícita en la documentación:**

- **AI-DLC** = metodología **principal** (cómo y cuándo usar IA en el ciclo de vida).
- **Scrum** = marco **complementario** de gestión (en qué sprints y bajo qué compromisos ocurre el trabajo).

Scrum aporta ritmo, Sprint Backlog, incrementos, criterios de aceptación y Definition of Done adaptada. AI-DLC opera **dentro** de cada sprint. Los roles Scrum clásicos (Product Owner, Scrum Master) **no se formalizan** de manera rígida; se fusionan en el desarrollador/líder con asesoría académica.

**MLOps básico**, **DevOps ligero** y **Context Engineering** son prácticas de apoyo documentadas aparte; no compiten con AI-DLC en la jerarquía.

---

## 4. Fases o etapas de AI-DLC identificadas

Las fases están definidas en [`ai-dlc.md`](ai-dlc.md) §4 y replicadas en [`metodologia-siderae.md`](metodologia-siderae.md) §4 y [`aplicacion-en-siderae.md`](aplicacion-en-siderae.md) §7 con ligeras variaciones de redacción. Son **secuenciales en lo conceptual** y admiten **retrocesos controlados** (por ejemplo, volver a preparación de contexto si la IA propone soluciones fuera de alcance).

| Etapa | Descripción según documentación | Aplicación posible en SIDERAE |
| ----- | ------------------------------- | ----------------------------- |
| **1. Análisis del requerimiento** | Lectura del sprint, README, DRS cuando aplique, `docs/arquitectura/`; identificación de riesgos (permisos, datos sensibles, contrato Laravel–Flask). | Antes de RF-04, RF-19 o RF-20: leer fila correspondiente en [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) y [`limitaciones.md`](../limitaciones.md); contrastar con DRS v2.1 §RF. |
| **2. Preparación del contexto** | Selección de archivos y extractos (API, modelos, React, `ml-service`, Docker, `.cursorrules`, `AGENTS.md`). | Empaquetar contexto acotado: p. ej. `RiesgoAcademicoService.php`, `ml-service/main.py`, mockups 09/10 para RF de riesgo. |
| **3. Planificación asistida por IA** | IA propone tareas, orden y checklist; el humano recorta y alinea al backlog real. | Desglose de RF-04 (reportes conductuales) en tareas backend + UI + permisos + tests, respetando 23 permisos implementados. |
| **4. Construcción asistida** | Implementación por pasos; cambios localizados; compilación/tests frecuentes; IA sugiere, humano integra. | Incrementos pequeños por capa; sede fija Chilca según `AGENTS.md`; sin tocar Flask/riesgo salvo RF explícito. |
| **5. Validación y pruebas** | Tests automatizados donde existan; pruebas manuales de flujos críticos; detección de regresiones. | PHPUnit con `memory_limit=512M` documentado; smoke manual por rol; **sin** Cypress (ausente en repo). |
| **6. Documentación y cierre** | Actualización de docs técnicos/sprint; registro de limitaciones si el DRS no está cerrado en código. | Actualizar matriz RF–Sprint–Test, `api.md`, manuales; registrar brecha en NC si aplica. |
| **7. Retroalimentación para el siguiente sprint** | Registro de aciertos/fallos en uso de IA y criterios de aceptación para el ciclo siguiente. | Mejorar prompts y contexto antes del siguiente RF; alimentar [`no-conformidades-y-mejora.md`](../calidad/no-conformidades-y-mejora.md). |

**Nota:** [`aplicacion-en-siderae.md`](aplicacion-en-siderae.md) §7 condensa el flujo en 7 pasos con nombres ligeramente distintos («Requisito o pendiente», «Prompt para Cursor/ChatGPT», «Revisión del avance»). No contradice las 7 fases de `ai-dlc.md`; las **operacionaliza** para el día a día.

---

## 5. Roles o responsabilidades dentro de AI-DLC

### Roles mencionados por la documentación

| Rol | Fuente | Función documentada |
| --- | ------ | --------------------- |
| **Desarrollador / líder del proyecto** | `metodologia-siderae.md` §8 | Prioriza backlog, implementa, ejecuta pruebas, valida salidas de IA, consolida documentación. En Scrum adaptado concentra también planificación operativa. |
| **IA generativa / Cursor (asistente técnico)** | `metodologia-siderae.md` §8, `ai-dlc.md` §5 | Acelera redacción y exploración; propone borradores sujetos a revisión. **No** es rol humano; es herramienta gobernada. |
| **Asesor o revisor académico** | `metodologia-siderae.md` §8 | Supervisa alineación con titulación, rigor metodológico y honestidad respecto a limitaciones del prototipo. |
| **Usuarios del sistema (perfiles demo)** | `metodologia-siderae.md` §8 | Validación funcional de flujos (roles, permisos, riesgo, alertas) con datos ficticios. |

**Roles Scrum clásicos:** `scrum-complemento.md` §4 indica que **Product Owner**, **Scrum Master** y **equipo de desarrollo separados** **no se formalizan** de manera rígida en el contexto académico.

**Roles no mencionados explícitamente** en `docs/metodologia/`: QA dedicado, documentador separado, equipo ML como rol formal, agente IA autónomo, usuario institucional real (solo «usuarios del sistema» en entorno académico).

### Roles sugeridos para aplicar la metodología

Estas propuestas **no** están en la documentación metodológica actual; se ofrecen para operacionalizar AI-DLC con el estado V1 del proyecto:

| Rol sugerido | Responsabilidad sugerida |
| ------------ | ------------------------ |
| **Product owner académico** | Priorizar RF pendientes (RF-04, RF-19, RF-20) según DRS v2.1 y NC; decidir retirados vs planificados. Ya aparece como responsable sugerido en NC-02, NC-12, NC-13. |
| **Revisor humano (par)** | Segunda lectura de diffs críticos (permisos, riesgo, datos sensibles) antes de cierre; complementa validación obligatoria AI-DLC. |
| **QA académico** | Ejecutar smoke manual por rol; archivar evidencia en `docs/pruebas/`; gestionar deuda Cypress (NC-05). |
| **Documentador / trazabilidad** | Mantener matriz RF–Sprint–Test, índice y auditoría al día tras cada incremento. |
| **Equipo ML** | Cambios en `ml-service/` y RF-18 futuro; aplicar MLOps básico documentado. |
| **Agente IA (Cursor)** | Asistente bajo reglas de `.cursorrules` y `AGENTS.md`; **no** agente autónomo end-to-end (coherente con `ai-dlc.md` §10). |

---

## 6. Cómo se aplicaría AI-DLC a SIDERAE-Blenkir

### DRS v2.1 como fuente de requerimientos

El DRS v2.1 ([`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md)) es la **fuente formal** de RF-01–RF-35. AI-DLC **no** automatiza su cumplimiento: estructura cómo el humano y la IA trabajan **contrastando** cada propuesta contra el DRS y contra el código real.

**Regla operativa:** antes de implementar un RF, leer su definición en DRS v2.1 y su estado en [`limitaciones.md`](../limitaciones.md) (confirmado / parcial / planificado / retirado).

### Matriz RF–Sprint–Test como trazabilidad

[`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) es la evidencia de trazabilidad académica RF → sprint → código → prueba. En AI-DLC, cada cierre de incremento debe **actualizar** la fila del RF afectado y registrar resultado de prueba **conocido** (sin afirmar «aprobado» sin corrida documentada).

### Limitaciones como control de alcance

[`docs/limitaciones.md`](../limitaciones.md) delimita V1: Chilca operativa, Auquimarca histórico, ML determinístico, SIAGIE/Fast Test/VSE/comunicación familiar retirados. Debe incluirse en **preparación de contexto** de todo prompt amplio para evitar que la IA proponga funcionalidades fuera de alcance.

### Auditoría documental como control de fuentes

[`docs/auditoria-documentacion.md`](../auditoria-documentacion.md) clasifica documentos vigentes, obsoletos e históricos. AI-DLC exige **Context Engineering** contra fuentes **vigentes** (DRS v2, no `contexto-drs-requerimientos.md` solo para alcance). Documentos marcados obsoletos requieren banner o no deben citarse como alcance.

### No conformidades como mejora continua

[`docs/calidad/no-conformidades-y-mejora.md`](../calidad/no-conformidades-y-mejora.md) registra NC-01–NC-21. La etapa de **retroalimentación** AI-DLC y el **registro de brechas** del ciclo RF deben alimentar este documento cuando un incremento cierre o deje deuda explícita.

### Manuales, API y seguridad como evidencia de cierre

Definition of Done del proyecto (vía `scrum-complemento.md` §8) incluye documentación actualizada cuando cambian contratos, permisos o flujos. Evidencias de cierre:

- [`docs/api.md`](../api.md) — catálogo de endpoints.
- [`docs/manual-usuario.md`](../manual-usuario.md) y [`docs/manual-tecnico.md`](../manual-tecnico.md).
- [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) — 23 permisos implementados, 8 sugeridos/planificados.

### Contexto vigente del prototipo (recordatorio)

| Tema | Estado V1 |
| ---- | --------- |
| DRS | v2.1, RF-01–RF-35 |
| Sede operativa | Chilca (`AGENTS.md`) |
| ML | Determinístico; RF-18 planificado |
| Pruebas E2E | Cypress ausente |
| ISO | Alineación progresiva; sin certificación |

---

## 7. Ciclo recomendado para implementar un RF

Propuesta de ciclo **basada en las fases AI-DLC** de `ai-dlc.md` §4 y el flujo de `aplicacion-en-siderae.md` §7, **mapeado** al procedimiento solicitado para implementación de RF:

| Paso | Actividad | Correspondencia AI-DLC / SIDERAE |
| ---- | --------- | -------------------------------- |
| **1. Selección del RF** | Elegir RF desde backlog (p. ej. RF-04, RF-19, RF-20); verificar prioridad en NC y DRS v2.1. | Análisis del requerimiento (inicio). |
| **2. Análisis de alcance** | Leer DRS v2.1, fila en matriz RF–Sprint–Test, `limitaciones.md`; declarar qué está retirado/planificado. | Análisis del requerimiento (profundización). |
| **3. Plan de implementación** | Desglose en tareas por capa (backend, frontend, tests, docs); dependencias y permisos. | Planificación asistida por IA + recorte humano. |
| **4. Prompt controlado** | Contexto estructurado: archivos, sprint, `.cursorrules`, `AGENTS.md`, criterios de aceptación; sin datos sensibles. | Preparación del contexto + Context Engineering. |
| **5. Generación asistida** | Cambios incrementales; commits lógicos; no «big bang». | Construcción asistida. |
| **6. Revisión humana** | Diff, seguridad, convenciones, alcance sprint/DRS; rechazar invenciones de API. | Validación humana obligatoria (principio AI-DLC). |
| **7. Pruebas** | PHPUnit, build frontend, smoke manual; documentar resultado conocido. | Validación y pruebas. |
| **8. Actualización documental** | Matriz, `api.md`, manuales, sprint si aplica. | Documentación y cierre. |
| **9. Registro de brechas** | NC nueva o actualización; pendientes explícitos si RF no cerrado al 100 %. | Documentación y cierre + honestidad académica. |
| **10. Cierre del sprint/fase** | Decisión de incremento entregable; retroalimentación para siguiente RF. | Cierre Scrum + retroalimentación AI-DLC. |

**Ajuste respecto al orden «ideal» de la metodología original:** la documentación coloca **preparación de contexto antes de planificación asistida** y agrupa revisión humana **dentro y después** de construcción. En la práctica RF, los pasos 3–4 pueden iterarse (plan → contexto → replan) antes del paso 5; es coherente con los «retrocesos controlados» de `ai-dlc.md` §4.

---

## 8. Relación con agentes

### ¿Conviene crear agentes?

**Sí, pero con el matiz que impone la documentación metodológica:** conviene definir **agentes metodológicos y de contexto** (instrucciones especializadas para Cursor u otro asistente), **no** agentes autónomos que modifiquen el repositorio sin supervisión. `ai-dlc.md` §10 declara explícitamente que **no** se documenta uso de agentes autónomos end-to-end; F-AIDLC-02 respalda copilotos y roles pasivo/activo **sin** pipeline empresarial ni agentes completos.

Los agentes serían útiles para **estandarizar Context Engineering** y reducir desvíos de alcance (Chilca, ML determinístico, fuentes vigentes).

### Tipos de agentes útiles (propuesta)

| Agente propuesto | Función | Base metodológica |
| ---------------- | ------- | ----------------- |
| **Analista RF / alcance** | Contrastar RF con DRS v2.1, matriz y limitaciones antes de codificar. | AI-DLC fase 1 + control de alcance. |
| **Implementador backend / frontend / ML** | Prompts acotados por capa y convenciones del repo. | AI-DLC fases 2–4; `.cursorrules`. |
| **Revisor de diff y seguridad** | Checklist permisos, rutas, no regresiones. | AI-DLC principio validación humana; Sprint 8. |
| **Documentador de trazabilidad** | Actualizar matriz, api, NC tras incremento. | AI-DLC fase 6; DoD Scrum. |
| **QA / pruebas** | Borradores de tests PHPUnit; smoke checklist (sin Cypress). | F-IASE-01; sprint 9. |

Estos agentes son **perfiles de instrucción**, no procesos desatendidos.

### ¿Deben estar en `AGENTS.md` o en documento metodológico?

| Contenido | Ubicación recomendada |
| --------- | --------------------- |
| Definición de AI-DLC, fases, principios, relación Scrum/MLOps | `docs/metodologia/` (ya existe; futuro `ai-dlc-aplicado-siderae.md` propuesto en §10). |
| Perfiles de agente, prompts tipo, checklist por RF | Documento metodológico aplicado **o** `.cursor/rules/` — **no** duplicar todo en `AGENTS.md`. |
| Decisiones operativas estables (Chilca, helpers sede, jerarquía DRS vs mockups) | `AGENTS.md` raíz (como hoy). |
| Reglas técnicas completas de desarrollo | `.cursorrules` (jerarquía de fuentes ya definida). |

### Qué debe ir en `AGENTS.md`

- Decisiones **operativas** de corta lectura para cualquier sesión IA (sede Chilca, helpers, fuera de alcance inmediato).
- Enlaces a metodología y a `.cursorrules`.
- **No** reemplazar la metodología completa ni duplicar `ai-dlc.md`.

### Qué no debe ir en `AGENTS.md`

- Definición extensa de AI-DLC, tablas de sprints completas, bibliografía F-AIDLC.
- Checklists completos de implementación por RF.
- Instrucciones que contradigan DRS v2.1 o `limitaciones.md`.
- Configuración de agentes autónomos sin revisión humana.

### ¿`backend/AGENTS.md`, `frontend/AGENTS.md`, `ml-service/AGENTS.md` ahora o más adelante?

**Más adelante**, tras:

1. Aprobar este análisis (Fase 1A).
2. Crear documento **AI-DLC aplicado** con perfiles de agente.
3. Actualizar `AGENTS.md` raíz con enlaces mínimos.

**Razón:** la metodología actual es **transversal**; `AGENTS.md` raíz ya concentra la decisión transversal (Chilca). AGENTS por carpeta tiene sentido cuando existan convenciones **locales** estables (p. ej. patrones curriculares en backend, guía UI en frontend, contrato Flask en ML) y el equipo las haya validado — evitando fragmentación prematura.

**Estado actual de `AGENTS.md`:** solo contiene decisión operativa sede Chilca (27 líneas); **no** describe AI-DLC ni agentes metodológicos. Coherente con no modificarlo en Fase 1A.

---

## 9. Recomendación para el proyecto

### Opciones evaluadas

| Opción | Descripción | Valoración |
| ------ | ----------- | ---------- |
| **A** | Solo documentar AI-DLC aplicado | Insuficiente: falta puente operativo RF → prompt → evidencia. |
| **B** | Documentar AI-DLC aplicado + agentes metodológicos | **Recomendada.** Respeta jerarquía docs existente; no sobrecarga `AGENTS.md`; alinea con AI-DLC sin agentes autónomos. |
| **C** | Actualizar solo `AGENTS.md` raíz con metodología completa | Riesgo de duplicar `ai-dlc.md` y mezclar metodología con decisiones operativas. |
| **D** | Crear AGENTS por carpeta inmediatamente | Prematuro; aumenta mantenimiento antes de validar perfiles en documento aplicado. |

### Decisión recomendada: **Opción B**

**Documentar AI-DLC aplicado + agentes metodológicos** en `docs/metodologia/` (nuevo documento propuesto, p. ej. `ai-dlc-aplicado-siderae.md`), manteniendo:

- `ai-dlc.md` como definición **canónica** del marco.
- `AGENTS.md` raíz **delgado** (operativa + enlaces).
- `.cursorrules` como reglas técnicas de desarrollo.

**Por qué:** la documentación ya separa metodología (`docs/metodologia/`), decisiones operativas (`AGENTS.md`) y reglas Cursor (`.cursorrules`). Opción B respeta esa arquitectura documental, implementa Context Engineering de forma repetible y evita contradecir `ai-dlc.md` §10 sobre agentes autónomos. AGENTS por carpeta (Opción D) queda como **fase posterior** cuando los perfiles estén probados en RF-04/19/20.

---

## 10. Próximos pasos

Orden sugerido para continuar después de esta Fase 1A:

1. **Revisar este análisis** — validación humana por líder del proyecto y asesoría académica.
2. **Decidir estructura de agentes** — aprobar perfiles propuestos en §8 (analista RF, implementadores por capa, revisor, documentador, QA).
3. **Actualizar índice** — añadir enlace a este informe en [`docs/metodologia/README.md`](README.md) y, en fase posterior, en [`docs/INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md).
4. **Crear documento AI-DLC aplicado** — p. ej. `docs/metodologia/ai-dlc-aplicado-siderae.md` con plantillas de prompt, checklist por fase y mapeo RF → ciclo §7.
5. **Recién después actualizar `AGENTS.md`** — enlaces breves a metodología aplicada y recordatorio de jerarquía de fuentes; **sin** duplicar `ai-dlc.md`.
6. **Luego iniciar RF-04 / RF-19 / RF-20** — usando el ciclo §7, matriz RF–Sprint–Test y registro de NC al cierre de cada incremento.

---

## Referencias internas

- Metodología: [`docs/metodologia/`](.)
- DRS v2.1: [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md)
- Trazabilidad: [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md)
- Alcance: [`docs/limitaciones.md`](../limitaciones.md)
- AGENTS (sin modificar): [`AGENTS.md`](../../AGENTS.md)
