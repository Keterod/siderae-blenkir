# F-EDA-04 - Application of the performance of machine learning techniques as support in the prediction of school dropout

## 1. Datos bibliográficos

- **Código:** F-EDA-04  
- **Título:** Application of the performance of machine learning techniques as support in the prediction of school dropout *(título en página 1 según PDF)*  
- **Autores:** Auria Lucia Jiménez-Gutiérrez, Cinthya Ivonne Mota-Hernández, Efrén Mezura-Montes, Rafael Alvarado-Corona  
- **Año:** 2024 *(Scientific Reports 14:3957; aceptación febrero 2024)*  
- **Tipo de fuente:** Artículo de investigación empírica  
- **Revista / conferencia / repositorio:** *Scientific Reports*  
- **DOI o enlace, si aparece:** https://doi.org/10.1038/s41598-024-53576-1  
- **Ubicación del PDF:** `docs/referencias/pdfs/estado-del-arte/school-dropout-ml-secondary-mexico-2024.pdf`

## 2. Tema principal

**Dato textual del PDF:** El artículo plantea construir modelos predictivos para deserción escolar usando datos abiertos del Censo y encuestas del INEGI (2010, 2020, EIC 2015), procesando más de **1 080 782** registros homogeneizados (personas 15 años o más relacionadas con bachillerato y educación superior según filtros descritos); compara redes neuronales, SVM, regresión Ridge/Lasso, optimización bayesiana sobre redes bayesianas y Random Forest; el texto declara alta confiabilidad (*reliability*) para algunas técnicas, destacando redes neuronales multicapa con pérdidas reportadas y ANN hasta ~99% en conclusiones *(cifras exactas dependen sección conclusiones/tablas).*  

*(Parámetro descriptivo inicial del artículo: meta declarada en resumen sobre modelo con “90% reliability” antes de desarrollo técnico; verificar formulación estadística vs. uso coloquial al citar oficialmente.)*

## 3. Contexto educativo estudiado

- **Mexico — datos censales y socio-demográficos a nivel población**, no registros longitudinal-clase única por escuela como en LMS.  
- Enfoque en trayectorias de personas en educación media superior y superior según filtros del estudio (**secundaria superior** y **educación superior** en el problema de política nacional descrito).

**Proximidad a SIDERAE-Blenkir:** Baja-medida por **unidad de análisis** (censo poblacional masivo versus seguimiento de un colegio presencial específico) y nivel educativo medio-superior; útil sobre todo para política poblacional de factores correlacionados de deserción a escala país.

## 4. Variables consideradas

Lista **Table 2** del PDF *(etiquetas y descripción sintética según tabla)*: código de entidad federativa *(ENT)*, municipio *(MUN)*, sexo *(SEXO)*, edad *(EDAD)*, uso de servicios de salud *(SERSALUD)*, pertenencia indígena/idioma *(PERTE_INDIGENA)*, nacionalidad/lugar de nacimiento *(NACIONALIDAD)*, asistencia escolar *(ASISTEN)*, escolaridad/último grado *(ESCOLARI, NIV ACAD)*, años acumulados *(ESCOACUM)*, estado civil/conyugal, situación laboral, tenencia de computadora/celular/internet en el hogar, ingreso laboral mensual del hogar *(INGTRHOG)*, tamaño de localidad *(TAMLOC)*, año del censo origen *(CPV)*, variable objetivo deserción *(DES)*.

## 5. Técnica, modelo o enfoque utilizado

**Dato textual del PDF:** Metodología KDD: selección, preprocesamiento, extracción de conocimiento, interpretación y evaluación; implementación en Python (Anaconda, scikit-learn, NumPy, Pandas, Matplotlib según texto); técnicas: multilayer perceptron ANN (ADAM, relu en hallazgos de conclusiones), redes bayesianas / optimización bayesiana citada como “Bayesian optimization”, Random Forest, Support Vector Regression/Machines según apartados técnicos, Ridge/Lasso; validación mencionada con k-cross-validation y errores fuera-de-bolsa donde aplica RF.

## 6. Resultados o aportes principales

- Se reporta que varias implementaciones tienen fiabilidades superiores al **91%** y redes neuronales con desempeño destacado (**99%** en conclusiones, con pérdidas del orden 2.3e-4 señalado en proceso ANN train).  
- Random Forest aparece como técnicamente costoso para los equipos de escritorio disponibles fuera del entorno supercomputacional descrito (*tiempos de proceso y bloqueos en hardware limitado mencionados*).  
- El artículo vincula resultados al diseño futuro declarado de una “open platform” para instituciones (*conclusiones como trabajo futuro, no resultado implementado aquí).*  

## 7. Limitaciones de la fuente

- Inferencia poblacional mediante censos/agrupación —no equivalente a riesgo académico en tiempo real institucional.  
- Sesgo muestral/desbalance y calidad declarada después de filtros debe evaluarse con tablas estadísticas cuando se cite finamente.  
- Objetivo “90% reliability” inicial vs métricas reales debe contrastarse antes de comunicación pública.  
- México ≠ contexto institucional de otros países/escuelas.

## 8. Relación con SIDERAE-Blenkir

**Interpretación cautelosa:** Refuerza que variables socioeconómicas/digital divide y trayectorias de grado están asociadas estadísticamente con deserción en datos demográficos nacionales; sirve como contraste: prototipo institucional de SIDERAE no opera sobre censo poblacional nacional.

**Aclaración:** No se extrapola interoperabilidad de modelos censales a la arquitectura de SIDERAE sin validación específica; SIDERAE no se caracteriza aquí como plataforma gubernamental de INEGI.

## 9. Uso recomendado dentro del estado del arte

- Deserción estudiantil *(ámbito macro / políticas)*  
- Predicción de deserción con Machine Learning  
- Brecha entre analítica censal institucional y sistemas locales de observación estudiantil  

## 10. Referencia APA preliminar

Jiménez-Gutiérrez, A. L., Mota-Hernández, C. I., Mezura-Montes, E., & Alvarado-Corona, R. (2024). Application of the performance of machine learning techniques as support in the prediction of school dropout. *Scientific Reports*, *14*, Article 3957. https://doi.org/10.1038/s41598-024-53576-1  
