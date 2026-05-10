# MLOps básico como complemento técnico del componente predictivo en SIDERAE-Blenkir

## 1. Introducción

SIDERAE-Blenkir incorpora un **microservicio de aprendizaje automático** implementado en **Flask**, invocado por el **backend Laravel** para calcular un **índice de riesgo** y un **nivel de riesgo** a partir de variables académicas y socioeconómicas. Esa separación introduce necesidades típicas de **operación y gobierno** de un modelo o de una lógica de inferencia: contrato de datos, despliegue, manejo de errores, persistencia y trazabilidad mínima.

En el marco metodológico del proyecto, **AI-DLC** es la **metodología principal** (uso disciplinado de inteligencia artificial generativa en el ciclo de desarrollo). **Scrum** se documenta como **marco complementario de gestión** por sprints. Las prácticas aquí agrupadas bajo el rótulo **MLOps básico** constituyen un **complemento técnico**, no una metodología de producto independiente: su función es **controlar y documentar** el componente predictivo con criterios de ingeniería reproducibles, **sin** equiparar el prototipo académico a un **pipeline MLOps empresarial**.

Este documento describe MLOps **a nivel académico y de prototipo**, en coherencia con el estado del código y con `docs/arquitectura/contexto-ml-service-flask.md` y `docs/arquitectura/resumen-arquitectura.md`. No se afirma la existencia de entrenamiento productivo completo ni de algoritmos avanzados del documento de requisitos formales (por ejemplo Random Forest, SVM o XGBoost) mientras **no** estén confirmados en el repositorio actual.

---

## 2. Propósito de MLOps básico en SIDERAE

El propósito de adoptar prácticas **MLOps básicas** en SIDERAE es:

**Ordenar el uso del microservicio ML**, de modo que la inferencia no sea un “script aislado” sino un **servicio** con interfaz HTTP estable (`POST /predict` según documentación técnica), configuración por variables de entorno y ciclo de vida acoplado al resto del sistema vía Docker Compose.

**Controlar entradas y salidas del modelo o de la lógica de cálculo**, alineando el payload construido en Laravel con los campos esperados por Flask y validando la forma de la respuesta antes de persistir.

**Registrar resultados de riesgo** en la base de datos gestionada por Laravel, de forma que el riesgo quede disponible para alertas, dashboard y seguimiento, según los módulos implementados.

**Facilitar la validación técnica** mediante pruebas y revisión de contratos (qué ocurre si el servicio ML no responde, si devuelve valores fuera de rango o si faltan datos mínimos).

**Preparar evoluciones futuras** —cuando el alcance lo permita— como **métricas** de calidad de inferencia, **versionado** explícito de artefactos de modelo y **reentrenamiento** (RF-18 en el DRS), sin presentar esas capacidades como ya implementadas si el repositorio no las contiene.

---

## 3. Alcance del MLOps aplicado

### 3.1 Lo que sí aplica en el estado documentado del repositorio

- **Separación del servicio ML en Flask**: código y dependencias acotadas (`ml-service/`), sin acceso directo del motor ML a MySQL según la arquitectura descrita.
- **Comunicación controlada Laravel → Flask**: cliente HTTP centralizado (por ejemplo `MlRiskService` hacia `{ML_SERVICE_URL}/predict`), sin que el frontend consuma Flask directamente para el cálculo de riesgo.
- **Validación de datos antes del cálculo**: el backend valida datos mínimos antes de invocar el servicio, coherente con un flujo de procesamiento explícito por estudiante.
- **Registro del índice de riesgo** (y nivel) tras una inferencia exitosa, persistiendo en tablas gestionadas por Laravel para uso posterior en el dominio académico.
- **Manejo de fallos del servicio ML**: timeouts, errores HTTP o cuerpos inválidos deben contemplarse para no dejar el sistema en estado inconsistente (detalle de implementación sujeto a revisión en código).
- **Posible evolución** hacia métricas, registro de versiones de modelo y pipelines de reentrenamiento, explícitamente como **trabajo futuro** si no existe flujo completo hoy.

