# Scrum como marco complementario de gestión en SIDERAE-Blenkir

## 1. Introducción

En el proyecto **SIDERAE-Blenkir**, la metodología **principal** del proceso de desarrollo es **AI-DLC** (*AI-Driven Development Life Cycle*), descrita en `docs/metodologia/ai-dlc.md` y enmarcada globalmente en `docs/metodologia/metodologia-siderae.md`. **Scrum no sustituye** ese núcleo metodológico: se adopta como **marco complementario de gestión** cuya función es **organizar la ejecución incremental** del trabajo en **sprints** documentados, con **objetivos**, **alcance**, **entregables** y **criterios de aceptación** explícitos.

En esta lectura, Scrum aporta **ritmo y estructura temporal** al repositorio (frontend React, backend Laravel, MySQL, microservicio Flask, Docker Compose, roles y permisos, alertas e intervenciones): divide el avance en **ciclos cerrables** que pueden revisarse frente al código y a la documentación interna. La **inteligencia artificial generativa** puede apoyar tareas dentro de cada ciclo según AI-DLC; **Scrum** define **qué** se pretende cerrar en cada iteración y **cómo** se declara el incremento respecto a ese objetivo.

La descripción siguiente es **adaptada al contexto académico** del proyecto. **No** constituye una aplicación literal ni certificable de Scrum empresarial. La **fuente oficial** disponible como respaldo inicial se codifica como **F-SCRUM-01** (véase §5).

---

## 2. Propósito de Scrum en SIDERAE

El propósito de usar Scrum de forma **complementaria** en SIDERAE es:

**Ordenar el trabajo por sprints.** Los archivos bajo `sprints/` y el resumen por sprint en el README del repositorio constituyen la **columna vertebral** de la planificación incremental: cada bloque temporal concentra un conjunto coherente de funcionalidades o refuerzos (infraestructura, seguridad, pruebas, documentación).

**Definir objetivos, alcance, actividades, entregables y criterios de aceptación.** Cada sprint documentado expresa qué se espera lograr y bajo qué condiciones se considera aceptable el resultado, lo cual reduce ambigüedad al contrastar implementación frente al documento de requisitos formal (DRS) cuando corresponda.

**Facilitar el seguimiento del avance.** Un lector externo (por ejemplo asesoría académica) puede ubicar en qué iteración se introdujo un módulo o un refuerzo (autenticación, riesgo, alertas, dashboard, control de accesos, pruebas, cierre documental).

**Permitir validación progresiva.** En lugar de validar solo al final del proyecto, cada incremento invita a **pruebas** y a **revisión** dentro del alcance declarado para ese sprint, alineado con la validación humana que exige AI-DLC.

---

## 3. Elementos Scrum aplicados

A continuación se describen **elementos de la familia Scrum** tal como se **reinterpretan** en SIDERAE. La nomenclatura ayuda a comunicar el proceso; la rigurosidad ceremonial se ajusta al tamaño del equipo y al carácter de prototipo académico.

**Product Backlog o lista de requerimientos.** El conjunto de necesidades funcionales y no funcionales del sistema, formalmente ancladas al DRS y contrastadas con el estado del código en `docs/arquitectura/resumen-arquitectura.md` y documentos afines. El backlog “vivo” del proyecto combina esas fuentes con decisiones de alcance registradas en sprints y README.

**Sprint Backlog o alcance del sprint.** Para cada iteración documentada, el conjunto de ítems, actividades y límites que el equipo se compromete a abordar en ese ciclo. Corresponde al contenido operativo de cada archivo de sprint en `sprints/`.

**Sprint Planning o planificación del sprint.** Actividad de lectura y acuerdo sobre el objetivo del sprint, dependencias (por ejemplo, permisos antes de exponer acciones en interfaz) y riesgos. Puede apoyarse en IA según AI-DLC, pero la **priorización** y el **recorte** de alcance son responsabilidad humana.

**Incremento funcional.** Resultado potencialmente entregable al cierre del sprint: código, configuración o documentación que cumple el alcance declarado y los criterios de aceptación de esa iteración. No todo incremento equivale a un requerimiento del DRS cerrado al 100 %; el propio README reconoce implementaciones **parciales** en algunos módulos.

