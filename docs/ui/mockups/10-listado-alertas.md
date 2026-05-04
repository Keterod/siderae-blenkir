# Mockup 10 — Listado de alertas

## Objetivo de la pantalla

Permitir consultar, filtrar y gestionar las alertas generadas para estudiantes en riesgo académico dentro del sistema SIDERAE-Blenkir.

Esta pantalla debe servir como panel principal de seguimiento de alertas, mostrando el estado actual de los casos, el nivel de riesgo asociado, la fecha de generación y el acceso al detalle de cada alerta.

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
  - `Buscar alertas, estudiantes...`
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Contenido principal

- Título: `Alertas`.
- Subtítulo: `Gestión y seguimiento de estudiantes en riesgo.`
- Botón principal:
  - `+ Nueva Alerta Manual`

### Tarjetas resumen

Se muestran tres tarjetas KPI:

1. `Riesgo Alto (Activas)`
   - valor: `24`
   - ícono de advertencia.

2. `En Atención`
   - valor: `18`
   - ícono de seguimiento o portapapeles.

3. `Cerradas (Mes)`
   - valor: `42`
   - ícono de check o confirmación.

### Panel de filtros

Incluye:

- Etiqueta: `Filtros:`
- Selector `Estado`
- Selector `Sede`
- Selector `Nivel`
- Selector `Grado`
- Selector `Sección`
- Campo de fecha con placeholder `mm/dd/yyyy`
- Acción `Limpiar Filtros`

### Tabla de alertas

Tabla con columnas:

- Estudiante
- Índice de riesgo
- Nivel
- Estado
- Fecha de generación
- Acción

Filas visibles de ejemplo:

- `Lucía Méndez`
  - grado/sección: `3ro Secundaria - A`
  - índice de riesgo: `85%`
  - nivel: `Alto`
  - estado: `Pendiente`
  - fecha: `24 Oct 2023, 08:30 AM`
  - acción: `Ver Detalle`

- `Carlos Rivera`
  - índice de riesgo: `60%`
  - nivel: `Medio`
  - estado: `En Atención`
  - fecha: `23 Oct 2023, 14:15 PM`
  - acción: `Ver Detalle`

- `Ana Pérez`
  - índice de riesgo: `25%`
  - nivel: `Bajo`
  - estado: `Cerrada`
  - fecha: `20 Oct 2023, 10:00 AM`
  - acción: `Ver Detalle`

- `Jorge Gómez`
  - índice de riesgo: `92%`
  - nivel: `Alto`
  - estado pendiente visible
  - fecha: `25 Oct 2023, 09:10 AM`
  - acción: `Ver Detalle`

### Paginación

- Texto inferior:
  - `Mostrando 1 a 4 de 42 alertas`
- Botones:
  - anterior
  - página 1
  - página 2
  - página 3
  - siguiente

---

## Distribución visual

La pantalla usa una estructura de gestión y monitoreo:

- Sidebar fijo a la izquierda.
- Header superior con buscador y usuario.
- Área principal con título y botón de acción.
- Fila superior con tarjetas resumen de alertas.
- Panel de filtros debajo de las tarjetas.
- Tabla principal de alertas debajo de los filtros.
- Paginación en la parte inferior de la tabla.

La información está organizada para que el usuario pueda identificar primero el volumen de alertas, luego aplicar filtros y finalmente revisar casos individuales.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: tono claro cálido derivado de `#F2F2F2`.
- Tarjetas y paneles: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Botón `Nueva Alerta Manual`: naranja oscuro o institucional `#C94A0C` / `#F05A0E`.
- Texto principal: `#333333`.
- Bordes de tarjetas, filtros y tabla: gris medio `#CCCCCC`.
- Riesgo alto: rojo `#DC2626`.
- Riesgo medio: amarillo/naranja `#F59E0B`.
- Riesgo bajo: verde `#2FAF7B`.
- Estado pendiente: rojo/naranja según criticidad.
- Estado en atención: naranja o amarillo.
- Estado cerrada: verde o neutro positivo.
- Acción `Ver Detalle`: botón con borde y texto en color institucional.

### Estilo

- Tarjetas KPI con íconos grandes y valores destacados.
- Filtros agrupados en un contenedor visible.
- Tabla amplia y legible.
- Barras de progreso para representar el índice de riesgo.
- Badges de nivel con fondo suave.
- Estados de alerta con ícono y color.
- Botones de acción claros y consistentes.
- Paginación compacta al final de la tabla.

---

## Comportamiento esperado

- Al cargar la pantalla, debe mostrarse el listado de alertas reales.
- El buscador debe permitir buscar por estudiante o identificador de alerta si la funcionalidad existe.
- Los filtros deben permitir filtrar por:
  - estado
  - sede
  - nivel
  - grado
  - sección
  - fecha
- La acción `Limpiar Filtros` debe restablecer los filtros.
- El botón `Ver Detalle` debe abrir el detalle de la alerta seleccionada.
- El botón `Nueva Alerta Manual` puede quedar como acción futura si el sistema solo genera alertas automáticas.
- Las tarjetas resumen deben mostrar datos reales si existe API de indicadores.
- La paginación debe navegar entre páginas de alertas si está implementada.
- Los datos del mockup son referenciales y no deben insertarse como datos reales.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo de alertas implementado en Sprint 5.

Se relaciona con:

- Alertas generadas automáticamente por riesgo alto.
- Índices de riesgo del Sprint 4.
- Estudiantes registrados.
- Intervenciones.
- Cierre de alertas.
- Permiso `ver_alertas`.

Esta pantalla es el punto de entrada para revisar alertas y acceder al seguimiento de cada caso.

---

## Observaciones para Cursor

- Rediseñar el listado de alertas existente usando este mockup como referencia visual.
- No modificar la lógica funcional ya implementada.
- No cambiar endpoints sin necesidad.
- No romper el flujo de generación automática de alertas.
- No romper el detalle de alertas.
- No romper intervenciones ni cierre de alertas.
- No usar los datos del mockup como datos reales.
- Consumir alertas reales desde la API existente.
- Mostrar el módulo solo si el usuario tiene permiso `ver_alertas`.
- El botón `Nueva Alerta Manual` no debe crear lógica nueva si no está definida en el backend; puede ocultarse o dejarse como acción futura controlada.
- Los filtros visuales pueden implementarse progresivamente si el backend todavía no soporta todos los filtros.
- Si no existe paginación backend, mantener la estructura visual preparada sin romper el listado actual.
- Mantener consistencia visual con:
  - dashboard
  - perfil del estudiante
  - riesgo académico
  - detalle de alerta
  - registro de intervención