# Mockup 08 — Variables socioeconómicas

## Objetivo de la pantalla

Permitir registrar y actualizar las variables socioeconómicas del estudiante, con el fin de complementar el análisis de riesgo académico y deserción estudiantil.

Esta pantalla debe facilitar la captura del contexto familiar, económico, de vivienda y acceso a servicios básicos del estudiante, manteniendo una interfaz clara, académica e institucional.

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

### Contenido principal

- Título: `Variables Socioeconómicas`.
- Descripción: `Gestión y actualización del contexto socioeconómico del estudiante.`
- Etiqueta identificadora:
  - `ID Estudiante: 2023-8942`

### Sección Información Familiar

Tarjeta titulada: `Información Familiar`.

Campos visibles:

- `Nivel Educativo Principal Sostenedor`
  - selector con placeholder: `Seleccionar nivel...`
- `Situación Laboral Sostenedor`
  - selector con placeholder: `Seleccionar situación...`
- `Integrantes del Grupo Familiar`
  - campo numérico o texto
  - placeholder: `Ej. 4`

### Sección Ingresos y Vivienda

Tarjeta titulada: `Ingresos y Vivienda`.

Campos visibles:

- `Rango de Ingreso Familiar (Mensual)`
  - selector con placeholder: `Seleccionar rango...`
- `Tipo de Tenencia de Vivienda`
  - selector con placeholder: `Seleccionar tenencia...`
- `Acceso a Servicios Básicos e Internet`
  - checkbox `Agua Potable`
  - checkbox `Electricidad`
  - checkbox `Internet Hogar (Banda Ancha)`

### Panel lateral de riesgo socioeconómico

Tarjeta titulada: `Estado Actual de Riesgo Socioeconómico`.

Incluye:

- Badge o bloque destacado:
  - `Riesgo Medio`
  - texto: `Basado en la última actualización (hace 6 meses)`

### Panel lateral de resumen

Tarjeta titulada: `Resumen de Datos Actuales`.

Incluye información resumida:

- `Beneficios Estatales Activos`
  - `Beca de Alimentación (BAES)`
  - `Subsidio Familiar`
- `Índice de Vulnerabilidad Escolar (IVE)`
  - `68% - Quintil 2`
- `Distancia Promedio al Establecimiento`
  - `15 km (Aprox. 45 min en transporte público)`

### Acciones

- Botón secundario: `Cancelar`
- Botón principal: `Guardar Variables`
- Ícono de guardado en el botón principal.

---

## Distribución visual

La pantalla usa una estructura de captura de datos socioeconómicos:

- Sidebar fijo a la izquierda.
- Header superior horizontal.
- Encabezado de pantalla con título, descripción e ID del estudiante.
- Área principal dividida en dos columnas:
  - columna izquierda amplia para formularios.
  - columna derecha para estado y resumen socioeconómico.
- Dentro de la columna izquierda:
  - tarjeta de información familiar.
  - tarjeta de ingresos y vivienda.
- Acciones de guardado ubicadas en la parte inferior del formulario.

Esta distribución permite registrar datos y, al mismo tiempo, visualizar un resumen del contexto socioeconómico actual.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro o tono cálido suave derivado de `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Botón `Guardar Variables`: naranja `#F05A0E`.
- Botón `Cancelar`: blanco o neutro con borde suave.
- Texto principal: `#333333`.
- Bordes de tarjetas, inputs y selects: gris medio `#CCCCCC`.
- Riesgo medio: amarillo/naranja `#F59E0B` o `#F05A0E`.
- Mensajes de riesgo: fondo amarillo/naranja claro.
- Íconos de sección: naranja institucional.

### Estilo

- Tarjetas con bordes suaves.
- Secciones separadas por temática.
- Formularios ordenados en dos columnas.
- Inputs y selects con altura uniforme.
- Checkboxes visibles y alineados.
- Panel lateral informativo con jerarquía clara.
- Botón principal destacado.
- Diseño limpio, institucional y fácil de leer.

---

## Comportamiento esperado

- El usuario puede registrar o actualizar las variables socioeconómicas del estudiante.
- El sistema debe validar los campos obligatorios según la migración real.
- Los selectores deben mostrar opciones válidas definidas por el sistema.
- Los checkboxes deben permitir marcar servicios disponibles.
- Al presionar `Guardar Variables`:
  - si los datos son válidos, se guarda o actualiza la información socioeconómica.
  - se muestra mensaje de éxito.
  - el resumen lateral debe actualizarse si corresponde.
- Al presionar `Cancelar`:
  - debe volver al perfil del estudiante o limpiar/cancelar el formulario según el flujo actual.
- Si hay errores:
  - deben mostrarse mensajes de validación cerca de los campos.
- El estado de riesgo socioeconómico debe basarse en datos reales si la lógica existe.
- Si aún no existe cálculo específico de riesgo socioeconómico, mostrar solo la información disponible sin inventar resultados.
- Los datos visibles en el mockup son referenciales y no deben ser usados como datos fijos.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo de variables socioeconómicas implementado en Sprint 3B.

Se relaciona con:

- Perfil del estudiante.
- Registro de datos académicos/contextuales.
- Procesamiento de riesgo académico del Sprint 4.
- Generación de alertas del Sprint 5.
- Permiso `registrar_datos_academicos`.

Las variables socioeconómicas registradas deben formar parte del conjunto de datos utilizado para calcular el índice de riesgo académico.

---

## Observaciones para Cursor

- Rediseñar la sección o pantalla de variables socioeconómicas usando este mockup como referencia visual.
- No modificar la lógica funcional existente.
- No cambiar endpoints.
- No romper el registro de variables socioeconómicas ya implementado.
- No inventar campos que no existan en la migración real de variables socioeconómicas.
- No usar los datos del mockup como datos reales.
- Consumir datos reales desde la API existente.
- Mantener relación con el estudiante seleccionado.
- Mantener compatibilidad con el perfil del estudiante.
- Mostrar u ocultar esta funcionalidad según el permiso `registrar_datos_academicos`.
- Si algunos campos visuales del mockup no existen en la base de datos actual, no agregarlos sin autorización; adaptar la interfaz a los campos reales existentes.
- Si el sistema actualmente muestra variables dentro del perfil, puede rediseñarse como sección interna o subpantalla.
- Mantener consistencia visual con:
  - registro de notas
  - registro de asistencia
  - perfil del estudiante
  - riesgo académico