### 3.2 Lo que no aplica o no aplica todavía

- **Pipeline MLOps empresarial completo** (orquestación de entrenamiento, registro de experimentos, despliegue canario masivo, gobernanza de datos a escala industrial).
- **Monitoreo productivo avanzado** (drift en producción, alertas operativas 24/7, SLO/SLA de inferencia) más allá de lo razonable para un prototipo académico.
- **Despliegue cloud MLOps** específico (por ejemplo servicios gestionados de ML) si el proyecto se ejecuta principalmente como stack local con Docker Compose según README.
- **Versionado formal de modelos** en el sentido de artefactos versionados y promocionados entre entornos, **si** el repositorio no expone ese mecanismo de forma explícita.
- **Entrenamiento automatizado completo** (`fit`, pipelines de features, validación cruzada en CI) **si** no está implementado: la documentación técnica del ML Service describe el cálculo actual como **prototipo determinístico** y no confirma librerías de modelos entrenados en `requirements.txt`.

---

## 4. Flujo ML en SIDERAE

A nivel conceptual, el flujo de riesgo académico en el prototipo sigue esta cadena:

1. El usuario con permisos adecuados **registra o consulta** datos académicos y socioeconómicos del estudiante (notas, asistencia, variables, etc.) a través del **frontend React** y la **API Laravel**.
2. **Laravel** consolida las variables necesarias y construye el **payload** acordado con el contrato del microservicio Flask.
3. **Laravel** invoca **Flask** en el endpoint de predicción; el servicio ML **no** sustituye la autorización ni las reglas de negocio del backend.
4. **Flask** devuelve **`indice_riesgo`** y **`nivel_riesgo`** en el formato documentado.
5. **Laravel** interpreta la respuesta, aplica reglas adicionales si existen (por ejemplo clasificación o persistencia según el dominio) y **guarda el resultado** en base de datos **MySQL**.
6. El sistema **reutiliza** ese resultado en **alertas**, **dashboard** u otros módulos de seguimiento, dentro del alcance implementado en cada sprint.

Este flujo es **orquestado por Laravel**; el ML actúa como **calculador** o servicio de inferencia acotado, coherente con `ARCHITECTURE.md` y con el resumen de arquitectura del repositorio.

---

## 5. Prácticas básicas de MLOps adoptadas

**Separación de responsabilidades.** El frontend no llama directamente a Flask para el riesgo; el backend centraliza autenticación, permisos y persistencia.

**Contrato de entrada y salida del servicio ML.** Campos de entrada y claves de salida alineados a lo documentado en `contexto-ml-service-flask.md` (por ejemplo `promedio_notas`, `porcentaje_asistencia`, … → `indice_riesgo`, `nivel_riesgo`). Cualquier cambio de contrato debe actualizarse de forma coordinada en Laravel y Flask.

**Validación de datos.** Comprobación de existencia y plausibilidad mínima de variables antes de gastar recursos en la llamada remota y antes de persistir resultados.

**Persistencia de resultados.** Historial en tablas de índices de riesgo gestionadas por Laravel, base para RF-20 en la medida en que el repositorio implemente consulta y visualización.

**Trazabilidad de ejecuciones.** Donde el proyecto registre actividad en operaciones sensibles (por ejemplo procesamiento de riesgo), la inferencia queda vinculada a acciones auditables a nivel de aplicación, según la documentación de auditoría parcial en Laravel.

**Manejo de errores.** Respuestas de error del servicio ML o fallos de red deben traducirse en comportamientos controlados en API (códigos HTTP y mensajes coherentes), evitando corrupción silenciosa de datos.

**Preparación para evaluación futura del modelo.** Cuando exista un modelo entrenado real, será necesario definir métricas offline/online y conjuntos de validación; hoy el documento solo **marca** esa dirección sin afirmar su implementación.

**Documentación técnica del componente ML.** Los archivos de arquitectura y el README del repositorio cumplen función MLOps “ligera” al fijar el estado real frente al DRS y al evitar confusiones entre alcance formal y prototipo.

