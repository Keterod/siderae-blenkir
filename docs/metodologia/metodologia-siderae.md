# Metodología AI-DLC con gestión iterativa Scrum y prácticas básicas de MLOps para el desarrollo de SIDERAE-Blenkir

## 1. Introducción

El desarrollo de **SIDERAE-Blenkir** —sistema web orientado a la detección temprana de riesgo académico y a la gestión de alertas e intervenciones asociadas— adopta una **metodología de ciclo de vida centrada en el uso de inteligencia artificial generativa** en las actividades de ingeniería de software, denominada aquí **AI-DLC (AI-Driven Development Life Cycle)** como **marco metodológico principal**. Este enfoque no sustituye la responsabilidad del desarrollador humano; articula cómo se planifica, implementa, revisa y documenta el trabajo cuando la IA actúa como asistente técnico (por ejemplo, mediante asistentes integrados en el entorno de desarrollo).

El trabajo queda **organizado en iteraciones temporales** mediante **Scrum**, utilizado como **marco complementario de gestión**: define sprints, prioridades relativas, criterios de aceptación y cierre incremental de entregables coherentes con un prototipo académico.

Por la presencia de un **microservicio de aprendizaje automático** (Flask) que calcula un índice y nivel de riesgo consumido por el backend Laravel, se incorpora **MLOps en nivel básico o académico**: control del servicio, contratos de entrada/salida, trazabilidad mínima y expectativas de evolución (por ejemplo, métricas o reentrenamiento), sin equiparar el alcance a un pipeline MLOps empresarial completo.

Como **prácticas de apoyo explícitamente secundarias** —no como metodologías principales— se emplean **DevOps ligero** (reproducibilidad del entorno con Docker Compose, comandos documentados, pruebas automatizadas donde existan) y **Context Engineering** (preparación de contexto para prompts, reglas de proyecto y revisión asistida), de modo que la IA reciba información suficiente y acotada para producir salidas útiles y revisables.

> Fuentes preliminares en `docs/referencias/resumenes/` y `matriz-fuentes.md` (códigos F-AIDLC-01/02, F-IASE-01/02, F-MLOPS-01/02, F-SCRUM-01). La **cita final en formato APA** está pendiente de revisión antes de sustentación.

El sistema, según la documentación interna del repositorio, integra **frontend React**, **API REST Laravel**, **MySQL**, **microservicio ML en Flask**, **Docker Compose**, **roles y permisos**, y módulos de **alertas, intervenciones y seguimiento de riesgo académico**. La metodología descrita debe interpretarse siempre en coherencia con ese **estado real de implementación** y con las **limitaciones** reconocidas en README y documentos de arquitectura (por ejemplo, alcance parcial frente al documento de requisitos formal y naturaleza de prototipo del componente ML).

---

## 2. Justificación de la metodología

### 2.1 Por qué Scrum solo no basta

Scrum aporta **ritmo**, **transparencia** y **definición de “hecho”** por sprint, pero no prescribe en detalle **cómo** redactar especificaciones, **cómo** explotar herramientas de IA generativa ni **cómo** gobernar un servicio de inferencia separado del monolito API. En un proyecto académico con stack heterogéneo (PHP/Laravel, React, Python/Flask) y documentación técnica distribuida, resulta insuficiente limitarse a ceremonias Scrum sin un marco explícito para el **uso disciplinado de la IA** y para las **prácticas técnicas transversales** (contenedores, pruebas, revisión de contratos entre servicios).

### 2.2 Por qué AI-DLC encaja con IA generativa en el ciclo

AI-DLC, entendido en sentido operativo para este proyecto, describe un ciclo en el que la IA asiste en **planificación** (descomposición de tareas, checklist), **implementación** (borradores de código y refactors sugeridos), **revisión** (detección de inconsistencias, seguridad básica), **análisis de errores** (logs, trazas) y **documentación** (síntesis de arquitectura y de sprints). Encaja con un equipo reducido y con necesidad de **homogeneizar** el estilo técnico y la trazabilidad entre backend, frontend y ML, siempre sujeto a **validación humana**.

