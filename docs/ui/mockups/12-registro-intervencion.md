# Mockup 12 — Registro de intervención

## Objetivo de la pantalla

Permitir registrar una intervención asociada a una alerta activa de un estudiante en riesgo.

Esta pantalla debe facilitar que el usuario documente las acciones tomadas frente a una alerta, indicando el tipo de intervención, la fecha de ejecución y la descripción de los acuerdos o acciones realizadas.

---

## Elementos visibles

### Navegación lateral

- Logo o inicial institucional.
- Nombre del sistema: `SIDERAE`.
- Subtítulo: `Blenkir Analytics`.
- Menú lateral:
  - Dashboard
  - Estudiantes
  - Alertas
  - Intervenciones
  - Reportes
  - Configuración
- Opción activa: `Intervenciones`, resaltada con fondo claro y acento naranja.

### Header superior

- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Encabezado principal

- Flecha de retorno.
- Título: `Registro de Intervención`.
- Descripción: `Complete los detalles de la acción tomada basada en la alerta activa.`

### Panel izquierdo: contexto del estudiante

Tarjeta titulada: `Contexto del Estudiante`.

Contiene:

- Avatar o foto del estudiante.
- Nombre del estudiante: `Ana Martínez`.
- ID del estudiante.
- Grado/sección: `3ro Medio B`.

#### Alerta activa

Bloque destacado con:

- Etiqueta: `ALERTA ACTIVA`.
- Título de alerta: `Ausentismo Crítico`.
- Badge: `Riesgo Alto`.
- Descripción:
  - `El estudiante ha registrado 5 inasistencias consecutivas sin justificación formal en los últimos 10 días lectivos.`

### Panel izquierdo: historial reciente

Tarjeta titulada: `Historial Reciente`.

Incluye una línea de tiempo simple con eventos:

- `Llamada a apoderado`
  - `Hace 2 días`
  - `Sin respuesta`
- `Notificación automática`
  - `Hace 5 días`
  - `Enviada por sistema`

### Panel derecho: detalles de la intervención

Tarjeta principal titulada: `Detalles de la Intervención`.

Subtítulo:

- `Registre los acuerdos y acciones tomadas para mitigar el riesgo de la alerta.`

Campos visibles:

- `Tipo de Intervención *`
  - selector con placeholder: `Seleccione una categoría`
- `Fecha de Ejecución *`
  - campo tipo fecha con valor visible: `10/24/2023`
- `Otros Participantes (Opcional)`
  - campo de texto con placeholder: `Ej: Profesor Jefe, Orientador...`
- `Descripción y Acuerdos *`
  - área de texto con placeholder:
    - `Detalle los puntos discutidos y los compromisos adquiridos por el estudiante o apoderado...`

### Acciones

- Botón secundario: `Cancelar`.
- Botón principal: `Guardar Intervención`.
- Ícono de guardado en el botón principal.

---

## Distribución visual

La pantalla está organizada en dos columnas principales:

- Columna izquierda:
  - contexto del estudiante.
  - alerta activa.
  - historial reciente.

- Columna derecha:
  - formulario principal de registro de intervención.

La parte superior contiene el título y una acción de retorno. El formulario ocupa la mayor parte del espacio para facilitar la escritura de descripciones y acuerdos.

La pantalla debe permitir que el usuario vea el contexto de la alerta mientras registra la intervención.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: tono claro cálido derivado de `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Naranja oscuro: `#C94A0C` para énfasis y estados activos.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Botón `Guardar Intervención`: naranja `#F05A0E`.
- Botón `Cancelar`: fondo blanco o neutro con borde.
- Texto principal: `#333333`.
- Bordes de tarjetas, inputs y formularios: gris medio `#CCCCCC`.
- Riesgo alto: rojo `#DC2626`.
- Bloque de alerta activa: fondo rojo/naranja claro con borde suave.
- Historial reciente: tonos neutros cálidos.

### Estilo

- Tarjetas con bordes suaves.
- Formulario amplio y claro.
- Campos obligatorios marcados con `*`.
- Área de texto grande para acuerdos.
- Panel de contexto visible para evitar pérdida de información.
- Botón principal destacado.
- Línea de tiempo simple para historial reciente.
- Diseño institucional, claro y orientado a trazabilidad.

---

## Comportamiento esperado

- Al abrir la pantalla, debe cargarse la alerta seleccionada y los datos del estudiante relacionado.
- El usuario debe registrar:
  - tipo de intervención
  - fecha de ejecución
  - descripción y acuerdos
- El campo `Otros Participantes` es opcional si no existe como campo obligatorio en la lógica actual.
- Al presionar `Guardar Intervención`:
  - si los datos son válidos, debe registrarse la intervención.
  - la alerta debe pasar a estado `en_atencion` si estaba `pendiente`.
  - debe mostrarse mensaje de éxito.
  - debe volver al detalle de alerta o actualizar el historial.
- Al presionar `Cancelar`:
  - debe volver al detalle de alerta sin guardar cambios.
- Si faltan campos obligatorios:
  - deben mostrarse mensajes de validación.
- Los datos del mockup son referenciales y no deben usarse como datos fijos.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo de intervenciones implementado en Sprint 5.

Se relaciona con:

- Detalle de alerta.
- Alertas activas.
- Estudiantes en riesgo.
- Registro de intervenciones.
- Cambio de estado de alerta.
- Permiso `registrar_intervencion`.

Esta pantalla forma parte del flujo:

`Alerta generada → Detalle de alerta → Registrar intervención → Alerta en atención → Cierre de alerta`.

---

## Observaciones para Cursor

- Rediseñar el formulario de registro de intervención usando este mockup como referencia visual.
- No modificar la lógica funcional ya implementada.
- No cambiar endpoints.
- No romper el registro de intervenciones.
- No romper el cambio de estado de alerta a `en_atencion`.
- No romper el cierre posterior de alerta.
- No usar los datos del mockup como datos reales.
- Consumir datos reales desde la API de alertas e intervenciones.
- Mostrar esta pantalla o acción solo si el usuario tiene permiso `registrar_intervencion`.
- Si el sistema actualmente registra intervención desde el detalle de alerta en un formulario embebido, puede rediseñarse como sección, modal o subpantalla, manteniendo la lógica actual.
- Si algunos campos visuales del mockup no existen en la migración real, no agregarlos sin autorización; adaptar la interfaz a los campos existentes.
- Mantener consistencia visual con:
  - detalle de alerta
  - listado de alertas
  - perfil del estudiante
  - riesgo académico