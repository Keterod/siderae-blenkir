# Resumen de arquitectura (v1)

## Descripcion breve del sistema
SIDERAE-Blenkir es un sistema web para deteccion temprana de riesgo academico y desercion estudiantil. El flujo confirmado en codigo integra gestion academica, procesamiento de riesgo, alertas e intervenciones.

## Base formal del alcance
- Documento formal: `DRS_SIDERAE_Blenkir_v1.pdf`.
- Regla de lectura para esta v1:
  - El DRS define el alcance funcional formal.
  - El repositorio define el estado real implementado.
  - Si hay diferencia, no se elimina el RF del alcance; se marca como pendiente o parcial.

## Arquitectura deseada segun DRS
- Frontend: React SPA.
- Backend: Laravel API REST.
- Base de datos: MySQL.
- Servicio ML: Python Flask.
- Orquestacion: Docker Compose.
- Flujo objetivo:
  - Usuario -> Frontend
  - Frontend -> Laravel
  - Laravel -> MySQL
  - Laravel -> Flask
  - Laravel -> alertas/intervenciones

## Arquitectura encontrada en el repositorio
- Frontend React + Vite en `frontend/`.
- Backend Laravel en `backend/`.
- Servicio Flask en `ml-service/`.
- MySQL en `docker-compose.yml` como `db-mysql`.
- Orquestacion con 4 servicios principales en `docker-compose.yml`.

## Servicios principales y puertos reales
- `app-frontend` -> `5173:5173`
- `app-backend` -> `8000:8000`
- `ml-engine` -> `5000:5000`
- `db-mysql` -> `3307:3306`

## Flujo general observado en codigo
1. Usuario autentica en frontend.
2. Frontend consume Laravel (`frontend/src/lib/api.js`).
3. Laravel persiste y consulta datos en MySQL.
4. Laravel llama a Flask via `MlRiskService`.
5. Laravel gestiona alertas, intervenciones y cierre.

## Validacion: arquitectura deseada vs actual

| Criterio | Esperado | Encontrado | Estado | Observacion |
|---|---|---|---|---|
| Frontend separado del backend | Front desacoplado | Carpetas separadas y consumo API | Cumple | `frontend/` consume `backend/` por HTTP |
| Backend API REST Laravel | API JSON con middleware | Rutas API y auth activas | Cumple | `backend/routes/api.php` y `backend/routes/auth.php` |
| MySQL como servicio independiente | Servicio DB separado | `db-mysql` en Compose | Cumple | Puerto host `3307` |
| ML Service separado | Servicio Flask aislado | `ml-service/main.py` | Cumple | Sin acoplamiento directo al front |
| Docker Compose orquesta servicios | Servicios principales definidos | 4 servicios activos en compose | Cumple | Front, back, db, ml |
| Frontend consume Laravel | Cliente API contra backend | `VITE_API_URL` + `/api/*` | Cumple | `frontend/src/lib/api.js` |
| Frontend no consume MySQL directo | Sin acceso DB desde UI | No hay llamadas SQL/MySQL en front | Cumple | Verificado en `frontend/src/` |
| Frontend no consume Flask directo | Riesgo via backend | Front llama `/api/estudiantes/{id}/procesar-riesgo` | Cumple | No se detecta llamada a `:5000` desde front |
| Laravel se comunica con Flask | Cliente HTTP a `/predict` | `MlRiskService` usa `services.ml.url` | Cumple | Integracion backend-ml confirmada |
| Laravel persiste en MySQL | Eloquent y migraciones | Modelos + tests Feature con `assertDatabaseHas` | Cumple | Persistencia confirmada en modulos revisados |
| ML sin acceso directo a MySQL | ML solo calculo | `ml-service` sin clientes SQL | Cumple | `requirements.txt` solo contiene `flask` |
| Roles y permisos en backend | RBAC en servidor | Sanctum + Spatie + middleware `permission:*` | Cumple | Validacion backend presente |
| Dashboard y export PDF basicos | RF-14/RF-16 (subset) | API dashboard + export DomPDF + UI parcial | Implementado parcialmente | No equivale a REQ-14.x / REQ-16.x completos del DRS (PNG, graficos completos, etc.) |

## Relacion DRS vs implementacion actual

