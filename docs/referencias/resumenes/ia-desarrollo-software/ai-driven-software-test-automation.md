# AI-Driven Software Test Automation: An AI4SE-Oriented Survey of Techniques, Tools, and Challenges

## 1. Datos bibliográficos

- **Título:** AI-Driven Software Test Automation: An AI4SE-Oriented Survey of Techniques, Tools, and Challenges  
- **Autores:** Aazaade Faraji; Nuno Pombo (según primera página del PDF).  
- **Año:** 2025 (fechas received/accepted/publication en el PDF: septiembre–octubre de 2025).  
- **Tipo de fuente:** Artículo de revisión (*survey*) en revista de acceso abierto.  
- **Revista / conferencia / repositorio:** *IEEE Access*, volumen 13, 2025.  
- **DOI o enlace, si aparece:** 10.1109/ACCESS.2025.3623944 (aparece en el PDF; verificar en IEEE Xplore).  
- **Ubicación del PDF:** `docs/referencias/pdfs/ia-desarrollo-software/AI-Driven_Software_Test_Automation_An_AI4SE-Oriented_Survey_of_Techniques_Tools_and_Challenges.pdf`

## 2. Tema principal

El documento ofrece una **visión sistemática** de cómo métodos de inteligencia artificial (ML, DL, LLM, RL, entre otros) se aplican a la **automatización de pruebas** a lo largo del ciclo de pruebas: planificación, generación, ejecución, reparación y mantenimiento. Analiza un conjunto declarado de **76 herramientas industriales y trabajos revisados por pares**, propone una taxonomía orientada al ciclo de vida y discute tendencias, limitaciones y oportunidades (incluidas XAI, pruebas basadas en NLP e integración con CI/CD).

En conjunto, posiciona la automatización de pruebas con IA dentro del paraguas **AI4SE** (*Artificial Intelligence for Software Engineering*).

## 3. Aporte metodológico

- **Pruebas asistidas por IA:** aporta categorías y ejemplos de técnicas y herramientas para argumentar el **sprint de pruebas** y la estrategia de validación en SIDERAE **sin** afirmar que el repositorio implemente todas las técnicas citadas.  
- **IA en ingeniería de software (AI4SE):** útil para enlazar la actividad de testing con el marco más amplio de IA aplicada al SDLC.  
- **AI-DLC / GenAI-augmented SDLC:** el *survey* menciona LLM para generación de casos desde especificaciones en lenguaje natural; sirve de **puente conceptual** hacia el uso de IA generativa en pruebas, diferenciándolo del núcleo MLOps del microservicio Flask.

## 4. Relación con SIDERAE-Blenkir

- **Pruebas:** refuerza la importancia de pruebas automatizadas y de integración en pipelines; coherente con `php artisan test`, builds y planes del sprint 9 **tal como** estén documentados en el proyecto.  
- **Validación y control humano:** el *survey* señala retos de explicabilidad, dependencia de datos e integración industrial; útil para **justificar** revisión humana y evidencias explícitas en un prototipo académico.  
- **Documentación:** puede citarse al describir **qué** tipo de prácticas de test con IA existen en la literatura frente al **alcance real** de SIDERAE.  
- **No** atribuye al proyecto herramientas o pipelines (p. ej. RL sobre GUI) que **no** estén demostrados en el repositorio.

## 5. Ideas clave aprovechables

1. Taxonomía **ciclo de pruebas ↔ técnica de IA** para estructurar el capítulo de pruebas de la memoria.  
2. Argumento de **AI4SE** como contexto disciplinar del test con IA.  
3. Mención de **LLM** para generación de casos como apoyo teórico al uso de IA en el sprint de pruebas (sin confundir con cobertura real).  
4. **CI/CD** como horizonte de integración; alineable con DevOps ligero del proyecto.  
5. Limitaciones (**explainability**, datos, mantenimiento de scripts) como **paralelo** honesto con deuda técnica del prototipo.  
6. Lista de **herramientas** del *survey* solo como panorama del campo, no como stack de SIDERAE.  
7. **Preguntas de investigación** del artículo (RQs) como guía para futuras extensiones de la tesis.  
8. Refuerzo de que el testing con IA **no sustituye** criterios de aceptación ni revisión del producto.

## 6. Limitaciones de la fuente

- Es **revisión / encuesta** (*survey*), no aporta datos primarios nuevos sobre SIDERAE.  
- **Artículo indexado** en *IEEE Access* según el PDF.  
- **Alcance amplio** industrial y académico; no centrado en aplicaciones educativas ni en riesgo académico.  
- **No** usa el término **AI-DLC** del proyecto.  
- Algunas menciones de técnicas (p. ej. RL en GUI) pueden **sobredimensionar** expectativas si no se contextualizan al prototipo.  
- **Requiere complementarse** con fuentes sobre pruebas en equipos reducidos y sobre validación en proyectos de grado.

## 7. Uso recomendado dentro del proyecto

- `docs/metodologia/metodologia-siderae.md` — encaje de pruebas en el marco global.  
- `docs/metodologia/aplicacion-en-siderae.md` — evidencias de prueba y uso de IA en validación.  
- `docs/metodologia/referencias-metodologia.md` — bloque AI4SE / testing.  
- `docs/referencias/matriz-fuentes.md` — fila **F-IASE-01**.  
- `docs/estado-del-arte/` — más adelante, subsección específica de test automation con IA.

## 8. Referencia APA preliminar

Faraji, A., & Pombo, N. (2025). AI-driven software test automation: An AI4SE-oriented survey of techniques, tools, and challenges. *IEEE Access*, *13*, páginas según versión final del PDF. https://doi.org/10.1109/ACCESS.2025.3623944

*(Preliminar: completar página inicial–final desde el PDF impreso o Xplore.)*
