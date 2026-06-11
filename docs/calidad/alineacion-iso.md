# Alineación ISO — SIDERAE-Blenkir

Documento de **referencia académica** (Fase ISO documental). Fecha de referencia: **2026-06-09**.

**Importante:** este material describe **alineación progresiva** con criterios inspirados en normas ISO. **No** constituye certificación, auditoría externa ni declaración de cumplimiento total.

Matrices de detalle: [`matriz-iso-25010.md`](matriz-iso-25010.md) · [`matriz-seguridad-iso-27000.md`](matriz-seguridad-iso-27000.md) · [`trazabilidad-iso-9001.md`](trazabilidad-iso-9001.md) · [`no-conformidades-y-mejora.md`](no-conformidades-y-mejora.md).

Índice documental: [`../INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md). DRS vigente: [`../drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md).

---

## 1. Propósito

Presentar cómo el prototipo académico **SIDERAE-Blenkir V1** documenta evidencias técnicas y documentales que permiten una **defensa honesta** ante tribunal o asesoría, usando normas ISO **solo como marco orientativo** de calidad del producto, seguridad de la información y trazabilidad del proceso.

---

## 2. Alcance

| Criterio | Incluido | Excluido |
|----------|----------|----------|
| Producto | Prototipo V1 — sede operativa **Chilca** | Operación multi-sede activa |
| Entorno | Docker local documentado | Despliegue productivo certificado |
| Seguridad | RBAC, Sanctum, pruebas 401/403 parciales | Pentest / auditoría externa |
| Pruebas | PHPUnit backend; informe Fase 1 | Cypress; suite completa verde @ 128M |
| Normas | Referencia académica | Certificación ISO formal |

---

## 3. Normas usadas como referencia

| Norma / familia | Uso en este proyecto |
|-----------------|----------------------|
| **ISO/IEC 25010** | Modelo de **calidad del producto software** (funcionalidad, usabilidad, fiabilidad, seguridad funcional, mantenibilidad, portabilidad). Matriz: [`matriz-iso-25010.md`](matriz-iso-25010.md). |
| **ISO/IEC 27000 / 27001** | **Familia de referencia** para seguridad de la información (activos, controles, riesgos). No SGSI implementado. Matriz: [`matriz-seguridad-iso-27000.md`](matriz-seguridad-iso-27000.md). |
| **ISO 9001** | Ideas de **documentación, trazabilidad y mejora continua** del proceso de desarrollo académico. No sistema de gestión de calidad certificado. Matriz: [`trazabilidad-iso-9001.md`](trazabilidad-iso-9001.md). |

---

## 4. Principio de interpretación

| Término | Significado en SIDERAE-Blenkir |
|---------|-------------------------------|
| **Alineación documental** | Existe documento que mapea criterio ↔ evidencia ↔ brecha |
| **Evidencia técnica** | Código, rutas, tests o artefactos verificables en el repositorio |
| **Brecha** | Requisito formal (DRS/ISO) no cubierto o solo parcialmente |
| **Certificación formal** | **No aplica** — requiere organismo acreditado y SGSI/SGC auditado |

---

## 5. Resumen de alineación

| Norma / referencia | Área evaluada | Evidencia principal | Estado | Documento de detalle |
|--------------------|---------------|----------------------|--------|----------------------|
| ISO/IEC 25010 | Calidad producto | Módulos UI, matriz RF, manuales, Docker | Evidencia parcial | [`matriz-iso-25010.md`](matriz-iso-25010.md) |
| ISO/IEC 27000 | Seguridad información | Sanctum, Spatie RBAC, 401/403, activity log parcial | Evidencia parcial | [`matriz-seguridad-iso-27000.md`](matriz-seguridad-iso-27000.md) |
| ISO 9001 (referencia) | Trazabilidad / mejora | Matriz RF–Sprint–Test, fases documentales, NC | Evidencia parcial | [`trazabilidad-iso-9001.md`](trazabilidad-iso-9001.md) |
| Mejora continua | Brechas registradas | Registro NC-01…NC-21 | Evidencia confirmada (documental) | [`no-conformidades-y-mejora.md`](no-conformidades-y-mejora.md) |

### RF-17 y RF-19 frente a ISO

| RF | Relación con ISO | Redacción permitida |
|----|------------------|---------------------|
| **RF-17** Auditoría | **Apoya** alineación progresiva con trazabilidad y control de acciones | «sirve como evidencia parcial», «criterio orientativo» |
| **RF-19** Semáforo completitud | **Apoya** calidad de datos y confiabilidad del riesgo | «apoya la alineación progresiva» |

**No usar:** «cumple ISO», «certificado», «obligatorio por ISO». **RF-17 y RF-19 no equivalen a certificación** ni son exigencia directa de normas ISO en este prototipo.

---

## 6. Evidencias transversales

Documentación consolidada (Fases 1–6 + ISO):

| Evidencia | Ruta |
|-----------|------|
| README operativo | [`README.md`](../../README.md) |
| Arquitectura | [`ARCHITECTURE.md`](../../ARCHITECTURE.md) |
| Manual técnico | [`docs/manual-tecnico.md`](../manual-tecnico.md) |
| Manual de usuario | [`docs/manual-usuario.md`](../manual-usuario.md) |
| Limitaciones V1 | [`docs/limitaciones.md`](../limitaciones.md) |
| Seguridad roles/permisos | [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) |
| Matriz RF–Sprint–Test | [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) |
| Informe de pruebas | [`docs/pruebas/informe-pruebas.md`](../pruebas/informe-pruebas.md) |
| Aula / notas / Excel | [`docs/aula-notas-excel.md`](../aula-notas-excel.md) |
| API | [`docs/api.md`](../api.md) |
| Hallazgos Fase 1 | [`docs/pruebas/hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md) |

---

## 7. Brechas principales

| Brecha | Fuente |
|--------|--------|
| Sin certificación ISO | [`limitaciones.md`](../limitaciones.md) §9 |
| Sin auditoría externa | Idem |
| Sin Cypress / E2E | [`informe-pruebas.md`](../pruebas/informe-pruebas.md) §9 |
| Activity log parcial (RF-17) | [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) §13 |
| Pruebas 401/403 no exhaustivas | Idem §12 |
| Suite PHPUnit OOM @ 128M | [`hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md) |
| RF planificados (RF-04, RF-10, RF-16, RF-18–20) | [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) |
| SIAGIE, Fast Test, VSE riesgo, comunicación familiar **retirados** | DRS v2.1 §2.3 |
| ML determinístico vs ensemble | [`limitaciones.md`](../limitaciones.md) §5 |
| DRS v2.1 publicado; PDF v1 histórico | [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) |

Registro formal: [`no-conformidades-y-mejora.md`](no-conformidades-y-mejora.md).

---

## 8. Uso en DRS actualizado

El **DRS v2.1** ([`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md)) integra esta fase ISO así:

1. Usar **«alineación progresiva con criterios inspirados en ISO/IEC 25010, ISO/IEC 27000 e ISO 9001»**.
2. **No** afirmar certificación, auditoría externa ni cumplimiento total.
3. **RF-17** y **RF-19** apoyan trazabilidad y calidad de datos como **evidencia parcial**, no como requisito ISO directo.
4. Referenciar matrices de `docs/calidad/` como **evidencia académica orientativa**.
5. Separar alcance formal DRS v1 vs estado V1 prototipo (RF-01 a RF-35).
6. Declarar SIAGIE/Fast Test/VSE/comunicación familiar según decisiones v2.1.

---

*Documento actualizado — Fase 9 / DRS v2.1.*
