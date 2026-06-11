# Auditoría de documentación — SIDERAE-Blenkir

**Fecha:** 2026-06-09  
**Alcance:** inventario y clasificación documental **sin borrar, mover ni modificar código**.  
**Referencia vigente:** DRS v2.1 — [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) · RF **RF-01 a RF-35**.

---

## 1. Propósito

Identificar qué documentación Markdown, PDF y planes del repositorio está **vigente**, **duplicada**, **histórica**, **obsoleta** o **candidata a eliminación**, tras la Fase 9 (reestructuración RF v2.1). Este informe **no ejecuta limpieza**; sirve como base para una fase posterior aprobada por el equipo.

**Metodología:** lectura de archivos, búsqueda de frases contradictorias con DRS v2.1, revisión de enlaces desde [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) y [`README.md`](README.md), contraste con `PermissionsSeeder.php` (solo lectura).

---

## 2. Criterios de clasificación

| Estado | Significado |
|--------|-------------|
| **Vigente principal** | Fuente formal o operativa; debe mantenerse y citarse en sustentación |
| **Vigente complementario** | Soporta operación, defensa académica o trazabilidad; alineado o matizable con v2.1 |
| **Histórico útil** | Desactualizado parcialmente pero aporta trazabilidad; conservar con banner histórico |
| **Duplicado / fusionable** | Repite función de otro documento más actual |
| **Obsoleto** | Contradice DRS v2.1 de forma material; puede confundir si se lee sin contexto |
| **Candidato a eliminar** | Borrador vacío, plan temporal o duplicado sin valor único |
| **No tocar** | Protegido por prioridad de conservación (§13) |
| **Revisar manualmente** | Requiere decisión humana antes de archivar o borrar |

---

## 3. Inventario general

### 3.1 Raíz del repositorio

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`README.md`](../README.md) | Entrada repo | **Vigente principal** | Resumen alcance v2.1, Docker, RF-01–35 | Mantener; revisar si cambia seeder |
| [`ARCHITECTURE.md`](../ARCHITECTURE.md) | Arquitectura | **Vigente complementario** | Vista general stack | Mantener; alinear menciones RF si envejece |
| [`AGENTS.md`](../AGENTS.md) | Decisión operativa | **Vigente principal** | Sede Chilca, reglas agente | **No tocar** |
| [`.cursorrules`](../.cursorrules) | Reglas Cursor | **Vigente principal** | Jerarquía fuentes, Chilca | **No tocar** |
| [`frontend/README.md`](../frontend/README.md) | Scaffold Vite | **Revisar manualmente** | Plantilla framework | Mantener o reducir a enlace |
| [`backend/README.md`](../backend/README.md) | Scaffold Laravel | **Revisar manualmente** | Plantilla framework | Mantener o reducir a enlace |

### 3.2 `docs/` — núcleo formal (Fase 9)

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) | DRS formal | **Vigente principal** | Fuente autoritativa v2.1, RF-01–35 | **No tocar** (salvo revisiones formales) |
| [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) | Índice maestro | **Vigente principal** | Mapa paquete, advertencias v2.1 | Añadir enlace a este informe en fase posterior |
| [`README.md`](README.md) | Entrada docs | **Vigente principal** | Jerarquía y advertencias | Mantener |
| [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) | Trazabilidad | **Vigente principal** | RF-01–35, sprint, tests | **No tocar** |
| [`limitaciones.md`](limitaciones.md) | Alcance real | **Vigente principal** | Retirado/planificado v2.1 | **No tocar** |
| [`manual-tecnico.md`](manual-tecnico.md) | Manual técnico | **Vigente principal** | Stack, Docker, pruebas | **No tocar** |
| [`manual-usuario.md`](manual-usuario.md) | Manual usuario | **Vigente principal** | Flujos por rol | Mantener (permisos corregidos 2026-06-09) |
| [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) | Seguridad RBAC | **Vigente principal** | 23 + 8 permisos | **No tocar** |
| [`aula-notas-excel.md`](aula-notas-excel.md) | Excel curricular | **Vigente principal** | RF-32/33, vs SIAGIE | **No tocar** |
| [`api.md`](api.md) | Catálogo API | **Vigente principal** | Endpoints y planificados | **No tocar** |
| [`ml-service.md`](ml-service.md) | ML Flask | **Vigente principal** | Determinístico, RF-18 planificado | **No tocar** |
| [`instalacion-docker.md`](instalacion-docker.md) | Operación | **Vigente complementario** | Arranque local | Mantener |

