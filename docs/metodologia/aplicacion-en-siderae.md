# Aplicación de la metodología en SIDERAE-Blenkir

## 1. Introducción

Este documento describe **cómo** la metodología adoptada en SIDERAE-Blenkir —**AI-DLC** como núcleo, **Scrum** como organización por sprints, **MLOps básico** sobre el servicio Flask, y prácticas de apoyo (**DevOps ligero**, **Context Engineering**)— se **aplica** al trabajo real del repositorio (React, Laravel, MySQL, Docker Compose, roles, riesgo, alertas). Complementa los textos `metodologia-siderae.md`, `ai-dlc.md`, `scrum-complemento.md` y `mlops-basico.md` sin repetir su contenido exhaustivo.

Se distingue en todo momento: **alcance documentado** (DRS y `docs/arquitectura/`), **avance reflejado en código y sprints**, y **pendientes** explícitos cuando la evidencia no sea suficiente.

---

## 2. Aplicación general de AI-DLC en el proyecto

**AI-DLC** se aplica como **disciplina de uso** de la inteligencia artificial generativa (por ejemplo Cursor u otras herramientas) en **planificación**, **implementación**, **revisión** y **documentación**. En SIDERAE ello se concreta en: preparar contexto (`README`, `docs/arquitectura/`, archivos de `sprints/`, fragmentos de código relevantes); solicitar planes o borradores; **validar** salidas contra rutas API, permisos y contrato Laravel–Flask; y cerrar con pruebas y diff revisado.

La metodología **no** automatiza el cumplimiento del DRS: solo **estructura** el trabajo humano–IA y exige **validación humana** antes de considerar cerrado un incremento.

---

## 3. Aplicación de Scrum en los sprints

**Scrum** (versión adaptada académica) se aplica mediante **archivos numerados** en `sprints/` (del 1 al 10, con subdivisiones 7.5 y 7.6), cada uno con **objetivo**, **alcance** y **criterios** propios. El sprint **acota** el compromiso de entrega y permite revisar el avance **incrementalmente** frente a regresiones o desalineación con permisos y flujos críticos.

La aplicación práctica es **leer el sprint vigente**, ejecutar el trabajo dentro de ese marco y actualizar documentación si el incremento cambia contratos o comportamiento observable.

---

## 4. Aplicación de MLOps básico en el componente predictivo

**MLOps básico** se aplica al **microservicio Flask** y a la integración **Laravel → `POST /predict`**: variables de entorno, contrato de payload/respuesta, validación mínima, persistencia del índice y nivel, y manejo de fallos sin corromper el dominio. El estado del código se documenta como **prototipo**; **no** se trata como pipeline de producción ni se afirma entrenamiento con Random Forest, SVM o XGBoost mientras el repositorio no lo demuestre.

Cualquier mejora del ML (métricas, versionado de modelo, reentrenamiento RF-18) se gestionaría como **incremento de sprint** con criterios explícitos, hoy en buena parte **futuros** respecto al alcance implementado.

---

## 5. Uso de DevOps ligero en el entorno técnico

**DevOps ligero** aparece en la **reproducibilidad** del entorno: Docker Compose, plantillas `.env.example`, comandos de arranque y pruebas descritos en el README. Su aplicación metodológica es **reducir fricción** entre “documento de sprint” y “comportamiento observable”: un revisor debe poder levantar el stack y repetir pasos mínimos de verificación **sin** depender de configuraciones no versionadas.

No equivale a una cadena CI/CD empresarial completa; su papel aquí es de **apoyo**, no de metodología principal.

---

## 6. Uso de Context Engineering en prompts y revisión con IA

**Context Engineering** se aplica al **empaquetar contexto** antes de prompts largos: rutas de archivos, extractos de `docs/arquitectura/contexto-*.md`, reglas de proyecto y límites del sprint. También al **revisar** respuestas de la IA contra ese mismo contexto para evitar endpoints o librerías inexistentes.

Es especialmente relevante en sprints de **UI** (mockups, guía) y en cambios **transversales** (permisos + UI), donde la superficie de error es grande si el contexto está incompleto.

