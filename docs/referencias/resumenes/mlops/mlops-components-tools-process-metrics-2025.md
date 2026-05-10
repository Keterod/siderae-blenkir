# MLOps Components, Tools, Process, and Metrics: A Systematic Literature Review

## 1. Datos bibliográficos

*(Convenciones: texto tomado literalmente del PDF salvo donde se etiqueta otro tipo de contenido.)*

- **Título:** MLOps Components, Tools, Process, and Metrics: A Systematic Literature Review
- **Autores:** Adrian P. Woźniak, Mateusz Milczarek, Joanna Woźniak *(como aparecen en el PDF, con posibles variaciones de tipografía por extracción)*
- **Año:** 2025 *(fechas editoriales indicadas en el PDF: fecha de publicación 27 January 2025; versión corriente 4 February 2025)*
- **Tipo de fuente:** Artículo de revisión sistemática *(el propio trabajo lo declara como systematic literature review)*
- **Revista / conferencia / editorial / repositorio:** *IEEE Access* — VOLUME **13**, 2025 *(según cabeceras y pie del PDF)*; página inicial numérica visible en el cuerpo: **22166** — **22175** *(rangos aparecen en pies de página)*
- **DOI o enlace, si aparece:** Digital Object Identifier **10.1109/ACCESS.2025.3534990** *(en el PDF la cadena aparece fragmentada como “10.1 109/ACCESS…”; aquí se unifica solo para legibilidad, verificar contra el PDF original)*
- **Ubicación del PDF:** `docs/referencias/pdfs/mlops/MLOps_Components_Tools_Process_and_Metrics_A_Systematic_Literature_Review.pdf`

**Tipo de contenido:** Dato textual del PDF (campos bibliográficos y metadatos de la primera página).

---

## 2. Tema principal

**Dato textual (resumen del propio PDF):** El trabajo declara revisar literatura sobre MLOps y responder preguntas de investigación sobre clases de herramientas, implementaciones más usadas, procesos dentro de MLOps y métricas de efectividad; indica haber identificado clases de herramientas, propone una **arquitectura de referencia de MLOps** basada en los hallazgos, **delimita etapas del proceso de producción del modelo**, y reconoce no haber encontrado métricas satisfactorias para medir efectividad de implementación de MLOps en la literatura revisada.

**Interpretación breve *(no es cita textual del resultado final de los autores)*:** El texto concentra el “qué existe en publicaciones revisadas” (componentes, herramientas nombradas, pasos del proceso) más que un manual de adopción de una organización.

---

## 3. Aporte metodológico

**Datos textuales o parafrasis estrictamente ancladas al PDF:**

- **Ciclo de vida ML / proceso:** El PDF describe un proceso de modelo en MLOps con etapas y pasos (**Fig. 5** citada): (1) análisis de problema de negocio — declarado **tangencial y fuera del alcance analizado** para profundización; (2) “Data Preparation” con pasos: recolección de datos, selección de características, **validación de datos**, análisis, preprocesamiento; (3) “Model Preparation”: entrenamiento y evaluación; (4) **despliegue** (variants más simple vs. microservicio con API y CI/CD analogía DevOps cuando el modelo se expone vía REST); (5) **monitoreo** (métricas de calidad del modelo y datos; mención explícita de **data drift** y posible recomienzo del proceso ante anomalías).
- **Componentes arquitectónicos más citados:** El PDF cuenta **referencias por literatura revisada**: **Model Repository** (22 menciones como clase), **Model Orchestrator** (20), **CI/CD** (19), además **Feature Store**, bases de datos, **Model Monitoring**, clases “DevOps clásicas” (repositorio de código, containerización, orquestador de contenedores, gestión de colas, etc.).
- **Herramientas nombradas (ejemplos en el texto, no tabla completa reproducida):** Lista explícitos entre orquestadores Kubeflow, Airflow, Azure ML, Jenkins, etc.; Kubernetes y Docker mencionados frecuentemente; **MLflow** como ejemplo dominante de Model Repository según ocurrencias; **Feast** como única implementación de Feature Store presentada así en el párrafo citado en la extracción; monitoreo: **Evidently AI, Prometheus, Grafana, Neptune AI** mencionados.
- **CI/CD:** El papel distingue CI/CD orientado a contenedores y pruebas de software de modelos envueltos en APIs REST (**Flask/Django mencionados como ejemplos de marco**) del rol del orquestador de modelos (entrenamiento, monitoreo, scoring).
- **Monitoreo / validación:** “Model Monitoring” se describe como cálculo de métricas e indicadores (incluye **drift de datos**) distinto de solo almacenar métricas en el repositorio de modelos. La **validación de datos** se describe más amplia que en software típico (completitud, vigencia, consistencia, etc., según el texto).
- **Trazabilidad:** No identificado en la revisión inicial con el término único “trazabilidad”; el PDF sí habla de metadatos de modelos, repositorios y experimentación implícitos en procesos revisados — **explicitar sólo**: “metadata about these models”, “experiment-runs” en otros trabajos no es objeto directo titular este artículo.
- **RQ4 métricas de efectividad MLOps:** El PDF establece textualmente que **no** encontraron en la literatura analizada métricas que cumplan ese fin; menciona dos publicaciones relacionadas con **madurez** y **deployability**, sin equipararlos a KPIs de efectividad de implementación organizacional *(texto literal: “unable to find a satisfactory answer in this area” para RQ4)*.
- **Limitaciones declaradas:** Fuentes (trabajo en empresas no publicado); evolución rápida e **historia corta** de MLOps; arquitectura difícil de fijar “por años”; consulta a abril 2024 afectando conteos por año *(según §III.C del PDF)*.

