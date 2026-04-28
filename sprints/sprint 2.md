# Sprint 2: Login React-Laravel + /api/me + roles/permisos mínimos

## Objetivo
Permitir ingreso real al sistema desde React y aplicar autorización mínima por rol/permisos en backend.

## Duración estimada
1 semana

## Alcance
- Login/logout funcionales desde frontend.
- Endpoint de sesión `GET /api/me`.
- Roles base con Spatie.
- Protección de endpoints con `auth:sanctum` y permisos.

## Actividades
1. Implementar pantalla de login en React.
2. Conectar `POST /login` y `POST /logout` con manejo de sesión.
3. Crear `GET /api/me` que retorne:
   - datos de usuario
   - roles
   - permisos
4. Configurar roles base:
   - `administrador`, `docente`, `coordinador_academico`, `psicologo_tutor`, `directivo`
5. Definir permisos mínimos:
   - `ver_dashboard`
   - `gestionar_estudiantes`
   - `registrar_datos_academicos`
   - `procesar_riesgo`
   - `ver_alertas`
   - `registrar_intervencion`
6. Proteger rutas backend con `auth:sanctum` + `permission:*`.
7. Crear contexto de autenticación en frontend:
   - usuario actual
   - estado de sesión
   - permisos activos

## Dependencias de entrada
Sprint 1 completado.

## Dependencias de salida
Habilita Sprint 3A.

## Criterios de aceptación
- Login y logout operativos desde React.
- `/api/me` responde con datos correctos.
- Accesos sin permiso son bloqueados.
- Menú frontend se adapta por permisos.

## Entregables
- Pantalla login funcional.
- Endpoint `/api/me`.
- Roles y permisos aplicados.
- Middleware de autorización activo.

## Pruebas asociadas

### Pruebas manuales
- Verificar que el usuario puede iniciar sesión con credenciales válidas.
- Verificar que no se permite login con credenciales incorrectas.
- Verificar que el usuario autenticado accede correctamente a su panel.
- Verificar que usuarios sin permisos no acceden a rutas restringidas.

### Pruebas automatizadas
- Ejecutar pruebas relacionadas a RF-15:
  - `test_admin_crea_usuario_con_rol_docente`
  - `test_backend_bloquea_acceso_sin_permiso_vía_api`

### Criterios de validación
- El login funciona correctamente desde el frontend.
- El endpoint `/api/me` devuelve el usuario autenticado.
- Los roles y permisos funcionan correctamente con Spatie.
- No existen accesos no autorizados.