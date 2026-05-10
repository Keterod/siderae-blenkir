# Machine learning operations landscape: platforms and tools

## 1. Datos bibliográficos

- **Título:** Machine learning operations landscape: platforms and tools
- **Autores:** Lisana Berberi · Valentin Kozlov · Giang Nguyen · Judith Sáinz-Pardo Díaz · Amanda Calatrava · Germán Moltó · Viet Tran · Álvaro López García *(orden y grafía como en primera página del PDF)*
- **Año:** 2025 *(“© The Author(s) 2025”; publicación en línea 17 March 2025 según primera página)*  
- **Tipo de fuente:** Artículo académico de revista (estudio/análisis de plataformas MLOps y marco evaluativo)
- **Revista:** *Artificial Intelligence Review* (**2025**), volumen/tomo **58**, número de artículo **167** *(como aparece: “Artificial Intelligence Review (2025) 58:167”)*
- **DOI:** https://doi.org/10.1007/s10462-025-11164-3 *(en el PDF aparece formato compacto tipo `doi.org` en la primera página)*
- **Ubicación del PDF:** `docs/referencias/pdfs/mlops/mlops-landscape-platforms-tools-2025.pdf`

**Licencia mencionada al final:** Creative Commons Attribution 4.0 International License *(bloque textual en la sección Declarations/Open Access).*  

**Funding:** trabajo financiado por la UE vía proyecto Horizon Europe **AI4EOSC**, número de **grant** según texto del PDF (**101058593**) *( páginas de conclusiones/información editorial del PDF).* 

**Tipo:** Dato textual del PDF.

---

## 2. Tema principal

El artículo presenta una **valoración/comprensión (“assessment”)** de plataformas MLOps de las que examina **16 herramientas de código abierto ampliamente usadas** (texto abstracto/propio). Combina tres pasos: **análisis de características** de plataformas, **valoración del crecimiento de estrellas en GitHub en los últimos 5 años**, y una **métrica de puntuación ponderada** (“weighted scoring”). Del proceso derivan elementos como **diagrama de decisión tipo flowchart**, discusión de **detección de drift**, comparación entre **Integración/despliegue continuos CI/CD** orientados al ML incluyendo la noción CT (*Continuous Training/Testing*, descrita como propiedad nueva en sistemas ML en la **Tabla 1** reproducida textualmente como CI/CD vs CT vs CD), contrastes con **DevOps/DataOps/ModelOps/AIOps**, y un modelo de ciclo en **BPMN** simplificado (**Figura 1** mencionada) con pasos: crear/actualizar modelo, entrenar/probar, evaluar, **almacenar en registro**, desplegar, **monitorizar** y ramas de decisión si la precisión no es satisfactoria o hay degradación (**retraining** mencionado en la descripción de la figura textual).

*(Mezcla: datos textuales de abstracto y §1–2; cualquier extrapolación a industrias concretas abajo debe citarse solo si aparece textual.)*

La conclusión (§“3 Conclusion and future work”) indica especialmente que la **monitorización del rendimiento del modelo aparece como la capacidad “least supported feature” entre las herramientas analizadas** — transcripción sintética fiel del enunciado en el PDF sobre “special focus… model performance monitoring as the least supported feature…”.

---

## 3. Aporte metodológico

**Desde el PDF:**

- **Ciclo de vida ML:** Proceso iterativo textual con BPMN (**Figura 1** citada §1.1): funciones mencionadas (creación/actualización, train/test con registro métricas, evaluar, registrar modelo, inferencia en producción, monitorizar). Pasos ejecutables manual o automático según el texto.
- **Procesos MLOps:** MLOps llamado práctica ingeniería que automatiza/streamline ciclo vida ML; toolchain que incluye *version control*, *testing frameworks*, *deployment automation*, **CI/CD**, **monitoring**, **CT**.
- **Herramientas:** Lista de **capacidades** evaluadas (abrevaturas del propio papel): Orchestration **(O)**, Distributed Training **(DT)**, Code Management **(CM)**, Model Development **(MDV)**, Model Testing/Validation **(MTV)**, Model Inference **(MI)**, Model Deployment **(MDP)**, Experiment Tracking and Metadata Store **(ETMS)**, Data Versioning and Management **(DVM)**, Model Performance Monitoring **(MPM)**, también FM/MVM descritos §2.1.1. **Tabla 3** asigna nivel de soporte (**full/partial percentages**) por producto (**MLflow**, **Prefect**, **Kubeflow**, etc.—listados pero no reproducimos fila por fila en este resumen corto para no extrapolar celdas no verificadas carácter a carácter).
- **Marco conceptual / selección de plataforma:** La fuente desarrolla un marco por **capacidades** y un **diagrama de flujo tipo flowchart para seleccionar herramientas** (texto Fig. 7 describe categorías ejemplo: plataforma comprehensiva con **ClearML, ZenML, Polyaxon**, soluciones “lighter” con **≈80%** cobertura **MLRun Flyte**, necesidades parciales **Prefect** para orquestación — **lista ilustrativa textualmente no exhaustiva**).
- **CI/CD / CT:** **Tabla 1** texto: CI extiende a datos/schemas/modelos; CD a pipelines entrenamiento; CT reentrena y sirve modelo automático **(sic según papel citando Google 2020 en la narrativa bibliográfica del artículo).**
- **Monitoreo / drift:** Sección contribuciones menciona herramientas **EvidentlyAI** incorporada versus **Alibi-detect custom**; texto introductorio menciona drift/concept drift y CT para reentreno automático cuando “needed” (**cita intermediaria Kreuzberger / Google dentro del papel**).
- **Validación MTV:** definido textualmente sobre pruebas unitarias comportamiento modelo y métricas en datos validación/prueba.
- **Limitaciones declaradas §3:** Revisión restringida a **herramientas gratuitas código abierto**; posible fuerza pero limitación para empresas pro software propietario; futuro trabajo eticidad, benchmarks, feedback usuarios (**dato textual**).

