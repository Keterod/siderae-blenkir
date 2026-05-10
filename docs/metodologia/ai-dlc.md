# AI-DLC (AI-Driven Development Life Cycle) como metodología principal en SIDERAE-Blenkir

## 1. Introducción

**AI-DLC** (*AI-Driven Development Life Cycle*) designa, en sentido conceptual, un **ciclo de vida del desarrollo de software** en el que la **inteligencia artificial generativa** participa de forma **sistemática** en actividades tradicionalmente ejecutadas solo por personas: comprensión de requerimientos, descomposición del trabajo, redacción o refactorización de código, revisión, análisis de fallos y producción de documentación técnica. No se trata de un único instrumento (un chat o un plugin), sino de una **postura metodológica**: la IA se incorpora al flujo con reglas explícitas de **gobierno**, **trazabilidad** y **validación humana**.

En el proyecto **SIDERAE-Blenkir** —sistema web para detección temprana de riesgo académico, gestión de datos estudiantiles y apoyo a alertas e intervenciones— **AI-DLC se adopta como denominación metodológica principal** del proceso de ingeniería: ordena *cómo* y *cuándo* se invoca la IA frente al código y la documentación del repositorio (React, Laravel, MySQL, microservicio Flask, Docker Compose, roles y permisos, sprints documentados). Esa etiqueta **no** aparece tal cual en las fuentes actuales del repositorio, que hablan de **AI-SDLC**, **GenAI-augmented** SE, **AI4SE** o revisiones generales de IA en SE; la relación es de **alineación y respaldo parcial**, no de equivalencia literal. Otros elementos del marco global del proyecto (gestión por iteraciones, prácticas técnicas transversales) se documentan aparte; **este texto se centra exclusivamente en AI-DLC**.

La definición operativa aquí expuesta **no pretende** sustituir una revisión bibliográfica formal. Los términos y la delimitación de fases responden a la **necesidad del equipo** de describir de manera honesta y auditable el trabajo realizado. El **respaldo académico preliminar** más cercano al hilo AI-DLC / IA en SDLC y pruebas se resume en esta sección mediante **F-AIDLC-01**, **F-AIDLC-02**, **F-IASE-01** y **F-IASE-02**. El inventario **completo** de fuentes metodológicas (incluidas **F-MLOPS-01**, **F-MLOPS-02**, **F-SCRUM-01**) aparece en `docs/referencias/matriz-fuentes.md` y en `referencias-metodologia.md`; las citas APA quedan **pendientes de revisión final**.

---

## 2. Propósito de AI-DLC en SIDERAE

El propósito de adoptar AI-DLC en SIDERAE es **triple**:

1. **Aumentar la productividad cognitiva** del desarrollador en tareas de exploración, redacción y generación de borradores, sin confundir velocidad con corrección automática.
2. **Homogeneizar la calidad del discurso técnico** (arquitectura, sprints, comentarios de cambio) al exigir que toda salida de la IA sea **contrastable** con archivos del repositorio y con criterios de aceptación del sprint vigente.
3. **Reducir la dispersión** en un stack heterogéneo (frontend, backend, servicio ML) al forzar un ciclo recurrente: requerimiento → contexto → plan → construcción → validación → documentación → lecciones para la siguiente iteración.

En la práctica del proyecto, AI-DLC se relaciona directamente con **planificación asistida** (desglose de tareas, identificación de riesgos), **implementación asistida** (propuestas de código sujetas a revisión), **validación** (lectura de errores, sugerencia de casos de prueba, checklist) y **documentación** (síntesis de arquitectura, alineación con README y `docs/arquitectura/`). La IA **no** fija el alcance frente al documento de requisitos formal ni sustituye la decisión sobre qué está “terminado”; solo **acelera** y **estructura** el trabajo bajo supervisión humana.

---

## 3. Principios de AI-DLC aplicados

Los principios siguientes guían el uso de AI-DLC en SIDERAE. Constituyen **normas de conducta** del proceso, no garantías formales de calidad.

