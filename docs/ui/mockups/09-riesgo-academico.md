# Mockup 09 — Riesgo académico

## Objetivo de la pantalla

Permitir procesar, visualizar e interpretar el riesgo académico de un estudiante a partir de los datos registrados en el sistema.

Esta pantalla debe mostrar de forma clara el índice de riesgo actual, el análisis del modelo, las variables consideradas y el historial de evaluaciones anteriores. Su objetivo es apoyar la toma de decisiones preventivas antes de que el estudiante llegue a una situación crítica.

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
- Opción activa visible: `Alertas`, resaltada con fondo claro y acento naranja.

### Header superior

- Nombre del sistema: `SIDERAE-Blenkir`.
- Buscador global con placeholder:
  - `Buscar estudiante o métrica...`
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Encabezado de pantalla

- Título: `Procesamiento de Riesgo Académico`.
- Datos del estudiante:
  - nombre del estudiante
  - ID del estudiante
- Panel derecho con:
  - texto `Última actualización: 23 Oct 2023`
  - botón principal `Procesar Riesgo`

### Índice de riesgo actual

Tarjeta principal izquierda:

- Título: `Índice de Riesgo Actual`.
- Valor destacado: `85%`.
- Badge o etiqueta: `Riesgo Alto`.
- Fecha de proceso:
  - `Fecha de proceso: 24 Oct 2023`
- Etiqueta del modelo:
  - `Modelo v2.4`

### Análisis del modelo

Tarjeta superior derecha:

- Título: `Análisis del Modelo`.
- Ícono relacionado con análisis/inteligencia.
- Texto interpretativo:
  - el estudiante presenta riesgo alto.
  - se recomienda seguimiento académico e intervención preventiva.
  - los factores de deserción muestran un patrón acelerado en las últimas semanas.

### Variables consideradas

Tarjeta inferior derecha titulada: `Variables consideradas`.

Incluye factores visibles:

- `Inasistencias Consecutivas`
  - impacto alto
- `Bajas Notas Promedio`
  - impacto alto
- `Acceso a Plataforma`
  - impacto medio
- `Retraso en Entregas`
  - impacto bajo

Cada variable se presenta en una tarjeta pequeña con indicador de color e impacto.

### Historial de evaluaciones

Tarjeta inferior titulada: `Historial de Evaluaciones`.

Incluye:

- Acción `Exportar`.
- Tabla con columnas:
  - Fecha
  - Índice
  - Nivel de riesgo
  - Acción sugerida

Filas visibles:

- `15 Sep 2023` — `62%` — `Riesgo Medio` — `Monitoreo activo de asistencia`
- `01 Sep 2023` — `35%` — `Riesgo Bajo` — `Sin acción requerida`
- `15 Ago 2023` — `28%` — `Riesgo Bajo` — `Evaluación inicial de ciclo`

---

## Distribución visual

La pantalla está organizada en una estructura de análisis:

- Sidebar fijo a la izquierda.
- Header superior con buscador y usuario.
- Encabezado principal con título, estudiante y botón de procesamiento.
- Primera sección en dos columnas:
  - tarjeta grande del índice de riesgo actual a la izquierda.
  - análisis del modelo y variables consideradas a la derecha.
- Segunda sección completa inferior:
  - historial de evaluaciones en tabla.

La lectura visual debe iniciar en el índice de riesgo actual, continuar con la interpretación del modelo y terminar con el historial de evaluaciones.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro o tono cálido suave derivado de `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Botón `Procesar Riesgo`: naranja oscuro/institucional `#C94A0C` o `#F05A0E`.
- Texto principal: `#333333`.
- Bordes de tarjetas y tabla: gris medio `#CCCCCC`.
- Riesgo alto: rojo `#DC2626`.
- Riesgo medio: amarillo/naranja `#F59E0B`.
- Riesgo bajo: verde `#2FAF7B`.
- Elementos informativos o acciones secundarias: azul institucional `#1E63B5`.

### Estilo

- El índice de riesgo debe ser visualmente dominante.
- El riesgo alto debe usar rojo para llamar la atención.
- Las tarjetas deben tener bordes suaves y buena separación.
- Los factores considerados deben mostrarse en tarjetas pequeñas.
- El historial debe presentarse en tabla clara y legible.
- El botón `Procesar Riesgo` debe ser una acción principal visible.
- El análisis del modelo debe tener un estilo de recomendación institucional, no técnico excesivo.

---

## Comportamiento esperado

- Al abrir la pantalla, debe cargarse el último índice de riesgo del estudiante si existe.
- Si no existe riesgo procesado, debe mostrarse un estado vacío claro.
- Al presionar `Procesar Riesgo`:
  - el sistema debe enviar los datos del estudiante al backend.
  - Laravel debe comunicarse con el microservicio Flask.
  - el sistema debe recibir el índice de riesgo.
  - debe guardar el resultado en la base de datos.
  - debe actualizar la pantalla con el nuevo índice, nivel y fecha.
- Si el resultado es riesgo alto:
  - debe generarse alerta automática según el flujo del Sprint 5, si aplica.
- El historial de evaluaciones debe listar riesgos procesados previamente.
- El botón `Exportar` puede preparar la exportación de historial si la funcionalidad existe o quedar como acción futura.
- Si faltan datos mínimos para procesar riesgo:
  - debe mostrarse un mensaje claro de error.
- Si el servicio ML no responde:
  - debe mostrarse un error controlado, sin romper la pantalla.

---

## Relación con el sistema actual

Esta pantalla corresponde al procesamiento de riesgo académico implementado en Sprint 4.

Se relaciona con:

- Datos del estudiante.
- Notas registradas.
- Asistencia registrada.
- Variables socioeconómicas.
- Microservicio Flask de Machine Learning.
- Tabla de índices de riesgo.
- Alertas automáticas del Sprint 5.
- Perfil del estudiante.
- Permiso `procesar_riesgo`.

El resultado de esta pantalla alimenta directamente el módulo de alertas cuando el nivel de riesgo es alto.

---

## Observaciones para Cursor

- Rediseñar la sección o pantalla de riesgo académico usando este mockup como referencia visual.
- No modificar la lógica de Machine Learning ya implementada.
- No cambiar endpoints.
- No romper la comunicación Laravel → Flask.
- No romper la persistencia del índice de riesgo.
- No romper la generación de alertas automáticas.
- No usar los datos del mockup como datos reales.
- Consumir el último riesgo y el historial desde los datos reales disponibles.
- Si actualmente el riesgo académico se muestra dentro del perfil del estudiante, puede rediseñarse como sección interna o subpantalla manteniendo la lógica actual.
- Mostrar u ocultar el botón `Procesar Riesgo` según el permiso `procesar_riesgo`.
- Mostrar errores claros cuando falten datos mínimos.
- Mantener consistencia visual con:
  - perfil del estudiante
  - dashboard
  - alertas
  - detalle de alerta