# Arquitectura del Sistema SIDERAE-Blenkir

## 1. Descripción General
SIDERAE-Blenkir utiliza una arquitectura de microservicios desacoplados orquestados mediante Docker, diseñada para cumplir con los estándares de mantenibilidad de ISO/IEC 25010.

## 2. Componentes del Sistema

### A. Frontend (Client Tier)
- **Tecnología:** React 18 + Vite.
- **Puerto Local:** `http://localhost:5173`
- **Responsabilidad:** Interfaz de usuario para los 5 roles, visualización del semáforo de riesgo (RF-19) y carga de archivos Excel (RF-01).

### B. Backend (Application Tier)
- **Tecnología:** Laravel 11 (PHP 8.3).
- **Puerto Local:** `http://localhost:8000`
- **Responsabilidad:** - API REST principal.
    - Gestión de autenticación y roles (RN-05).
    - CRUD de estudiantes y notas.
    - Orquestación: Envía datos a Flask y recibe predicciones de riesgo.

### C. ML Service (Intelligence Tier)
- **Tecnología:** Python 3.11 + Flask.
- **Puerto Local:** `http://localhost:5000`
- **Responsabilidad:** - Ejecución de modelos Random Forest, SVM y XGBoost.
    - Retorno del "Índice de Riesgo" (valor 0 a 1).

### D. Database (Data Tier)
- **Tecnología:** MySQL 8.0.
- **Puerto Local:** `3306`
- **Nombre BD:** `siderae_db`

## 3. Flujo de Datos Crítico (Detección de Riesgo)
1. **Frontend:** El docente sube notas (RF-01).
2. **Backend:** Valida y almacena en MySQL. Automáticamente dispara una solicitud `POST` a `http://ml-engine:5000/predict`.
3. **ML Service:** Procesa los datos y devuelve el índice de riesgo.
4. **Backend:** Evalúa **RN-01**. Si el promedio es < 11 o el índice es > 0.7, marca al estudiante en "Riesgo Alto".
5. **Frontend:** Actualiza el semáforo a Rojo (RF-19).

## 4. Red de Docker (Network)
Los servicios se comunican internamente usando los nombres de servicio definidos en el `docker-compose.yml`:
- El backend se conecta a la DB vía `db-mysql`.
- El backend se conecta a la IA vía `ml-engine`.