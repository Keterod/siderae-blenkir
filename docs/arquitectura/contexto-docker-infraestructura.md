# Contexto Docker e infraestructura (v1)

## Objetivo de infraestructura local
Proveer un entorno reproducible para ejecutar frontend, backend, base de datos y ML Service con Docker Compose, alineado al alcance formal del DRS.

## Servicios definidos (`docker-compose.yml`)
- `db-mysql`
- `app-backend`
- `app-frontend`
- `ml-engine`

## Contenedores (nombre explicito en compose)
- `siderae_mysql`
- `siderae_backend`
- `siderae_frontend`
- `siderae_ml`

## Puertos expuestos
- MySQL: `3307:3306`
- Backend Laravel: `8000:8000`
- Frontend Vite: `5173:5173`
- ML Flask: `5000:5000`

## Dependencias entre servicios
- `app-backend` depende de `db-mysql` con condicion `service_healthy`.
- No se define dependencia explicita de backend hacia `ml-engine` en compose; la conexion se realiza por URL de servicio en runtime.

## Healthchecks detectados
- `db-mysql` tiene healthcheck (`mysqladmin ping`).
- No se observan healthchecks declarados en compose para backend/frontend/ml.

## Variables de entorno relevantes (plantillas `.env.example`)
- Backend:
  - `DB_HOST=db-mysql`
  - `DB_PORT=3306`
  - `DB_DATABASE=siderae_db`
  - `ML_SERVICE_URL=http://ml-engine:5000`
  - `FRONTEND_URL=http://localhost:5173`
- Frontend:
  - `VITE_API_URL=http://localhost:8000`
- ML:
  - `PORT=5000`

## Comandos basicos de entorno
- `docker compose up -d --build`
- `docker compose ps`
- `docker compose logs`

## Troubleshooting basico
- Si el backend no responde al inicio, esperar a que termine instalacion y migraciones del contenedor Laravel.
- Si una llamada HTTP devuelve vacio o error inesperado, revisar `docker compose logs` por servicio.
- Si falla autenticacion con Sanctum en frontend, limpiar cookies de sesion y repetir login.

## Relacion con el DRS
- Compose soporta separacion de capas (frontend/backend/mysql/ml).
- Mantiene arquitectura desacoplada definida como alcance formal.
- Favorece portabilidad del entorno local, alineado a requerimientos no funcionales del DRS.

## Reglas para Cursor (infra)
- No cambiar puertos sin autorizacion.
- No cambiar nombres de servicios sin revisar dependencias en `.env`, backend y frontend.
- No exponer secretos ni credenciales reales en documentacion.
- No asumir integraciones externas no declaradas en compose/codigo.

## Pendientes de verificar
- Si el entorno final de despliegue tendra diferencias frente al entorno local Docker.
- Si se agregaran healthchecks para backend y ml en siguiente iteracion.
