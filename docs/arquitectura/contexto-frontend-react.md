# Contexto frontend React (v1)

## Rol del frontend
El frontend React es la capa de interfaz del usuario. Consume la API Laravel, muestra estados por permisos y permite operar flujos de estudiantes, datos academicos, riesgo y alertas.

## Relacion con el DRS
- El DRS define el alcance funcional esperado de UI.
- La UI actual confirma parte de ese alcance; varias funcionalidades del DRS aun no se observan completas en `frontend/src`.

## Stack verificado (`frontend/package.json`)
- React.
- Vite.
- Tailwind CSS.
- ESLint.

## Estructura base detectada (`frontend/src`)
- Entrada:
  - `main.jsx`
  - `App.jsx`
- Sesion:
  - `context/AuthContext.jsx`
  - `components/LoginForm.jsx`
- API:
  - `lib/api.js`
- Modulos:
  - `components/estudiantes/*`
  - `components/alertas/AlertasPanel.jsx`

## Cliente API detectado
- Archivo central: `frontend/src/lib/api.js`.
- Opera contra `VITE_API_URL` (por defecto `http://localhost:8000`).
- Endpoints consumidos: auth, `/api/me`, estudiantes, notas, asistencias, variables, procesamiento de riesgo, alertas, intervenciones y cierre.

## Manejo de sesion/autenticacion
- `AuthContext` carga sesion con `/api/me`.
- Login y logout via API (`/login`, `/logout`).
- Sesion basada en cookies y CSRF (`/sanctum/csrf-cookie`).
- Menu y acciones condicionadas por permisos devueltos por backend.

## Componentes/paneles principales detectados
- `LoginForm` (inicio de sesion).
- `EstudiantesPanel` (lista/alta/edicion/perfil).
- `EstudiantePerfilDatos` (notas, asistencia, variables socioeconomicas).
- `EstudiantePerfilRiesgo` (procesar riesgo, mostrar ultimo indice).
- `AlertasPanel` (listado, detalle, intervencion, cierre).

## Relacion con mockups (`docs/ui/mockups/`)
- Existen mockups 01-12 y guia UI.
- El frontend actual cubre flujos funcionales base, pero no se confirma alineacion visual completa con todos los mockups.

## Estado UI frente a RF visibles
- RF-01 (carga manual/importacion):
  - Carga manual de notas: **Confirmado en codigo**.
  - Importacion `.xlsx/.csv`: **Pendiente de verificar**.
- RF-02 asistencia: **Confirmado en codigo**.
- RF-05 variables socioeconomicas: **Confirmado en codigo**.
- RF-06/RF-07 riesgo: **Confirmado en codigo**.
- RF-08 alertas: **Confirmado en codigo**.
- RF-09 intervencion: **Confirmado en codigo**.
- RF-14 dashboard: **Implementado parcialmente** (opcion por permiso en menu; pantalla completa no confirmada en `frontend/src` revisado).
- RF-16 exportacion: **Pendiente de desarrollo** (no se confirma flujo UI de export).
- RF-19 semaforo: **Pendiente de desarrollo** (no se observa componente semaforo verde/amarillo/rojo).
- RF-20 historial de riesgo: **Implementado parcialmente** (se muestra ultimo indice; historial visual bimestral no confirmado).

## Reglas visuales basicas para Cursor
- Seguir `docs/ui/mockups/guia-ui-siderae.md` como referencia visual.
- No dejar botones muertos sin accion o estado controlado.
- Ocultar/deshabilitar acciones segun permisos reales del backend.
- Mantener estados de carga, error y vacio en cada panel.
- No presentar como implementado algo que solo esta definido en DRS.

## Pruebas futuras
- No se confirma suite Cypress en el estado actual revisado.
- Recomendado: automatizar flujos criticos UI con Cypress en version posterior.

## Pendientes de verificar
- Pantalla dashboard funcional y filtros por rol.
- Flujo de exportacion PDF/PNG desde UI.
- Semaforo de completitud completo (RF-19).
- Vista de historial de riesgo por bimestre (RF-20) con grafico/linea de tiempo.