**Desarrollo asistido por IA.** La IA participa como copiloto en tareas de análisis, redacción y codificación, siempre invocada con instrucciones explícitas y, cuando sea posible, con fragmentos o rutas de archivos relevantes del proyecto.

**Validación humana obligatoria.** Ningún cambio se considera aceptado por el solo hecho de haber sido propuesto por un modelo de lenguaje. El desarrollador revisa semántica, seguridad, convenciones del repositorio y compatibilidad con pruebas existentes.

**Uso de contexto estructurado.** Antes de pedir cambios amplios, se reúne contexto legible para la IA: descripción del sprint, archivos de arquitectura, reglas de proyecto, partes del código involucradas. Así se mitiga la invención de APIs o rutas inexistentes.

**Iteración incremental.** El trabajo se alinea con **entregas por sprint** (documentados del 1 al 10 en el repositorio): cada ciclo AI-DLC produce incrementos pequeños y revisables, no “big bang” generados de una sola solicitud vaga.

**Trazabilidad de decisiones.** Las decisiones relevantes deben quedar reflejadas en mensajes de commit, comentarios de revisión o documentación actualizada, de modo que un tercero pueda reconstruir *por qué* se adoptó un enfoque, independientemente de si la idea surgió de la IA o del desarrollador.

**Validación técnica antes del cierre.** Cierre de una tarea o sprint solo después de ejecutar pruebas acordadas (por ejemplo pruebas automatizadas del backend o verificaciones manuales de flujos críticos descritos en documentación de sprint), no únicamente tras una respuesta satisfactoria del modelo.

**Documentación como parte del ciclo.** La documentación no es un apéndice final opcional: forma parte del “hecho” metodológico de AI-DLC, porque es el medio principal para que la asesoría y futuros lectores validen el uso responsable de la IA y el estado del prototipo.

---

## 4. Fases de AI-DLC adaptadas al proyecto

Las fases siguientes adaptan el ciclo AI-DLC al contexto de SIDERAE. Son **secuenciales en lo conceptual**, pero admiten **retrocesos controlados** (por ejemplo, volver a preparación de contexto si la IA propone soluciones fuera de alcance).

**Análisis del requerimiento.** Lectura del objetivo y alcance del sprint en `sprints/`, del README y, cuando corresponda, de `docs/arquitectura/` (por ejemplo flujo Laravel–Flask para riesgo, o matriz de permisos). Se identifican riesgos: datos sensibles, autorización, contratos entre servicios.

**Preparación del contexto.** Se seleccionan archivos y extractos pertinentes (rutas API, modelos, componentes React, `ml-service`, `docker-compose`) para acotar la ventana de contexto de la sesión con la IA.

**Planificación asistida por IA.** La IA puede proponer lista de tareas, orden de implementación y checklist de pruebas. El desarrollador **recorta, reordena y descarta** lo que no encaje con el backlog real o con dependencias entre capas.

**Construcción asistida.** Implementación por pasos: cambios localizados, compilación o ejecución de tests frecuentes, revisión de diff. La IA sugiere; el desarrollador integra y corrige.

**Validación y pruebas.** Ejecución de pruebas automatizadas donde existan, pruebas manuales de flujos críticos (autenticación, permisos, procesamiento de riesgo, alertas) y verificación de que no se introdujeron regresiones obvias.

**Documentación y cierre.** Actualización de documentación técnica o de sprint cuando el incremento lo requiera; registro explícito de limitaciones si el alcance del DRS no está cerrado en código.

**Retroalimentación para el siguiente sprint.** Breve registro de qué funcionó o no en el uso de la IA (prompts demasiado amplios, contexto insuficiente, falsos positivos en revisión) para mejorar la **preparación de contexto** y los **criterios de aceptación** del siguiente ciclo.

---

## 5. Relación con el uso de Cursor y ChatGPT

En el marco AI-DLC del proyecto, **Cursor** y **ChatGPT** se emplean como **asistentes técnicos** intercambiables según conveniencia: el primero suele estar más acoplado al árbol de código y a la edición; el segundo puede usarse para razonamiento o redacción cuando el contexto se pega de forma controlada.

