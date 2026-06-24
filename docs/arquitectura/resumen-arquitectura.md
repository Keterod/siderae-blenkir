# Resumen de arquitectura (v1)

> **Documento de contexto.** Fuente formal vigente V1: [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) · Índice: [`docs/INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md).

## Descripcion breve del sistema
SIDERAE-Blenkir es un sistema web para deteccion temprana de riesgo academico y desercion estudiantil. El flujo confirmado en codigo integra gestion academica, procesamiento de riesgo, alertas e intervenciones.

## Decisión operativa: sede única Chilca

- **Confirmado en codigo (V1):** la UI y los seeders demo nuevos operan con sede **Chilca** unicamente; no hay selectores visibles de sede.
- **Conteos Fase 1:** si la BD local auditada incluye registros Auquimarca, eso documenta **datos existentes en ese entorno**, no operacion multi-sede en V1 (decision vigente: Chilca).
- **Pendiente de desarrollo (DRS / futuro):** vista multi-sede para directivo u otras sedes (p. ej. Auquimarca) sin cambiar el esquema actual.
- **Reglas:** mantener columna y validaciones `sede`; usar `frontend/src/lib/sedeOperativa.js` y `App\Support\SedeOperativa`; no alterar Flask ni riesgo academico por este criterio.

## Base formal del alcance
- **DRS vigente V1:** [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md).
- DRS histórico: `DRS_SIDERAE_Blenkir_v1.pdf` (**fuente externa al repo**).
- Resumen operativo IA (histórico): `docs/arquitectura/contexto-drs-requerimientos.md`.
- Alcance real del prototipo: `docs/limitaciones.md`.
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
| Modulo curricular (malla, notas CE, bimestre, Excel) | Parcial DRS / Sprint 8.5 | `/api/curricular/*` + paneles React | Confirmado en codigo | Flujo operativo principal en UI V1 |
| Seed automatico al arrancar Docker | No esperado | Solo `migrate` en Compose | Parcial operativo | Seed manual: `migrate:fresh --seed` |

## Relacion DRS vs implementacion actual

| Elemento / RF | Definido en DRS | Confirmado en codigo | Estado actual | Observacion |
|---|---|---|---|---|
| RF-01 Carga/importacion de datos academicos | Si | Parcial | Implementado parcialmente | Carga manual + import **plantilla Excel curricular** confirmada; **importacion SIAGIE pendiente** |
| RF-02 Registro de asistencia | Si | Si | Confirmado en codigo | Asistencia legacy API + **asistencia curricular diaria** en UI |
| RF-03 Importacion Fast Test | Si | No | Pendiente | Sin flujo import Fast Test confirmado |
| RF-04 Reportes conductuales | Si | Si | Confirmado en codigo | API + UI perfil estudiante V1 minimo; **8 passed** Fase 2E |
| RF-05 Variables socioeconomicas | Si | Parcial | Implementado parcialmente | API confirmada; **UI pausada** (pestaña no expuesta en perfil) |
| RF-06 Calculo de indice de riesgo | Si | Parcial | Implementado parcialmente | Laravel → Flask **deterministico**; **UI riesgo activada V1** (NC-11 cerrada V1); sin RF/SVM/XGBoost |
| RF-07 Clasificacion de nivel de riesgo | Si | Si | Confirmado en codigo | Clasificacion Alto/Medio/Bajo en backend |
| RF-08 Alertas tempranas | Si | Si | Confirmado en codigo | Generacion y listado de alertas presentes |
| RF-09 Intervencion preventiva | Si | Si | Confirmado en codigo | Registro de intervenciones presente |
| RF-10 Derivacion por directivo | Si | No | Pendiente | Sin rutas API de derivacion |
| RF-11 Atencion psicologica perfil integrado | Si | V1 | Implementado con smoke manual pendiente | Panel de seguimiento psicologo/tutor (`PerfilPsicologoTutorPanel.jsx`); no diagnostico clinico |
| RF-12 Comunicacion familia | Si | No | Pendiente de desarrollo | Modulo comunicacion trazable DRS REQ-12.x |
| RF-13 Cierre de alerta | Si | Parcial | Implementado parcialmente | Cierre con intervencion confirmado; derivacion (RF-10) y comunicacion (RF-12) pendientes |
| RF-14 Dashboard | Si | Parcial | Implementado parcialmente | DRS REQ-14.1-14.5 (graficos, filtros directivo, export PNG/PDF, % alertas, actualizacion automatica); API dashboard minima puede existir sin cerrar RF-14 |
| RF-15 Roles y permisos | Si | Si | Confirmado en codigo | Spatie (23 permisos) + UI usuarios + `/api/me` |
| RF-16 Exportacion PDF | Si | Parcial | Implementado parcialmente | `GET /api/dashboard/export` + vista `pdf/dashboard.blade.php` confirmados; otros reportes PDF del DRS no confirmados |
| RF-17 Auditoria | Si | Parcial | Implementado parcialmente | `activity_log` + registros manuales `activity()` en controladores API (Sprint 7.5A); consulta UI de logs y cobertura total REQ-17.x **pendiente de verificar** |
| RF-18 Reentrenamiento ML | Si | No | Pendiente de desarrollo | No se detectan endpoints/flujo de reentrenamiento |
| RF-19 Semaforo de completitud | Si | Si | Confirmado en codigo V1 | `CompletitudDatosService`, endpoint API, tests (`SemaforoCompletitudTest` 11 passed) y UI perfil estudiante (`EstudiantePerfilSemaforoCompletitud.jsx`) |
| RF-20 Historial de riesgo | Si | V1 | Implementado V1 | Backend + frontend tabla simple en perfil estudiante; smoke manual navegador pendiente |

## Limites actuales del prototipo
- El ML actual es un prototipo deterministico en `ml-service/main.py`; no se confirma ejecucion real de Random Forest, SVM y XGBoost en codigo actual (DRS RF-06 REQ-06.2).
- Capacidades DRS pendientes: RF-03, RF-10–RF-12, RF-18, import SIAGIE, Cypress/E2E, despliegue productivo. **RF-19 implementado V1; smoke manual navegador pendiente.**
- Conteos demo **varian** segun historial BD local; ver `docs/pruebas/hallazgos-fase1-documentacion.md` (no usar como constantes universales). **Nota:** conteos Fase 1 con sede Auquimarca pertenecen al entorno local auditado; no implican operacion multi-sede en V1 (sede operativa Chilca).
- Suite PHPUnit completa puede fallar por `memory_limit` 128M en tests Excel; `ExcelAulaTest` paso con 512M (Fase 1).
- Documentacion consolidada Fases 1–8: ver [`docs/INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md).

## Documentos de contexto por componente
- [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) (**vigente V1**)
- `docs/arquitectura/contexto-drs-requerimientos.md` (resumen histórico v1 PDF; **no sustituye DRS v2**)
- `docs/arquitectura/contexto-backend-laravel.md`
- `docs/arquitectura/contexto-frontend-react.md`
- `docs/arquitectura/contexto-ml-service-flask.md`
- `docs/arquitectura/contexto-docker-infraestructura.md`
- `docs/instalacion-docker.md`
- `docs/manual-tecnico.md`
- `docs/ml-service.md`
- `docs/api.md`

*Ultima actualizacion documental: saneamiento post-Fase 8 (2026-06-09). RF-19 cerrado V1 Fase 3E — 2026-06-23.*

