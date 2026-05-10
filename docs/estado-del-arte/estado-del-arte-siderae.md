# Estado del arte de SIDERAE-Blenkir

## 1. Introducción

SIDERAE-Blenkir se ubica en el campo de los sistemas de apoyo al seguimiento académico, la detección de riesgo académico y las alertas tempranas en contexto escolar. La documentación del repositorio lo presenta como prototipo académico funcional que integra gestión de estudiantes, registro de notas y asistencia, variables socioeconómicas, cálculo de riesgo vía Flask, y alertas e intervenciones acotadas por permisos; la salida debe leerse como apoyo cognitivo a docentes y directivos, no como sustituto del juicio pedagógico ni de políticas institucionales definidas fuera del software.

Este estado del arte sintetiza, con la prudencia que exige usar sólo fichas y matrices del proyecto (F-EDA-01 a F-EDA-08), literatura sobre: modelos predictivos educativos; riesgo y deserción en distintos niveles; sistemas de alerta temprana (*early warning systems*, EWS); combinación típica de variables académicas, de asistencia, conductuales o sociofamiliares según fuente; e implicaciones de intervención y seguimiento tras la alerta.

## 2. Estrategia de búsqueda y selección de fuentes

Las ocho fuentes provienen de PDF académico revisado manualmente y sintetizado en `docs/referencias/resumenes/estado-del-arte/` y `docs/estado-del-arte/matriz-comparativa.md`, con búsqueda académica asistida donde aplica. Se privilegió cercanía temática con primaria, secundaria o secundaria superior y publicación aproximada 2022–2025 dentro del lote disponible, incorporando revisiones y estudios poblacionales sólo como marco cuando aportaban pertinencia conceptual. Al no constar un protocolo público con nombres de bases de datos aplicable a este equipo más allá de los archivos citados, aquí se usa la fórmula de búsqueda asistida más revisión manual de PDFs priorizados por pertinencia, evitando depender principalmente de educación superior, Moodle o *e-learning* no centrales para el seguimiento escolar, admitiendo que una revisión sistemática agrega asimismo literatura con LMS y entornos en línea como parte de su corpus.

## 3. Riesgo académico y deserción estudiantil

Los trabajos convocados describen deserción o riesgo como multifactorial. En Finlandia, un modelo longitudinal cruza familia, ausencias, conducta, motivación, participación, acoso, salud y medios, cognición y rendimiento lecto-matemático con datos disponibles hasta el final de la educación básica (*grade* 9) o hasta el final de primaria (*grade* 6), según el escenario de modelado descrito en la fuente; las variables de mayor importancia relativa se agrupan con frecuencia en cognición y desempeño temprano (F-EDA-02). El desenlace clasificado en ese artículo corresponde a abandono o no abandono en educación secundaria superior (*upper secondary*) en Finlandia, distinto tanto del riesgo académico intracurricular de SIDERAE como del nivel institucional del prototipo. En México, con datos censales, intervienen sexo, edad, asistencia, escolaridad, indigenismo, nacionalidad y variables de hogar o localidad vinculadas al abandono en la unidad de análisis del estudio (F-EDA-04). En Turquía, encuesta vocacional-técnica incorpora estructura familiar, salud, absentismo, bajo rendimiento y dificultad financiera entre muchos atributos (F-EDA-05). En Bangladés, la encuesta MICS combina edad, sexo, riqueza, residencia, división administrativa, educación parental, etnia y trayectoria de grado con un desenlace binario construido desde asistencia anual (F-EDA-07). En conjunto sugieren que no basta con mirar únicamente calificaciones cuando el fenómeno se modela con amplitud de dominios disponibles en cada diseño.

Frente a SIDERAE, el prototipo trabaja índices de *riesgo académico* y alertas con datos institucionales típicamente académico-sociales, sin pretender clasificar «deserción real» poblacional como en estudios muestrales o censales antes citados.

La brecha consiste en justificar sólo dimensiones efectivamente observadas en el colegio, explicitando cuando el nivel educativo, la definición de desenlace o el país divergen respecto del prototipo.

## 4. Predicción de deserción estudiantil con Machine Learning

(F-EDA-01), revisión sistemática con 124 estudios finales, contrasta métodos ML (SVM, ANN, DT, entre otros mencionados) con modelos estadísticos clásicos y advierte sobre sesgos, transparencia y preprocesamiento heterogéneo. Finlandia usa Balanced Random Forest, ensembles y Bagging Decision Tree con validación estratificada (F-EDA-02). México desarrolla un flujo tipo KDD con ANN, SVM, redes bayesianas, Random Forest y Ridge/Lasso (F-EDA-04). Turquía compara regresión logística, DT, RF y Naïve Bayes con SMOTE (F-EDA-05). Bangladés añade RF, XGBoost e interpretación mediante SHAP y LIME dentro de ese artículo (F-EDA-07).