La IA puede **proponer** diseños, fragmentos de código, explicaciones de error, borradores de pruebas o secciones de documentación; puede **revisar** consistencia nominal o detectar patrones riesgosos en un diff. No obstante, **el desarrollador humano valida, decide y corrige**: acepta o rechaza cambios, ajusta la arquitectura real del repositorio y asume la responsabilidad académica y técnica del entregable. AI-DLC formaliza esa división de labor; no la elimina.

---

## 6. Aplicación práctica en SIDERAE

La aplicación concreta de AI-DLC en SIDERAE incluye, entre otras, las siguientes líneas de trabajo:

- **Creación de prompts por sprint**, alineados al archivo de sprint correspondiente y a las rutas o módulos que se van a tocar (por ejemplo permisos en backend y visibilidad en frontend).
- **Revisión de arquitectura**, contrastando respuestas de la IA con `ARCHITECTURE.md`, `docs/arquitectura/resumen-arquitectura.md` y los documentos de contexto por componente (Laravel, React, Flask, Docker).
- **Generación de planes** de implementación y de orden de dependencias entre tareas (por ejemplo, consolidar API antes de ajustar la UI consumidora).
- **Apoyo en errores**, aportando trazas de log o mensajes de compilación para acotar causas, siempre verificadas contra el comportamiento real en contenedores locales.
- **Documentación técnica** (síntesis de flujos, listas de endpoints, explicación de decisiones de seguridad o de integración ML) como salida revisada, no como texto autónomo.
- **Preparación de pruebas**: borradores de casos o de aserciones, sujetos a criterios del sprint y al plan de pruebas del proyecto cuando aplique.
- **Revisión de avances**: solicitar a la IA un resumen de diff o de riesgos de regresión antes de fusionar o antes de cerrar el sprint, sin sustituir la lectura humana del código.

Todo ello opera sobre el conjunto tecnológico ya citado (React, Laravel, MySQL, Flask, Docker Compose, roles, alertas e intervenciones) y sobre la **trazabilidad** ya presente en el repositorio (sprints 1–10, documentos de arquitectura).

---

## 7. Diferencia frente a desarrollo tradicional

En un **ciclo de desarrollo tradicional** centrado exclusivamente en el humano, la planificación detallada, la redacción de borradores largos, la búsqueda de patrones en código ajeno al módulo actual y la primera versión de documentación suelen consumir una fracción grande del tiempo calendario, sobre todo en equipos reducidos y con múltiples lenguajes.

**AI-DLC** desplaza parte de ese esfuerzo hacia un **bucle humano–máquina**: la máquina produce candidatos de solución o de texto en segundos; el humano **filtra, corrige y valida**. El desarrollador deja de ser únicamente el “ejecutor manual de cada línea” y pasa a ser, de forma explícita, **supervisor, integrador y validador técnico** del trabajo conjunto. Ello **no** implica menor competencia técnica: exige mayor capacidad de **lectura crítica**, de **prueba** y de **gobernanza del alcance**, porque los errores de la IA pueden ser plausibles pero incorrectos en el contexto del proyecto.

---

## 8. Riesgos y controles

**Riesgos**

- **Errores de la IA**: código que compila pero viola reglas de negocio, permisos o contratos entre servicios.
- **Código no alineado al proyecto**: uso de librerías, patrones o convenciones ajenas al repositorio.
- **Invención de funcionalidades** o de endpoints que no existen, por contexto incompleto o prompts ambiguos.
- **Cambios no autorizados** respecto al alcance del sprint o al documento de requisitos formal, si el operador acepta sugerencias sin contraste.
- **Dependencia excesiva de prompts** (“prompt engineering” sin ingeniería): iteraciones largas sin pruebas ni commits incrementales.

**Controles**

- **Revisión humana sistemática** de todo cambio sustancial.
- **Criterios de aceptación** del sprint como lista de comprobación explícita.
- **Pruebas** automatizadas y manuales según disponibilidad en el proyecto.
- **Inspección de `git diff`** antes de consolidar, para detectar archivos tocados accidentalmente o alcance desbordado.
- **Commits por avance** lógico, con mensajes que permitan revertir o auditar.
- **Documentación de decisiones** cuando se elija una alternativa frente a la sugerida por la IA (por ejemplo, limitar una funcionalidad al estado real del prototipo).

