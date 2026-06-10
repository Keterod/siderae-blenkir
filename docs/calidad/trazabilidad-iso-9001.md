# Trazabilidad y mejora continua — Referencia ISO 9001

Referencia académica inspirada en principios de **ISO 9001** (documentación, trazabilidad, mejora continua). **No** implica sistema de gestión de calidad certificado ni auditoría externa.

Documento padre: [`alineacion-iso.md`](alineacion-iso.md). Brechas: [`no-conformidades-y-mejora.md`](no-conformidades-y-mejora.md).

---

## 1. Propósito

Describir cómo el proyecto SIDERAE-Blenkir documenta la **trazabilidad** entre requisitos, implementación, pruebas y entregables documentales, como evidencia de **mejora continua orientada** (proceso académico), sin certificación ISO 9001.

---

## 2. Alcance

- Repositorio y carpeta `docs/` (Fases 1–8).
- Prototipo V1 — sede **Chilca**.
- Excluye auditoría de organismo certificador.

---

## 3. Trazabilidad documental

| Elemento | Documento / evidencia | Estado | Observación |
|----------|----------------------|--------|-------------|
| DRS v2 (estado V1) | [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) | Evidencia confirmada | Fase 7 — actualización documental |
| DRS v1 PDF | `DRS_SIDERAE_Blenkir_v1.pdf` — **externo al repo** | Histórico | Resumen: [`contexto-drs-requerimientos.md`](../arquitectura/contexto-drs-requerimientos.md) |
| README operativo | [`README.md`](../../README.md) | Evidencia confirmada | Docker, limitaciones, usuarios demo |
| Arquitectura | [`ARCHITECTURE.md`](../../ARCHITECTURE.md), [`resumen-arquitectura.md`](../arquitectura/resumen-arquitectura.md) | Evidencia confirmada | — |
| Manual técnico | [`manual-tecnico.md`](../manual-tecnico.md) | Evidencia confirmada | Stack Laravel ^13 |
| Manual usuario | [`manual-usuario.md`](../manual-usuario.md) | Evidencia confirmada | Por rol V1 |
| Seguridad | [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) | Evidencia confirmada | 23 permisos |
| Matriz RF–Sprint–Test | [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) | Evidencia confirmada | RF-01–RF-20 |
| Informe pruebas | [`informe-pruebas.md`](../pruebas/informe-pruebas.md) | Evidencia confirmada | OOM 128M documentado |
| Aula / notas / Excel | [`aula-notas-excel.md`](../aula-notas-excel.md) | Evidencia confirmada | SIAGIE ≠ plantilla curricular |
| Limitaciones | [`limitaciones.md`](../limitaciones.md) | Evidencia confirmada | Alcance V1 vs DRS |
| Sprints | [`sprints/`](../../sprints/) | Evidencia confirmada | Planificación histórica |
| Hallazgos Fase 1 | [`hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md) | Evidencia confirmada | Conteos BD auditada |
| Alineación ISO | [`calidad/alineacion-iso.md`](alineacion-iso.md) | Evidencia confirmada | Fase ISO |
| Plan documental | [`.cursor/plans/plan_documentación_siderae_aa3a21ed.plan.md`](../../.cursor/plans/plan_documentación_siderae_aa3a21ed.plan.md) | Evidencia confirmada | Trazabilidad fases |

---

## 4. Trazabilidad requisito → implementación → prueba

Cubierta principalmente por [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md):

| Nivel | Contenido | Estado |
|-------|-----------|--------|
| RF (DRS) | RF-01–RF-20 mapeados | Evidencia parcial — varios RF pendientes |
| Sprint | Sprint 1–10 + sub-sprints 8.5 | Evidencia confirmada (documental) |
| Código / ruta | `api.php`, UI `App.jsx` | Evidencia confirmada en RF activos |
| Prueba | 49 archivos `backend/tests/` | Evidencia parcial — suite incompleta @ 128M |

**Brechas de trazabilidad:** ver §9 [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) y [`no-conformidades-y-mejora.md`](no-conformidades-y-mejora.md).

---

## 5. Control de cambios documental

| Fase | Entregable principal | Estado |
|------|---------------------|--------|
| Fase 1 | [`limitaciones.md`](../limitaciones.md), hallazgos Fase 1 | Completada |
| Fase 2 | README, ARCHITECTURE, sede Chilca | Completada |
| Fase 3 | [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) | Completada |
| Fase 4 | [`manual-usuario.md`](../manual-usuario.md) | Completada |
| Fase 5 | [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md), [`informe-pruebas.md`](../pruebas/informe-pruebas.md) | Completada |
| Fase 6 | [`aula-notas-excel.md`](../aula-notas-excel.md) | Completada |
| **Fase ISO** | `docs/calidad/*` | Completada |
| Fase 7 | [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) | Completada |
| Fase 8 | [`docs/INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md) — revisión final | Completada |

Los documentos históricos (p. ej. matriz Sprint 8) se conservan con banner **histórico** — no borrados.

---

## 6. No conformidades / brechas

Registro centralizado: [`no-conformidades-y-mejora.md`](no-conformidades-y-mejora.md) (NC-01 a NC-12+).

---

## 7. Criterio de mejora continua

Proceso **recomendado** (referencia académica, no SGC certificado):

1. **Identificar** brecha (matriz RF, informe pruebas, limitaciones, NC).
2. **Clasificar** prioridad (crítica / alta / media / baja).
3. **Planificar** acción en sprint o fase documental.
4. **Implementar** o **documentar limitación** si fuera de alcance V1.
5. **Verificar** con prueba o revisión documental.
6. **Actualizar** matriz RF, DRS v2 y registro NC tras cambios.

---

*Referencia académica — no certificación ISO 9001.*
