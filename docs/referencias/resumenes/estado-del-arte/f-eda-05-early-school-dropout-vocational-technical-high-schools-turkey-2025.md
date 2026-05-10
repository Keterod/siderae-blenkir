# F-EDA-05 - Detection of Early School Dropout in Vocational and Technical High Schools in Turkey

## 1. Datos bibliográficos

- **Código:** F-EDA-05  
- **Título:** Detection of Early School Dropout in Vocational and Technical High Schools in Turkey *(según página de título; grafía “T urkey” por salto de línea en extracción)*  
- **Autores:** Özgür Korkmaz y Mehmet Nafiz Aydın *(grafía de apellidos según primera página PDF)*  
- **Año:** 2025 *(SAGE Open, July-September 2025)*  
- **Tipo de fuente:** Artículo de investigación (*Original Research*)  
- **Revista / conferencia / repositorio:** *SAGE Open*  
- **DOI o enlace, si aparece:** https://doi.org/10.1177/21582440251370443  
- **Ubicación del PDF:** `docs/referencias/pdfs/estado-del-arte/early-school-dropout-vocational-technical-high-schools-turkey-2025.pdf`

## 2. Tema principal

**Dato textual del PDF:** Investiga factores tempranos de deserción en escuelas vocacionales y técnicas de Turquía mediante encuesta detallada a estudiantes de una de las mayores instituciones de ese tipo en Estambul; captura **35** características; aplica clasificadores de ML declarando alta precisión predictiva para identificar estudiantes en riesgo; usa **SMOTE** para clase minoritaria; el árbol de decisión (*Decision Tree*) con SMOTE muestra mejor desempeño relativo entre modelos contrastados *(valores específicos p. ej. precisión/recall listados tablas Table 3 en PDF)*.

**Interpretación:** Caso país-específico de analítica educativa vocacional-secondary con énfasis familiar-socioeconomía.

## 3. Contexto educativo estudiado

- **Educación técnica/vocacional secundaria** en Turquía — un centro específico de gran tamaño en Estambul.  
**Proximidad a SIDERAE-Blenkir:** Parcial temáticamente (riesgo/deserción, factores sociofamiliares) pero **contexto institucional, país y vía tecnológico-vocacional** difieren si SIDERAE se orientó a ciclo primario/secundaria general otro país.

## 4. Variables consideradas

**Dato textual (Tabla 1 del PDF, columna inglés —35 factores declarados más identificador de deserción final):**

Educación maternal/paternal máxima primaria, ser hijo único, cinco o más hermanos, padres por separado, divorcio, vivir sólo madre/padre, madre/padres fallecidos, otros estatus familiares (abuelos, otros familiares, acogimiento, residencia institución protección infantil —con conteos muestrales frecuentemente cero en varias modalidades según tabla), presencia crónico familiar, salud mental familiar, dependencia alcohol/sustancias familia, antecedentes penales familia, trabajadores estacionales familia, violencia doméstica, alta capacidad diagnosticada (*gifted*), informe necesidades educación especial, enfermedad crónica propia estudiante, salud mental propia estudiante, medidas consejería/educativa, **dificultad financiera declarada**, **absentismo continuo**, trabajo mientras cursa estudios (**working while studying**), **bajo rendimiento académico** (*Low academic performance*), pertenencia grupo pares “riesgo”, etiqueta resultado **school dropout** muestra.

**Dato textual complementario:** antes de SMOTE, el conjunto descrito comprende **220** observaciones con **25** casos de deserción etiquetados.

## 5. Técnica, modelo o enfoque utilizado

- Encuesta + dataset pequeño (*small dataset* mencionado en límites).  
- Algoritmos comparados textualmente incluyen **Decision Trees, Logistic Regression, Random Forest, Naive Bayes** y otros listados antes de/decisiones SMOTE *(ver sección method/results Table 2/3)*.  
- **SMOTE** para sobremuestreo de clase minoritaria.  
- Análisis de importancia de atributos con árboles *(texto posterior de resultados).*

## 6. Resultados o aportes principales

- El **Decision Tree + SMOTE** se describe como el más confiable dentro del conjunto probado *(tablas muestran p. ej. precision ~0.947 y recall citados en párrafos de resultados en extracción de texto para una configuración; verificar valores exactos al citar).*  
- El estudio enlaza ML con necesidad intervención temprana y políticas institucionales.

## 7. Limitaciones de la fuente

- Dataset pequeño y columnas zero-filled según **abstract**.  
- Un único establecimiento en Estambul: **falta de generalización** geográfica/institucional.  
- Contexto nacional turco político-educativo (historia coeficientes universidad, MoNE mencionados) específico.  
- Posible clasificación alta inflada si desbalance y evaluación no son interpretados cautelosamente *(revisión de protocolo).*  

## 8. Relación con SIDERAE-Blenkir

**Interpretación cautelosa:** Aporta argumento cualitativo sobre que factores sociofamiliares y financieros se integran repetidamente como predictores fuertes de intención/deserción en estudios localesizados ML.

**Aclaración:** SIDERAE no se caracteriza aquí como despliegue en escuela vocacional turca ni garantiza igual conjunto instrumental de datos.

## 9. Uso recomendado dentro del estado del arte

- Deserción estudiantil  
- Predicción con Machine Learning *(dataset encuesta secundaria)*  
- Factores sociofamiliares e intervención educativa (*discursos de política/implicancia, no efectos RCT propios aquí).*  

## 10. Referencia APA preliminar

Korkmaz, Ö., & Aydın, M. N. (2025). Detection of early school dropout in vocational and technical high schools in Turkey. *SAGE Open*. https://doi.org/10.1177/21582440251370443 *(preliminar: confirmar vol/issue/pagination exactos en archivo editorial descargado).*  
