# Mockup 06 — Registro de notas

## Objetivo de la pantalla

Permitir registrar nuevas calificaciones del estudiante y consultar el historial de notas registradas por año escolar, bimestre y curso.

Esta pantalla debe facilitar la captura ordenada de datos académicos, ya que las notas forman parte de la información utilizada para el análisis de riesgo académico del sistema SIDERAE-Blenkir.

---

## Elementos visibles

### Navegación lateral

- Logo o ícono institucional.
- Nombre del sistema: `SIDERAE`.
- Subtítulo: `Blenkir Analytics`.
- Menú lateral:
  - Dashboard
  - Estudiantes
  - Alertas
  - Intervenciones
  - Reportes
  - Configuración
- Opción activa: `Estudiantes`, resaltada con fondo claro y acento naranja.

### Header superior

- Nombre del sistema: `SIDERAE-Blenkir`.
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Navegación interna

- Enlace o acción para regresar:
  - `Volver a Perfil del Estudiante`

### Contenido principal

- Título: `Registro de Notas`.
- Información del estudiante:
  - `Estudiante: Maria Gomez (3er Año de Secundaria)`

### Formulario de nueva calificación

Tarjeta izquierda titulada: `Nueva Calificación`.

Campos visibles:

- `Año Escolar`
  - selector con valor visible: `2023-2024`
- `Bimestre`
  - selector con valor visible: `1er Bimestre`
- `Curso`
  - selector con valor visible: `Matemáticas`
- `Nota (0-20)`
  - campo numérico
  - placeholder: `Ej. 15`
- `Nota de Conducta`
  - selector con valor visible: `A`
- Botón principal:
  - `Guardar Nota`
  - ícono de guardado

### Historial de calificaciones

Tarjeta derecha titulada: `Historial de Calificaciones`.

Filtros visibles:

- `Todos los Años`
- `Todos los Bimestres`

Tabla con columnas:

- Curso
- Bimestre
- Nota
- Conducta
- Fecha de Registro

Filas de ejemplo:

- Matemáticas — 1er Bimestre — 12 — B — 15 Oct 2023
- Literatura — 1er Bimestre — 18 — A — 14 Oct 2023
- Física — 1er Bimestre — 09 — C — 16 Oct 2023

Las notas se muestran con etiquetas de color según rendimiento.

---

## Distribución visual

La pantalla está organizada en dos columnas principales:

- Columna izquierda:
  - formulario para registrar una nueva nota.
- Columna derecha:
  - historial de calificaciones registradas.

La parte superior contiene:

- botón o enlace para volver al perfil del estudiante.
- título de pantalla.
- identificación breve del estudiante.

El layout debe permitir registrar una nota y revisar inmediatamente el historial sin cambiar de pantalla.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Botón `Guardar Nota`: naranja `#F05A0E`.
- Texto principal: `#333333`.
- Bordes de inputs, selects y tabla: gris medio `#CCCCCC`.
- Acción de volver: texto gris/neutral o azul institucional `#1E63B5`.
- Notas altas: verde `#2FAF7B`.
- Notas medias: amarillo/naranja `#F59E0B`.
- Notas bajas: rojo `#DC2626`.

### Estilo

- Formulario compacto y claro.
- Tarjetas con bordes suaves.
- Inputs y selects con tamaño uniforme.
- Botón principal ancho y visible.
- Historial en tabla limpia.
- Notas destacadas mediante badges o etiquetas de color.
- Filtros ubicados en la cabecera del historial.

---

## Comportamiento esperado

- El usuario puede registrar una nota para el estudiante seleccionado.
- El sistema debe validar:
  - año escolar obligatorio
  - bimestre obligatorio
  - curso obligatorio
  - nota obligatoria
  - nota dentro del rango permitido
  - nota de conducta si aplica según la migración
- Al presionar `Guardar Nota`:
  - si los datos son válidos, se registra la nota.
  - el historial debe actualizarse.
  - se debe mostrar mensaje de éxito.
- Si hay errores:
  - mostrar mensajes de validación cerca de los campos.
- Los filtros `Todos los Años` y `Todos los Bimestres` deben permitir filtrar el historial.
- El enlace `Volver a Perfil del Estudiante` debe regresar al perfil del estudiante.
- Las notas del mockup son datos referenciales y no deben ser datos fijos.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo de datos académicos implementado en Sprint 3B.

Se relaciona con:

- Perfil del estudiante.
- Registro de datos académicos.
- Tabla de notas.
- Procesamiento de riesgo académico del Sprint 4.
- Permiso `registrar_datos_academicos`.

Las notas registradas forman parte del conjunto de datos utilizado para calcular el riesgo académico del estudiante.

---

## Observaciones para Cursor

- Rediseñar la sección o pantalla de registro de notas usando este mockup como referencia visual.
- No modificar la lógica funcional existente.
- No cambiar endpoints.
- No romper el registro de notas ya implementado.
- No inventar campos que no existan en la migración real de notas.
- No usar los datos del mockup como datos reales.
- Consumir las notas reales desde la API existente.
- Mantener relación con el estudiante seleccionado.
- Mantener compatibilidad con el perfil del estudiante.
- Mostrar u ocultar esta funcionalidad según el permiso `registrar_datos_academicos`.
- Si el sistema actualmente muestra las notas dentro del perfil, puede rediseñarse como sección interna o subpantalla, manteniendo la lógica actual.
- Mantener consistencia visual con:
  - perfil del estudiante
  - registro de asistencia
  - variables socioeconómicas
  - riesgo académico