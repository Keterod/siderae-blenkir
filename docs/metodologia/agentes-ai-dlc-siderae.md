# Agentes metodológicos AI-DLC — SIDERAE-Blenkir

**Fase:** 1B — Perfiles de trabajo e instrucción para IA asistida  
**Fecha:** 2026-06-10  
**Base:** [`analisis-ai-dlc-siderae.md`](analisis-ai-dlc-siderae.md) · [`ai-dlc-aplicado-siderae.md`](ai-dlc-aplicado-siderae.md)

---

## 1. Propósito

Los **agentes metodológicos** descritos aquí son **perfiles de trabajo e instrucción** para usar inteligencia artificial generativa (Cursor, ChatGPT u otras) de forma **controlada** dentro del marco **AI-DLC** del proyecto.

**No son:**

- Agentes **autónomos** que modifiquen el repositorio de extremo a extremo sin supervisión.
- Procesos desatendidos ni pipelines empresariales de IA.
- Sustitutos del desarrollador humano, la asesoría académica ni la validación técnica real.

**Son:** roles **conceptuales** que el operador humano asume o invoca en secuencia al preparar prompts, revisar diffs y cerrar fases — tal como recomienda el análisis Fase 1A y la guía [`ai-dlc-aplicado-siderae.md`](ai-dlc-aplicado-siderae.md).

---

## 2. Reglas generales para todos los agentes

Todo perfil debe respetar estas reglas sin excepción:

1. **Seguir DRS v2.1** — [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) como fuente formal de RF.
2. **No inventar alcance** — Retirados v2.1: SIAGIE, Fast Test (RF-03), VSE en flujo de riesgo (RF-05), comunicación familiar (RF-12).
3. **No tocar módulos fuera de la tarea** — Diff acotado al RF o corrección aprobada.
4. **No modificar código sin plan** — Completar plantilla Plan AI-DLC (§5 de `ai-dlc-aplicado-siderae.md`) antes de construcción asistida.
5. **No borrar datos físicamente** — Sin `git rm` de archivos del repo salvo decisión humana explícita; sin truncar BD en documentación como si fuera operación normal.
6. **No tocar Flask** salvo **RF-18** o **corrección técnica aprobada** y documentada.
7. **No tocar Docker** salvo necesidad explícita del plan (infra, variables) aprobada por el responsable humano.
8. **Mantener V1 Chilca** — Sede operativa única; helpers `sedeOperativa` / `SedeOperativa::defaultConsulta()` ([`AGENTS.md`](../../AGENTS.md)).
9. **Mantener Auquimarca como histórico/local** — No multi-sede operativo V1; no sembrar demo nuevo en Auquimarca.
10. **Mantener ISO como alineación progresiva** — Sin afirmar certificación ISO 9001/27001/25010.
11. **Reportar pruebas ejecutadas y no ejecutadas** — Honestidad académica; Cypress **no existe** en el repo.
12. **Revisión humana obligatoria** — Ningún perfil cierra un RF sin aprobación del desarrollador/líder.
13. **No afirmar implementación sin evidencia** — Código en ruta documentada o prueba con resultado conocido.

Jerarquía complementaria: [`.cursorrules`](../../.cursorrules) · [`limitaciones.md`](../limitaciones.md) · [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md).

---

## 3. Agente Analista RF

### Debe

- Leer el RF en **DRS v2.1** y su fila en **matriz RF–Sprint–Test**.
- Revisar **`limitaciones.md`** (confirmado / parcial / planificado / retirado).
- Consultar **`auditoria-documentacion.md`** si hay duda sobre vigencia de una fuente.
- Definir **alcance incluido** y **fuera de alcance** para la fase actual.
- Preparar el **Plan AI-DLC del RF** (plantilla §5 de `ai-dlc-aplicado-siderae.md`), secciones 1–4 y 12.

### No debe

- Implementar código.
- Cambiar requerimientos del DRS sin aprobación humana formal.
- Usar documentos **históricos** (`contexto-drs-requerimientos.md`, DRS v1 PDF) como fuente principal de alcance.

---

## 4. Agente Arquitecto Técnico

### Debe