**Sprint Review o revisión del resultado.** Contraste del incremento con el objetivo del sprint y, cuando aplica, demostración de flujos en entorno local (por ejemplo login, registro de datos, procesamiento de riesgo, alertas). La evidencia es **el comportamiento del sistema** y la documentación actualizada, no solo la intención declarada.

**Sprint Retrospective o mejora del proceso.** Reflexión breve sobre qué facilitó u obstaculizó el ciclo (calidad del contexto para la IA, tamaño del alcance, pruebas insuficientes) para alimentar el siguiente sprint. En equipos reducidos puede ser informal pero **registrada** cuando sea relevante para trazabilidad académica.

**Definition of Done o definición de “terminado”.** Conjunto de condiciones mínimas que debe cumplir un avance antes de considerarse cerrado dentro del sprint (véase sección **8** de este documento). Sirve de puente entre el incremento y la revisión.

---

## 4. Adaptación académica de Scrum

No se aplica un **Scrum empresarial completo** con todos los roles, artefactos y eventos en la forma prescrita por guías corporativas o certificaciones. El proyecto es un **prototipo académico** con un equipo **reducido**, por lo que varios “roles” clásicos de Scrum (**Product Owner**, **Scrum Master**, **equipo de desarrollo** como roles separados) **no se formalizan** de manera rígida.

Se utiliza una **versión adaptada**: el **desarrollador o líder del proyecto** concentra planificación operativa, ejecución técnica y validación cotidiana del incremento, con **asesoría académica** para alinear prioridades y criterios de calidad, y con **usuarios o perfiles de prueba** (por ejemplo roles demo locales) para validación funcional cuando el sprint lo exija.

Esta adaptación es coherente con el uso simultáneo de **AI-DLC**: la IA acelera y estructura trabajo dentro del sprint, pero **no** reemplaza la responsabilidad sobre el alcance, las pruebas ni el cierre documental.

---

## 5. Fuente de respaldo metodológico

**F-SCRUM-01** corresponde a la **guía oficial** *The Scrum Guide* (versión noviembre de 2020 según el PDF del repositorio), codificada en `docs/referencias/matriz-fuentes.md` y resumida en `docs/referencias/resumenes/scrum/scrum-guide-2020-definitive-guide-rules-of-the-game.md`.

- **Tipo de vínculo:** la guía es **fuente directa** de la **definición canónica** del marco Scrum (empirismo, pilares **transparencia**, **inspección** y **adaptación**, eventos contenidos en el Sprint, artefactos y compromisos asociados, **Definition of Done** formal del Increment). Este documento del proyecto es **adaptación contextual**, no reprodución textual de esa guía.

- **Adaptación en SIDERAE:** Scrum se usa **solo como marco complementario** para **organizar sprints**, **entregables incrementales**, **revisión de avances**, **Definition of Done** operativa mediante criterios de aceptación documentados por sprint y validación humana continuada (**AI-DLC** sigue como metodología principal). Lo descrito aquí —equipo compacto, roles fusionados— **no** equivale a **Scrum empresarial completo** ni a la multiplicidad de equipos u obligaciones institucionales que podrían deducirse de la guía en contextos grandes.

Las afirmaciones de la fuente oficial sobre Scrum “en su totalidad” no implican certificación externa ni estándar de madurez alcanzado por este prototipo académico.

---

## 6. Relación con los sprints de SIDERAE

La tabla resume la **línea de sprints** descrita en la documentación del proyecto (archivos en `sprints/` y resumen en el README). Los textos de **incremento** condensan el propósito del bloque sin sustituir el detalle de cada archivo de sprint; donde el README indica **alcance parcial** frente al DRS, el incremento debe entenderse en ese matiz.