La denominación **AI-DLC** es **propia del proyecto**; su justificación se apoya de forma **preliminar** en líneas de investigación afines documentadas en `docs/referencias/resumenes/`, sin identidad terminológica completa con cada fuente: el marco **AI-SDLC** (ciclo de vida optimizado con IA embebida), la literatura sobre **ingeniería de software aumentada con GenAI** (*GenAI-augmented* procesos y productos), la **IA aplicada al SDLC** en sentido amplio, y el uso de **IA en pruebas y validación** (encuestas y taxonomías bajo el paraguas AI4SE). Ninguna de esas fuentes **valida de forma definitiva** ni **certifica** la etiqueta AI-DLC adoptada aquí; sirven como **respaldo conceptual y documental** sujeto a revisión de citas.

### 2.3 Por qué Scrum sigue siendo necesario

Aun con asistencia de IA, hace falta un **orden de trabajo** acordado: entregables por sprint, dependencias entre módulos (por ejemplo, permisos antes de exponer acciones en UI), **criterios de aceptación** verificables y **pruebas** (manuales y automatizadas) que cierren incrementos sin ambigüedad. Los documentos en `sprints/` del repositorio formalizan objetivos, alcance y criterios por iteración; eso cumple la función de **marco de gestión** que Scrum proporciona en este contexto académico.

### 2.4 Por qué MLOps básico es necesario

El riesgo académico no es solo una regla de negocio en Laravel: depende de un **servicio externo** con contrato HTTP (`/predict`), payload acordado y respuesta interpretada y persistida. Eso introduce riesgos de **despliegue**, **versionado del modelo o de la lógica de cálculo**, **observabilidad mínima** y **validación**. Un MLOps “básico” —acotado al nivel de prototipo— da criterios para tratar ese componente con la misma seriedad que el resto de la API, sin confundir prototipo con producto analítico industrial.

### 2.5 Respaldo documental preliminar

Las fuentes codificadas en `docs/referencias/matriz-fuentes.md` y resumidas en Markdown ofrecen un **respaldo inicial** (no exhaustivo) para situar AI-DLC frente a discursos académicos cercanos:

| Código | Rol respecto a AI-DLC en SIDERAE | Qué respalda (según resumen interno) |
| ------ | -------------------------------- | ------------------------------------- |
| **F-AIDLC-01** | Fuente **equivalente parcial** en el eje “ciclo de vida + IA + optimización”: el artículo usa el término **AI-SDLC**, no “AI-DLC”. | Integración de IA a lo largo del SDLC, roles y automatización híbrida descritos en *IEEE Access* (2025). |
| **F-AIDLC-02** | Fuente **complementaria** sobre **GenAI-augmented** procesos y productos en SE. | Roadmap de investigación que estructura formas de aumento con GenAI (p. ej. dimensión proceso/producto y rol pasivo/activo), sin adoptarse íntegramente en el proyecto. |
| **F-IASE-01** | Fuente **complementaria** focalizada en **pruebas** y AI4SE. | Encuesta sobre automatización de pruebas con IA; útil para argumentar el sprint de pruebas y la validación técnica, sin atribuir al repositorio herramientas o pipelines no documentados. |
| **F-IASE-02** | Fuente **complementaria** de **panorama amplio** IA en SE. | Revisión sobre innovaciones y retos (SDLC, roles, XAI a alto nivel); apoya el discurso general de IA en el ciclo sin sustituir el estado del arte. |
| **F-MLOPS-01** | Fuente **complementaria** para el componente técnico predictivo. | Revisión sistemática sobre componentes, procesos y herramientas MLOps en literatura revisada por los autores; arquitectura de referencia y vacío declarado sobre métricas globales de efectividad (**según resumen interno**). Orienta vocabulario técnico; **no** presupone pipeline empresarial en SIDERAE. |
| **F-MLOPS-02** | Fuente **complementaria** sobre el **panorama de plataformas** MLOps. | Artículo que evalúa capacidades open source, CI/CD/CT y límites de monitorización relativos (**según resumen/conclusiones de la fuente**). Contrasta alcance bibliográfico con **MLOps básico** real del proyecto. |
| **F-SCRUM-01** | Fuente **directa** respecto del texto de referencia Scrum (guía oficial 2020). | **Complementaria** en la **aplicación** de SIDERAE: respalda términos de empirismo, eventos y Definition of Done donde el equipo los adopta como **adaptación académica** ligera frente al texto canónico. |