SIDERAE incorpora orientación institucional hacia seguimiento asistido al integrar Laravel y el servicio Flask (`/predict`, según README) con persistencia del índice de riesgo descrita en `docs/arquitectura/resumen-arquitectura.md`. El mismo README clasifica ese componente ML como prototipo determinístico, explícitamente no equivalente, salvo nueva evidencia de código, a *pipelines* con Random Forest, SVM ni XGBoost citados ampliamente en la literatura, ni a métodos SHAP ni LIME, que sólo aparecen combinados en (F-EDA-07) entre las fichas disponibles aquí sin que dichas técnicas estén declaradas como implementadas para SIDERAE.

La brecha es preservar esa distancia metodológica en memorias públicas sin atribuir al prototipo reproducibilidad de métricas o algoritmos que sólo constan en artículos ajenos.

## 5. Learning Analytics y Educational Data Mining

(F-EDA-01) enlaza uso de datos con pronóstico de rendimiento, identificación temprana y agenda de interpretabilidad; su síntesis abarca entornos LMS en la literatura agregada, lo que muestra dispersión contextual del campo. (F-EDA-03) inserta minería sobre datos públicos Marruecos: etapa *collect/analyze/detect/alert/act* articulada con EMIS multimódulo según texto de la fuente.

(F-EDA-08) permite leer esa misma línea desde la evaluación de impactos: integra datos administrativos tipo EWS y dispositivos institucionales de seguimiento, pero el resultado estadístico publicado debe recordarse cuando se reflexiona sobre efectos (tratado en la sección 6).

En relación con SIDERAE, el prototipo actúa como organizador institucional de registros locales y disparador humano revisable de alertas, dentro del flujo documentado.

La brecha es no confundir almacenamiento y reglas institucionalmente acotadas con estandarización del campo empírico ni con obligación de impactos medibles externos al alcance técnico del repositorio.

## 6. Sistemas de alerta temprana académica

Los EWS de (F-EDA-03) modelan ciclo primario nacional con alto volumen y énfasis en KNN como reporta el ABSTRACT reproducido en ficha proyecto. (F-EDA-06) predice longitudinalmente dentro de año escolar indicadores *attendance-behavior-course performance* a partir del SDQ inicial y controles disciplinarios y plantea complementar registros administrativos tardíos con cribado según objetivos declarados por sus autores. (F-EDA-08) evalúa experimentalmente modelo IKO en secundaria superior noruega: tras dos años, la ficha proyecto recoge ausencia de efectos significativos en absentismo ligado a clases, culminación ni resultados académicos medidos. Esa última evidencia sirve especialmente para matizar que tener alerta tecnológico-institucional no equivale a garantizar mejora automática observable en indicadores públicos cuando el seguimiento no consolida resultados estadísticos bajo ese diseño específico.

SIDERAE aproxima EWS mediante alertas e intervenciones gestionadas según los módulos documentados en el repositorio, con datos académico-sociales institucionalmente capturados, sin equivalentes documentados a EMIS multimódulo nacional, RCT cluster ni Django de (F-EDA-03); la prudencia exigida por (F-EDA-08) aplica igualmente como límite a expectativas de impacto medible.

La brecha es enfatizar rutas revisables persona-a-persona y evaluaciones futuras propias antes de extrapolar mejoras estudiantiles.

## 7. Intervenciones académicas y seguimiento estudiantil

Las fuentes convienen cualitativamente en que clasificar estudiantes no sustituye apoyo efectivo: (F-EDA-03) explicita hasta acción institucional; (F-EDA-05) discute intervención temprana ligada política institucional; (F-EDA-06) plantea expansión mediante cribado emocional-conductual; (F-EDA-07) argumenta comunicación modelada hacia formulación política; (F-EDA-08) ilustra nulidad estadística de outcomes pese infraestructura EWS institucional, recordatorio contextual sin generalizar causa a otros software.

SIDERAE ofrece módulos de intervención y cierre con permisos diferenciados, según README y `docs/arquitectura/resumen-arquitectura.md`, sin automatizar prácticas pedagógicas ajenas al software ni sustituir requerimientos que el mismo resumen marca como incompletos o pendientes respecto del DRS (por ejemplo comunicación sistemática con las familias).

La brecha queda institucional: completar tutela, comunicación familia-escuela u otras prácticas no cubiertas o parciales en código.

## 8. Sistemas similares o enfoques relacionados