### 3.3 `docs/calidad/`

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`calidad/alineacion-iso.md`](calidad/alineacion-iso.md) | ISO marco | **Vigente principal** | Alineación progresiva, sin certificación | **No tocar** |
| [`calidad/no-conformidades-y-mejora.md`](calidad/no-conformidades-y-mejora.md) | NC / mejora | **Vigente complementario** | NC-01–NC-21 v2.1 | Mantener |
| [`calidad/matriz-iso-25010.md`](calidad/matriz-iso-25010.md) | Matriz calidad | **Obsoleto** (parcial) | Cita RF-03/12 pendientes, SIAGIE pendiente | Fase A: actualizar brecha § |
| [`calidad/matriz-seguridad-iso-27000.md`](calidad/matriz-seguridad-iso-27000.md) | Matriz seguridad | **Vigente complementario** | Evidencia parcial RBAC | Mantener |
| [`calidad/trazabilidad-iso-9001.md`](calidad/trazabilidad-iso-9001.md) | Trazabilidad | **Vigente complementario** | Proceso académico, no certificación | Mantener |

### 3.4 `docs/arquitectura/`

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`arquitectura/resumen-arquitectura.md`](arquitectura/resumen-arquitectura.md) | Resumen RF | **Obsoleto** (parcial) | Tabla RF con SIAGIE/Fast Test/RF-12 pendientes | Fase A: banner + tabla o archivar |
| [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) | Contexto IA | **Histórico útil** | Transcripción DRS v1; banner v2.1 pero cuerpo desactualizado | Marcar histórico; no usar para alcance |
| [`arquitectura/contexto-backend-laravel.md`](arquitectura/contexto-backend-laravel.md) | Contexto IA | **Obsoleto** (parcial) | «SIAGIE pendiente» en RF-01 | Fase A: corregir o banner histórico |
| [`arquitectura/contexto-frontend-react.md`](arquitectura/contexto-frontend-react.md) | Contexto IA | **Obsoleto** (parcial) | «SIAGIE pendiente» | Idem |
| [`arquitectura/contexto-ml-service-flask.md`](arquitectura/contexto-ml-service-flask.md) | Contexto IA | **Vigente complementario** | ML determinístico | Revisar al evolucionar RF-18 |
| [`arquitectura/contexto-docker-infraestructura.md`](arquitectura/contexto-docker-infraestructura.md) | Contexto IA | **Vigente complementario** | Docker | Mantener |
| [`arquitectura/matriz-control-accesos-sprint8.md`](arquitectura/matriz-control-accesos-sprint8.md) | Matriz RBAC | **Histórico útil** | Sustituida por `seguridad-roles-permisos.md` | Archivar en Fase B |