- Revisar impacto en **Laravel**, **React**, **Flask**, **BD**, **permisos** y **documentación**.
- Identificar **riesgos técnicos** (regresiones, contratos API, integración ML).
- Señalar si el RF requiere **migración**, **endpoint**, **componente UI**, **test** o **actualización documental**.
- Respetar arquitectura **Frontend → Laravel → MySQL / Flask** (Flask solo vía Laravel para riesgo).

### No debe

- Cambiar arquitectura base (p. ej. frontend directo a Flask) sin aprobación.
- Proponer que **React llame directamente a Flask**.
- Proponer que **Flask lea MySQL directamente** (no documentado en arquitectura vigente).

---

## 5. Agente Backend Laravel

### Debe

- Trabajar **rutas**, **controllers**, **requests**, **services**, **modelos**, **middleware** y **tests** PHPUnit.
- Respetar **Sanctum** y **Spatie** permission middleware.
- Agregar o actualizar pruebas **401/403** cuando el RF exponga endpoints protegidos.
- Documentar **permisos nuevos** en `seguridad-roles-permisos.md` si se agregan al seeder.
- Mantener **V1 Chilca** en consultas y datos nuevos (`SedeOperativa`).

### No debe

- Tocar **frontend** salvo necesidad documentada en el plan RF.
- Tocar **Flask** salvo **RF-18** o corrección aprobada.
- Crear permisos sin documentarlos (23 implementados + 8 planificados — no confundir).
- Exponer rutas sensibles sin middleware cuando corresponda.

---

## 6. Agente Frontend React

### Debe

- Implementar UI según **permisos** (`moduloPermitido`, menú lateral).
- Manejar estados de **carga**, **error**, **vacío** y **validación**.
- Consumir **solo Laravel API** (base URL backend documentada).
- Mantener **experiencia por rol** coherente con [`manual-usuario.md`](../manual-usuario.md).

### No debe

- Llamar **Flask** directamente.
- Mostrar acciones que el usuario **no puede ejecutar** (ocultar ≠ sustituir RBAC backend).
- Crear **multi-sede V1** (selectores de sede, demo Auquimarca).
- Reintroducir flujos **retirados**: SIAGIE, Fast Test, VSE en riesgo, comunicación familiar.

---

## 7. Agente Seguridad/RBAC

### Debe

- Revisar **23 permisos implementados** y **8 sugeridos/planificados** ([`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) §16).
- Validar **middleware** `permission:*` en rutas nuevas o modificadas.
- Revisar respuestas **401/403** en tests.
- Revisar brechas como **`POST /register` público** (NC-09) si se prepara despliegue fuera de V1 local.
- **Confirmar en `PermissionsSeeder.php`** antes de afirmar que un permiso está implementado.

### No debe

- Afirmar que permisos **planificados** existen en código si no están en el seeder.
- Sustituir **seguridad backend** por solo ocultar botones en frontend.

---

## 8. Agente QA/Test

### Debe

- Proponer y revisar pruebas **PHPUnit** Feature/Unit acordes al RF.
- Separar explícitamente pruebas **ejecutadas** vs **no ejecutadas**.
- Considerar **OOM** de suite completa @ **128M** ([`hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md), NC-06).
- Recomendar **`memory_limit=512M`** para pruebas Excel (`ExcelAulaTest`).
- Recomendar **smoke manual por rol** cuando no haya Cypress (NC-05).

### No debe

- Afirmar suite completa **verde** si no se ejecutó con evidencia archivada.
- Inventar **Cypress** o E2E automatizado como existente en el repositorio.

---

## 9. Agente Documentación

### Debe

- Actualizar **`matriz-rf-sprint-test.md`** tras cada RF cerrado o parcializado.
- Actualizar **`manual-usuario.md`** si cambia UI visible por rol.
- Actualizar **`api.md`** si cambian endpoints o contratos.
- Actualizar **`seguridad-roles-permisos.md`** si cambian permisos.
- Actualizar **`limitaciones.md`** y **NC** si aparecen brechas nuevas.
- Mantener **Markdown** como fuente vigente del paquete formal.

### No debe

- Afirmar implementación **no existente** en código.
- Convertir Markdown a **PDF/Word** sin aprobación humana.
- Eliminar trazabilidad **histórica** sin decisión humana ([`auditoria-documentacion.md`](../auditoria-documentacion.md)).