**Jerarquía metodológica (sin cambiar la posición de AI-DLC):** **AI-DLC** permanece como **metodología principal**. **Scrum** (**F-SCRUM-01**) fundamenta léxico y prácticas incrementalistas **complementarias** y **no** sustituye al núcleo AI-DLC. **MLOps básico** (**F-MLOPS-01/02**) respalda el **discurso técnico** sobre el microservicio predictivo dentro de límites de prototipo académico. El conjunto es **respaldo preliminar** y **no** constituye un estado del arte exhaustivo cerrado para la sustentación.

Detalle fuentes AI-DLC / IA-SE: `docs/referencias/resumenes/ai-dlc/comprehensive-framework-ai-sdlc.md`, `genai-augmented-se-roadmap-2026.md`, `ia-desarrollo-software/ai-driven-software-test-automation.md`, `ai-driven-innovations-software-engineering.md`. MLOps: `docs/referencias/resumenes/mlops/mlops-components-tools-process-metrics-2025.md`, `mlops-landscape-platforms-tools-2025.md`. Scrum: `docs/referencias/resumenes/scrum/scrum-guide-2020-definitive-guide-rules-of-the-game.md`.

---

## 3. Componentes de la metodología adoptada

### 3.1 AI-DLC como metodología principal

Constituye el **eje** del proceso: define cómo se integra la IA en las fases del ciclo (requerimiento → diseño/implementación → validación → documentación), con énfasis en **revisión humana** y en **coherencia con el código existente**. Las decisiones de alcance respecto al DRS y el estado “real vs deseado” se toman con base en evidencia en repositorio, no solo en salidas del modelo de lenguaje.

### 3.2 Scrum como marco de gestión iterativa

Se utilizan **sprints** documentados, objetivos por iteración y dependencias entre ellos (por ejemplo, pruebas integrales posteriores al refuerzo de seguridad y permisos). Los roles clásicos de Scrum pueden estar **fusionados** en un equipo pequeño; lo relevante metodológicamente es la **planificación incremental** y el **cierre** con criterios explícitos.

### 3.3 MLOps básico para el componente predictivo

Aplica al **microservicio Flask** y a su relación con Laravel: contrato de inferencia, manejo de errores, posibilidad de registro de resultados en base de datos, y visión de **mejora futura** (métricas, reentrenamiento) alineada a lo declarado como pendiente en documentación interna cuando el código aún no implementa pipelines avanzados.

### 3.4 DevOps ligero como soporte técnico

Apoya la **reproducibilidad** (Docker Compose, variables de entorno documentadas, comandos de prueba y de base de datos en README) y la **integración continua ligera** en el sentido de poder ejecutar builds y tests en entorno contenedorizado. **No** se presenta como metodología de producto independiente, sino como **habilitador** del flujo de desarrollo y de la verificación por pares o por asesoría.

### 3.5 Context Engineering como práctica de apoyo

Consiste en **estructurar el contexto** (archivos de arquitectura, reglas de proyecto, resúmenes de sprint, fragmentos de código relevante) antes de solicitar cambios a la IA, y en **revisar** las salidas contra ese mismo contexto. Reduce alucinaciones y desvíos de convenciones del repositorio. Es **complementaria** a AI-DLC y a Scrum, no un sustituto de ninguna de las dos.

---

## 4. Flujo metodológico aplicado a SIDERAE

El siguiente flujo resume la cadena típica **por sprint** o por conjunto de historias técnicas, alineada con la operación descrita en documentación interna:

1. **Análisis del requerimiento** — Lectura del alcance del sprint y, cuando aplica, contraste con el DRS y con `docs/arquitectura/`; identificación de riesgos (permisos, datos sensibles, llamadas al ML).
2. **Preparación de contexto** — Selección de archivos y documentos guía (rutas API, modelos, servicio `MlRiskService`, `ml-service`, Compose) para sesiones de implementación o revisión con IA.
3. **Planificación asistida por IA** — Desglose en tareas, orden de dependencias, checklist de pruebas; la salida se ajusta manualmente al backlog real.
4. **Implementación incremental** — Cambios acotados por capa (backend, frontend, ML, infra) con commits y mensajes trazables al sprint.
5. **Validación técnica y funcional** — Pruebas automatizadas donde existan (`php artisan test`, build de frontend, suites E2E si están definidas en el plan de sprint), pruebas manuales y verificación de códigos HTTP y permisos.
6. **Documentación y cierre del sprint** — Actualización de README, sprints, arquitectura o matrices de acceso según corresponda; registro de limitaciones conocidas.

