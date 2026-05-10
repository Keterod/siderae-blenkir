# F-EDA-02 - Machine learning predicts upper secondary education dropout as early as the end of primary school

## 1. Datos bibliográficos

- **Código:** F-EDA-02  
- **Título:** Machine learning predicts upper secondary education dropout as early as the end of primary school *(título según primera página)*  
- **Autores:** Maria Psyridou, Fabi Prezja, Minna Torppa, Marja-Kristiina Lerkkanen, Anna-Maija Poikkeus, Kati Vasalampi *(según PDF)*  
- **Año:** 2024 *(Scientific Reports 14:12956)*  
- **Tipo de fuente:** Artículo de investigación empírica  
- **Revista / conferencia / repositorio:** *Scientific Reports* (Nature Portfolio)  
- **DOI o enlace, si aparece:** https://doi.org/10.1038/s41598-024-63629-0  
- **Ubicación del PDF:** `docs/referencias/pdfs/estado-del-arte/primary-secondary-dropout-ml-2024.pdf`

## 2. Tema principal

**Dato textual del PDF:** El estudio desarrolla modelos de clasificación supervisada para distinguir abandono y no abandono de la educación secundaria superior (*upper secondary*) en Finlandia, usando un seguimiento longitudinal de 13 años (desde kindergarten hasta el tercer año de secundaria superior), con datos disponibles hasta el final de la educación básica (*Grade 9*) o hasta el final de primaria (*Grade 6*) según el escenario de modelado.

**Interpretación:** Aporta evidencia sobre predicción temprana de trayectorias de deserción en transición a educación post-obligatoria.

## 3. Contexto educativo estudiado

- **Primaria y secundaria baja (comprehensive school):** datos desde kindergarten hasta Grade 9.  
- **Secundaria superior:** resultado de completitud/no completitud a ~3,5 años del inicio de upper secondary, según registros escolares.  
- **Vocacional y general upper secondary:** el texto indica que se enfocan estudiantes que ingresaron a escuela general upper secondary o vocacional.

**Proximidad a SIDERAE-Blenkir:** Parcial: comparte interés en señales tempranas y datos académicos/conductuales desde etapas escolares tempranas, pero el país, el sistema (*Finland*, registros finlandeses) y la definición operativa del desenlace son distintos a un prototipo institucional no documentado igual en el repo.

## 4. Variables consideradas

Según las secciones *Measures* y *Results* del PDF (dominos y ejemplos textuales): antecedentes familiares (p. ej. educación parental, estatus socioeconómico), factores individuales (sexo, ausencias escolares, *burn-out* escolar), conducta (conducta prosocial, hiperactividad), motivación (*self-concept*, *task value*, relación alumno-profesor, engagement en clase), acoso escolar (*bullied*, *bullying*), salud y consumo (*smoking*, *alcohol use*), uso de medios, habilidades cognitivas (p. ej. *rapid naming*, *Raven*) y resultados académicos (fluidez lectora, comprensión lectora, puntuaciones PISA mencionadas, aritmética, multiplicación). El estudio menciona inicialmente evaluación de 586 rasgos y 311 características tras filtros por datos faltantes.

## 5. Técnica, modelo o enfoque utilizado

**Dato textual del PDF:** Cuatro clasificadores supervisados enfoque datos desbalanceados (paquete *imbalanced-learn* citado como *Imbalanced Learning Python package*): Balanced Random Forest (B-RandomForest), Easy Ensemble (Adaboost Ensemble), RSBoost (Adaboost) y Bagging Decision Tree; validación cruzada estratificada 6 folds; métricas incluyen AUC, matrices de confusión, precisión balanceada y otras métricas descritas en el artículo.

## 6. Resultados o aportes principales

- Los modelos alcanzan AUC medio de **0,61** con datos hasta Grade 6 y **0,65** con datos hasta Grade 9 con el clasificador B-RandomForest (mejor rendimiento entre los cuatro comparados en el texto reportado para AUC/discriminación principal).  
- Con datos hasta Grade 9, balanced mean accuracy declarada ~0,61 para B-RandomForest; con datos hasta Grade 6 ~0,59.  
- Los autores destacan que las variables de mayor ranking en importancia pertenecen sobre todo a dominios de habilidades cognitivas y desempeño académico temprano (lectura, aritmética, etc.).  
- La discusión enfatiza que la aplicación práctica debe ir precedida de más datos, validación e investigación independiente correlacional/causal.

## 7. Limitaciones de la fuente

- Contexto país y sistema educativo específico (Finlandia).  
- Muestra con homogeneidad étnico-cultural descrita en el texto.  
- Alta dimensionalidad y problema de dimensionalidad mencionados explícitamente.  
- Desenlace centrado en **upper secondary dropout/completion registrado**, no en riesgo académico intracurricular de un establecimiento concreto.  
- Advertencia propia sobre necesidad de validación adicional antes de despliegue.

## 8. Relación con SIDERAE-Blenkir

**Interpretación cautelosa:** Ilustra la viabilidad parcial de señales tempranas multidominio para clasificar trayectorias de salida tardía de la escolaridad obligatoria-superior, coherente con la idea analítica de combinar resultado académico, ausencias y otros factores en modelos supervisados —siempre que los datos institucionales del prototipo coincidieran tipo y calidad *(no garantizado aquí)*.

**Aclaración:** SIDERAE-Blenkir no queda caracterizado como equivalente longitudinal finlandés ni como sistema de política nacional; cualquier paralelo es bibliográfico, no técnico.

## 9. Uso recomendado dentro del estado del arte

- Deserción estudiantil  
- Predicción con Machine Learning  
- Learning Analytics / Educational Data Mining  
- Identificación temprana de estudiantes en riesgo *(como problema de clasificación estadística, sin confundir con implementación institucional de SIDERAE)*  

## 10. Referencia APA preliminar

Psyridou, M., Prezja, F., Torppa, M., Lerkkanen, M.-K., Poikkeus, A.-M., & Vasalampi, K. (2024). Machine learning predicts upper secondary education dropout as early as the end of primary school. *Scientific Reports*, *14*, Article 12956. https://doi.org/10.1038/s41598-024-63629-0