---

## 9. Fuentes de respaldo metodológico

Las siguientes fuentes (**resúmenes** en `docs/referencias/resumenes/`, códigos en `matriz-fuentes.md`) aportan contexto académico **preliminar**. Ninguna **valida de forma definitiva** la etiqueta **AI-DLC** del proyecto; varias usan **otros términos** (AI-SDLC, GenAI-augmented SE, AI4SE, revisión general de IA en SE).

| Código | Tipo de vínculo con AI-DLC | Contribución metodológica (según resumen interno) |
| ------ | -------------------------- | --------------------------------------------------- |
| **F-AIDLC-01** | **Equivalente parcial** en el eje ciclo de vida + IA: la fuente denomina **AI-SDLC**, no AI-DLC. | Marco de ciclo de vida con IA y optimización integradas en fases de requisitos a mantenimiento. |
| **F-AIDLC-02** | **Complementaria** (GenAI-augmented SE / roadmap). | Clasificación de aumentos con GenAI en procesos y productos; discute copilotos, roles pasivo/activo y líneas de investigación, **sin** implicar que SIDERAE despliegue agentes autónomos completos ni un pipeline empresarial. |
| **F-IASE-01** | **Complementaria** específica de **pruebas** (encuesta AI4SE orientada a test automation). | Fundamenta la dimensión de **pruebas y validación asistidas por IA** y el marco AI4SE en el ámbito de testing; no atribuye al repositorio herramientas o resultados no documentados. |
| **F-IASE-02** | **Complementaria** de **panorama amplio** (revisión de innovaciones en SE con IA). | Contextualiza IA en el SDLC (p. ej. generación de código, depuración, mantenimiento predictivo, retos éticos) como telón de fondo; el **énfasis** del documento no es el de F-IASE-01 (testing). |

Este apartado **no** sustituye un estado del arte: solo ancla la metodología del proyecto en **lecturas ya resumidas** en el repositorio.

---

## 10. Limitaciones en el proyecto

AI-DLC en SIDERAE se aplica en un **contexto académico de prototipo**, no en un producto con gobierno corporativo de IA ni con equipos de revisión independiente. Ello acota la generalización de cualquier conclusión sobre eficacia o riesgo.

No se documenta aquí el uso de **agentes autónomos** que modifiquen el repositorio de extremo a extremo sin intervención humana continua; el flujo descrito supone **asistencia** bajo control del desarrollador.

Tampoco se asume **despliegue empresarial** ni cumplimiento de estándares sectoriales más allá de lo que el propio repositorio y la asesoría académica exijan.

Este capítulo sintetiza el vínculo con las **cuatro fuentes citadas arriba** respecto del núcleo **AI-DLC**; Scrum y MLOps cuentan con códigos y resúmenes propios en la misma matriz y en otros documentos de `docs/metodologia/`. Pueden añadirse **nuevas** lecturas siguiendo el mismo procedimiento si la asesoría lo recomienda. El texto sigue siendo **guía metodológica del proyecto**, no certificación externa del marco AI-DLC en abstracto.

---

## 11. Conclusión

**AI-DLC** permite **ordenar** el uso de la inteligencia artificial generativa en el desarrollo de SIDERAE-Blenkir: define fases, principios y controles mínimos para que la IA sea una **herramienta gobernada** dentro del ciclo de vida, y no un factor impredecible ajeno a la trazabilidad del repositorio. La metodología **refuerza** que la IA **apoya** en planificación, construcción, revisión y documentación, pero **no reemplaza** la **validación humana** ni la responsabilidad sobre el código, las pruebas y la honestidad respecto al alcance del prototipo.

---

Referencias APA finales: pendientes de revisión; borradores en `docs/referencias/resumenes/` y listado codificado en `referencias-metodologia.md` / `matriz-fuentes.md`.
