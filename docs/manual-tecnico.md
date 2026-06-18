# Manual técnico — SIDERAE-Blenkir

Documento breve para desarrolladores y defensa académica del prototipo V1. DRS vigente: [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md). PDF v1 externo al repo.

Referencias: [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) · [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) · [`limitaciones.md`](limitaciones.md) · [`instalacion-docker.md`](instalacion-docker.md) · [`manual-usuario.md`](manual-usuario.md) · [`aula-notas-excel.md`](aula-notas-excel.md) · [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) · [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) · [`calidad/alineacion-iso.md`](calidad/alineacion-iso.md) · [`api.md`](api.md) · [`ml-service.md`](ml-service.md)

---

## 1. Propósito

Describir stack, servicios, configuración, flujos técnicos y pruebas **confirmados en código**, con advertencias sobre alcance parcial y pendientes.

---

## 2. Stack tecnológico

| Capa | Tecnología | Evidencia |
|------|------------|-----------|
| Frontend | React 18, Vite, Tailwind | `frontend/package.json` |
| Backend | PHP 8.3, Laravel ^13 | `backend/composer.json` |
| Auth | Laravel Sanctum + Breeze | `routes/auth.php`, Sanctum |
| RBAC | Spatie Permission (5 roles, 23 permisos) | `PermissionsSeeder.php` |
| BD | MySQL 8 | `docker-compose.yml` |
| ML | Flask (determinístico) | `ml-service/main.py` |
| PDF | DomPDF | `DashboardController` export |
| Excel | Maatwebsite Excel | import plantilla curricular |
| Auditoría | Spatie Activitylog | `ActivityLogTest.php` |
| Infra | Docker Compose (4 servicios) | `docker-compose.yml` |

**No confirmado:** suite E2E completa, despliegue productivo, certificación ISO. Cypress existe solo como smoke mínimo RF-04.

---

## 3. Arquitectura lógica

```text
Browser → React :5173
       → Laravel API :8000 → MySQL :3307 (host)
       → Flask ML :5000 (solo vía Laravel)
```

Detalle: [`../ARCHITECTURE.md`](../ARCHITECTURE.md), [`arquitectura/resumen-arquitectura.md`](arquitectura/resumen-arquitectura.md).

---

## 4. Servicios Docker

| Compose | Contenedor | Puerto host |
|---------|------------|-------------|
| `app-frontend` | `siderae_frontend` | 5173 |
| `app-backend` | `siderae_backend` | 8000 |
| `ml-engine` | `siderae_ml` | 5000 |
| `db-mysql` | `siderae_mysql` | 3307 → 3306 |

Arranque: ver [`instalacion-docker.md`](instalacion-docker.md).

**Notas:**

- Backend depende de MySQL healthy; **no** de ML en Compose.
- Solo MySQL tiene healthcheck.
- Arranque backend: migrate sí, seed **no**.

---

## 5. Variables de entorno

Usar solo `.env.example` como referencia:

- `backend/.env.example` — DB, Sanctum, `ML_SERVICE_URL=http://ml-engine:5000`
- `frontend/.env.example` — `VITE_API_URL=http://localhost:8000`
- `ml-service/.env.example` — `PORT=5000`

Config ML en Laravel: [`backend/config/services.php`](../backend/config/services.php) → `config('services.ml.url')`.

---

## 6. Autenticación y API

### Flujo SPA

1. `GET /sanctum/csrf-cookie`
2. `POST /login` (JSON: email, password)
3. `GET /api/me` → `{ usuario, roles, permisos }`
4. Peticiones `/api/*` con cookies + header `X-XSRF-TOKEN`

Cliente: [`frontend/src/lib/api.js`](../frontend/src/lib/api.js), [`AuthContext.jsx`](../frontend/src/context/AuthContext.jsx).

### Rutas auth

[`backend/routes/auth.php`](../backend/routes/auth.php): login, logout, register (**público**), reset password.

### Autorización

Middleware `auth:sanctum` + `permission:*` en [`backend/routes/api.php`](../backend/routes/api.php).

Matriz rol–permiso vigente: [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md). Histórico Sprint 8: [`matriz-control-accesos-sprint8.md`](arquitectura/matriz-control-accesos-sprint8.md).

---

## 7. Módulos backend

| Módulo | Prefijo / rutas | Estado |
|--------|-----------------|--------|
| Core (estudiantes, riesgo, alertas) | `/api/*` | Confirmado |
| Legacy materias/lotes | `/api/materias`, lotes | API sí; UI menú no |
| Curricular | `/api/curricular/*` | Confirmado |
| Usuarios | `/api/usuarios/*` | Confirmado |

Catálogo endpoints: [`api.md`](api.md).

Contexto ampliado: [`arquitectura/contexto-backend-laravel.md`](arquitectura/contexto-backend-laravel.md).

---

## 8. Frontend

SPA por módulos en [`App.jsx`](../frontend/src/App.jsx). Sin llamadas directas a Flask.

