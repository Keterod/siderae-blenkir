# Arquitectura del Sistema SIDERAE-Blenkir

## 1. Descripción General
SIDERAE-Blenkir utiliza una arquitectura de microservicios desacoplados orquestados mediante Docker, orientada a un **prototipo académico** con alcance formal definido en el DRS. El código real del repositorio puede ir por detrás del alcance DRS en varios RF; priorizar siempre verificación en código.

## Decisión operativa: sede única Chilca

- **V1:** operación institucional solo en sede **Chilca** (UI y demo; consultas por defecto).
- **`sede` en datos:** se conserva en tablas y contratos API; validaciones siguen aceptando `chilca` y `auquimarca` para datos históricos y extensión futura.
- **Implementación:** sin selectores de sede en React; helpers `frontend/src/lib/sedeOperativa.js` y `App\Support\SedeOperativa` en Laravel.
- **Fuera de alcance de este criterio:** cambios en `ml-service/`, `RiesgoAcademicoService` o eliminación de columnas/migraciones de `sede`.

## 2. Componentes del Sistema

### A. Frontend (Client Tier)
- **Tecnología:** React + Vite (ver `frontend/package.json` para versiones).
- **Puerto local típico:** `http://localhost:5173`
- **Responsabilidad:** Interfaz para los roles definidos en seeders; consumo de la API Laravel. El **semáforo RF-19** y la **importación masiva Excel/CSV RF-01** **no están confirmados** en el frontend actual: tratarlos como **pendientes de desarrollo** salvo evidencia en código.

### B. Backend (Application Tier)
- **Tecnología:** Laravel (versión según `backend/composer.json`, actualmente ^13) y PHP 8.3.
- **Puerto local típico:** `http://localhost:8000`
- **Responsabilidad:** API REST principal, autenticación Sanctum, autorización Spatie (`permission:*` en rutas sensibles), persistencia en MySQL, orquestación del cálculo de riesgo llamando a Flask, alertas e intervenciones. **Auditoría:** registro de acciones críticas con `spatie/laravel-activitylog` en controladores API (Sprint 7.5A).

### C. ML Service (Intelligence Tier)
- **Tecnología:** Python + Flask (ver `ml-service/requirements.txt`).
- **Puerto local típico:** `http://localhost:5000`
- **Responsabilidad:** Endpoint `POST /predict` que devuelve `indice_riesgo` y `nivel_riesgo`. En el estado actual del código, el cálculo es un **prototipo determinístico** documentado en `ml-service/main.py`. **No confirmado en el estado actual:** entrenamiento ni inferencia con Random Forest, SVM y XGBoost como producto separado (alcance formal DRS / RF-06 avanzado).

### D. Database (Data Tier)
- **Tecnología:** MySQL 8.0 (contenedor `db-mysql`).
- **Puerto desde el host (Docker Compose del repo):** `3307` → `3306` en el contenedor.
- **Nombre BD (ejemplo):** `siderae_db`

## 3. Flujo de Datos Crítico (Detección de Riesgo)
1. **Frontend:** El usuario con permiso `procesar_riesgo` dispara el procesamiento (p. ej. acción que llama `POST /api/estudiantes/{id}/procesar-riesgo`).
2. **Backend:** Valida datos mínimos, construye el payload y llama a Flask (`MlRiskService` → `POST {ML_SERVICE_URL}/predict`).
3. **ML Service:** Devuelve índice y nivel.
4. **Backend:** Clasifica/persiste en `indices_riesgo`, puede generar alerta si el nivel es Alto (lógica en `App\Models\Alerta`), y **registra actividad** en `activity_log` para trazabilidad.
5. **Frontend:** Muestra el último índice en el perfil del estudiante según la API.

**Nota:** No hay en el código revisado un disparo **automático** a Flask inmediatamente después de cada `POST` de nota; el flujo explícito de riesgo es el endpoint de procesamiento.

## 4. Red de Docker (Network)
Los servicios se comunican internamente usando los nombres de servicio definidos en el `docker-compose.yml`:
- El backend se conecta a la DB vía `db-mysql`.
- El backend se conecta al servicio ML vía el hostname configurado en `ML_SERVICE_URL` (p. ej. `ml-engine` dentro de Compose).
