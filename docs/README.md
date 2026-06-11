# Documentación SIDERAE-Blenkir

Entrada principal a la carpeta `docs/` del repositorio. **Paquete vigente en Markdown (`.md`)** — listo para revisión humana, sustentación interna y futura conversión formal (PDF/Word en etapa posterior).

**Prototipo V1:** sede operativa **Chilca** · Laravel **^13.0** · ML **determinístico** · fecha de referencia documental **2026-06-09**.

---

## 1. Documento formal vigente

| Documento | Ruta |
|-----------|------|
| **DRS v2.1 (estado V1 real)** | [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) — versión documental **2.1**; RF **RF-01 a RF-35** |

El PDF `DRS_SIDERAE_Blenkir_v1.pdf` permanece como **histórico** (externo al repositorio).

---

## 2. Índice maestro

| Documento | Ruta |
|-----------|------|
| Índice completo del paquete | [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) |

Incluye orden de lectura para jurado, advertencias globales y listado por categorías.

---

## 3. Documentación funcional

| Documento | Ruta |
|-----------|------|
| Manual de usuario | [`manual-usuario.md`](manual-usuario.md) |
| Aula, notas y Excel | [`aula-notas-excel.md`](aula-notas-excel.md) |
| Módulo curricular (análisis) | [`analisis/modulo-curricular-academico.md`](analisis/modulo-curricular-academico.md) |
| Limitaciones y alcance real | [`limitaciones.md`](limitaciones.md) |

---

## 4. Documentación técnica

| Documento | Ruta |
|-----------|------|
| Manual técnico | [`manual-tecnico.md`](manual-tecnico.md) |
| Arquitectura general | [`../ARCHITECTURE.md`](../ARCHITECTURE.md) |
| Resumen arquitectura | [`arquitectura/resumen-arquitectura.md`](arquitectura/resumen-arquitectura.md) |
| Catálogo API | [`api.md`](api.md) |
| Instalación Docker | [`instalacion-docker.md`](instalacion-docker.md) |
| ML Service | [`ml-service.md`](ml-service.md) |

Contextos por componente: [`arquitectura/`](arquitectura/) (backend, frontend, ML, Docker).

---

## 5. Seguridad y calidad

| Documento | Ruta |
|-----------|------|
| Seguridad, roles y permisos | [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) |
| Alineación ISO (marco) | [`calidad/alineacion-iso.md`](calidad/alineacion-iso.md) |
| Matriz ISO/IEC 25010 | [`calidad/matriz-iso-25010.md`](calidad/matriz-iso-25010.md) |
| Matriz seguridad ISO/IEC 27000 | [`calidad/matriz-seguridad-iso-27000.md`](calidad/matriz-seguridad-iso-27000.md) |
| Trazabilidad ISO 9001 | [`calidad/trazabilidad-iso-9001.md`](calidad/trazabilidad-iso-9001.md) |
| No conformidades y mejora | [`calidad/no-conformidades-y-mejora.md`](calidad/no-conformidades-y-mejora.md) |

ISO = **alineación progresiva / referencia académica** — sin certificación.

---

## 6. Metodología y desarrollo asistido por IA

| Documento | Ruta |
|-----------|------|
| Entrada metodología | [`metodologia/README.md`](metodologia/README.md) |
| Análisis AI-DLC (Fase 1A) | [`metodologia/analisis-ai-dlc-siderae.md`](metodologia/analisis-ai-dlc-siderae.md) |
| AI-DLC aplicado (Fase 1B) | [`metodologia/ai-dlc-aplicado-siderae.md`](metodologia/ai-dlc-aplicado-siderae.md) |
| Agentes metodológicos | [`metodologia/agentes-ai-dlc-siderae.md`](metodologia/agentes-ai-dlc-siderae.md) |