Contexto: [`arquitectura/contexto-frontend-react.md`](arquitectura/contexto-frontend-react.md).

Decisión sede Chilca: [`../AGENTS.md`](../AGENTS.md), `sedeOperativa.js`. V1 no expone selectores de sede; la BD local puede contener registros Auquimarca por historial previo — no implica operación multi-sede en V1.

---

## 9. ML Service

Integración Laravel → Flask documentada en [`ml-service.md`](ml-service.md).

- **Confirmado:** `POST /predict`, prototipo determinístico.
- **Planificado:** ML real y reentrenamiento (RF-18) cuando exista dataset; **no** RF/SVM/XGBoost entrenados en V1.
- **Variables socioeconómicas:** retiradas del flujo de riesgo vigente (v2.1).

Servicio: [`MlRiskService.php`](../backend/app/Services/MlRiskService.php).

---

## 10. Base de datos y seeders

- Migraciones: `backend/database/migrations/` (33 archivos).
- Seed principal: `DatabaseSeeder` → roles, demo users, curricular, estudiantes (demo nuevos solo sede **Chilca** — [`AGENTS.md`](../AGENTS.md)).
- Legacy opcional: `DemoAcademicDataSeeder` (no incluido por defecto).

> **Conteos Fase 1:** si la BD auditada muestra estudiantes Auquimarca, son datos del entorno local medido, no alcance operativo V1 multi-sede. Ver [`pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) §4.

Persistencia local: `docker/mysql_data/`.

---

## 11. Pruebas

### PHPUnit

```bash
docker compose exec app-backend php artisan test
docker compose exec app-backend php artisan test --filter=Curricular
docker compose exec app-backend php -d memory_limit=512M artisan test --filter=ExcelAulaTest
```

~49 archivos en `backend/tests/`.

### Resultado Fase 1 (2026-06-09)

| Ejecución | Resultado |
|-----------|-----------|
| Suite completa (`memory_limit` 128M) | **Incompleta** — OOM en `ExcelAulaTest` tras ~277 tests OK |
| `ExcelAulaTest` con 512M | **8 passed** |

Ver [`pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md).

### Frontend build

```bash
docker compose exec app-frontend npm run build
```

### Cypress

Configuración mínima para RF-04:

```bash
cd frontend
npm run cy:open
npm run cy:run
```

Variables requeridas:

```bash
CYPRESS_E2E_EMAIL=
CYPRESS_E2E_PASSWORD=
CYPRESS_E2E_STUDENT_TEXT=
```

`CYPRESS_E2E_STUDENT_TEXT` es opcional. Alcance: solo smoke E2E RF-04 reportes conductuales; no es suite Cypress completa del sistema. Detalle: [`pruebas/cypress-rf04.md`](pruebas/cypress-rf04.md).

---

## 12. Comandos operativos

### Riesgo masivo (excepcional)

```bash
docker compose exec app-backend php artisan demo:procesar-riesgos \
  --sede=chilca --anio=2026 --bimestre=1 --confirmar-post-import
```

No es flujo diario. Requiere ML activo.

### Artisan útil

```bash
docker compose exec app-backend php artisan route:list --path=api
docker compose exec app-backend php artisan migrate:status
```

---

## 13. Alcance vs DRS (resumen — v2.1)

| Área | Estado |
|------|--------|
| Auth, RBAC, estudiantes, curricular **RF-21–RF-35**, Excel plantilla/aula, riesgo, alertas | Confirmado / parcial |
| **RF-04** reportes conductuales (perfil estudiante) | **Implementado V1 mínimo** — Fases 2B–2E |
| SIAGIE, Fast Test, VSE en riesgo, comunicación familiar | **Fuera del alcance vigente** |
| Escalamiento directivo, semáforo, reportes riesgo RF-16, historial evolutivo | **Planificado** |
| Dashboard académico-institucional | Parcial (riesgo subset hoy) |
| ML ensemble, reentrenamiento RF-18 | **Planificado** — no implementado |

RF vigentes: **RF-01 a RF-35**. Detalle: [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) v2.1, [`limitaciones.md`](limitaciones.md).

---

## 14. Normas ISO (referencia orientativa)

ISO/IEC 25010, ISO/IEC 27000 e ISO 9001 se mencionan en documentación del proyecto **solo como marco académico orientativo** ([`sprints/sprint 10.md`](../sprints/sprint%2010.md)). **No** se afirma certificación ni auditoría externa.

---

## 15. Documentación relacionada

Índice maestro: [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md).

Documentación vigente: [`drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md), [`manual-usuario.md`](manual-usuario.md), [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md), [`matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md), [`pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md), [`aula-notas-excel.md`](aula-notas-excel.md), [`calidad/alineacion-iso.md`](calidad/alineacion-iso.md), [`limitaciones.md`](limitaciones.md).

---

*Fase 2 documental — 2026-06-09.*
