# Mockup 05 — Perfil del estudiante

## Objetivo de la pantalla

Mostrar una ficha integral del estudiante, reuniendo en una sola vista la información académica, asistencia, variables socioeconómicas, análisis de riesgo, alertas activas e historial de intervenciones.

Esta pantalla debe permitir al usuario comprender rápidamente la situación del estudiante y tomar acciones preventivas como editar datos, procesar riesgo, revisar alertas o registrar intervenciones.

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
- Opción activa: `Estudiantes`, resaltada con fondo claro y acento naranja.

### Header superior

- Ruta o breadcrumb:
  - `Estudiantes > Perfil del Estudiante`
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Cabecera del estudiante

- Foto o avatar del estudiante.
- Nombre completo del estudiante.
- Código del estudiante.
- Sede.
- Nivel y grado/sección.
- Año escolar.
- Tarjeta destacada de riesgo:
  - `Riesgo Alto`
  - porcentaje o índice visible.
- Botón secundario: `Editar Estudiante`.
- Botón principal: `Procesar Riesgo`.

### Tarjeta de datos académicos

- Título: `Datos Académicos`.
- Promedio general.
- Cursos jalados.
- Comportamiento.
- Créditos faltantes.

### Tarjeta de asistencia

- Título: `Asistencia`.
- Etiqueta de estado: `Atención requerida`.
- Tasa de asistencia.
- Indicador visual de advertencia.
- Resumen de:
  - inasistencias injustificadas
  - justificadas
  - tardanzas

### Tarjeta de variables socioeconómicas

- Título: `Variables Socioeconómicas`.
- Información resumida:
  - estructura familiar
  - beca / ayuda
  - distancia al centro
  - acceso a internet

### Análisis de riesgo académico

- Título: `Análisis de Riesgo Académico`.
- Tarjetas por factor:
  - Factor académico
  - Factor asistencia
  - Factor conductual
- Cada factor muestra:
  - porcentaje
  - barra de progreso
  - breve explicación
  - color según gravedad

### Alertas activas

- Título: `Alertas activas (últimos 30 días)`.
- Alertas visibles:
  - riesgo inminente de repitencia
  - ausentismo prolongado
- Cada alerta incluye:
  - ícono de advertencia
  - título
  - descripción breve
  - botón `Revisar`

### Historial de intervenciones

- Título: `Historial de Intervenciones`.
- Acción: `+ Nueva Intervención`.
- Tabla con columnas:
  - fecha
  - tipo
  - responsable
  - estado
  - acción
- Estados visibles:
  - completada
  - en progreso
  - pendiente
- Acción visual con ícono de ojo para revisar detalle.

---

## Distribución visual

La pantalla está organizada como una ficha académica integral:

- Sidebar fijo a la izquierda.
- Header superior con breadcrumb y usuario.
- Cabecera principal del estudiante en una tarjeta horizontal.
- Cuerpo dividido en dos columnas:
  - columna izquierda con datos académicos, asistencia y variables socioeconómicas.
  - columna derecha con análisis de riesgo, alertas activas e historial de intervenciones.
- La información crítica de riesgo se muestra en la parte superior para lectura inmediata.
- Las acciones principales se ubican en la cabecera del estudiante.

La pantalla debe permitir una lectura rápida del estado general del estudiante sin obligar al usuario a navegar por muchas pantallas separadas.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Texto principal: `#333333`.
- Bordes suaves: `#CCCCCC`.
- Riesgo alto: rojo `#DC2626`.
- Riesgo medio / atención requerida: amarillo o naranja `#F59E0B` / `#F05A0E`.
- Riesgo bajo / estado positivo: verde `#2FAF7B`.
- Acciones secundarias: azul institucional `#1E63B5`.

### Estilo

- Tarjetas blancas con bordes suaves y sombras ligeras.
- Badges de estado con fondo suave y texto fuerte.
- Barras de progreso por factor de riesgo.
- Botón principal naranja para `Procesar Riesgo`.
- Botón secundario blanco o azul para `Editar Estudiante`.
- Información crítica resaltada visualmente sin saturar la pantalla.
- Jerarquía clara entre cabecera, indicadores y detalle histórico.

---

## Comportamiento esperado

- Al abrir el perfil, el sistema debe cargar los datos reales del estudiante.
- El botón `Editar Estudiante` debe abrir el formulario de edición.
- El botón `Procesar Riesgo` debe ejecutar el flujo de riesgo académico implementado en Sprint 4.
- Si el riesgo calculado es alto, debe poder generar alerta automática según Sprint 5.
- El bloque de riesgo debe mostrar el último índice procesado.
- Las tarjetas académicas deben resumir datos existentes de notas, asistencia y variables socioeconómicas.
- Las alertas activas deben mostrar las alertas reales relacionadas al estudiante.
- El botón `Revisar` debe abrir el detalle de la alerta.
- El botón `+ Nueva Intervención` debe abrir el formulario de intervención si el usuario tiene permiso.
- El historial de intervenciones debe mostrar registros reales asociados al estudiante o a sus alertas.
- Si no existen datos en una sección, se debe mostrar un estado vacío claro, por ejemplo:
  - `Sin notas registradas`
  - `Sin alertas activas`
  - `Sin intervenciones registradas`.

---

## Relación con el sistema actual

Esta pantalla integra funcionalidades implementadas en varios sprints:

- Sprint 3A: perfil y datos básicos del estudiante.
- Sprint 3B: notas, asistencia y variables socioeconómicas.
- Sprint 4: procesamiento e índice de riesgo académico.
- Sprint 5: alertas e intervenciones.

Se relaciona con los permisos:

- `gestionar_estudiantes`
- `registrar_datos_academicos`
- `procesar_riesgo`
- `ver_alertas`
- `registrar_intervencion`

---

## Observaciones para Cursor

- Rediseñar el perfil actual del estudiante tomando este mockup como referencia visual.
- No modificar la lógica funcional ya implementada.
- No cambiar endpoints.
- No romper el CRUD de estudiantes.
- No romper el procesamiento de riesgo.
- No romper alertas ni registro de intervenciones.
- No usar los datos del mockup como datos reales.
- Consumir los datos reales desde las APIs existentes.
- Mostrar u ocultar acciones según permisos del usuario autenticado.
- Mantener el botón `Procesar Riesgo` conectado al flujo actual.
- Mantener el botón `Editar Estudiante` conectado al formulario actual.
- Si alguna métrica del mockup aún no existe en la API, mostrar solo los datos disponibles o dejar un estado temporal controlado.
- Priorizar componentes reutilizables:
  - tarjeta de resumen
  - badge de riesgo
  - barra de progreso
  - tabla de historial
  - tarjeta de alerta
- Mantener consistencia visual con:
  - listado de estudiantes
  - registro de estudiante
  - riesgo académico
  - alertas
  - intervenciones