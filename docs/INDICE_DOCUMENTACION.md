# Índice de documentación — SIDERAE-Blenkir

Paquete documental consolidado (Fases 1–8). Fecha de referencia: **2026-06-09**.

**Formato de entrega:** todo el paquete formal está en **Markdown (`.md`)** en el repositorio. Es la fuente vigente para revisión humana, sustentación interna y defensa académica. **No se requiere conversión inmediata** a PDF o Word; la maquetación formal (PDF/Word) queda como **etapa posterior opcional**, tras revisión humana del contenido.

Entrada a `docs/`: [`README.md`](README.md).

**Prototipo V1:** sede operativa **Chilca** · ML **determinístico** · **sin** certificación ISO · **sin** SIAGIE · **sin** Cypress.

Índice maestro para jurado, tribunal y trazabilidad del proyecto.

---

## 1. Documento formal principal

| Documento | Ruta | Notas |
|-----------|------|-------|
| **DRS v2 (vigente — estado V1 real)** | [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) | Fuente formal vigente en Markdown |
| DRS v1 (histórico) | `DRS_SIDERAE_Blenkir_v1.pdf` | **Externo al repositorio** — no modificado |
| Resumen DRS v1 (IA) | [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) | Transcripción RF/RN/RNF; **histórico** — usar DRS v2 |

---

## 2. Documentación de ejecución

| Documento | Ruta |
|-----------|------|
| README | [`README.md`](../README.md) |
| Instalación Docker | [`instalacion-docker.md`](instalacion-docker.md) |
| Manual técnico | [`manual-tecnico.md`](manual-tecnico.md) |
| Entrada carpeta docs | [`README.md`](README.md) |
| AGENTS.md (decisión Chilca) | [`AGENTS.md`](../AGENTS.md) |

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
| Arquitectura general | [`ARCHITECTURE.md`](../ARCHITECTURE.md) |
| Resumen arquitectura | [`arquitectura/resumen-arquitectura.md`](arquitectura/resumen-arquitectura.md) |
| Catálogo API | [`api.md`](api.md) |
| ML Service | [`ml-service.md`](ml-service.md) |
| Backend Laravel (contexto) | [`arquitectura/contexto-backend-laravel.md`](arquitectura/contexto-backend-laravel.md) |
| Frontend React (contexto) | [`arquitectura/contexto-frontend-react.md`](arquitectura/contexto-frontend-react.md) |
| ML Flask (contexto) | [`arquitectura/contexto-ml-service-flask.md`](arquitectura/contexto-ml-service-flask.md) |
| Docker (contexto) | [`arquitectura/contexto-docker-infraestructura.md`](arquitectura/contexto-docker-infraestructura.md) |

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

---

## 6. Pruebas y trazabilidad

| Documento | Ruta |
|-----------|------|
| Matriz RF–Sprint–Test | [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) |
| Informe de pruebas | [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) |
| Hallazgos Fase 1 | [`pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) |
| Plan de pruebas (formal) | [`pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md`](pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md) — *histórico/parcial; matizado post-Fase 8* |
| Fichas automatizadas | [`pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) — *referencia `ImportarDatosTest` obsoleto* |

---

## 7. Documentos históricos o de referencia

| Documento | Ruta | Notas |
|-----------|------|-------|
| Matriz control accesos Sprint 8 | [`arquitectura/matriz-control-accesos-sprint8.md`](arquitectura/matriz-control-accesos-sprint8.md) | **Histórico** — usar `seguridad-roles-permisos.md` |
| Contexto DRS v1 | [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) | **Histórico** — usar DRS v2 |
| Sprints | [`sprints/`](../sprints/) | Planificación por etapas |
| Mockups UI | [`ui/mockups/`](ui/mockups/) | Referencia visual; flujo legacy ≠ curricular completo |
| Reglas Cursor | [`.cursorrules`](../.cursorrules) | Jerarquía de fuentes desarrollo |

---

## 8. Orden recomendado de lectura para jurado

1. [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) — alcance y RF con estado honesto  
2. [`README.md`](README.md) — entrada a `docs/` · [`../README.md`](../README.md) — visión y arranque del repo  
3. [`manual-usuario.md`](manual-usuario.md) — flujos por rol  
4. [`manual-tecnico.md`](manual-tecnico.md) — stack, Docker, pruebas  
5. [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) — trazabilidad RF → código → test  
6. [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) — evidencias y limitaciones de prueba  
7. [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) — roles, permisos, brechas  
8. [`calidad/alineacion-iso.md`](calidad/alineacion-iso.md) — alineación ISO progresiva  
9. [`aula-notas-excel.md`](aula-notas-excel.md) — Excel curricular vs SIAGIE  
10. [`limitaciones.md`](limitaciones.md) — brechas consolidadas  

---

## 9. Advertencias globales

| Advertencia | Detalle |
|-------------|---------|
| **V1 Chilca** | Única sede operativa en UI y consultas por defecto |
| **Auquimarca histórico/local** | Registros en BD auditada; **no** operación multi-sede V1 |
| **Sin certificación ISO** | Solo alineación progresiva / referencia académica |
| **Sin auditoría externa** | No pentest ni organismo certificador |
| **Sin SIAGIE** | Importación institucional global **pendiente**; plantilla Excel curricular **sí** |
| **ML determinístico** | Sin Random Forest / SVM / XGBoost entrenados; sin reentrenamiento (RF-18) |
| **Sin Cypress** | No hay suite E2E automatizada en el repositorio |
| **Pruebas memoria** | Suite PHPUnit completa OOM @ **128M**; `ExcelAulaTest` OK @ **512M** |
| **Seed oficial pendiente** | Conteos BD Fase 1 = entorno local auditado, no referencia única |
| **Activity log parcial** | RF-17 incompleto vs DRS v1 |
| **Register público** | `POST /register` guest — brecha pre-producción |
| **DRS v1 PDF** | Histórico; usar **DRS v2 Markdown** para estado V1 |
| **Formato entrega** | Paquete en **Markdown**; PDF/Word = etapa posterior opcional |

---

*Índice actualizado — cierre documental Markdown (2026-06-09).*
