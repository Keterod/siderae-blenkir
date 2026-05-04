# Mockup 11 — Detalle de alerta

## Objetivo de la pantalla

Mostrar el detalle completo de una alerta académica asociada a un estudiante en riesgo, permitiendo revisar el contexto del caso, el estado actual, la recomendación del sistema y el historial de intervenciones realizadas.

Esta pantalla debe servir como vista principal de seguimiento de una alerta específica, facilitando acciones como registrar intervención, cerrar alerta o imprimir un reporte del caso.

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
- Opción activa: `Alertas`, resaltada con fondo claro y acento naranja.

### Header superior

- Buscador global con placeholder:
  - `Buscar estudiantes, ID o alertas...`
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Navegación interna

Breadcrumb visible:

- `Inicio`
- `Alertas`
- `Alerta #ALT-8402`

### Encabezado de pantalla

- Título: `Detalle de Alerta`.
- Subtítulo: `Revisión exhaustiva y gestión de caso activo.`
- Botones de acción:
  - `Imprimir Reporte`
  - `Cerrar Alerta`
  - `Registrar Intervención`

### Tarjeta principal del estudiante

Contiene:

- Foto o avatar del estudiante.
- Nombre del estudiante: `Mateo Valenzuela Ríos`.
- ID del estudiante.
- Grado y sección.
- Indicador visual pequeño de alerta sobre la foto.
- Badge de nivel:
  - `Riesgo Alto`
- Badge de estado:
  - `En Atención`

### Contexto de la alerta

Tarjeta titulada: `Contexto de la Alerta`.

Incluye:

- Fecha de detección.
- Categoría de la alerta.
- Descripción del sistema.

La descripción aparece dentro de un bloque destacado con fondo suave.

### Recomendación SIDERAE

Bloque destacado titulado: `Recomendación SIDERAE`.

Incluye:

- Recomendación de intervención inmediata.
- Sugerencia de entrevista con apoderado y estudiante.
- Sugerencia de activar protocolo de reforzamiento académico.

### Historial de intervenciones

Panel lateral derecho titulado: `Historial de Intervenciones`.

Incluye una línea de tiempo con eventos:

- Asignación de caso.
- Generación de alerta.
- Notificación previa.

Cada evento muestra:

- fecha/hora.
- título del evento.
- descripción breve.

También incluye enlace:

- `Ver historial completo`

---

## Distribución visual

La pantalla está organizada en dos columnas:

- Columna principal izquierda:
  - encabezado del detalle.
  - acciones principales.
  - tarjeta del estudiante.
  - contexto de la alerta.
  - recomendación del sistema.

- Columna lateral derecha:
  - historial de intervenciones en formato línea de tiempo.

La información crítica se ubica arriba:

1. datos del estudiante,
2. nivel de riesgo,
3. estado de alerta,
4. acciones principales.

La recomendación del sistema se muestra como un bloque destacado para facilitar la toma de decisiones.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: tono claro cálido derivado de `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Naranja oscuro: `#C94A0C` para acción importante `Registrar Intervención`.
- Verde `#2FAF7B` para acción positiva `Cerrar Alerta`.
- Rojo `#DC2626` para riesgo alto y alertas críticas.
- Amarillo/naranja `#F59E0B` para estado `En Atención`.
- Texto principal: `#333333`.
- Bordes suaves: `#CCCCCC`.
- Bloques de alerta/recomendación: fondos suaves derivados del naranja o rojo.

### Estilo

- Tarjetas con bordes suaves y sombras ligeras.
- Badges de estado claros y diferenciados por color.
- Botones de acción visibles y alineados.
- Línea de tiempo vertical para intervenciones.
- Bloque de recomendación con fondo destacado.
- Uso moderado del naranja para mantener estilo institucional sin saturar.

---

## Comportamiento esperado

- Al abrir la pantalla, debe cargarse el detalle real de la alerta seleccionada.
- El botón `Registrar Intervención` debe abrir el formulario o pantalla de registro de intervención.
- El botón `Cerrar Alerta` debe permitir cerrar la alerta solo si existe al menos una intervención registrada.
- Si se intenta cerrar una alerta sin intervención, debe mostrarse error de validación.
- El botón `Imprimir Reporte` puede generar o preparar un reporte del caso si la funcionalidad está implementada.
- El historial debe mostrar intervenciones reales asociadas a la alerta.
- El enlace `Ver historial completo` debe mostrar más intervenciones o navegar a una vista ampliada si existe.
- Los badges deben reflejar el estado real de la alerta:
  - pendiente
  - en atención
  - cerrada
- Los datos del mockup son referenciales y no deben usarse como datos fijos.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo de alertas implementado en Sprint 5.

Se relaciona con:

- Listado de alertas.
- Alertas generadas por riesgo alto.
- Índices de riesgo académico.
- Estudiantes.
- Registro de intervenciones.
- Cierre de alertas.
- Permisos:
  - `ver_alertas`
  - `registrar_intervencion`

---

## Observaciones para Cursor

- Rediseñar el detalle de alerta existente usando este mockup como referencia visual.
- No modificar la lógica funcional ya implementada.
- No cambiar endpoints.
- No romper el listado de alertas.
- No romper el registro de intervenciones.
- No romper el cierre controlado de alertas.
- No usar los datos del mockup como datos reales.
- Consumir datos reales desde la API de alertas.
- Mostrar u ocultar acciones según permisos:
  - `ver_alertas` para acceder al detalle.
  - `registrar_intervencion` para registrar intervención y cerrar alerta.
- El botón `Imprimir Reporte` puede quedar como acción futura si no existe funcionalidad de reportes.
- Mantener el cierre de alerta bloqueado si no hay intervenciones.
- Mantener consistencia visual con:
  - listado de alertas
  - registro de intervención
  - perfil del estudiante
  - riesgo académico