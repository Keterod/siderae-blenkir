# Mockup 04 — Registro / edición de estudiante

## Objetivo de la pantalla

Permitir registrar un nuevo estudiante o actualizar la información académica y personal de un estudiante existente dentro del sistema SIDERAE-Blenkir.

Esta pantalla debe ofrecer un formulario claro, ordenado y validado, manteniendo coherencia visual con el módulo de estudiantes.

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

- Título: `Registro de Estudiante`.
- Descripción: `Ingrese o actualice la información académica y personal del estudiante.`
- Ícono de usuario con símbolo de agregar.

### Formulario

Campos visibles:

- `Código del Estudiante *`
  - Placeholder: `Ej. EST-2024-001`
  - Texto de apoyo: `Identificador único institucional.`
- `Año Escolar *`
  - Selector: `Seleccione el año`
- `Nombres *`
- `Apellidos *`
- `Fecha de Nacimiento *`
  - Campo tipo fecha
  - Placeholder: `mm/dd/yyyy`
- `Sexo *`
  - Selector: `Seleccione`
- Sección: `Información Académica`
- `Sede *`
  - Selector: `Seleccione la sede principal`
  - Texto de ayuda: `Debe asignar una sede al estudiante.`
- `Nivel *`
  - Selector: `Seleccione`
- `Grado *`
  - Selector: `Seleccione`
- `Sección *`
  - Selector: `Seleccione`

### Acciones

- Botón `Cancelar`.
- Botón `Guardar / Actualizar`.
- Ícono de guardado junto al botón de acción.

---

## Distribución visual

La pantalla tiene una estructura de formulario académico:

- Sidebar fijo a la izquierda.
- Header superior horizontal.
- Área principal centrada hacia la parte superior.
- Formulario organizado en filas y columnas.
- Campos principales en dos columnas.
- Campo `Sede` ocupa una fila completa.
- Campos `Nivel`, `Grado` y `Sección` se distribuyen en tres columnas.
- Botones de acción ubicados en la parte inferior derecha del formulario.

La pantalla debe evitar saturación visual y mantener jerarquía clara entre información personal e información académica.

---

## Colores y estilo visual

Usar la paleta institucional definida en `guia-ui-siderae.md`.

### Colores aplicados

- Fondo general: gris claro o blanco cálido derivado de `#F2F2F2`.
- Formulario / contenedor principal: blanco `#FFFFFF`.
- Color principal: naranja `#F05A0E`.
- Menú activo: fondo naranja muy claro con borde/acento naranja.
- Texto principal: `#333333`.
- Bordes de inputs y selects: gris medio `#CCCCCC`.
- Botón principal `Guardar / Actualizar`: naranja `#F05A0E`.
- Botón `Cancelar`: neutro, con fondo blanco o gris claro.
- Mensajes de ayuda: gris/neutral `#88726B`.
- Mensajes de validación o error: rojo `#DC2626`.

### Estilo

- Inputs amplios y legibles.
- Bordes redondeados.
- Separación clara entre campos.
- Etiquetas visibles encima de cada campo.
- Campos obligatorios marcados con `*`.
- Botón principal destacado.
- Botón cancelar con menor jerarquía visual.
- Mantener consistencia con el listado de estudiantes.

---

## Comportamiento esperado

- Si se accede desde `Nuevo Estudiante`, el formulario debe estar vacío.
- Si se accede desde `Editar`, el formulario debe cargar los datos existentes del estudiante.
- El campo `Código del Estudiante` debe ser único.
- Los campos obligatorios deben validar:
  - código
  - nombres
  - apellidos
  - fecha de nacimiento
  - sexo
  - año escolar
  - sede
  - nivel
  - grado
  - sección
- Al presionar `Guardar / Actualizar`:
  - si los datos son válidos, debe guardar o actualizar el estudiante.
  - si hay errores, debe mostrar mensajes de validación.
- Al presionar `Cancelar`, debe volver al listado de estudiantes o al perfil del estudiante.
- Los selects deben mostrar valores reales definidos por el sistema o por la base de datos.
- No debe permitir guardar registros incompletos.

---

## Relación con el sistema actual

Esta pantalla corresponde al CRUD de estudiantes implementado en Sprint 3A.

Se relaciona con:

- Registro de estudiantes.
- Edición de estudiantes.
- Listado de estudiantes.
- Perfil del estudiante.
- Validaciones backend de estudiante.
- Permiso `gestionar_estudiantes`.

---

## Observaciones para Cursor

- Rediseñar el formulario existente de creación/edición de estudiantes usando este mockup como referencia visual.
- No modificar la lógica del CRUD.
- No cambiar endpoints.
- No romper validaciones existentes.
- No romper permisos ni autenticación.
- No inventar campos que no existan en la migración real de estudiantes.
- Mantener el formulario compatible con los campos reales del modelo `Estudiante`.
- El texto `Guardar / Actualizar` puede adaptarse dinámicamente:
  - `Guardar estudiante` para creación.
  - `Actualizar estudiante` para edición.
- Los valores del mockup son referenciales y no deben insertarse como datos fijos.
- Mantener consistencia visual con:
  - listado de estudiantes
  - perfil del estudiante
  - dashboard