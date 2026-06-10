# Arquitectura del Sistema SIDERAE-Blenkir

Prototipo académico **V1**. Alcance formal resumido en [`docs/arquitectura/contexto-drs-requerimientos.md`](docs/arquitectura/contexto-drs-requerimientos.md) (DRS PDF = fuente externa al repo). Alcance real: [`docs/limitaciones.md`](docs/limitaciones.md).

---

## 1. Descripción general

SIDERAE-Blenkir usa una arquitectura de **microservicios desacoplados** orquestada con Docker Compose:

- **Frontend** (React/Vite) — interfaz por roles.
- **Backend** (Laravel API) — negocio, persistencia, autorización, orquestación ML.
- **ML Service** (Flask) — cálculo de índice de riesgo (`POST /predict`).
- **MySQL** — datos académicos, riesgo, alertas, módulo curricular.

El código puede ir **por detrás** del alcance DRS en varios RF; verificar siempre en rutas y tests.

---

## 2. Decisión operativa: sede única Chilca

- **V1:** UI y consultas por defecto solo sede **Chilca** ([`AGENTS.md`](AGENTS.md)).
- Columna `sede` (`chilca` / `auquimarca`) se mantiene en BD/API para compatibilidad y expansión futura.
- Conteos Fase 1 que incluyen registros Auquimarca (p. ej. 253 Chilca / 196 Auquimarca en BD local auditada) reflejan **datos existentes en ese entorno**, no alcance operativo multi-sede en V1.
- Helpers: [`frontend/src/lib/sedeOperativa.js`](frontend/src/lib/sedeOperativa.js), [`backend/app/Support/SedeOperativa.php`](backend/app/Support/SedeOperativa.php).
- Fuera de este criterio: cambios en `ml-service/` y lógica de riesgo académico por sede.

---

## 3. Componentes del sistema

### A. Frontend (Client Tier)

- **Tecnología:** React + Vite + Tailwind ([`frontend/package.json`](frontend/package.json)).
- **Puerto:** http://localhost:5173
- **Responsabilidad:** SPA por módulos (`moduloActivo` en [`frontend/src/App.jsx`](frontend/src/App.jsx)); consume Laravel vía [`frontend/src/lib/api.js`](frontend/src/lib/api.js).
- **No** llama a Flask ni a MySQL directamente.
- RF-19 semáforo e import SIAGIE: **pendientes** (no confirmados en UI).

### B. Backend (Application Tier)

- **Tecnología:** Laravel ^13, PHP 8.3 ([`backend/composer.json`](backend/composer.json)).
- **Puerto:** http://localhost:8000
- **Responsabilidad:**
  - API REST (`backend/routes/api.php`, `backend/routes/auth.php`)
  - Sanctum + Spatie Permission (`permission:*`)
  - Módulo curricular (`/api/curricular/*`)
  - Riesgo vía `RiesgoAcademicoService` → `MlRiskService`
  - Alertas, intervenciones, dashboard, usuarios
  - Auditoría parcial (`spatie/laravel-activitylog`)
- **Legacy:** API materias y lotes notas/asistencia coexisten; UI operativa es curricular.

### C. ML Service (Intelligence Tier)

- **Tecnología:** Flask ([`ml-service/requirements.txt`](ml-service/requirements.txt) — solo `flask`).
- **Puerto:** http://localhost:5000
- **Endpoints:** `GET /`, `POST /predict` ([`ml-service/main.py`](ml-service/main.py)).
- **Estado:** prototipo **determinístico**; no RF/SVM/XGBoost entrenados; sin reentrenamiento (RF-18 pendiente).
- **Sin** acceso a MySQL.

### D. Database (Data Tier)

- **MySQL 8.0** — servicio `db-mysql`, contenedor `siderae_mysql`.
- **Host:** `localhost:3307` → `3306` interno; BD `siderae_db`.
- Persistencia: `./docker/mysql_data/`.

---

## 4. Red Docker

Servicios en [`docker-compose.yml`](docker-compose.yml):