**Interpretación metodológica:** La fuente es útil como **taxonomía y mapa procesal** revisado sistemáticamente, no como garantía de qué debe implementarse en un proyecto concreto.

---

## 4. Relación con SIDERAE-Blenkir

**Interpretación proyectada sobre el repositorio; no atribuye al proyecto prácticas no documentadas en el código:**

- La **separación de un microservicio** que expone inferencia (**REST** citado como patrón frecuente en la literatura revisada por los autores) es **conceptualmente paralela** — en SIDERAE se ha descrito comunicación Laravel → Flask; **no afirmamos** paralelismo 1:1 con referencias industriales ni pipeline MLOps completo.
- **Validación de entradas** y **persistencia del resultado**: el PDF describe validación fuerte en etapa “Data Preparation” en contextos de MLOps; **posible uso** es invocarlo como vocabulario técnico para justificar prácticas básicas de validación/contratos en API **solo en la medida** que la memoria o el código ya lo documenten.
- **Fallos:** El PDF discute pipelines y rollback implícitos al relanzar proceso completo ante degradación/monitoreo; **uso en SIDERAE** como horizonte o literatura comparativa sin afirmar reentrenamiento ni monitoreo productivos avanzados en el prototipo salvo evidencia futura explícita.
- **Monitoreo / reentrenamiento:** El texto revisado menciona drift y automatización CT en **otras citas/discusiones literarias** dentro del papel; para SIDERAE esto debe leerse como **evolución futura plausible**, no como estado implementado *(regla explícita del encargo)*.

---

## 5. Ideas clave aprovechables

1. Distinguir componentes (**orquestador, repositorio de modelos, CI/CD**) al describir una capa ML pequeña frente a un marco corporativo *(derivado del análisis de clases más frecuentes en la SLR).*  
2. Enmarcar validación no solo como “schema” si se argumenta ingestión datos/histórico, siguiendo el sentido textual de validación amplia *(idea del PDF).*  
3. Usar como **literatura revisada sistemáticamente** la ausencia consolidada de **métricas universales de éxito MLOps** para justificar criterios de proyecto propios sobrios *(afirmación contenida explícita en §IV.D).*  
4. Relacionar despliegue vía microservicio + API con el hilo sobre modelos envueltos en REST (**Flask** nombrado en el papel como ejemplo típico, no como elección empírica sobre SIDERAE).*  
5. Citar límites (empresa gray literature, rápido cambio tecnológico) al hablar de “madurez MLOps básica” en tesis *(sección Limitations del PDF).*  
6. Diferenciar monitorización como actividad de **cómputo activo de indicadores/drift**, no solo archivo de modelo.  
7. Vinculo académico con **IEEE Access**, **2025**, **Creative Commons Attribution 4.0** mencionados en primera página *(licencia y copyright según texto legal del PDF).*  
8. Comparar papel de **experiment tracking/metadata** donde se discuta trazabilidad de modelo/versiones cuando el proyecto cite MLflow solo si el repositorio refleje uso *(no declarado aquí).*  

Donde aparece paralelismo con SIDERAE, debe marcarse como **posible uso** en memoria posterior.

---

## 6. Limitaciones de la fuente

- Es **revisión sistemática** guiada por Kitchenham & Charters *(según cita textual en método)* sobre conjunto acotado (2615→41 artículos en flujo que describe).
- Alcance centrado en **publicaciones seleccionadas** y bases académicas; reconoce **sesgo** por prácticas no publicadas empresarialmente.
- **No describe** SIDERAE.
- Orientada a panorama **general** organizacional/industrial revisado vs. prototipo académico (**requiere adaptación**).
- Algunos **diagramas/tablas numéricos** sólo pueden citarse cualitativamente si no se reproduce la tabla íntegra.
- Métricas de éxito MLOps: **vacío declarado por los autores** en RQ4 (limitación contenida).

---

## 7. Uso recomendado dentro del proyecto

- `docs/metodologia/mlops-basico.md` *(marco vocabulario proceso/componentes).*  
- `docs/metodologia/metodologia-siderae.md` *(posicionamiento MLOps básico).*  
- `docs/metodologia/aplicacion-en-siderae.md` *(líneas de comparación Laravel–Flask y límites).*  
- `docs/metodologia/referencias-metodologia.md` *(después — no modificado en este encargo).*  
- `docs/referencias/matriz-fuentes.md`.  
- `docs/estado-del-arte/` **posteriormente** *(pendiente política editorial del equipo).*

---

## 8. Referencia APA preliminar

Woźniak, A. P., Milczarek, M., & Woźniak, J. (2025). *MLOps components, tools, process, and metrics: A systematic literature review*. *IEEE Access*, *13*, páginas 22166–22175 (aprox.). https://doi.org/10.1109/ACCESS.2025.3534990

*(“Aprox.” y formato final: páginas y grafía de ó en apellidos — revisar contra PDF impreso/visual; año y volumen según primera página)*
