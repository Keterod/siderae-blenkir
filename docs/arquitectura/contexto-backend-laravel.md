# Contexto backend Laravel (v1)

## Rol del backend
El backend Laravel es la capa central de negocio: autentica usuarios, valida permisos, expone API REST, persiste en MySQL, coordina el calculo de riesgo con Flask y gestiona alertas/intervenciones.

## Relacion con el DRS
- El DRS (`DRS_SIDERAE_Blenkir_v1.pdf`) define el alcance formal de RF y RN.
- El backend actual confirma parte importante del flujo operativo, pero no todo el alcance formal del DRS esta implementado en codigo revisado.

## Stack verificado (`backend/composer.json`)
- Laravel Framework.
- Laravel Sanctum.
- Spatie Permission.
- Spatie Activitylog.
- Laravel Excel (dependencia presente).
- Barryvdh DomPDF (dependencia presente).

## Rutas principales verificadas
- Desde `backend/routes/auth.php`:
  - `POST /login`
  - `POST /logout`
- Desde `backend/routes/api.php`:
  - `GET /api/health`
  - `GET /api/me`
  - `GET /api/dashboard` (ruta declarada con permiso)
  - CRUD parcial de estudiantes
  - Endpoints de notas, asistencias y variables socioeconomicas
  - `POST /api/estudiantes/{id}/procesar-riesgo`
  - Endpoints de alertas, intervenciones y cierre

## Controladores principales detectados
- API:
  - `EstudianteController`
  - `NotaController`
  - `AsistenciaController`
  - `VariableSocioeconomicaController`
  - `ProcesarRiesgoController`
  - `AlertaController`
  - `IntervencionController`
  - `AlertaCierreController`
- Auth:
  - `AuthenticatedSessionController`
  - `RegisteredUserController`
  - otros controladores de flujo auth/recuperacion/verificacion

## Modelos principales detectados
- `User`
- `Estudiante`
- `Nota`
- `Asistencia`
- `VariableSocioeconomica`
- `IndiceRiesgo`
- `Alerta`
- `Intervencion`
- `ReporteConductual`

## Autenticacion y autorizacion
- **Sanctum**: confirmado por middleware `auth:sanctum` y flujo frontend.
- **Spatie roles/permisos**: confirmado por `HasRoles`, seeders de roles/permisos y middleware `permission:*`.
- Roles seeders detectados: administrador, docente, coordinador_academico, psicologo_tutor, directivo.

## Relacion con MySQL
- Conexion definida en `backend/.env.example` (`DB_CONNECTION=mysql`, `DB_HOST=db-mysql`).
- Migraciones y modelos activos confirman persistencia por Eloquent.

## Relacion con ML Service Flask
- `backend/config/services.php` define `services.ml.url`.
- `App\Services\MlRiskService` llama `POST {ML_SERVICE_URL}/predict`.
- `ProcesarRiesgoController` construye payload, invoca ML y persiste `indices_riesgo`.

## Relacion con alertas e intervenciones
- Generacion de alerta por riesgo alto: confirmada en flujo de `ProcesarRiesgoController`.
- Listado/detalle de alertas: `AlertaController`.
- Registro de intervencion: `IntervencionController`.
- Cierre de alerta: `AlertaCierreController` (validado por pruebas Feature).

## Auditoria (`activity_log`, Spatie)
- Dependencia y migraciones de tabla: **confirmadas en codigo**.
- **Sprint 7.5A:** registro manual con `activity()->causedBy(...)->performedOn(...)->withProperties(...)->log(...)` en:
  - `EstudianteController` (store, update)
  - `NotaController::store`
  - `AsistenciaController::store`
  - `VariableSocioeconomicaController::store`
  - `ProcesarRiesgoController::store` (log `riesgo.procesado` y, si aplica, `alerta.generada`)
  - `IntervencionController::store`
  - `AlertaCierreController::store`
  - `DashboardController::export` (`dashboard.pdf_exportado`, sin `subject` morph)
- **No publicado** en repo: `config/activitylog.php` (se usan valores por defecto del paquete).
- **Pendiente de desarrollo / Sprint 8+:** pantalla o API de consulta de logs, cobertura total segun RF-17 del DRS, decision de retencion y matriz rol–accion–auditoria.

## Pruebas Feature detectadas
- Auth:
  - `AuthenticationTest`, `RegistrationTest`, `PasswordResetTest`, `EmailVerificationTest`
- Dominio:
  - `EstudianteTest`
  - `DatosAcademicosTest`
  - `RiesgoTest`
  - `AlertaIntervencionTest`
  - `DashboardTest`
  - `ActivityLogTest` (auditoria en acciones criticas)

## Estado backend frente a RF relevantes
- RF-01: **Implementado parcialmente** (carga manual de notas confirmada; importacion `.xlsx/.csv` no confirmada por rutas/controladores revisados).
- RF-02: **Confirmado en codigo**.
- RF-05: **Confirmado en codigo**.
- RF-06: **Confirmado en codigo**.
- RF-07: **Confirmado en codigo**.
- RF-08: **Confirmado en codigo**.
- RF-09: **Confirmado en codigo**.
- RF-13: **Confirmado en codigo**.
- RF-15: **Confirmado en codigo**.
- RF-17: **Implementado parcialmente** (registros en acciones criticas API desde Sprint 7.5A; consulta de logs y alcance total REQ-17.x **pendiente de verificar** frente al DRS).

## Reglas para Cursor (backend)
- No romper autenticacion ni sesion.
- No cambiar endpoints existentes sin necesidad funcional clara.
- No modificar migraciones sin autorizacion explicita.
- No inventar permisos ni roles fuera de seeders/politicas reales.
- Mantener proteccion backend por middleware real (`auth:sanctum`, `permission:*`).
- Distinguir siempre entre alcance DRS y estado implementado.

## Pendientes de verificar
- Ampliacion de dashboard/export frente a todos los REQ del DRS (RF-14, RF-16).
- Endpoints/servicios de importacion Excel/CSV (RF-01 completo).
- Cobertura completa de auditoria segun RF-17 (UI, politicas, retencion).
- Matriz rol–permiso–cierre de alerta (p. ej. directivo sin `registrar_intervencion` en seeder actual): decision de negocio para Sprint 8.
