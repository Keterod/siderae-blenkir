# Sprint 3A: CRUD estudiantes + perfil básico + navegación

## Objetivo
Implementar el módulo base de estudiantes para operar el dominio principal del sistema.

## Duración estimada
1 semana

## Alcance
- CRUD de estudiantes.
- Perfil básico de estudiante.
- Navegación frontend del módulo.

## Actividades
1. Crear API de estudiantes:
   - `GET /api/estudiantes`
   - `POST /api/estudiantes`
   - `GET /api/estudiantes/{id}`
   - `PUT /api/estudiantes/{id}`
2. Implementar validaciones mínimas:
   - código único
   - grado, sección, nivel, sede, anio_escolar obligatorios
3. Frontend:
   - listado de estudiantes
   - formulario de alta/edición
   - vista de perfil básico
4. Proteger endpoints con permiso `gestionar_estudiantes`.

## Dependencias de entrada
Sprint 2 completado.

## Dependencias de salida
Habilita Sprint 3B.

## Criterios de aceptación
- CRUD operativo sin errores críticos.
- Validaciones activas.
- Navegación listado -> perfil funcionando.

## Entregables
- API estudiantes.
- Páginas de listado/formulario/perfil básico.

## Pruebas asociadas

### Pruebas manuales
- Verificar que se puede registrar un nuevo estudiante con datos válidos.
- Verificar que el sistema no permite registrar dos estudiantes con el mismo código.
- Verificar que se puede editar la información básica de un estudiante.
- Verificar que se puede visualizar el perfil básico del estudiante.
- Verificar la navegación desde el listado hacia el perfil del estudiante.

### Pruebas automatizadas
- Crear prueba de endpoint para listar estudiantes.
- Crear prueba de endpoint para registrar estudiante.
- Crear prueba de validación para código único.
- Crear prueba de endpoint para actualizar estudiante.
- Crear prueba básica de acceso protegido con permiso `gestionar_estudiantes`.

### Criterios de validación
- El CRUD de estudiantes funciona correctamente.
- Los datos se guardan en MySQL.
- El código del estudiante es único.
- El perfil básico muestra la información registrada.
- Solo usuarios autorizados pueden gestionar estudiantes.