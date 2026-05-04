# Mockup 07 — Registro de asistencia

## Objetivo de la pantalla

Permitir registrar y consultar la asistencia de un estudiante, indicando el año escolar, bimestre, semana de inicio, estado de asistencia y observaciones.

Esta pantalla debe facilitar el seguimiento de la asistencia del estudiante, ya que este dato influye directamente en el análisis de riesgo académico y en la detección temprana de posibles alertas.

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

- Buscador global con placeholder: `Buscar estudiante...`.
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Navegación interna

- Breadcrumb:
  - `Estudiantes`
  - `García, Valentina`
  - `Registro de Asistencia`

### Contenido principal

- Título: `Registro de Asistencia`.
- Información del estudiante:
  - `Estudiante: García, Valentina - 4to Año Secundaria`.

### Formulario de nuevo registro

Tarjeta principal titulada: `Nuevo Registro`.

Campos visibles:

- `Año Escolar`
  - selector con valor visible: `2023-2024`
- `Bimestre`
  - selector con valor visible: `1er Bimestre`
- `Semana de Inicio`
  - campo tipo fecha
  - placeholder: `mm/dd/yyyy`
- `Estado`
  - botones de selección:
    - `Presente`
    - `Tardanza`
    - `Falta`
- `Observaciones (Opcional)`
  - área de texto con placeholder: `Detalles adicionales sobre la asistencia...`
- Botón principal:
  - `Guardar Asistencia`
  - ícono de guardado

### Tarjetas resumen

Tarjeta derecha superior:

- Título: `Asistencia Total`
- Porcentaje visible: `92%`
- Estado: `Estado Óptimo`

Tarjeta derecha inferior:

- Título: `Total Faltas (Bimestre)`
- Valor: `3 días`
- Estado: `Requiere Monitoreo`

### Registros recientes

Tarjeta inferior titulada: `Registros Recientes`.

Incluye:

- Enlace: `Ver historial completo`
- Tabla con columnas:
  - Fecha
  - Bimestre
  - Estado
  - Registrado por

Filas visibles:

- `12 Nov, 2023` — `3er Bimestre` — `Presente` — `Prof. A. Mendoza`
- `11 Nov, 2023` — `3er Bimestre` — `Tardanza` — `Prof. A. Mendoza`
- `10 Nov, 2023` — `3er Bimestre` — `Presente` — `Prof. A. Mendoza`
- `09 Nov, 2023` — `3er Bimestre` — `Falta` — `Prof. A. Mendoza`

---

## Distribución visual

La pantalla se organiza como una vista de registro y seguimiento:

- Sidebar fijo a la izquierda.
- Header superior con buscador y usuario.
- Breadcrumb en la parte superior del contenido.
- Título y datos del estudiante debajo del breadcrumb.
- Cuerpo principal en dos columnas:
  - columna izquierda amplia con formulario de nuevo registro.
  - columna derecha con tarjetas resumen de asistencia.
- Parte inferior con tabla de registros recientes.

La pantalla debe permitir registrar asistencia y revisar rápidamente el estado global del estudiante sin abandonar el módulo.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Botón `Guardar Asistencia`: naranja `#F05A0E`.
- Texto principal: `#333333`.
- Bordes de inputs, selects y tarjetas: gris medio `#CCCCCC`.
- Estado `Presente`: verde `#2FAF7B`.
- Estado `Tardanza`: amarillo/naranja `#F59E0B`.
- Estado `Falta`: rojo `#DC2626`.
- Enlaces secundarios: azul institucional `#1E63B5`.

### Estilo

- Formulario con campos amplios y bien separados.
- Botones de estado tipo selector.
- Tarjetas resumen con datos grandes y visibles.
- Badges de estado con fondo suave y texto coloreado.
- Tabla limpia con filas separadas.
- Botón principal destacado al final del formulario.
- Diseño académico, claro y orientado al seguimiento.

---

## Comportamiento esperado

- El usuario puede registrar asistencia para el estudiante seleccionado.
- El sistema debe validar:
  - año escolar obligatorio
  - bimestre obligatorio
  - semana de inicio obligatoria
  - estado obligatorio
- El estado debe permitir seleccionar solo una opción:
  - presente
  - tardanza
  - falta
- El campo de observaciones es opcional si no existe como campo obligatorio en la migración.
- Al presionar `Guardar Asistencia`:
  - si los datos son válidos, se registra la asistencia.
  - se actualiza la tabla de registros recientes.
  - se actualizan los indicadores de asistencia si están disponibles.
- Si hay errores:
  - mostrar mensajes de validación cerca de los campos.
- El enlace `Ver historial completo` debe llevar a una vista completa o ampliar el historial si existe.
- Los valores del mockup son referenciales y no deben ser datos fijos.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo de asistencia implementado en Sprint 3B.

Se relaciona con:

- Perfil del estudiante.
- Registro de datos académicos.
- Tabla de asistencias.
- Procesamiento de riesgo académico del Sprint 4.
- Generación de alertas del Sprint 5.
- Permiso `registrar_datos_academicos`.

La asistencia registrada debe ser utilizada como parte de las variables que influyen en el índice de riesgo académico.

---

## Observaciones para Cursor

- Rediseñar la sección o pantalla de registro de asistencia usando este mockup como referencia visual.
- No modificar la lógica funcional existente.
- No cambiar endpoints.
- No romper el registro de asistencia ya implementado.
- No inventar campos que no existan en la migración real de asistencia.
- No usar los datos del mockup como datos reales.
- Consumir asistencias reales desde la API existente.
- Mantener relación con el estudiante seleccionado.
- Mantener compatibilidad con el perfil del estudiante.
- Mostrar u ocultar esta funcionalidad según el permiso `registrar_datos_academicos`.
- Si el sistema actualmente muestra asistencia dentro del perfil, puede rediseñarse como sección interna o subpantalla.
- Los indicadores como `Asistencia Total` y `Total Faltas` deben calcularse con datos reales si la API los proporciona; si no, deben quedar como estructura visual preparada para el Sprint correspondiente.
- Mantener consistencia visual con:
  - registro de notas
  - variables socioeconómicas
  - perfil del estudiante
  - riesgo académico