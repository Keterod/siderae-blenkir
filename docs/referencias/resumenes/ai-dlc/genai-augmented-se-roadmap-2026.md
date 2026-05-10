# A Research Roadmap for Augmenting Software Engineering Processes and Software Products with Generative AI

## 1. Datos bibliográficos

- **Título:** A Research Roadmap for Augmenting Software Engineering Processes and Software Products with Generative AI  
- **Autores:** Domenico Amalfitano; Andreas Metzger; Marco Autili; Tommaso Fulcini; Tobias Hey; Jan Keim; Patrizio Pelliccione; Vincenzo Scotti; Anne Koziolek; Raffaela Mirandola; Andreas Vogelsang (lista en metadatos y primera página del PDF).  
- **Año:** El PDF incluye referencia ACM “2025” y sello arXiv “4 Feb 2026” para la versión `v3` (arXiv:2510.26275v3). Para citas finales, unificar criterio (año de publicación ACM vs. fecha arXiv) consultando la versión de registro definitiva.  
- **Tipo de fuente:** Roadmap / artículo largo derivado de *design science* y revisión rápida de literatura; manuscrito indicado como “Manuscript submitted to ACM” en el PDF, con identificador arXiv.  
- **Revista / conferencia / repositorio:** arXiv (`cs.SE`) y contexto de publicación ACM (TOSEM-SI mencionado en el PDF); verificar estado editorial actual fuera de este resumen.  
- **DOI o enlace, si aparece:** https://doi.org/10.48550/arXiv.2510.26275 (en metadatos del PDF); https://arxiv.org/abs/2510.26275v3  
- **Ubicación del PDF:** `docs/referencias/pdfs/ai-dlc/genai-augmented-se-roadmap-2026.pdf`

## 2. Tema principal

La fuente construye una **hoja de ruta de investigación** para la ingeniería de software **aumentada con GenAI**, combinando ciclos de *design science*, evidencia de talleres (p. ej. FSE 2025 “Software Engineering 2030”), revisiones rápidas y retroalimentación entre pares. Utiliza dimensiones que distinguen el aumento de **procesos** frente a **productos** de software, y el rol **pasivo** frente a **activo** de la GenAI, obteniendo cuatro formas de aumento (p. ej. copiloto, *GenAIware*, compañero de equipo GenAI, robot GenAI, según el cuerpo del artículo).

El objetivo declarado es ofrecer una base **transparente y reproducible** para analizar efectos de la GenAI en procesos, métodos y herramientas, y para orientar investigación futura.

## 3. Aporte metodológico

- **GenAI-augmented SDLC / procesos de SE:** articula explícitamente la necesidad de replantear modelos de ciclo de vida y artefactos (incluido código, datos, modelos y *prompts*).  
- **IA en ingeniería de software:** cubre automatización de actividades (requisitos, generación de pruebas, CI/CD, refactorización) y productos cuya funcionalidad se realiza con GenAI.  
- **AI-DLC / AI-SDLC:** el documento **no** denomina “AI-DLC” al proyecto SIDERAE; aporta vocabulario y clasificación (**GenAI-augmented** proceso vs. producto; pasivo vs. activo) útil para **alinear** la descripción metodológica del proyecto con la literatura reciente.

## 4. Relación con SIDERAE-Blenkir

- **Planificación:** encaja con el uso de IA en desglose de tareas y priorización **dentro** de sprints documentados; SIDERAE no implementa el roadmap completo, pero puede **motivar** la argumentación sobre aumento de procesos.  
- **Implementación y revisión técnica:** las formas “copiloto” y similares describen de manera académica el uso de Cursor/LLM como **asistente**, coherente con validación humana obligatoria.  
- **Documentación y validación:** el artículo enfatiza retos de rendición de cuentas, equipos híbridos humano–IA y coordinación proceso–producto; útil para **matizar** limitaciones del prototipo.  
- **Pruebas:** menciona explícitamente generación y priorización de casos de prueba como ámbito de GenAI; relacionable con el sprint de pruebas y con encuestas sobre test automation **sin** afirmar herramientas concretas no usadas en el repo.  
- **Control humano y trazabilidad:** el marco distingue autonomía pasiva vs. activa; SIDERAE puede citar esto para **justificar** el no uso de agentes totalmente autónomos sin supervisión.

## 5. Ideas clave aprovechables

1. Clasificar el uso de GenAI en SIDERAE como aumento de **proceso** (principalmente) frente a **producto** (riesgo vía ML es otro eje).  
2. Explicitar interacción **humano-disparador** vs. escenarios **más autónomos** como deuda o futuro.  
3. Vincular **prompts** y artefactos de IA a la noción de “artefactos” del SDLC ampliado en el roadmap.  
4. Usar las **predicciones hacia 2030** del paper como marco reflexivo, no como compromiso del proyecto.  
5. Conectar **McLuhan tetrads** y DSR solo si la memoria lo explique con rigor; si no, mencionar el método del paper de forma breve.  
6. Apoyar la necesidad de **matrices de trazabilidad** y evidencias entre proceso y producto.  
7. Relacionar **Agentic AI** con limitaciones actuales del prototipo (sin agentes desatados).  
8. Citar **desafíos transversales** (prompt engineering, accountability) en limitaciones de la tesis.

## 6. Limitaciones de la fuente

- Es un **roadmap** de investigación, no un manual de implementación.  
- Combina **revisión rápida**, taller y DSR; **no** es meta-análisis empírico primario de efectos en un solo sistema.  
- Incluye referencia ACM con **placeholder** de DOI (`https://doi.org/XXXXXXX.XXXXXXX`) en una sección del PDF: la referencia final debe **corregirse** cuando exista DOI ACM definitivo.  
- **Alcance general** de la disciplina SE; no estudia sistemas educativos ni riesgo académico.  
- **No** usa el término interno **AI-DLC** del proyecto.  
- **Requiere complementarse** con fuentes empíricas sobre equipos pequeños y sobre MLOps educativo.

## 7. Uso recomendado dentro del proyecto

- `docs/metodologia/metodologia-siderae.md` — posicionamiento frente a “GenAI-augmented SE”.  
- `docs/metodologia/ai-dlc.md` — vocabulario y límites del aumento de procesos.  
- `docs/metodologia/aplicacion-en-siderae.md` — cómo se mapea el uso de Cursor al esquema proceso/producto.  
- `docs/metodologia/referencias-metodologia.md` — categoría GenAI / roadmap.  
- `docs/referencias/matriz-fuentes.md` — fila **F-AIDLC-02**.  
- `docs/estado-del-arte/` — en fase posterior, para contrastar con otros roadmaps.

## 8. Referencia APA preliminar

Amalfitano, D., Metzger, A., Autili, M., Fulcini, T., Hey, T., Keim, J., Pelliccione, P., Scotti, V., Koziolek, A., Mirandola, R., & Vogelsang, A. (2025). A research roadmap for augmenting software engineering processes and software products with generative AI. *Manuscrito asociado a ACM / arXiv*, arXiv:2510.26275v3. https://doi.org/10.48550/arXiv.2510.26275

*(Preliminar: confirmar datos bibliográficos finales —volumen, páginas, DOI ACM— cuando el artículo esté publicado de forma estable.)*