---

## 6. Relación con AI-DLC y Scrum

**AI-DLC** gobierna **cómo** se usa la inteligencia artificial **generativa** en planificación, implementación, revisión y documentación del desarrollo. **Scrum** (adaptado) organiza el trabajo en **sprints** e incrementos revisables. **MLOps básico** aplica a **un componente concreto**: el **servicio predictivo** y su interfaz con el backend.

Las tres perspectivas son **compatibles**: durante un sprint se puede mejorar el contrato Laravel–Flask, la robustez ante errores o la documentación del ML; cada mejora se trata como **incremento** sujeto a criterios de aceptación y validación humana, sin confundir asistencia de IA en el editor con gobierno automático del modelo en producción.

---

## 7. Relación con los requerimientos de SIDERAE

El documento de requisitos formales (DRS) define expectativas de mayor alcance que el código puede tener ya cubiertas solo en parte. La siguiente tabla resume la **relación conceptual** entre RFs citados por la arquitectura y el **rol de MLOps básico**, sin citar bibliografía.

| RF | Tema | Rol de MLOps básico / observación sobre el estado documentado |
| -- | ---- | -------------------------------------------------------------- |
| **RF-06** | Procesamiento multivariable e índice de riesgo | **Contrato**, llamada Laravel→Flask y **persistencia** del índice. Respecto al DRS, la ejecución real de Random Forest, SVM y XGBoost **no está confirmada** en el código actual del `ml-service`; el cálculo se documenta como **prototipo determinístico**. |
| **RF-07** | Evaluación automática del nivel de riesgo | **Salida estructurada** (`nivel_riesgo`) y uso en backend; MLOps básico exige validar coherencia entre índice y nivel antes de efectos colaterales (alertas). |
| **RF-18** | Reentrenamiento del modelo ML | **Evolución prevista**: versionado de datos, pipeline de entrenamiento y promoción de modelos. En el repositorio figura como **pendiente de desarrollo** si no existen endpoints ni flujo de reentrenamiento. |
| **RF-20** | Historial de riesgo por estudiante | **Persistencia** de ejecuciones en base de datos; la **visualización histórica completa** en UI puede ser **parcial** según `resumen-arquitectura.md`. MLOps básico asegura al menos **trazabilidad de datos** a nivel de persistencia. |

---

## 8. Fuentes de respaldo metodológico

Las filas **F-MLOPS-01** y **F-MLOPS-02** están codificadas en `docs/referencias/matriz-fuentes.md` y resumidas en `docs/referencias/resumenes/mlops/`. Actúan como **fuentes complementarias** respecto del marco técnico del prototipo (no prescriben el diseño exacto del repositorio). **Adaptación al proyecto:** SIDERAE asume prácticas **básicas o académicas**, explícitamente inferiores en alcance a lo que dichas fuentes describen como MLOps de plataformas industriales completas.

- **F-MLOPS-01** (*MLOps Components, Tools, Process, and Metrics: A Systematic Literature Review*, *IEEE Access*): respalda una lectura revisada sistemáticamente sobre **componentes**, **implementaciones típicas de herramientas**, **pasos del proceso** de ciclo vida ML en bibliografía revisada por los autores, **arquitectura de referencia** propuesta por los autores a partir del conjunto revisado y la **pregunta abierta declarada por los mismos autores** sobre métricas de efectividad de implementación de MLOps (sin resultado satisfactorio en la revisión para esa pregunta). En SIDERAE, eso permite **vocabulizar con rigor bibliográfico** lo que sí se cubre aquí (**separación del servicio Flask, contrato y validación Laravel → Flask, persistencia del resultado de riesgo, manejo básico de fallos**) frente a un MLOps “completo” del que habla la literatura (orquestadores, registros formales multi-entorno, monitorización avanzada en producción, etc.) **sin afirmar** que el prototipo implemente todo el arquetipo.