| Elemento / RF | Definido en DRS | Confirmado en codigo | Estado actual | Observacion |
|---|---|---|---|---|
| RF-01 Carga/importacion de datos academicos | Si | Parcial | Implementado parcialmente | Carga manual de notas confirmada; importacion `.xlsx/.csv` no confirmada en rutas/controladores revisados |
| RF-02 Registro de asistencia | Si | Si | Confirmado en codigo | Endpoints y UI de asistencia presentes |
| RF-03 Importacion Fast Test | Si | No | Pendiente de verificar | DRS RF-03 define import Excel Fast Test por Coordinador Academico |
| RF-04 Reportes conductuales | Si | Parcial | Pendiente de verificar | Modelo `ReporteConductual` en backend; cumplimiento REQ-04.x no auditado aqui |
| RF-05 Variables socioeconomicas | Si | Si | Confirmado en codigo | Endpoints, modelo y UI presentes |
| RF-06 Calculo de indice de riesgo | Si | Si | Confirmado en codigo | Laravel llama Flask y persiste indice |
| RF-07 Clasificacion de nivel de riesgo | Si | Si | Confirmado en codigo | Clasificacion Alto/Medio/Bajo en backend |
| RF-08 Alertas tempranas | Si | Si | Confirmado en codigo | Generacion y listado de alertas presentes |
| RF-09 Intervencion preventiva | Si | Si | Confirmado en codigo | Registro de intervenciones presente |
| RF-10 Derivacion por directivo | Si | No | Pendiente de verificar | DRS REQ-10.x (derivar psicologo, filtros sede); flujo completo no confirmado |
| RF-11 Atencion psicologica perfil integrado | Si | Parcial | Pendiente de verificar | Depende RF-10; acceso perfil estudiante parcialmente cubierto en otros RF segun contexto proyecto |
| RF-12 Comunicacion familia | Si | No | Pendiente de desarrollo | Modulo comunicacion trazable DRS REQ-12.x |
| RF-13 Cierre de alerta | Si | Si | Confirmado en codigo | Cierre con intervencion validado; DRS tambien admite derivacion (RF-10) o comunicacion (RF-12) como prerequisito |
| RF-14 Dashboard | Si | Parcial | Implementado parcialmente | DRS REQ-14.1-14.5 (graficos, filtros directivo, export PNG/PDF, % alertas, actualizacion automatica); API dashboard minima puede existir sin cerrar RF-14 |
| RF-15 Roles y permisos | Si | Si | Confirmado en codigo | Spatie + middleware + `/api/me` |
| RF-16 Exportacion PDF | Si | Parcial | Implementado parcialmente | `GET /api/dashboard/export` + vista `pdf/dashboard.blade.php` confirmados; otros reportes PDF del DRS no confirmados |
| RF-17 Auditoria | Si | Parcial | Implementado parcialmente | `activity_log` + registros manuales `activity()` en controladores API (Sprint 7.5A); consulta UI de logs y cobertura total REQ-17.x **pendiente de verificar** |
| RF-18 Reentrenamiento ML | Si | No | Pendiente de desarrollo | No se detectan endpoints/flujo de reentrenamiento |
| RF-19 Semaforo de completitud | Si | No | Pendiente de desarrollo | No se observa semaforo visual ni logica explicita de estados verde/amarillo/rojo |
| RF-20 Historial de riesgo | Si | Parcial | Implementado parcialmente | Persistencia historica en `indices_riesgo`; visualizacion historica en UI no confirmada completa |

## Limites actuales del prototipo
- El ML actual es un prototipo deterministico en `ml-service/main.py`; no se confirma ejecucion real de Random Forest, SVM y XGBoost en codigo actual (DRS RF-06 REQ-06.2).
- Capacidades DRS pendientes de cierre incluyen entre otras RF-03 (Fast Test), RF-10–RF-12 (derivacion, perfil psicologo extendido segun DER, comunicacion familia), RF-18 (reentrenamiento), RF-19 (semaforo completitud); ver `docs/arquitectura/contexto-drs-requerimientos.md` para checklist formal.
- `ARCHITECTURE.md` fue alineado en Sprint 7.5A con el stack y puertos reales; conviene revisarlo tras cambios mayores en Compose o `composer.json`.

## Documentos de contexto por componente
- `docs/arquitectura/contexto-drs-requerimientos.md` (resumen operativo RFs/RN/RNF para IA; **no sustituye** `DRS_SIDERAE_Blenkir_v1.pdf`)
- `docs/arquitectura/contexto-backend-laravel.md`
- `docs/arquitectura/contexto-frontend-react.md`
- `docs/arquitectura/contexto-ml-service-flask.md`
- `docs/arquitectura/contexto-docker-infraestructura.md`

