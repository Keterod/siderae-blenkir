# Mockup 03 — Listado de estudiantes

## Objetivo de la pantalla

Permitir la gestión, búsqueda, filtrado y consulta de estudiantes registrados en el sistema SIDERAE-Blenkir.

Esta pantalla debe servir como punto principal de acceso al módulo de estudiantes, permitiendo localizar rápidamente a un alumno, revisar su último nivel de riesgo y acceder a acciones como ver perfil o editar información.

---

## Elementos visibles

### Navegación lateral

- Logo o ícono institucional.
- Nombre del sistema: `SIDERAE`.
- Subtítulo: `Blenkir Analytics`.
- Menú lateral con opciones:
  - Dashboard
  - Estudiantes
  - Alertas
  - Intervenciones
  - Reportes
  - Configuración
- Opción activa: `Estudiantes`, resaltada con fondo claro y borde/acento naranja.

### Header superior

- Buscador global con placeholder: `Buscar...`.
- Ícono de notificaciones.
- Ícono de ayuda.
- Avatar del usuario.

### Contenido principal

- Título: `Estudiantes`.
- Subtítulo: `Gestión y monitoreo del alumnado.`
- Botón principal: `+ Nuevo Estudiante`.

### Filtros

Panel de filtros con borde suave que contiene:

- Campo de búsqueda: `Nombre o Código...`
- Selector de sede:
  - valor visible: `Todas`
- Selector de nivel:
  - valor visible: `Todos`
- Selector de grado:
  - valor visible: `Todos`
- Selector de sección:
  - valor visible: `Todas`
- Selector de año escolar:
  - valor visible: `2023-2024`

### Tabla de estudiantes

Tabla con columnas:

- Código
- Nombres
- Apellidos
- Nivel
- Grado
- Sección
- Sede
- Último Nivel de Riesgo
- Acciones

Filas de ejemplo visibles:

- Estudiante con riesgo `Alto`
- Estudiante con riesgo `Medio`
- Estudiante con riesgo `Bajo`

### Acciones por estudiante

Cada fila tiene acciones visuales:

- Ícono de ojo para ver perfil.
- Ícono de lápiz para editar.

### Paginación

Parte inferior de la tabla:

- Texto: `Mostrando 1 - 3 de 142 estudiantes`
- Botón de página anterior.
- Botón de página siguiente.

---

## Distribución visual

La pantalla mantiene una estructura de dashboard:

- Sidebar fijo a la izquierda.
- Header superior horizontal.
- Área principal con fondo claro.
- Título y descripción en la parte superior izquierda.
- Botón `Nuevo Estudiante` alineado a la derecha.
- Panel de filtros debajo del encabezado.
- Tabla principal debajo de los filtros.
- Paginación en la parte inferior derecha de la tabla.

La distribución debe priorizar búsqueda rápida, legibilidad y acceso directo al perfil del estudiante.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro o tono cálido suave derivado de `#F2F2F2`.
- Tarjetas, filtros y tabla: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja claro con borde/acento naranja.
- Botón `Nuevo Estudiante`: naranja `#F05A0E`.
- Texto principal: `#333333`.
- Bordes de filtros, inputs y tabla: gris medio `#CCCCCC`.
- Acciones secundarias: azul institucional `#1E63B5` o tono oscuro neutro.
- Riesgo alto: rojo `#DC2626`.
- Riesgo medio: amarillo/naranja `#F59E0B`.
- Riesgo bajo: verde `#2FAF7B`.

### Estilo

- Tabla limpia con bordes suaves.
- Filtros agrupados en un panel superior.
- Inputs y selects con bordes redondeados.
- Botón principal destacado.
- Badges de riesgo con fondo suave y texto coloreado.
- Íconos de acción compactos y fáciles de identificar.
- Espaciado amplio para evitar saturación visual.

---

## Comportamiento esperado

- Al cargar la pantalla, se debe listar estudiantes registrados.
- El usuario puede buscar por nombre o código.
- Los filtros deben permitir filtrar por:
  - sede
  - nivel
  - grado
  - sección
  - año escolar
- El botón `Nuevo Estudiante` debe abrir la pantalla o formulario de registro.
- El ícono de ojo debe abrir el perfil del estudiante.
- El ícono de lápiz debe abrir la pantalla o formulario de edición del estudiante.
- La paginación debe permitir navegar entre páginas de estudiantes.
- El badge de riesgo debe mostrar el último nivel de riesgo procesado si existe.
- Si el estudiante aún no tiene riesgo procesado, debe mostrarse un estado neutro como `Sin procesar`.

---

## Relación con el sistema actual

Esta pantalla corresponde al módulo implementado en Sprint 3A.

Se relaciona con:

- CRUD de estudiantes.
- Endpoint de listado de estudiantes.
- Perfil del estudiante.
- Edición de estudiante.
- Último índice de riesgo calculado en Sprint 4.
- Permiso `gestionar_estudiantes`.

---

## Observaciones para Cursor

- Rediseñar el listado de estudiantes existente usando este mockup como referencia visual.
- No modificar la lógica funcional ya implementada.
- No cambiar endpoints.
- No romper el CRUD de estudiantes.
- No romper permisos ni autenticación.
- No usar los datos del mockup como datos reales.
- La tabla debe consumir datos reales desde la API de estudiantes.
- Los badges de riesgo deben usar el último riesgo real del estudiante si está disponible.
- Si no existe paginación real en backend, mantener una estructura visual compatible para implementarla después.
- El botón `Nuevo Estudiante` debe usar el flujo actual de creación.
- El ícono de edición debe usar el flujo actual de edición.
- El ícono de visualización debe abrir el perfil existente.
- Mantener consistencia visual con:
  - Dashboard
  - Perfil del estudiante
  - Registro/edición de estudiante