- **F-MLOPS-02** (*Machine learning operations landscape: platforms and tools*, *Artificial Intelligence Review*): respalda un marco de **evaluación de plataformas de código abierto** (análisis de capacidades, tendencia de adopción vía estrellas en GitHub y puntuación ponderada en el propio artículo), el contraste **CI/CD/CT** frente a DevOps clásico en el texto, y la discusión sobre **monitorización** y prácticas de **reentreno** tal como aparecen formuladas como parte del discurso MLOps en esa fuente. Las **conclusiones del artículo** subrayan, respecto del conjunto de herramientas analizadas, limitaciones relativas al soporte en **monitorización del rendimiento del modelo**. En **adaptación académica**, SIDERAE usa solo un subconjunto: microservicio aislado, comunicación Laravel → Flask y trazabilidad mínima; **la selección automatizada de plataforma, el CI/CD corporativo integral, la monitorización drifts en tiempo real y el reentrenamiento automatizado** quedan como **literatura orientativa / evolución futura**, no como descripción auditable del estado actual del código salvo nueva evidencia en el repo.

Las dos fuentes **describen MLOps en sentido amplio y de infraestructura**; este documento explicita que el proyecto solo adopta **un subconjunto básico** acorde a prototipo y a la documentación interna existente.

---

## 9. Controles de calidad para el componente ML

- **Validar datos mínimos** antes de invocar Flask (evitar llamadas vacías o inconsistentes).
- **Verificar la respuesta** del servicio: presencia de campos obligatorios, rangos plausibles para `indice_riesgo` y valores permitidos para `nivel_riesgo`.
- **Registrar errores** de integración (red, timeout, JSON inválido) para diagnóstico y para no perder el hilo de auditoría operativa.
- **Evitar que un fallo del ML** deje la aplicación en estado irrecuperable: respuestas controladas al cliente y transacciones acordes en persistencia.
- **Revisar coherencia** entre el índice devuelto y las reglas de negocio del backend (por ejemplo umbrales para “alto/medio/bajo”).
- **Documentar limitaciones** del modelo o de la lógica actual frente al DRS, para que resultados predictivos no se interpreten como veredictos absolutos ni como certificación científica institucional.
- **No presentar** la salida del servicio como **garantía** de deserción o de diagnóstico: el sistema es un **apoyo** a la gestión académica en contexto de prototipo.

---

## 10. Limitaciones

- **MLOps aplicado de forma básica**: adecuado a proyecto académico, no a escala de producto analítico industrial.
- **Datos de prueba o prototipo** en entornos locales; las conclusiones sobre calidad predictiva no son generalizables sin diseño experimental formal.
- **Modelo posiblemente determinístico o parcial** según `ml-service/main.py` y documentación asociada; no se debe inferir despliegue de ensemble avanzado sin evidencia en código.
- **Falta de métricas productivas** (latencia p95 en producción, tasas de error por versión de modelo, etc.) si no están instrumentadas.
- **Reentrenamiento** como **evolución futura** mientras no exista flujo completo versionado y reproducible.
- **Validación académica y técnica** humana sigue siendo obligatoria: MLOps básico no sustituye revisión por pares, asesoría ni pruebas del sistema integrado.

---

## 11. Conclusión

Las prácticas agrupadas como **MLOps básico** en SIDERAE-Blenkir **complementan** la metodología principal **AI-DLC** y el ordenamiento por **sprints**: aportan **control**, **contrato**, **persistencia** y **criterios mínimos de operación** al **componente predictivo** desplegado en Flask y consumido por Laravel. Su función es dar **trazabilidad y calidad mínima** al servicio ML dentro de un **prototipo académico**, **no** reemplazar la **validación humana** ni convertir el sistema, por el solo hecho de exponer un endpoint de riesgo, en un **producto MLOps empresarial** con modelos avanzados ya productivos, cuando el repositorio documenta explícitamente **limitaciones** y **pendientes** frente al alcance formal del DRS.

---

Referencias: resúmenes **F-MLOPS-01** y **F-MLOPS-02** en `docs/referencias/resumenes/mlops/` y `matriz-fuentes.md`; citas APA finales pendientes de consolidación antes de sustentación.