La metodología **no reemplaza** el DRS v2.1. **AI-DLC** (*AI-Driven Development Life Cycle*) es la metodología principal y guía el proceso de desarrollo con IA asistida y revisión humana obligatoria. Los **agentes** en `agentes-ai-dlc-siderae.md` son **perfiles metodológicos** de instrucción, **no** agentes autónomos end-to-end. **Scrum** organiza sprints ([`metodologia/scrum-complemento.md`](metodologia/scrum-complemento.md)). **MLOps básico** aplica al componente Flask ([`metodologia/mlops-basico.md`](metodologia/mlops-basico.md)), especialmente RF-18 planificado; ML actual **determinístico**.

---

## 7. Pruebas y trazabilidad

| Documento | Ruta | Notas |
|-----------|------|-------|
| Matriz RF–Sprint–Test | [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) | Trazabilidad RF → código → test |
| Informe de pruebas V1 | [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) | **Referencia vigente** de pruebas |
| Hallazgos Fase 1 | [`pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) | Auditoría técnica |
| Plan de pruebas | [`pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md`](pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md) | **Histórico/parcial** (TO-BE 02/04/2026) |

---

## 8. Documentos históricos

| Documento | Ruta | Notas |
|-----------|------|-------|
| DRS v1 PDF | `DRS_SIDERAE_Blenkir_v1.pdf` | Externo al repo — no modificado |
| Contexto DRS v1 (IA) | [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) | Histórico; usar DRS v2 |
| Matriz Sprint 8 | [`arquitectura/matriz-control-accesos-sprint8.md`](arquitectura/matriz-control-accesos-sprint8.md) | Histórico — usar `seguridad-roles-permisos.md` |
| Mockups UI legacy | [`ui/mockups/`](ui/mockups/) | Referencia visual |
| Sprints | [`../sprints/`](../sprints/) | Planificación por etapas |

---

## 9. Advertencias globales

| Tema | Estado V1 |
|------|-----------|
| **Sede operativa** | Solo **Chilca** en UI y consultas por defecto |
| **Auquimarca** | Dato **histórico/local** en BD auditada; no operación multi-sede V1 |
| **SIAGIE** | **Fuera del alcance actual** — plantillas Excel RF-32/RF-33 |
| **Fast Test / VSE en riesgo / comunicación familiar** | **Retirados/eliminados** del alcance v2.1 |
| **ML** | **Determinístico**; RF-18 reentrenamiento **planificado**, no implementado |
| **Permisos RBAC** | **23 implementados** en seeder; **8 sugeridos/planificados** — ver [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) §16 |
| **RF vigentes** | **RF-01 a RF-35** (curricular RF-21–RF-35 confirmado en código según matriz) |
| **Cypress / E2E** | **No confirmado** en el repositorio |
| **ISO** | Referencia académica únicamente; **sin certificación** ni auditoría externa |
| **Seed oficial** | **Pendiente** — conteos BD Fase 1 = entorno local auditado |
| **Pruebas PHPUnit** | Suite completa OOM @ 128M; `ExcelAulaTest` OK @ 512M |
| **Formato entrega** | Todo el paquete formal está en **Markdown**; conversión PDF/Word = etapa posterior |

Detalle: [`limitaciones.md`](limitaciones.md) · [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) §9.

---

## 10. Regla de jerarquía documental

Al interpretar alcance, estado V1 o evidencias, aplicar este orden:

1. **[`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md)** — documento formal vigente (Markdown).
2. **[`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md)** — mapa del paquete y orden de lectura.
3. **[`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md)** — trazabilidad RF → sprint → código → test.
4. **[`limitaciones.md`](limitaciones.md)** — alcance real vs formal y brechas consolidadas.
5. **Manuales y especializados:** [`manual-tecnico.md`](manual-tecnico.md) · [`manual-usuario.md`](manual-usuario.md) · [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) · [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) · [`aula-notas-excel.md`](aula-notas-excel.md) · [`calidad/`](calidad/).
6. **Documentos históricos** (§7) — solo contexto; no sustituyen DRS v2.1.

Repositorio raíz: [`../README.md`](../README.md) · reglas desarrollo: [`../AGENTS.md`](../AGENTS.md) · [`../.cursorrules`](../.cursorrules).

---

*Cierre documental Markdown — 2026-06-09; metodología AI-DLC Fase 1B — 2026-06-10.*
