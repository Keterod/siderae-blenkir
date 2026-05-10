# Estado del arte — SIDERAE-Blenkir

## Propósito de esta carpeta

Centralizar el **estado del arte** documentado de SIDERAE-Blenkir: riesgo académico, deserción estudiantil, modelos predictivos educativos, analíticas y minería sobre datos escolares, sistemas de alerta temprana (EWS), variables académico-sociales y marcos de intervención y seguimiento tal como se relacionan en la literatura seleccionada. El contenido puede ampliarse antes de la sustentación final; esta revisión **no se considera cerrada de forma definitiva**.

## Documentos incluidos

| Documento | Propósito | Estado |
| --------- | --------- | ------ |
| [estado-del-arte-siderae.md](./estado-del-arte-siderae.md) | Estado del arte académico frente al prototipo SIDERAE-Blenkir y las fuentes F-EDA-01 a F-EDA-08. | Redacción inicial creada; pendiente de revisión final. |
| [fuentes-pendientes.md](./fuentes-pendientes.md) | Temas y necesidades bibliográficas por eje temático; tabla resumen de cobertura. | Base inicial parcialmente cubierta; fuentes ampliables. |
| [matriz-comparativa.md](./matriz-comparativa.md) | Comparación sintética de las fuentes revisadas contra variables, métodos y relación con SIDERAE. | Matriz inicial con 8 fuentes F-EDA-01 a F-EDA-08. |

## Orden recomendado de trabajo (actualización continua)

1. Mantener **alineación** entre PDF, ficha en `docs/referencias/resumenes/estado-del-arte/` y fila correspondiente en `matriz-comparativa.md`.
2. Refinar **`estado-del-arte-siderae.md`** (tono académico, citaciones definitivas APA cuando proceda, coherencia con fichas tras cambios menores).
3. Actualizar **`fuentes-pendientes.md`** al incorporar o excluir temas cuando se amplíe el corpus.
4. Completar o ajustar la **estrategia de búsqueda** en el documento principal si el equipo publica protocolo replicable más adelante.

## Estado actual

Existe ya una base operativa de ocho fuentes codificadas **F-EDA-01 a F-EDA-08**, con PDFs locales, fichas de resumen y una matriz comparativa poblada; el texto central del estado del arte está **redactado en versión inicial** y conviene revisarlo antes de la sustentación final. Siguen pendientes revisión bibliográfica fina (por ejemplo homogeneización APA), posible incorporación de nuevas fuentes y síntesis crítica más extensa conforme definan los autores institucionales.

## Diferencia entre metodología y estado del arte

- **Metodología** (`docs/metodologia/`): describe *cómo* se ejecuta el proyecto (procesos, roles, ciclo de vida del software, prácticas de desarrollo aplicables). No debe confundirse con la revisión de literatura sobre el dominio educativo-predictivo.
- **Estado del arte** (esta carpeta y referencias enlazadas): sintetiza *qué establece la literatura y estudios relacionados* respecto del problema (riesgo, deserción, EWS, etc.) y sitúa a SIDERAE frente a ello sin presentar esa revisión como exhaustiva ni definitiva.

## Almacenamiento centralizado de fuentes del estado del arte

Las evidencias utilizadas para construir esta revisión están organizadas así:

| Ubicación | Uso |
| --------- | --- |
| [docs/referencias/pdfs/estado-del-arte/](../referencias/pdfs/estado-del-arte/) | PDF íntegro de cada artículo base (actualmente los ocho trabajos F-EDA asociados al corpus inicial). |
| [docs/referencias/resumenes/estado-del-arte/](../referencias/resumenes/estado-del-arte/) | Fichas o resúmenes (`f-eda-*.md`) derivados de esos PDFs. |

Mantener coherencia entre cada PDF, su ficha y la fila correspondiente en `matriz-comparativa.md` facilita la auditoría bibliográfica y futuras ampliaciones.
