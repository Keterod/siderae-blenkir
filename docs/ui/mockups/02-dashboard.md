# Mockup 02 — Dashboard principal

## Objetivo de la pantalla

Mostrar un resumen general del estado académico y del riesgo estudiantil dentro del sistema SIDERAE-Blenkir.

Esta pantalla debe funcionar como la vista inicial después del inicio de sesión, permitiendo al usuario identificar rápidamente indicadores clave, distribución de riesgo, alertas recientes y estudiantes procesados recientemente.

---

## Elementos visibles

### Navegación lateral

- Logo o ícono institucional de SIDERAE.
- Nombre del sistema: `SIDERAE`.
- Subtítulo: `Blenkir Analytics`.
- Menú lateral con opciones:
  - Dashboard
  - Estudiantes
  - Alertas
  - Intervenciones
  - Reportes
  - Configuración
- Opción activa: `Dashboard`, resaltada con fondo suave y borde/acento naranja.

### Header superior

- Buscador global con placeholder: `Buscar estudiante, ID...`.
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.
- Nombre o rol del usuario: `Admin`.
- Desplegable del usuario.

### Contenido principal

- Título: `Dashboard Principal`.
- Subtítulo: `Resumen de indicadores de riesgo y actividad académica.`
- Filtros superiores:
  - Sede
  - Nivel
  - Grado
  - Sección
  - Botón `Filtrar`

### Tarjetas KPI

- `Total estudiantes`
  - valor: `1,248`
  - ícono de grupo/personas
- `Riesgo alto`
  - valor: `84`
  - variación: `↑ 5%`
  - ícono de alerta
- `Riesgo medio`
  - valor: `312`
  - variación: `0%`
  - ícono de advertencia
- `Riesgo bajo`
  - valor: `852`
  - variación: `↓ 2%`
  - ícono de check

### Distribución de riesgo

- Tarjeta titulada: `Distribución de Riesgo`.
- Gráfico visual tipo anillo/cuadrado segmentado.
- Total central: `1,248`.
- Leyenda:
  - Alto
  - Medio
  - Bajo

### Resumen de alertas

- Tarjeta titulada: `Resumen de Alertas`.
- Enlace: `Ver todas`.
- Estados:
  - Pendientes
  - En Progreso
  - Cerradas
- Cantidad asociada a cada estado.

### Últimos estudiantes procesados

- Tarjeta principal titulada: `Últimos Estudiantes Procesados`.
- Ícono de actualizar.
- Ícono de menú de opciones.
- Tabla con columnas:
  - Estudiante
  - Grado/Sección
  - Última evaluación
  - Nivel de riesgo
  - Acción
- Acciones visibles:
  - `Ver Perfil`
- Enlace inferior:
  - `Ver lista completa`

---

## Distribución visual

La pantalla usa una estructura tipo dashboard administrativo:

- Sidebar fijo a la izquierda.
- Header superior horizontal.
- Área principal con fondo gris claro.
- Contenido organizado en bloques:
  1. Encabezado de pantalla y filtros.
  2. Fila de tarjetas KPI.
  3. Distribución de riesgo y resumen de alertas en columna izquierda.
  4. Tabla de últimos estudiantes procesados en columna derecha.

La estructura debe priorizar lectura rápida de indicadores y navegación clara entre módulos.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal / acentos: naranja `#F05A0E`.
- Sidebar activo: fondo naranja muy claro con borde/acento naranja.
- Texto principal: `#333333`.
- Bordes suaves: `#CCCCCC`.
- Riesgo alto: rojo `#DC2626`.
- Riesgo medio: amarillo/naranja `#F59E0B` o `#F05A0E`.
- Riesgo bajo: verde `#2FAF7B`.
- Acciones tipo enlace: azul institucional `#1E63B5`.

### Estilo

- Tarjetas con bordes suaves.
- Sombras ligeras.
- Íconos dentro de contenedores redondeados.
- Badges de riesgo con color de fondo suave y texto de color fuerte.
- Tablas limpias con separación horizontal sutil.
- Filtros compactos alineados a la derecha del encabezado.

---

## Comportamiento esperado

- El usuario accede al dashboard después de iniciar sesión.
- Los filtros deben permitir actualizar los indicadores según:
  - sede
  - nivel
  - grado
  - sección
- El botón `Filtrar` debe aplicar los filtros seleccionados.
- El buscador global debe permitir buscar estudiantes por nombre o ID si la funcionalidad está disponible.
- El enlace `Ver Perfil` debe llevar al perfil del estudiante seleccionado.
- El enlace `Ver todas` debe llevar al módulo de alertas.
- El enlace `Ver lista completa` debe llevar al listado de estudiantes o a una vista de estudiantes procesados.
- El ícono de actualizar debe recargar los datos del dashboard si se implementa.
- Los datos numéricos del mockup son referenciales y deben ser reemplazados por datos reales de la API.

---

## Relación con el sistema actual

Esta pantalla corresponde al futuro módulo de dashboard del sistema.

Se relaciona con:

- Estudiantes registrados.
- Índices de riesgo procesados.
- Alertas generadas.
- Intervenciones registradas.
- Roles y permisos existentes.
- Sprint 6A: Dashboard mínimo.
- Sprint 6B: Filtros, exportación y ajustes por rol.

---

## Observaciones para Cursor

- Implementar esta pantalla como rediseño del dashboard, no como módulo aislado sin conexión.
- No usar datos fijos del mockup como información real.
- Consumir datos reales desde las APIs existentes cuando estén disponibles.
- Si todavía no existe una API específica para KPIs del dashboard, preparar la estructura visual sin romper la lógica actual.
- Mantener la navegación lateral visible en la mayoría de pantallas internas.
- Usar componentes reutilizables para:
  - sidebar
  - header
  - tarjetas KPI
  - tabla
  - badges de riesgo
  - filtros
- No modificar autenticación.
- No modificar permisos.
- No modificar backend salvo que el sprint correspondiente lo indique.
- Mantener coherencia visual con los demás mockups.