De forma sintética: (F-EDA-03) muestra arquitectura EWS público nacional; (F-EDA-08) aporta ensayo RCT EWS institucional; (F-EDA-05) combina caso escolar vocacional y SMOTE clasificatorio; (F-EDA-06) analiza estadísticamente ABCs antes que producto tecnológico; (F-EDA-07) representa estado metodológico avanzado (estadística + RF/XGB + XAI descrita en ese artículo).

Muchos artículos enfatizan predicción o revisión métodos (F-EDA-01, F-EDA-02, F-EDA-04, F-EDA-07); otros, implementación institucional o comportamiento estudiado estadísticamente (F-EDA-03, F-EDA-06, F-EDA-08). SIDERAE mezcla gestión cotidiana, alertas/intervenciones y servicio Flask de riesgo en entorno Laravel–React centrado institución singular, nivel primaria/secundaria documentado prototípicamente, sin escala país ni garantía experimental.

## 9. Relación con SIDERAE-Blenkir

SIDERAE se orienta según README y `docs/arquitectura/resumen-arquitectura.md` a un establecimiento de educación primaria y secundaria presencial que registra datos en el mismo sistema, en contraste con unidades poblacionales (F-EDA-04) o muestrales nacionales (F-EDA-07). Su alcance se declara prototipo académico funcional y no producto empresarial en sentido organizacional ni objeto de RCT comparable al modelo IKO en (F-EDA-08). No sustituye el juicio docente ni garantiza por sí mismo prácticas marcadas como parciales frente al DRS en documentación técnica. En términos sólo comprobados en ese repositorio, integra gestión académica, registros socioeconómicos mediante los módulos previstos, alertas e intervenciones con autorización backend, comunicación Laravel hacia Flask y persistencia donde la implementación lo confirma, en línea cualitativa —sin equiparancia metodológica— con la idea de que los datos locales deben traducirse en información priorizable para el personal, no en decisiones pedagógicas sin revisión humana.

## 10. Brecha identificada

La literatura reúne modelos predictivos sofisticados y EWS a escala país o poblacional, con heterogeneidad de niveles y contextos que dificultan la transferencia mecánica a un solo colegio presencial. Donde existió ensayo experimental (F-EDA-08), la existencia de infraestructura de alerta y seguimiento no produjo efectos significativos en los resultados académicos y de permanencia medidos en ese estudio, lo que subraya la insuficiencia potencial de la sola detección sin procesos humanos robustos. Incluso modelos con métricas elevadas en sus propios conjuntos de datos no reemplazan gobernanza ni evaluación local. Queda espacio para prototipos escolares que integren de forma trazable registro de notas y asistencia, variables socioeconómicas tomadas con criterio institucional, alertas gobernadas por roles y un apoyo predictivo modesto coherente con lo implementado, sin pretender equivalencia con censos, encuestas nacionales o plataformas ministeriales.

SIDERAE pretende contribuir parcialmente a esa brecha en el marco académico ya descrito, manteniendo el estado del arte abierto a futuras incorporaciones bibliográficas.

## 11. Conclusión

Las ocho fuentes permiten trazar un estado del arte inicial sólido en torno a riesgo, deserción, aprendizaje automático educativo y EWS, reforzando que conviene combinar datos académicos y de asistencia con otros dominios que cada artículo documenta (familia, socioeconomía, conducta o bienestar autoinformado, según referencia) y que el seguimiento posterior a la alerta es tan discutido en la literatura como la predicción misma. SIDERAE no reproduce la diversidad metodológica ni la escala de esos trabajos; adapta, en cambio, principios de priorización con datos locales y servicios acoplados descritos en el repositorio, dentro de los límites de un prototipo cuyo motor de riesgo no debe confundirse con los algoritmos avanzados o la explicabilidad que sólo algunas fuentes implementan hasta que exista evidencia explícita adicional.

El presente texto queda como primera base revisable ante ampliaciones bibliográficas y redacción de referencias formato APA definitivo más adelante, sin caracterizar cerrado este estado del arte.

## Referencias utilizadas

- F-EDA-01: Modelos predictivos con fines educativos (revisión sistemática).
- F-EDA-02: Aprendizaje automático y trayectorias hacia abandono en educación secundaria superior (Finlandia, longitudinal).
- F-EDA-03: Desarrollo de sistema de alerta temprana para estudiantes en riesgo (planificación educativa, datos administrativos).
- F-EDA-04: Predicción de desescolarización con técnicas de aprendizaje automático (México, datos censales).
- F-EDA-05: Deserción temprana en escuelas vocacionales y técnicas (Turquía).
- F-EDA-06: Cribado conductual universal e indicadores de EWS en escuelas medias.
- F-EDA-07: Marco híbrido estadístico, ML y explicabilidad para abandono escolar (Bangladés).
- F-EDA-08: Efectos de sistema de alerta temprana sobre ausencia y culminación (Noruega, RCT por conglomerados).