| Sprint | Propósito | Incremento entregado (lectura global) | Relación con la metodología |
| ------ | --------- | --------------------------------------- | ----------------------------- |
| **1** | Infraestructura Docker y arranque del entorno | Stack local reproducible (servicios base según README) | Establece el **incremento mínimo** sobre el que se apoyan los sprints siguientes; define el **contexto técnico** para AI-DLC y pruebas. |
| **2** | Autenticación, roles y permisos | API y modelo de autorización operativos para el resto de módulos | **Backlog del producto** ejecutable en capa seguridad; reduce riesgo de retrabajo en sprints posteriores. |
| **3A** | CRUD de estudiantes y perfil básico | Gestión de estudiantes con validaciones | Incremento funcional **acotado**; base para datos académicos en 3B. |
| **3B** | Notas, asistencia y variables socioeconómicas | Registro de datos académicos y socioeconómicos integrados al perfil | Incremento orientado a **insumos** para el cálculo de riesgo en el sprint 4. |
| **4** | Integración Laravel con Flask ML y persistencia de riesgo | Flujo explícito de procesamiento de riesgo e índice persistido (según documentación técnica del repo) | Incremento **transversal** (API + servicio ML); exige **validación** de contrato y pruebas acordes al sprint. |
| **5** | Alertas, intervenciones y cierre | Módulo de alertas e intervenciones alineado al flujo de riesgo | Incremento de **negocio** sobre el núcleo predictivo; cierra un ciclo valorizable para revisión. |
| **6A** | Dashboard mínimo (KPIs, tabla de riesgo, alertas por estado) | Endpoint y vista de dashboard demostrable con datos operativos (según `sprints/sprint 6A.md`) | Incremento **visible** acotado; base para filtros y export en 6B. |
| **6B** | Filtros, export básico y ajuste por rol | Filtros sobre dashboard, exportación básica y visibilidad por rol (según `sprints/sprint 6B.md`) | Complementa 6A; el README documenta **implementación parcial** del dashboard/export frente al DRS donde corresponda. |
| **7A** | Rediseño UI/UX global | Layout, guía visual y componentes base alineados a mockups (`sprints/sprint 7A.md`) | Incremento de **presentación** transversal sin sustituir validación backend. |
| **7B** | Pantallas completas y navegación según mockups | Pantallas principales y navegación alineadas a la guía UI (`sprints/sprint 7B.md`) | Incremento de **usabilidad** y coherencia de interfaz; prepara correcciones finas de 7.5. |
| **7.5A** | Correcciones funcionales P0 antes de seguridad | Brechas críticas DRS vs código, trazas mínimas de auditoría y coherencia documental (`sprints/sprint 7.5A.md`) | **Puente** hacia Sprint 8; incremento de **estabilidad** y trazabilidad, sin ampliar alcance de forma descontrolada. |
| **7.5B** | Corrección visual final de interfaces | Ajuste visual de interfaces frente a mockups y guía, sin nuevas funcionalidades de backend (`sprints/sprint 7.5B.md`) | Refina el incremento de UI; coherencia **solo presentación** respecto a 7B. |
| **7.6A** | Materias/cursos administrables por estructura académica | Catálogo de materias asociado a sede/nivel/grado/año (`sprints/sprint 7.6A.md`) | Incremento de **datos maestros**; prerequisito lógico para notas masivas con materia catalogada en 7.6B. |
| **7.6B** | Registro masivo de asistencia y notas | Endpoints y flujos de carga masiva de asistencia y notas (`sprints/sprint 7.6B.md`) | Incremento de **operación institucional**; el propio sprint documenta dependencias y puntos **pendientes de verificar** antes de codificar. |
| **8** | Seguridad, roles, auditoría y control de accesos | Refuerzo de autorización y coherencia UI–permisos (matriz documentada en arquitectura) | Incremento de **gobierno** y trazabilidad; prepara campañas de prueba del sprint 9. |
| **9** | Pruebas integrales y regresión | Evidencias de ejecución de pruebas y corrección de defectos según plan de sprint | Incremento de **calidad** y estabilidad declarada; alimenta el cierre documental del sprint 10. |
| **10** | Documentación final y cierre de calidad | Manuales, arquitectura y paquete de evidencias para defensa académica | Incremento **documental**; cierra el ciclo de visibilidad del proyecto sin confundir con certificación externa. |

---

## 7. Relación con AI-DLC

**AI-DLC** orienta **cómo** se emplea la inteligencia artificial generativa (planificación asistida, construcción asistida, revisión, documentación) con **validación humana obligatoria**. **Scrum**, en este proyecto, orienta **en qué ciclos** y **bajo qué compromisos de alcance** ocurre ese trabajo: cada sprint acota el **Sprint Backlog** y el **incremento** esperado.