---

## 10. Agente ML/MLOps

### Debe

- Entrar **principalmente en RF-18** (reentrenamiento ML real).
- Revisar [`ml-service.md`](../ml-service.md) y [`mlops-basico.md`](mlops-basico.md).
- Si se implementa ML real: definir **dataset**, **variable objetivo**, **entrenamiento**, **métricas**, **versionado de artefacto** y contrato Laravel → Flask actualizado.
- Mantener trazabilidad **Laravel → Flask**; persistencia en MySQL vía Laravel.

### No debe

- Afirmar **Random Forest / SVM / XGBoost** implementados sin código real en `ml-service/`.
- Cambiar **Flask** sin plan RF-18 aprobado o corrección documentada.
- Leer **MySQL directamente desde Flask** sin redefinición arquitectónica aprobada.

**Estado V1:** ML **determinístico**; RF-18 **planificado** (NC-03, NC-04).

---

## 11. Uso práctico de los agentes

Orden recomendado **por RF** (un humano puede asumir varios perfiles en equipo reducido):

| Paso | Perfil | Entrega |
| ---- | ------ | ------- |
| 1 | **Analista RF** | Plan AI-DLC §1–4, 12 |
| 2 | **Arquitecto Técnico** | Plan §5–8, 11; riesgos arquitectura |
| 3 | **Backend Laravel** y/o **Frontend React** | Código + tests iniciales |
| 4 | **Seguridad/RBAC** | Revisión middleware, permisos, 401/403 |
| 5 | **QA/Test** | Ejecución/prueba smoke; lista no ejecutadas |
| 6 | **Documentación** | Matriz, API, manuales, NC |
| 7 | **Revisión humana final** | Aprobación §14 del plan; cierre fase §9 `ai-dlc-aplicado-siderae.md` |

El perfil **ML/MLOps** se inserta en el paso 2–3 **solo** cuando el RF es RF-18 o impacta contrato Flask aprobado.

Los perfiles son **checklists de prompt y revisión**, no procesos paralelos autónomos.

---

## 12. Ejemplo de flujo para RF-04

**RF-04 — Registro reportes conductuales** (planificado; migración `reportes_conductuales` sin API — [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md)).

| Paso | Perfil | Acción (esta fase: solo planificación; **no implementar**) |
| ---- | ------ | ------------------------------------------------------------ |
| 1 | **Analista RF** | Leer DRS v2.1 §RF-04; confirmar estado planificado; alcance = CRUD/registro conductual vinculado a estudiante; fuera = SIAGIE, multi-sede, ML. |
| 2 | **Arquitecto Técnico** | Identificar migración existente; diseñar rutas `/api/...`, modelo, requests; permiso planificado en §16 seguridad; sin Flask. |
| 3 | **Backend Laravel** | *(Fase futura)* Controller, FormRequest, policy/middleware, tests Feature. |
| 4 | **Frontend React** | *(Fase futura)* Sección en perfil estudiante o módulo acordado; visibilidad por permiso. |
| 5 | **Seguridad/RBAC** | Validar permiso nuevo en seeder + middleware; tests 403 rol sin permiso. |
| 6 | **QA/Test** | PHPUnit CRUD + autorización; smoke manual registro conductual. |
| 7 | **Documentación** | Actualizar matriz, `api.md`, manual usuario, NC si brecha cerrada. |
| Final | **Revisión humana** | Diff, pruebas, docs; decidir cierre parcial o completo RF-04. |

Este ejemplo ilustra la secuencia; **RF-04 no se implementa en Fase 1B**.

---

## 13. Conclusión

Los **agentes metodológicos AI-DLC** guían el **desarrollo asistido por IA** en SIDERAE-Blenkir: prompts acotados, revisiones por capa y cierre con evidencia — **sin reemplazar** al equipo humano ni convertir AI-DLC en agentes autónomos end-to-end.

Guía operativa del ciclo: [`ai-dlc-aplicado-siderae.md`](ai-dlc-aplicado-siderae.md). Marco teórico: [`ai-dlc.md`](ai-dlc.md).

---

*Perfiles metodológicos Fase 1B — 2026-06-10.*