| Servicio Compose | Contenedor | Puerto host |
|------------------|------------|-------------|
| `app-frontend` | `siderae_frontend` | 5173 |
| `app-backend` | `siderae_backend` | 8000 |
| `ml-engine` | `siderae_ml` | 5000 |
| `db-mysql` | `siderae_mysql` | 3307 |

**Dependencias:**

- `app-backend` → `db-mysql` (`condition: service_healthy`).
- `app-backend` → `ml-engine` **solo en runtime** vía `ML_SERVICE_URL=http://ml-engine:5000` ([`backend/.env.example`](backend/.env.example), [`backend/config/services.php`](backend/config/services.php)).
- **Healthcheck:** solo MySQL. Backend/ML sin healthcheck en Compose.

**Arranque backend:** `composer install`, `key:generate`, `migrate --force` — **sin seed automático**.

---

## 5. Flujos críticos

### 5.1 Autenticación

1. Frontend obtiene CSRF: `GET /sanctum/csrf-cookie`
2. Login: `POST /login` ([`backend/routes/auth.php`](backend/routes/auth.php))
3. Sesión: `GET /api/me` (roles + permisos)

### 5.2 Detección de riesgo

1. Usuario con `procesar_riesgo` → `POST /api/estudiantes/{id}/procesar-riesgo`
2. Laravel valida y arma payload (`RiesgoAcademicoService`)
3. `MlRiskService` → `POST {ML_SERVICE_URL}/predict`
4. Persistencia `indices_riesgo`; alerta si nivel alto (`Alerta`)
5. Frontend muestra último índice en perfil

**Nota:** no hay disparo automático a Flask tras cada nota; riesgo es endpoint explícito o comando `demo:procesar-riesgos`.

### 5.3 Módulo curricular

Flujo operativo en UI: malla → criterios → asignación docente → notas semanales (CE) → evaluación bimestral → asistencia diaria → Excel aula/import plantilla.

API bajo `/api/curricular/*` — ver [`docs/api.md`](docs/api.md).

### 5.4 Tablas sin API (pendiente)

Migraciones existen para `reportes_conductuales`, `comunicaciones_familiares`; **sin rutas** en `api.php` (RF-04, RF-10, RF-12 pendientes).

---

## 6. Seguridad (resumen)

- Autorización efectiva: **backend** (401 sin sesión, 403 sin permiso).
- Frontend oculta menú; no sustituye validación servidor.
- Matriz vigente: [`docs/seguridad-roles-permisos.md`](docs/seguridad-roles-permisos.md).
- Referencia histórica parcial: [`docs/arquitectura/matriz-control-accesos-sprint8.md`](docs/arquitectura/matriz-control-accesos-sprint8.md).

---

## 7. Pruebas

- PHPUnit Feature/Unit: [`backend/tests/`](backend/tests/) (~49 archivos).
- Suite completa puede requerir `memory_limit=512M` para tests Excel ([`docs/pruebas/hallazgos-fase1-documentacion.md`](docs/pruebas/hallazgos-fase1-documentacion.md)).
- Cypress: **no confirmado** en repo.

---

## 8. Documentos de arquitectura

| Documento | Uso |
|-----------|-----|
| [`docs/arquitectura/resumen-arquitectura.md`](docs/arquitectura/resumen-arquitectura.md) | Matriz DRS vs código |
| [`docs/arquitectura/contexto-backend-laravel.md`](docs/arquitectura/contexto-backend-laravel.md) | Backend |
| [`docs/arquitectura/contexto-frontend-react.md`](docs/arquitectura/contexto-frontend-react.md) | Frontend |
| [`docs/arquitectura/contexto-ml-service-flask.md`](docs/arquitectura/contexto-ml-service-flask.md) | ML (complemento: [`docs/ml-service.md`](docs/ml-service.md)) |
| [`docs/arquitectura/contexto-docker-infraestructura.md`](docs/arquitectura/contexto-docker-infraestructura.md) | Docker (complemento: [`docs/instalacion-docker.md`](docs/instalacion-docker.md)) |

---

*Última actualización documental: Fase 2 (2026-06-09).*