Este flujo es **prescriptivo a nivel de proceso**, no garantiza por sí solo un nivel de madurez empresarial; su valor es **uniformizar** cómo el equipo y la asesoría pueden auditar el avance.

---

## 5. Relación con los sprints del proyecto

El proyecto **ya está organizado en sprints**; la numeración y el detalle fino aparecen en archivos bajo `sprints/` y en el README. La metodología aquí descrita **no sustituye** esos documentos; solo **relaciona** fases típicas con una lectura **general** por bloques funcionales, reconociendo que en el repositorio existen **subdivisiones** adicionales (por ejemplo 7.5A/7.5B, 7.6A/7.6B) que refinan el trabajo de UI, datos académicos y operación.

| Lectura general (bloque) | Tema principal | Nota de alineación con el repositorio |
| ------------------------ | -------------- | ------------------------------------- |
| **Sprint 1** | Infraestructura base (Docker, servicios) | Coherente con README (“Infraestructura Docker y servicios base”). |
| **Sprint 2** | Autenticación y permisos (Sanctum, Spatie, `/api/me`) | Coherente con README y matriz de accesos en documentación de arquitectura. |
| **Sprint 3A / 3B** | Estudiantes y datos académicos base (notas, asistencia, variables) | 3A y 3B están documentados por separado en `sprints/`; el README resume sus entregables. |
| **Sprint 4** | Integración ML (Laravel → Flask, persistencia de índice de riesgo, procesamiento explícito) | La integración está descrita en README y `ARCHITECTURE.md`; el modelo en código se documenta como **prototipo determinístico**, no como despliegue productivo avanzado. |
| **Sprint 5** | Alertas e intervenciones | Coherente con README (gestión y cierre de alertas). |
| **Sprint 6A / 6B** | Dashboard y reportes (export PDF en alcance parcial) | README indica **implementación parcial** frente al DRS; la metodología no afirma cierre total de esos requerimientos. |
| **Sprint 7A / 7B** (y **7.5A / 7.5B**, **7.6A / 7.6B**) | UI/UX, pantallas, auditoría parcial, catálogo y cargas masivas según documentos de sprint | La numeración extendida en el repo **complementa** la visión 7A/7B; no se inventan funcionalidades no reflejadas en esos archivos. |
| **Sprint 8** | Seguridad, control de accesos y coherencia UI–backend | Alineado a README y `docs/arquitectura/matriz-control-accesos-sprint8.md`. |
| **Sprint 9** | Pruebas integrales, regresión y corrección de defectos | Objetivo y alcance descritos en `sprints/sprint 9.md` (campanas de prueba y evidencias). |
| **Sprint 10** | Documentación final y cierre de calidad académico | Incluye manuales y trazabilidad; el sprint 10 del repo aclara uso de normas ISO **solo como referencia orientativa**, **sin** certificación formal. |

**Advertencia metodológica:** cualquier afirmación sobre “completitud” de un módulo debe contrastarse con **código y pruebas**, no solo con el título del sprint. Los documentos de arquitectura listan requerimientos **parciales** o **pendientes** (por ejemplo RF-18, RF-19) cuando el repositorio así lo registra.

---

## 6. Aplicación del uso de IA generativa

La IA generativa (incluido el uso de **Cursor** u otras herramientas similares) se emplea como **apoyo** en:

- **Generación de planes** de tareas y de orden de implementación a partir de la descripción del sprint.
- **Diseño de prompts** y iteraciones de refinamiento (**Context Engineering**).
- **Análisis de errores** a partir de logs, trazas de API o fallos de prueba.
- **Redacción y síntesis de documentación** técnica y metodológica.
- **Revisión de arquitectura** frente a fragmentos de código o diagramas lógicos descritos en Markdown.
- **Apoyo en pruebas** (borradores de casos, scaffolding de tests), sujeto a revisión.

En todos los casos, las **decisiones finales** —aceptar un cambio, fusionar código, declarar un requerimiento como satisfecho, publicar una conclusión en documentación— recaen en el **desarrollador humano** y, cuando aplica, en la **asesoría académica**. La IA reduce coste de redacción y exploración; **no** sustituye la verificación contra el comportamiento real del sistema.

---

## 7. Aplicación de MLOps básico

A **nivel académico y de prototipo**, MLOps básico en SIDERAE se interpreta como:

- **Control del servicio ML**: despliegue vía Docker, endpoint estable, configuración por variables de entorno documentadas.
- **Validación de entradas y contratos**: el backend construye el payload esperado por Flask; respuestas malformadas o errores de red deben tratarse sin corromper datos.
- **Registro de resultados**: persistencia del índice y nivel en base de datos y trazas de negocio según la lógica implementada en Laravel.
- **Métricas y observabilidad futuras**: posibilidad de ampliar logs, métricas de latencia o calidad de inferencia cuando el alcance lo permita.
- **Reentrenamiento**: reconocido en documentación interna como **evolución futura** cuando exista decisión de alcance y evidencia en código; **no** debe afirmarse un pipeline de entrenamiento productivo completo si el repositorio describe el cálculo actual como **determinístico** o prototipo.

Estas prácticas **no equivalen** a MLOps empresarial (gobernanza de datos, feature store, despliegue canario masivo, etc.), pero **sí** dan marco conceptual para que el componente ML no quede fuera del discurso de calidad y trazabilidad del proyecto.

> Respaldo MLOps: **F-MLOPS-01** y **F-MLOPS-02** en `docs/referencias/resumenes/mlops/` (véase también `docs/metodologia/mlops-basico.md` §8).

---

## 8. Roles dentro de la metodología

| Rol | Función en el marco adoptado |
| --- | ---------------------------- |
| **Desarrollador / líder del proyecto** | Prioriza backlog, implementa, ejecuta pruebas, valida salidas de IA y consolida documentación técnica. |
| **IA generativa / Cursor (asistente técnico)** | Acelera redacción y exploración de código; propone borradores sujetos a revisión. |
| **Asesor o revisor académico** | Supervisa alineación con objetivos de titulación, rigor metodológico y honestidad respecto a limitaciones del prototipo. |
| **Usuarios del sistema (o perfiles demo)** | Validación funcional orientada a flujos reales (roles, permisos, registro de datos, riesgo, alertas); en entorno académico suele realizarse con datos ficticios y escenarios controlados. |

---

## 9. Ventajas esperadas

- **Mayor velocidad de desarrollo** en tareas repetitivas o de búsqueda de patrones, sin eliminar la revisión humana.
- **Trazabilidad por sprints** y documentos en `sprints/` y `docs/`.
- **Mejor organización documental** al exigir contexto explícito para la IA y para la asesoría.
- **Validación progresiva** al cerrar cada iteración con criterios y pruebas acordadas.
- **Apoyo en análisis técnico** (seguridad, consistencia API–UI, integración ML).
- **Entorno reproducible** gracias a Docker Compose y a la documentación de arranque en README.

---

## 10. Limitaciones

- **Dependencia de la revisión humana**: la IA puede introducir errores o supuestos no válidos en el dominio institucional.
- **Riesgo de respuestas incorrectas o desactualizadas** respecto al código vigente si el contexto proporcionado es incompleto.
- **Obligación de validar código y pruebas**; la metodología no reduce la necesidad de evidencia en ejecución.
- **Alcance académico del prototipo**: diferencias frente al DRS y funciones pendientes deben declararse explícitamente, según ya hace la documentación interna.
- **MLOps aplicado de forma básica**: sin afirmar despliegue analítico industrial ni entrenamiento continuo verificado en el repositorio actual.
- **No se reclama certificación ISO** ni cumplimiento normativo certificado; cuando el sprint 10 mencione ISO, es como **marco orientativo**, no como acreditación obtenida.

---

## 11. Conclusión

La metodología de SIDERAE-Blenkir se articula en torno a **AI-DLC** como **base metodológica principal**, entendida como integración disciplinada de la IA generativa en el ciclo de desarrollo con validación humana. **Scrum** organiza la **ejecución incremental** y el cierre por sprints documentados en el repositorio. **MLOps básico** aporta criterios para tratar el **microservicio ML** con seriedad de contrato, persistencia y evolución futura, en coherencia con el estado de **prototipo** descrito en la documentación técnica. **DevOps ligero** y **Context Engineering** actúan como **prácticas de apoyo** para la reproducibilidad del entorno y para el uso **controlado y revisable** de la IA, sin confundirse con el marco principal de gestión ni con el núcleo metodológico del proyecto.

> Cierre bibliográfico: completar citas APA a partir de `docs/referencias/resumenes/` y revisión con asesoría; no sustituye el DRS ni la evidencia en código.
