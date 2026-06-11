# Carpeta `docs/metodologia/` — SIDERAE-Blenkir

## Propósito

Esta carpeta concentra la **documentación metodológica** del proyecto **SIDERAE-Blenkir**: cómo se combinan **AI-DLC** (metodología principal), **Scrum** (gestión por sprints), **MLOps básico** (componente predictivo), y prácticas de apoyo (**DevOps ligero**, **Context Engineering**), en coherencia con el estado real del repositorio y con los documentos de arquitectura y sprints.

Sirve como **base para sustentación académica** y para una futura revisión con **fuentes bibliográficas reales** (no incluidas aún en estos archivos).

## Documentos incluidos

| Documento | Propósito | Estado |
| --------- | ----------- | ------ |
| `metodologia-siderae.md` | Visión integrada: AI-DLC + Scrum + MLOps básico + prácticas de apoyo; flujo y límites del prototipo. | Redactado |
| `ai-dlc.md` | Marco principal: uso gobernado de IA generativa en el ciclo de vida; principios, fases y controles. | Redactado |
| `scrum-complemento.md` | Scrum adaptado como marco complementario: sprints, artefactos y Definition of Done; tabla de sprints. | Redactado |
| `mlops-basico.md` | Prácticas MLOps básicas sobre el microservicio Flask y el contrato Laravel → Flask; límites frente al DRS. | Redactado |
| `aplicacion-en-siderae.md` | Cómo se aplica la metodología al trabajo diario del proyecto: flujo, evidencias esperadas y limitaciones. | Redactado |
| `matriz-metodologia-sprints.md` | Matriz sprint ↔ componente metodológico ↔ evidencias; estado de revisión (borrador refinable). | Borrador operativo |
| `referencias-metodologia.md` | Plan de fuentes metodológicas **pendientes**; criterios y vínculo con `docs/referencias/`. | Pendiente de fuentes externas |
| `analisis-ai-dlc-siderae.md` | Análisis Fase 1A: comprensión de AI-DLC existente; recomendación Opción B. | Redactado |
| `ai-dlc-aplicado-siderae.md` | Guía operativa Fase 1B: ciclo por RF, fuentes de verdad, RF priorizados, cierre de fase. | Redactado |
| `agentes-ai-dlc-siderae.md` | Perfiles metodológicos de instrucción para IA asistida (no agentes autónomos). | Redactado |

## Metodología y desarrollo asistido por IA

Esta carpeta describe **cómo** desarrollar SIDERAE-Blenkir con IA generativa bajo supervisión humana. Aclaraciones:

- La metodología **no reemplaza** el DRS v2.1 ([`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md)).
- **AI-DLC** (*AI-Driven Development Life Cycle*) es la **metodología principal**; guía el proceso de desarrollo (planificación, construcción, revisión, documentación).
- Los **agentes** en [`agentes-ai-dlc-siderae.md`](agentes-ai-dlc-siderae.md) son **perfiles metodológicos** de trabajo e instrucción, **no** agentes autónomos end-to-end.
- **Scrum** ([`scrum-complemento.md`](scrum-complemento.md)) **organiza sprints** y criterios de cierre incremental.
- **MLOps básico** ([`mlops-basico.md`](mlops-basico.md)) aplica al **componente Flask** y especialmente a **RF-18** (reentrenamiento planificado; ML actual determinístico).

**Entrada recomendada post-DRS v2.1:** [`analisis-ai-dlc-siderae.md`](analisis-ai-dlc-siderae.md) → [`ai-dlc-aplicado-siderae.md`](ai-dlc-aplicado-siderae.md) → [`agentes-ai-dlc-siderae.md`](agentes-ai-dlc-siderae.md).

## Orden recomendado de lectura

1. `metodologia-siderae.md` — panorama y posición de cada pieza metodológica.  
2. `ai-dlc.md` — núcleo AI-DLC.  
3. `scrum-complemento.md` — organización por sprints.  
4. `mlops-basico.md` — componente ML y gobierno técnico mínimo.  
5. `aplicacion-en-siderae.md` — aplicación práctica y evidencias.  
6. `matriz-metodologia-sprints.md` — vista tabular para trazabilidad.  
7. `referencias-metodologia.md` — qué falta citar y dónde ubicarlo después.  
8. `analisis-ai-dlc-siderae.md` — análisis Fase 1A (comprensión del marco).  
9. `ai-dlc-aplicado-siderae.md` — guía operativa para continuar desarrollo por RF.  
10. `agentes-ai-dlc-siderae.md` — perfiles metodológicos para prompts y revisiones.

## Estado actual de la documentación

Los textos describen el **proceso** y el **marco** adoptados por el equipo, contrastados con el **código y la documentación interna** (README, `docs/arquitectura/`, `sprints/`). **No** sustituyen el documento de requisitos formal (DRS) ni afirman certificación ISO ni productividad ML industrial. Los matices “parcial”, “pendiente de verificar” o “prototipo” se mantienen cuando el repositorio así lo documenta.

Las **fuentes académicas** (papers, guías, normas como referencia orientativa) se incorporarán después en **`docs/referencias/`** y se enlazarán desde `referencias-metodologia.md`; hasta entonces, esta carpeta es **autocontenida** salvo remisiones a rutas internas del proyecto.

---

Referencias académicas y técnicas pendientes de incorporar en `docs/referencias/`.