**Interpretación:** Documento sirve sobre todo para **clasificar expectativas** de plataformas y ubicar donde el proyecto se queda corto versus industria (**sin etiquetar ese gap como fracaso**, por regla ética comparativa sobria para tesis académica).

---

## 4. Relación con SIDERAE-Blenkir

**Posibles usos**, sin afirmaciones no verificadas en el repositorio:

- Servir de **contexto léxico**: microservicios predicción, contrato API, versioning/datos (*comparación conceptual*: el paper habla niveles automatización industriales CT/monitoring intensivos).
- **Validación entrada / persistencia resultado**: paralelo débil con MTV + inferencia (**MI**) + **persistencia modelo en registro** en marco papel — SIDERAE: solo si código ya documenta igual.
- **Manejo de fallos**: el BPMN muestra bifurcación ante mala precision/degradacion; usar como vocabulario conceptual, **sin** declarar bifuración automática si no existe.
- **Trazabilidad básica:** ETMS/DVM papel vs logging manual en proyecto: **solo comparación cualitativa** si proyecto lo describe así.
- **Monitoreo / re-pipeline training:** papel subraya MPM como soporte relativamente menor en lista analizada para open source y plantea mejoras drift — **literatura aspiracional**, no backlog producto.

---

## 5. Ideas clave aprovechables

1. Articular **CI/CD/CT diferenciados** en discurso cuando se explique porque ML pipeline ≠ sólo código apps. *(Tabla 1 paper)*  
2. Usar resultado **MPM menor soporte** para matizar modestia de monitorización cualquier prototipo estudiantil. *(Conclusión)*  
3. Reconocimiento **dual open-source vs proprietary** cuando se motive elección tecnológica económica. *(Limitaciones)*  
4. **Lista O/DT/CM/MTV/DVM/MPM** como checklist retórico al describir mejoras futuras **sin sustituir** el backlog funcional propio del proyecto.  
5. Financiación UE **AI4EOSC**: solo como dato contextual del artículo, no aplicable a financiación de SIDERAE salvo nueva evidencia (*texto Funding del PDF sobre los autores*).  
6. **Diagrama tipo flowchart** del artículo como referencia cualitativa al comparar el stack propio frente al panorama (**no reproducir Fig. 7** sin revisar normas citación institucional de figuras ajenas).  
7. Vincular en discurso **Experiment Tracking / Metadata Store (ETMS)** con la idea de registros reproducibles donde el proyecto así lo admita (**no atribución automática al repo**).  
8. Mencionar riesgos **ethical AI / benchmarking** mencionados en **future work** como agenda investigación campo. *(no conclusiones proyecto)*  

---

## 6. Limitaciones de la fuente

- Artículo de **revista** con mezcla análisis + material web/blogs/industria según metodología descrita §1.2 (20+ artículos académicos + >10 blogs proveedores cloud): **alcance híbrido**.  
- **No describe SIDERAE**.  
- Enfoque **open source** **excluye** muchas soluciones empresariales (texto limitación).  
- Posible **sesgo popularidad GitHub stars** (metodología explícita).  
- Prácticas empresariales **completas** descritas no equivalen a **MLOps básico** en prototipo académico.  
- Estado del arte MLOps: el paper mismo posiciona aportes relativos sobre literatura revisada — **requiere cautela**.

---

## 7. Uso recomendado dentro del proyecto

- `docs/metodologia/mlops-basico.md`  
- `docs/metodologia/metodologia-siderae.md`  
- `docs/metodologia/aplicacion-en-siderae.md`  
- `docs/metodologia/referencias-metodologia.md` *(posteriormente)*  
- `docs/referencias/matriz-fuentes.md`  
- `docs/estado-del-arte/` **posteriormente**

---

## 8. Referencia APA preliminar

Berberi, L., Kozlov, V., Nguyen, G., Sáinz-Pardo Díaz, J., Calatrava, A., Moltó, G., Tran, V., & López García, Á. (2025). Machine learning operations landscape: Platforms and tools. *Artificial Intelligence Review*, *58*, Article 167. https://doi.org/10.1007/s10462-025-11164-3  

*(Nombre inicial “Valentin”: verificar grafía frente al PDF página de autores; volumen/issue y número de artículo — revisar formato editorial final.)*