Dentro de cada sprint, la IA puede apoyar la **planificación** (desglose de tareas), la **implementación** (borradores de código), la **revisión** (lectura de diffs, detección de riesgos obvios) y la **documentación** (síntesis alineada a `docs/arquitectura/`). El **cierre del sprint** sigue exigiendo **validación humana**: criterios de aceptación, pruebas y revisión de cambios frente al alcance declarado, independientemente de la velocidad aparente aportada por la IA.

En suma: **AI-DLC** gobierna el uso de la IA a lo largo del ciclo de vida; **Scrum** (adaptado) **compartimenta** el tiempo y el **compromiso** de entrega en iteraciones revisables.

---

## 8. Criterios de aceptación y Definition of Done

Los siguientes criterios, aplicados con criterio humano por sprint, orientan la **Definition of Done** del proyecto. No todos serán igualmente exigibles en cada iteración (por ejemplo, el sprint 10 pondera documentación sobre código nuevo), pero el conjunto define el **espíritu** del “terminado” en SIDERAE.

- **Funcionalidad implementada según el alcance** descrito en el documento del sprint correspondiente y coherente con el estado reconocido en README o en `docs/arquitectura/` cuando haya matices de alcance parcial.
- **Sin ruptura intencionada de módulos anteriores**: regresiones conocidas deben detectarse en pruebas o quedar **registradas** como deuda explícita.
- **Pruebas manuales ejecutadas** para los flujos críticos del incremento cuando el sprint lo defina (por ejemplo autenticación, permisos, flujo de riesgo y alertas).
- **Pruebas automatizadas ejecutadas cuando correspondan** (por ejemplo `php artisan test`, build de frontend), según disponibilidad y plan del sprint.
- **Documentación actualizada** si el incremento altera contratos, despliegue, permisos o flujos descritos para asesoría o para el usuario del prototipo.
- **Revisión de `git diff`** antes de considerar cerrado el trabajo, para evitar cambios colaterales no acordados.
- **Pendientes registrados** cuando el incremento no cierre un requerimiento formal completo: la honestidad metodológica forma parte del DoD del proyecto académico.

---

## 9. Ventajas del uso de Scrum en SIDERAE

- **Orden incremental:** el avance se fragmenta en unidades narrables y defendibles académicamente.
- **Trazabilidad:** vínculo claro entre objetivo de sprint, código y documentación asociada.
- **Control de alcance:** el Sprint Backlog acota qué se intenta en cada ciclo frente a un DRS amplio.
- **Priorización:** dependencias naturales (infraestructura y seguridad antes de refinamientos de UI o de campañas de prueba) queden explícitas en la secuencia de sprints.
- **Validación progresiva:** reduce el riesgo de acumular deuda técnica y funcional hasta una única entrega final opaca.
- **Mejor preparación para sustentación:** la defensa puede apoyarse en la **línea temporal** de incrementos y en evidencias del sprint de pruebas y del de documentación.

---

## 10. Limitaciones

- **No es Scrum empresarial completo:** faltan por diseño formalizaciones de roles, métricas de flujo y ceremonias a escala organizacional.
- **Equipo reducido:** la carga de planificación, ejecución, prueba y documentación recae en pocas personas; el marco Scrum aquí es **ligero**.
- **Algunos eventos se adaptan o fusionan:** retrospectivas o revisiones pueden ser más breves que en un entorno industrial, sin perder el sentido de **mejora** y **contraste con el objetivo**.
- **La revisión depende de evidencia real:** el incremento se juzga por ejecución en entorno reproducible y por pruebas, no por la retórica del informe.
- **Citación APA de la guía:** el vínculo con la definición canónica se documenta mediante el resumen **F-SCRUM-01**; el formato APA final debe revisarse contra la versión concreta del PDF antes de sustentación.

---

## 11. Conclusión

En SIDERAE-Blenkir, **Scrum cumple un rol complementario** respecto a **AI-DLC**: no define la metodología principal de uso de la inteligencia artificial, pero **organiza** el trabajo en **sprints** con **objetivos**, **entregables** y **criterios de aceptación** que hacen **auditable** el avance incremental del prototipo. Su principal aporte es **estructurar** la ejecución y la **validación progresiva** del sistema (desde infraestructura hasta pruebas y documentación final), de modo que AI-DLC opere **dentro** de ciclos de compromiso claros y revisables.

---

Respaldo Scrum: ver **F-SCRUM-01** en `docs/referencias/resumenes/scrum/` y `matriz-fuentes.md`.
