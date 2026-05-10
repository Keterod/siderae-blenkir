# F-EDA-07 - A hybrid framework of statistical, machine learning, and explainable AI methods for school dropout prediction

## 1. Datos bibliográficos

- **Código:** F-EDA-07  
- **Título:** A hybrid framework of statistical, machine learning, and explainable AI methods for school dropout prediction *(texto oficial en PDF)*  
- **Autores:** Mst. Rokeya Khatun, Mithila Akter Mim, Md. Mahadi Tasin, Md. Minoar Hossain *(orden según página 1 PDF)*  
- **Año:** 2025 *(publicado 10 septiembre 2025 según página 1 PLOS ONE)*  
- **Tipo de fuente:** Artículo de investigación (*RESEARCH ARTICLE*)  
- **Revista / conferencia / repositorio:** *PLOS ONE*, vol. *20*(9), artículo e0331917  
- **DOI o enlace, si aparece:** https://doi.org/10.1371/journal.pone.0331917  
- **Ubicación del PDF:** `docs/referencias/pdfs/estado-del-arte/hybrid-xai-school-dropout-prediction-2025.pdf`

## 2. Tema principal

**Dato textual del PDF:** Analiza abandono escolar entre estudiantes de **6–24 años** usando encuesta nacional **MICS 2019 Bangladés**, combinando estadística inferencial (**regresión logística**) con clasificadores **Random Forest (RF)** y **Extreme Gradient Boosting (XGB)**; usa **hybrid feature selection** (significación estadística + importancia modelo/SHAP+RF scores según método); aplica interpretabilidad con **SHAP** (importancia global) y **LIME** (insights locales).

## 3. Contexto educativo estudiado

- **Mixto poblacional nacional**: niños/jóvenes 6–24 años en muestra anonimizada MICS 2019, urbano+rural todas divisiones distrito descritas método.  
- **No específico a un único colegio** ni curso LMS.

**Proximidad a SIDERAE-Blenkir:** Baja institucional, alta tema metodológica (risk factors + prediction + explanación modelos supervisados aplicados población escolarizada).

## 4. Variables consideradas

Selección inicial **11 variables** descritas sección Dataset (códigos MICS textual):

- HL6 (*age*)  
- HL4 (*sex*)  
- **windex5** (*wealth index*)  
- HH6 *(area/residence urbana rural)*  
- HH7 *(administrative division)*  
- melevel *(mother education)*  
- felevel *(father education)*  
- ethnicity *(group étnico)*  
- ED6 *(whether student completed grade)*  
- ED16B *(highest grade attained)*  
- ED9 codificado resultado **Attend_school_thisYear** — variable dependiente binaria 1 estudiantes sin asistencia anual declarada dropout para análisis (texto método detalla invertido orientación dropout vs attendance)

*(El PDF reporta proporción dropout aprox ~18.72% clase positiva después codificación textual.)*

## 5. Técnica, modelo o enfoque utilizado

Estadísticas descriptivas e inferenciales (OR, p-valor, CI 95%); encodings categóricos; pipelines ML supervisados RF/XGB; métricas: accuracy, precision, recall, F1, NPV, ROC-AUC textual results; interpretación SHAP+LIME combinada análisis híbridos selección características.

Mejor modelo reportado textual: **XGB** accuracy **94.41%**, precision **0.949**, recall **0.984**, F1 **0.9662**, NPV y ROC-AUC valores detallados sección resultado *(consultar valores RF secundarios en tabla si contraste requerido).*

## 6. Resultados o aportes principales

- XGB mejor desempeño global segundo métricas tabuladas.  
- Factores destacados texto abstract/conclusion incluyen age, sex, completed grade, last education grade, division, wealth index, father mother education *.  
- Se afirma aporte combinando estadística robusta no linealidades con transparencia explicativa para formulación política.

## 7. Limitaciones de la fuente

- **No aplicaron pesos muestrales MICS** —autores dicen modelo predictivo nacional representativo población no objetivo estadísticos poblacionales.  
- Dataset acceso UNICEF gated email permissions.  
- Contexto desarrollo económico/social Bangladesh ≠ otros países.  
- Riesgo clasificación alta reflejar leakage / codificación objetivo debe auditarse ante citas finas.  

## 8. Relación con SIDERAE-Blenkir

**Interpretación cautelosa:** Ilustra marco donde modelos supervisados alta capacidad discriminativa combinados SHAP/LIME permiten comunicar contribuciones predictoras a usuarios institucional —útil sólo nivel **marco técnico** si se documentará XAI dentro SIDERAE en futuros trabajos; **no hábito documentado en repo ⇒ no afirmación implementativa.**

**Aclaración explícita:** Presencia SHAP/XAI en este artículo **no confiere** disponibilidad en prototipo Blenkir hasta evidencia proyecto exista.

## 9. Uso recomendado dentro del estado del arte

- Predicción deserción / asistencia con Machine Learning  
- Explicabilidad del riesgo (*XAI en educación bajo modelo survey macro*)  
- Brecha identificada (*combinar estadística+híbridos feature selection+XAI comunicación).*  

## 10. Referencia APA preliminar

Khatun, M. R., Mim, M. A., Tasin, M. M., & Hossain, M. M. (2025). A hybrid framework of statistical, machine learning, and explainable AI methods for school dropout prediction. *PLOS ONE*, *20*(9), Article e0331917. https://doi.org/10.1371/journal.pone.0331917  