### 3.5 `docs/pruebas/`

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) | Informe | **Vigente principal** | Evidencia Fase 1/5 | **No tocar** |
| [`pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) | Hallazgos | **Vigente complementario** | OOM, conteos BD | Mantener |
| [`pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md`](pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md) | Plan formal | **Histórico útil** | TO-BE 2026; matizado parcialmente | INDICE ya marca histórico/parcial |
| [`pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) | Fichas | **Obsoleto** (parcial) | Referencia `ImportarDatosTest` inexistente | Fase A: corregir o archivar |
| [`pruebas/Fichas_Pruebas_Manuales_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Manuales_SIDERAE_Blenkir.md) | Fichas | **Vigente complementario** | Smoke manual | Revisar flujos vs manual-usuario |

### 3.6 `docs/analisis/`

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`analisis/modulo-curricular-academico.md`](analisis/modulo-curricular-academico.md) | Análisis técnico | **Duplicado / fusionable** | Solapa DRS §8.21 y `aula-notas-excel.md`; «SIAGIE pendiente» | Fase D: fusionar o banner + enlace DRS |
| [`analisis/sprint-8.5A-fase2-backend-especificacion.md`](analisis/sprint-8.5A-fase2-backend-especificacion.md) | Spec sprint | **Histórico útil** | Diseño 8.5A; decisiones ya en código | Archivar en Fase B |

### 3.7 `docs/ui/mockups/` (13 `.md` + PNG)

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| `ui/mockups/01-login` … `12-registro-intervencion` | Mockups | **Histórico útil** | Flujo **legacy**; INDICE lo advierte | Mantener como referencia visual |
| [`ui/mockups/08-variables-socioeconomicas.md`](ui/mockups/08-variables-socioeconomicas.md) | Mockup VSE | **Obsoleto** (contexto) | VSE retiradas del flujo de riesgo | Banner «no vigente en V1» |
| [`ui/mockups/guia-ui-siderae.md`](ui/mockups/guia-ui-siderae.md) | Guía mockups | **Histórico útil** | Índice mockups legacy | Mantener |

### 3.8 `docs/revision-cursor/` (7 archivos)

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| `checklist-*.md` (5 archivos) | Plantillas vacías | **Candidato a eliminar** | Solo checkboxes `- [ ]` sin contenido | Fase C o `.gitignore` |
| [`revision-cursor/checklist-revision-general.md`](revision-cursor/checklist-revision-general.md) | Checklist | **Candidato a eliminar** | Vacío | Idem |
| [`revision-cursor/hallazgos-pendientes.md`](revision-cursor/hallazgos-pendientes.md) | Registro | **Candidato a eliminar** | Tabla vacía (solo cabecera) | Idem o usar `no-conformidades-y-mejora.md` |

**Nota:** carpeta **no enlazada** desde `INDICE_DOCUMENTACION.md`.

### 3.9 `docs/metodologia/` (8 archivos)

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`metodologia/metodologia-siderae.md`](metodologia/metodologia-siderae.md) | Metodología | **Vigente complementario** | AI-DLC + Scrum; defensa académica | Mantener; enlazar desde INDICE |
| [`metodologia/README.md`](metodologia/README.md) | Índice metodología | **Vigente complementario** | Entrada carpeta | Enlazar desde INDICE |
| Resto (`ai-dlc.md`, `scrum-complemento.md`, `mlops-basico.md`, etc.) | Soporte | **Vigente complementario** | Bibliografía metodológica | Mantener |

**Nota:** carpeta **no enlazada** desde `INDICE_DOCUMENTACION.md` (solo citada en sprints/metodología).

### 3.10 `docs/estado-del-arte/` (4 archivos + PDF entregable)

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| [`estado-del-arte/estado-del-arte-siderae.md`](estado-del-arte/estado-del-arte-siderae.md) | Estado del arte | **Vigente complementario** | Defensa académica; no contradice ML determinístico | Mantener |
| [`estado-del-arte/matriz-comparativa.md`](estado-del-arte/matriz-comparativa.md) | Matriz | **Vigente complementario** | Comparativa breve | Mantener |
| [`estado-del-arte/fuentes-pendientes.md`](estado-del-arte/fuentes-pendientes.md) | Seguimiento | **Revisar manualmente** | Fuentes bibliográficas | Mantener |
| [`entregables/Estado_del_Arte_SIDERAE_Blenkir.pdf`](entregables/Estado_del_Arte_SIDERAE_Blenkir.pdf) | PDF entregable | **Vigente complementario** | Versión formal EDA | No tocar PDF |

### 3.11 `docs/procesos/` (11 `.md` + DOCX fuentes)

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| `procesos/01` … `05` + `diagramas/` | Procedimientos IE | **Histórico útil** | Procesos **institucionales** Blenkir (mencionan SIAGIE, Fast Test) | **No confundir** con alcance software; banner «proceso institucional, no DRS v2.1» |
| [`procesos/README.md`](procesos/README.md) | Índice procesos | **Histórico útil** | Contexto operativo colegio | Enlazar como institucional |

### 3.12 `docs/referencias/` (bibliografía)

| Grupo | Cantidad aprox. | Estado sugerido | Motivo | Acción recomendada |
|-------|-----------------|-----------------|--------|-------------------|
| [`referencias/matriz-fuentes.md`](referencias/matriz-fuentes.md) | 1 | **Vigente complementario** | Codificación fuentes | Mantener |
| `referencias/resumenes/**/*.md` | 24 | **Vigente complementario** | Resúmenes APA/bibliografía | Mantener |
| `referencias/pdfs/**/*.pdf` | 17 | **Vigente complementario** | Fuentes primarias | Mantener |
| `referencias/curriculo-nacional-2016-2.pdf` | 1 | **Vigente complementario** | Marco curricular PE | Mantener |

### 3.13 `sprints/` (20 archivos `.md`)

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| `sprints/sprint 1.md` … `sprint 10.md` + sub-sprints | Planificación | **Histórico útil** | Trazabilidad iteraciones; citados en matriz RF | **Archivar** en Fase B; no borrar |
| Varios sprints | — | **Obsoleto** (parcial) | Priorizan DRS v1 PDF | Banner «planificación histórica» |

### 3.14 `.cursor/` y planes

| Elemento | Estado | Motivo |
|----------|--------|--------|
| Carpeta `.cursor/` | **No encontrada** en repo | Sin planes Cursor versionados |
| Archivos `*.plan.md` | **No encontrados** | — |

### 3.15 Otros entregables

| Archivo | Tipo | Estado sugerido | Motivo | Acción recomendada |
|---------|------|-----------------|--------|-------------------|
| `entregables/Informe_Final_*.docx` | DOCX | **Revisar manualmente** | Posible borrador académico | No borrar sin tribunal |
| `entregables/Estado_del_Arte_*.docx` | DOCX | **Vigente complementario** | Par de PDF EDA | Mantener |

---

## 4. Documentos vigentes principales

1. [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) — DRS v2.1  
2. [`docs/INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md)  
3. [`docs/README.md`](README.md)  
4. [`README.md`](../README.md)  
5. [`docs/matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md)  
6. [`docs/limitaciones.md`](limitaciones.md)  
7. [`docs/manual-tecnico.md`](manual-tecnico.md)  
8. [`docs/manual-usuario.md`](manual-usuario.md)  
9. [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md)  
10. [`docs/aula-notas-excel.md`](aula-notas-excel.md)  
11. [`docs/api.md`](api.md)  
12. [`docs/ml-service.md`](ml-service.md)  
13. [`docs/calidad/alineacion-iso.md`](calidad/alineacion-iso.md)  
14. [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md)  
15. [`AGENTS.md`](../AGENTS.md) · [`.cursorrules`](../.cursorrules)

---

## 5. Documentos vigentes complementarios

- [`ARCHITECTURE.md`](../ARCHITECTURE.md), [`docs/instalacion-docker.md`](instalacion-docker.md)  
- Contextos técnicos: `contexto-docker-infraestructura.md`, `contexto-ml-service-flask.md` (con revisión periódica)  
- Calidad: `no-conformidades-y-mejora.md`, matrices ISO 27000, trazabilidad 9001  
- Pruebas: `hallazgos-fase1-documentacion.md`, fichas manuales  
- Metodología: `docs/metodologia/*`  
- Estado del arte: `docs/estado-del-arte/*`, PDF en `entregables/`  
- Bibliografía: `docs/referencias/*`  
- Sprints: `sprints/*` (como evidencia de planificación, no como alcance vigente)

---

## 6. Documentos históricos útiles

| Documento | Motivo de conservación |
|-----------|------------------------|
| [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) | Transcripción DRS v1; trazabilidad nombres RF |
| [`arquitectura/matriz-control-accesos-sprint8.md`](arquitectura/matriz-control-accesos-sprint8.md) | Evolución RBAC Sprint 8 |
| [`pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md`](pruebas/Plan_de_Pruebas_SIDERAE_Blenkir.md) | Plan formal original |
| `sprints/*.md` | Cronología desarrollo |
| `docs/ui/mockups/*` | Diseño legacy UI |
| `docs/procesos/*` | Procedimientos institucionales del colegio |
| `DRS_SIDERAE_Blenkir_v1.pdf` (externo) | Fuente formal histórica |

---

## 7. Documentos duplicados o fusionables

| Documento duplicado | Documento que lo reemplaza / prevalece | Recomendación |
|---------------------|----------------------------------------|---------------|
| [`arquitectura/resumen-arquitectura.md`](arquitectura/resumen-arquitectura.md) | DRS v2.1 + [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) | Actualizar tabla RF o archivar |
| [`arquitectura/matriz-control-accesos-sprint8.md`](arquitectura/matriz-control-accesos-sprint8.md) | [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) | Mantener solo como histórico |
| [`analisis/modulo-curricular-academico.md`](analisis/modulo-curricular-academico.md) | DRS §8.21 + [`aula-notas-excel.md`](aula-notas-excel.md) | Fusionar en Fase D o reducir a enlace |
| [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) | [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) | Solo histórico; no duplicar mantenimiento |
| [`ARCHITECTURE.md`](../ARCHITECTURE.md) vs contextos `arquitectura/contexto-*.md` | Complementarios | ARCHITECTURE = vista general; contextos = detalle por capa |
| Mockups `06-registro-notas`, `07-registro-asistencia` | UI curricular real (`App.jsx`) | Mockups legacy; no sustituyen manual-usuario |

---

## 8. Documentos obsoletos

Contradicciones materiales con DRS v2.1 (no incluye menciones correctas de «sin certificación»).

| Archivo | Frase / estado encontrado | Recomendación |
|---------|---------------------------|---------------|
| [`manual-usuario.md`](manual-usuario.md) | ~~«24 permisos del prototipo»~~ | **Corregido** → 23 + 8 planificados |
| [`arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) | RF-01 «SIAGIE pendiente»; RF-03 «Pendiente»; RF-12 «Pendiente» | Banner histórico; no editar cuerpo salvo Fase A |
| [`arquitectura/resumen-arquitectura.md`](arquitectura/resumen-arquitectura.md) | «importacion SIAGIE pendiente»; RF-03/12 «Pendiente» | Fase A: actualizar o mover a `historico/` |
| [`arquitectura/contexto-backend-laravel.md`](arquitectura/contexto-backend-laravel.md) | «SIAGIE pendiente» | Fase A |
| [`arquitectura/contexto-frontend-react.md`](arquitectura/contexto-frontend-react.md) | «SIAGIE pendiente» | Fase A |
| [`analisis/modulo-curricular-academico.md`](analisis/modulo-curricular-academico.md) | «Importación SIAGIE … Pendiente» | Fase A: «fuera del alcance» |
| [`calidad/matriz-iso-25010.md`](calidad/matriz-iso-25010.md) | «RF-03, RF-04, RF-10–12, RF-18–19 pendientes; SIAGIE pendiente» | Fase A: alinear con v2.1 |
| [`pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) | `ImportarDatosTest` / SIAGIE | Fase A: alinear a tests reales |
| [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) | «SIAGIE, derivación, comunicación familia … pendientes» (§ resumen) | Fase A: matizar retirado vs planificado |
| `sprints/sprint 7.6A.md` | «Priorizar DRS_SIDERAE_Blenkir_v1.pdf» | Histórico; banner en carpeta sprints |

**No encontrado como afirmación falsa de implementado:** RF-18/19 como implementados, Random Forest implementado, Cypress implementado, certificación ISO obtenida, multi-sede operativo V1 (salvo procesos institucionales que describen operación del colegio, no del software).

---

## 9. Candidatos a eliminación

| Archivo | Motivo | Riesgo de borrar | Recomendación |
|---------|--------|------------------|---------------|
| [`revision-cursor/checklist-backend.md`](revision-cursor/checklist-backend.md) | Plantilla vacía | **Bajo** | Eliminar en Fase C o no versionar |
| [`revision-cursor/checklist-frontend.md`](revision-cursor/checklist-frontend.md) | Plantilla vacía | **Bajo** | Idem |
| [`revision-cursor/checklist-ml-service.md`](revision-cursor/checklist-ml-service.md) | Plantilla vacía | **Bajo** | Idem |
| [`revision-cursor/checklist-arquitectura.md`](revision-cursor/checklist-arquitectura.md) | Plantilla vacía | **Bajo** | Idem |
| [`revision-cursor/checklist-metodologia.md`](revision-cursor/checklist-metodologia.md) | Plantilla vacía | **Bajo** | Idem |
| [`revision-cursor/checklist-revision-general.md`](revision-cursor/checklist-revision-general.md) | Plantilla vacía | **Bajo** | Idem |
| [`revision-cursor/hallazgos-pendientes.md`](revision-cursor/hallazgos-pendientes.md) | Tabla sin filas | **Bajo** | Eliminar; usar `no-conformidades-y-mejora.md` |
| Carpeta `.cursor/plans/` (si aparece) | Planes locales Cursor | **Bajo** | Añadir a `.gitignore` |

**Riesgo alto de borrar (no recomendado):** sprints, mockups, procesos institucionales, referencias PDF, entregables DOCX/PDF, `contexto-drs-requerimientos.md`.

---

## 10. Enlaces rotos o rutas incorrectas

| Origen | Enlace / referencia | Problema | Recomendación |
|--------|---------------------|----------|---------------|
| Varios docs | `DRS_SIDERAE_Blenkir_v1.pdf` | PDF **no está en el repo** | Correcto como «externo»; no enlazar como path local |
| — | `docs/docs/README.md` | **No encontrado** en repo | No requiere acción (no existe) |
| [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) §8 paso 2 | [`README.md`](README.md) dentro de `docs/` | Enlace apunta a `docs/README.md`, no a raíz | Aclarar texto (ya menciona `../README.md`) |
| Documentos no indexados | `metodologia/`, `estado-del-arte/`, `procesos/`, `revision-cursor/` | **Sin enlace** desde INDICE | Fase A: ampliar INDICE §7 |
| Este informe | — | **Nuevo** | Enlazar desde INDICE en fase posterior |

**Enlaces internos Markdown revisados:** rutas relativas en INDICE, README y DRS apuntan a archivos existentes en su mayoría.

---

## 11. Hallazgos críticos

1. **Dos capas de verdad sobre RF:** DRS v2.1 y documentos «contexto IA» (`contexto-drs-requerimientos`, `resumen-arquitectura`, `contexto-backend/frontend`) siguen describiendo SIAGIE/Fast Test/RF-12 como pendientes. Riesgo alto de confusión en Cursor/tribunal si se leen sin jerarquía de [`.cursorrules`](../.cursorrules).

2. **Permisos:** único error en manual principal ya corregido (`24` → `23 + 8 planificados`). `seguridad-roles-permisos.md` y DRS v2.1 alineados.

3. **Procesos institucionales** (`docs/procesos/`) mencionan SIAGIE y Fast Test como práctica del colegio; **no** deben interpretarse como alcance del software SIDERAE v2.1.

4. **Mockup VSE** (`08-variables-socioeconomicas`) contradice retiro de VSE del flujo de riesgo si se presenta como UI vigente.

5. **RF-21–RF-35** solo están completamente detallados en DRS §8.21 y matriz; `modulo-curricular-academico.md` es anterior y parcial.

6. **No hay planes Cursor** versionados; bajo riesgo de basura en repo por `.cursor/plans/`.

---

## 12. Propuesta de limpieza por fases

### Fase A — Correcciones pequeñas (bajo riesgo)

- Corregir tablas RF en: `resumen-arquitectura.md`, `contexto-backend-laravel.md`, `contexto-frontend-react.md`, `matriz-iso-25010.md`, `modulo-curricular-academico.md`.
- Añadir banner estándar en históricos: «**Histórico — usar DRS v2.1**».
- Actualizar `informe-pruebas.md` y fichas automatizadas (quitar `ImportarDatosTest`).
- Enlazar desde `INDICE_DOCUMENTACION.md`: metodología, estado del arte, procesos (como institucional), este informe.
- Banner en mockup VSE y en carpeta `procesos/`.

### Fase B — Archivar históricos (sin borrar)

- Crear `docs/historico/` (cuando se apruebe) y mover: `matriz-control-accesos-sprint8.md`, `sprint-8.5A-fase2-backend-especificacion.md`, opcionalmente `Plan_de_Pruebas` si se congela.
- Mantener `sprints/` como archivo de planificación; opcional `sprints/README.md` con banner histórico.

### Fase C — Eliminar candidatos claros (tras aprobación)

- Eliminar `docs/revision-cursor/checklist-*.md` vacíos y `hallazgos-pendientes.md` vacío.
- Añadir `.cursor/plans/` a `.gitignore` si el equipo usa Cursor localmente.

### Fase D — Consolidar documentación

- Reducir `modulo-curricular-academico.md` a enlace + notas puntuales, o fusionar único contenido no repetido en `aula-notas-excel.md`.
- Unificar tabla RF de `resumen-arquitectura.md` con matriz RF–Sprint–Test (una sola fuente).
- Revisar `ARCHITECTURE.md` vs contextos para evitar tres narrativas paralelas.

**Ninguna fase ejecutada en esta auditoría.**

---

## 13. Lista de archivos que NO deben borrarse

- [`README.md`](../README.md)  
- [`ARCHITECTURE.md`](../ARCHITECTURE.md)  
- [`docs/README.md`](README.md)  
- [`docs/INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md)  
- [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md)  
- [`docs/matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md)  
- [`docs/limitaciones.md`](limitaciones.md)  
- [`docs/manual-tecnico.md`](manual-tecnico.md)  
- [`docs/manual-usuario.md`](manual-usuario.md)  
- [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md)  
- [`docs/api.md`](api.md)  
- [`docs/aula-notas-excel.md`](aula-notas-excel.md)  
- [`docs/ml-service.md`](ml-service.md)  
- [`docs/calidad/alineacion-iso.md`](calidad/alineacion-iso.md)  
- [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md)  
- [`AGENTS.md`](../AGENTS.md) · [`.cursorrules`](../.cursorrules)  
- [`sprints/`](../sprints/) (histórico planificación)  
- [`docs/referencias/`](../docs/referencias/) (bibliografía)  
- Entregables académicos PDF/DOCX en `docs/entregables/`

---

## 14. Conclusión

### Qué mantener

- **Núcleo v2.1:** DRS, índice, READMEs, matriz RF, limitaciones, manuales, seguridad, API, Excel, ML, calidad ISO, informe de pruebas.  
- **Complementos académicos:** metodología, estado del arte, referencias, sprints.  
- **Históricos con valor:** contexto DRS v1, mockups, procesos institucionales, matriz Sprint 8.

### Qué marcar como histórico

- Contextos IA desactualizados, resumen-arquitectura RF, plan de pruebas formal, sprints, mockups legacy, procesos del colegio (con aclaración «no es alcance software»).

### Qué revisar manualmente

- Entregables DOCX finales, fusión `modulo-curricular-academico.md`, política sobre `frontend/backend/README.md`, ampliación del INDICE.

### Qué borrar después de aprobación humana

- Checklists vacíos en `docs/revision-cursor/` (7 archivos).  
- Opcional: ignorar `.cursor/plans/` en git.

### Permisos — estado documental verificado

| Categoría | Cantidad | Detalle |
|-----------|----------|---------|
| **Implementados** | **23** | `PermissionsSeeder.php` (8 legacy + 15 curriculares, incl. `descargar_excel_aula`) |
| **Sugeridos/planificados** | **8** | Documentados en `seguridad-roles-permisos.md` §16; **no** en seeder |

---

*Informe generado en auditoría documental — sin eliminación ni movimiento de archivos.*