---

## 7. Flujo aplicado de trabajo

El flujo habitual **por incremento** (dentro de un sprint o subconjunto de tareas) puede describirse así:

1. **Requisito o pendiente** — Identificación a partir del sprint, del DRS (cuando aplica) o de un hallazgo en prueba; acotación del “qué” y del “fuera de alcance”.
2. **Preparación del contexto** — Selección de documentación y código pertinente; datos sensibles fuera del prompt.
3. **Prompt para Cursor/ChatGPT** — Instrucción explícita con criterios de aceptación y referencias a archivos.
4. **Implementación** — Cambios pequeños, commits razonables, compilación o tests frecuentes.
5. **Validación** — Pruebas automatizadas cuando existan; pruebas manuales de flujos críticos (login, permisos, riesgo, alertas según alcance).
6. **Documentación** — Actualización de README, arquitectura o sprint si el contrato o el despliegue cambian.
7. **Revisión del avance** — Diff revisado, pendientes registrados, decisión de cierre o de deuda explícita.

Este flujo es **prescriptivo** a nivel de hábito de equipo; la rigurosidad exacta depende del sprint y de la disponibilidad de pruebas.

---

## 8. Ejemplo general de aplicación por sprint

Sin enumerar cada historia de usuario, la lógica común es: el **archivo de sprint** define el **incremento**; **Scrum** da el marco temporal y de aceptación; **AI-DLC** apoya redacción e implementación bajo revisión; **MLOps básico** entra de forma destacada cuando el sprint toca **contrato o despliegue del ML** (p. ej. sprint 4); **DevOps ligero** es transversal en sprints que tocan **Docker** o entorno; **Context Engineering** gana peso en sprints de **interfaz y guías visuales** (p. ej. 7A/7B y afines). Los sprints **9–10** desplazan el esfuerzo hacia **pruebas** y **documentación de cierre**, manteniendo la misma secuencia de validación humana.

Los detalles concretos de cada sprint deben tomarse de **`sprints/sprint *.md`**; este apartado solo fija el **patrón** metodológico.

---

## 9. Relación entre fuentes y aplicación en SIDERAE

Los códigos **F-AIDLC-01**, **F-AIDLC-02**, **F-IASE-01** y **F-IASE-02** (`docs/referencias/matriz-fuentes.md` y resúmenes en `docs/referencias/resumenes/`) respaldan de forma **preliminar** la narrativa metodológica; no sustituyen evidencia en código ni **validan** por sí solos cada decisión del proyecto.

- **F-AIDLC-01** (fuente **directa** del término **AI-SDLC** en el artículo; **equivalente parcial** respecto al **AI-DLC** operativo del proyecto): respalda argumentar el uso de **IA en el ciclo de vida** de extremo a extremo (requisitos, diseño, pruebas, despliegue, mantenimiento) como **referencia académica**, siempre con la salvedad de que la etiqueta del paper no es “AI-DLC”. En SIDERAE se traduce en **planificación e implementación asistidas** dentro de sprints, con revisión humana.

- **F-AIDLC-02** (fuente **complementaria**, roadmap **GenAI-augmented** SE): respalda situar **ChatGPT** y **Cursor** como herramientas de **aumento de procesos** (p. ej. rol pasivo reactivo a prompts), en la línea de copilotos y asistencia, **sin** afirmar que el prototipo cuente con **agentes autónomos completos** ni con un **pipeline empresarial** de IA.

- **F-IASE-01** (fuente **complementaria**, encuesta **AI-driven test automation** / AI4SE): respalda la **dimensión de pruebas y validación asistidas por IA** (taxonomías, CI/CD en la literatura de testing), alineable con el **sprint de pruebas**, `php artisan test` y planes manuales o E2E **solo** en la medida en que el repositorio y los sprints lo documenten; **no** atribuye al proyecto herramientas o coberturas no verificadas.

- **F-IASE-02** (fuente **complementaria**, **revisión** de innovaciones con IA en SE): respalda hablar de **IA aplicada a planificación, documentación y mantenimiento** a nivel de **tendencias y retos** descritos en la revisión (p. ej. generación de código, depuración, XAI a alto nivel), como **marco general** sin confundir el contenido del artículo con el alcance real de SIDERAE.

