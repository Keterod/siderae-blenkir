# F-EDA-03 - Development of an Early Warning System to Support Educational Planning Process by Identifying At-Risk Students

## 1. Datos bibliográficos

- **Código:** F-EDA-03  
- **Título:** Development of an Early Warning System to Support Educational Planning Process by Identifying At-Risk Students *(según página de título del PDF)*  
- **Autores:** M. Skittou, M. Merrouchi, T. Gadi *(según PDF)*  
- **Año:** No identificado en la revisión inicial como año de volumen editorial completo *(el PDF indica versión de autores “accepted for publication in IEEE Access”)*; identificador DOI: **10.1109/ACCESS.2023.3348091** *(año 2023 en el DOI; no se asume número/paginación final sin el documento publicado completo en esta ficha)*  
- **Tipo de fuente:** Artículo de investigación / aplicación de sistema de apoyo a la decisión *(manuscrito aceptado según nota de pie)*  
- **Revista / conferencia / repositorio:** *IEEE Access* *(indicado en el PDF como destino de publicación)*  
- **DOI o enlace, si aparece:** https://doi.org/10.1109/ACCESS.2023.3348091  
- **Ubicación del PDF:** `docs/referencias/pdfs/estado-del-arte/early-warning-system-at-risk-students-2024.pdf`

## 2. Tema principal

**Dato textual del PDF:** Los autores describen el desarrollo de un sistema de alerta temprana (EWS) orientado a la planificación educativa, integrando factores socioculturales, estructurales y educativos que impactan la decisión de abandono; construyen una base de datos original agregando sistemas operativos marroquíes (Massar, ESISE, GRESA, SAGE) y comparan algoritmos de ML; reportan desempeño particularmente alto con K-NN y visualización mediante una aplicación Django.

**Interpretación:** Ejemplo de EWS educativo anclado en datos administrativos nacionales y flujo predictivo-soporte a decisión.

## 3. Contexto educativo estudiado

**Dato textual del PDF:** El estudio se presenta como caso del sistema educativo de Marruecos; indica confinar la investigación al **ciclo primario** (*“confine this research to the primary cycle”*), con **125 354** alumnos en la base y clases admitidos / no admitidos / abandono según Tabla 1 del artículo.

**Proximidad a SIDERAE-Blenkir:** Parcial: es primaria y gestión pública de datos, no un único colegio privado/prototipo; el marco de integración de EMIS es ilustrativo del tipo de problemas de datos que tienen los EWS.

## 4. Variables consideradas

Atributos descritos en el PDF (nombres y significado según sección *Data Set*): área urbana/rural (*CD_MIL*), sexo (*Genre*), ayuda social (*SocialAid*), procedencia educativa previa (*Provenance*), tipo de centro (*TypeEtab*: público/privado/no formal), discapacidad (*Disability*/*Handicap* con tipos), nivel/grado del ciclo primario (*Niveau*), media de calificaciones (*Moy*), retraso escolar calculado (*RetardSco*), y variable objetivo *Result* con clases admitido / no admitido / abandonó (Tabla 1). También se menciona unión con datos de sistemas de exámenes y asistencia social comunal.

## 5. Técnica, modelo o enfoque utilizado

- Marco conceptual EWS —etapas *collect, analyze, detect, alert, notify, assess risk, act* sintetizadas en el texto.  
- Educational Data Mining y rol en EMIS.  
- Pipeline de extracción, *join* por identificadores de estudiante, imputación y codificación binaria cuando aplica.  
- Modelos contrastados textualmente incluyen **KNN**, SVM, Random Forest, SGD y otros mencionados en la sección de resultados *(la tabla cuantitativa principal en el PDF cita valores de accuracy para KNN en entrenamiento y prueba muy altos, p. ej. >99% como indica el resumen; verificar cifras exactas en tabla del PDF si se citan formalmente).*  
- Herramienta de visualización: aplicación Django *(según RESUMEN/ABSTRACT)*.

## 6. Resultados o aportes principales

- Según **ABSTRACT** del PDF, el algoritmo **KNN** alcanzó una precisión (**accuracy**) superior al **99,5 %** en el conjunto de entrenamiento y superior al **99,3 %** en el conjunto de prueba.  
- En la comparación textual de clasificadores, el mismo apartado menciona otros algoritmos (SVM, Random Forest, SGD, etc.) con métricas reportadas en tablas posteriores de la versión disponible *(valores línea por línea: reproducir sólo desde la tabla del PDF al citarlos formalmente).*  
- Los autores vinculan el sistema desarrollado a la planificación educativa mediante una aplicación **Django** para visualización.

## 7. Limitaciones de la fuente

- Versión **“author’s accepted manuscript”**: contenido puede variar antes de la publicación definitiva IEEE Access.  
- Metadatos placeholder en cabeceras del PDF (“VOLUME XX”, fechas xxx) según texto extraído.  
- Enfoque en **primaria Marruecos** —limitada transferencia directa.  
- Riesgos metodológicos típicos de alta performance en clasificación (sobreajuste o protocolo evaluativo) deben revisarse contra tablas completas antes de extrapolar políticas.

## 8. Relación con SIDERAE-Blenkir

**Interpretación cautelosa:** Documenta cómo sistemas institucionales pueden alimentar un EWS mediante integración EMIS-like y ML — útil conceptualmente si SIDERAE apunta a datos administrativos y alertas cualitativas.

**Aclaración:** Sin evidencia del repositorio aportado aquí, **no se afirma** que SIDERAE use Massar/KNN/Django ni que reproduzca tasas predictivas mencionadas; SIDERAE se entiende como **prototipo académico** con alcance ajeno al EMIS nacional descrito.

## 9. Uso recomendado dentro del estado del arte

- Sistemas de alerta temprana académica  
- Educational Data Mining / analítica administrativa  
- Planificación educativa y soporte a decisión institucional  
- Sistemas similares o enfoques relacionados  

## 10. Referencia APA preliminar

Skittou, M., Merrouchi, M., & Gadi, T. (fecha por confirmar contra versión final). Development of an early warning system to support educational planning process by identifying at-risk students. *IEEE Access*. https://doi.org/10.1109/ACCESS.2023.3348091 *(preliminar: completar año, volumen y páginas con la versión de publicación final).*