- **F-MLOPS-01** (fuente **complementaria**, revisión sistemática en *IEEE Access*): conecta el **microservicio Flask**, el contrato Laravel → Flask y la **persistencia** del resultado de riesgo con el **tipo de proceso y componentes** que la fuente ordena desde literatura (**adaptación**, no igualdad industrial). Ayuda a explicar **validación**, **fallos controlados** y **trazabilidad mínima** sin proyectar registros formales ni monitorización drift que solo aparecen como horizonte en esa literatura (**no** equivalen necesariamente a implementación vigente).

- **F-MLOPS-02** (fuente **complementaria**, *Artificial Intelligence Review* sobre plataformas **open source**): refuerza el discurso de **capacidades** (capas CI/CD vs CT tratadas por la fuente) y los **límites** que el propio texto atribuye a la cobertura de monitorización entre herramientas analizadas, matizando expectativas en un **prototipo académico** sin declarar pipelines ni reentreno automatizado (**adaptación**: evolución futura documentada sólo donde el código lo admita).

- **F-SCRUM-01** (guía **directa**, uso en proyecto **adaptado complementario**): se alinea con la **organización por sprints**, **incrementos revisables**, **criterios de aceptación** y **Definition of Done** operativa descrita en `scrum-complemento.md`; aporta lexico empírico (**transparencia, inspección, adaptación**) al **control de alcance** iterativo sin convertir Scrum en marco dominante (**AI-DLC** sigue gobernando asistencias generativas y revisión humana). En conjunto con **F-IASE-01** y **F-IASE-02**, encaja sprint de **pruebas** y **documentación** como inspección formalizable en el ciclo (**sin Scrum empresarial completo**).

---

## 10. Evidencias metodológicas esperadas

- **Documentos de sprint** en `sprints/` con objetivo, alcance y criterios.  
- **Commits** en el historial de Git con mensajes que permitan asociar cambios a un incremento o decisión.  
- **Pruebas** (`php artisan test`, build de frontend, planes de prueba manuales o E2E cuando existan).  
- **Documentación técnica** en `docs/arquitectura/` y matrices de acceso cuando el alcance lo requiera.  
- **README** del repositorio como resumen operativo del estado del prototipo.  
- **Matriz de trazabilidad** (por ejemplo RF–sprint–prueba) cuando se consolide en documentación de cierre; **pendiente de completar** si aún no está consolidada en el repo.

La ausencia de alguna evidencia no implica que el trabajo no exista; solo indica **deuda documental** a cerrar con el asesor.

---

## 11. Limitaciones

- **Equipo reducido** y **prototipo académico**: la metodología no implica madurez organizacional plena.  
- **Parcialidades** frente al DRS (dashboard, ML avanzado, RF-18, etc.) deben mantenerse visibles en documentación y no ocultarse tras la retórica de “sprint cerrado”.  
- **Dependencia de revisión humana** y de la calidad del contexto entregado a la IA.  
- **Trazabilidad fina** (cada commit ligado a caso de prueba) puede ser **futuro refinamiento** si hoy no está homogeneizada.

---

## 12. Conclusión

La metodología de SIDERAE se **aplica** mediante la combinación de **AI-DLC** (gobierno del uso de IA en el desarrollo), **Scrum** (sprints documentados), **MLOps básico** (servicio predictivo), **DevOps ligero** (entorno reproducible) y **Context Engineering** (calidad del contexto en prompts y revisiones). Su valor práctico es **ordenar** el trabajo y **dejar rastro** auditable para sustentación. El **respaldo bibliográfico inicial** incluye también **F-MLOPS-01**, **F-MLOPS-02** y **F-SCRUM-01** (véase `matriz-fuentes.md`); las **citas APA finales** quedarán para revisión previa a sustentación.

---

Referencias APA: pendientes de revisión final; resúmenes por código en `docs/referencias/resumenes/` y `matriz-fuentes.md`.
