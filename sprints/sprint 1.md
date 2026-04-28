# Sprint 1: Infraestructura Docker + arranque real + health checks

## Objetivo
Dejar el entorno técnico listo para desarrollo funcional real, con arranque reproducible de servicios.

## Duración estimada
1 semana

## Alcance
- Docker Compose funcional sin `tail -f /dev/null`.
- Backend, frontend, MySQL y ML levantando automáticamente.
- Variables de entorno alineadas entre servicios.
- Health checks mínimos de backend y ML.

## Actividades
1. Corregir `docker-compose.yml` para arranque real de:
   - `db-mysql` (`3307:3306`)
   - `app-backend` (`8000:8000`)
   - `app-frontend` (`5173:5173`)
   - `ml-engine` (`5000:5000`)
2. Ajustar comandos de inicio:
   - Backend: `composer install`, `php artisan migrate --seed`, `php artisan serve`.
   - Frontend: `npm install`, `npm run dev -- --host 0.0.0.0`.
   - ML: `pip install -r requirements.txt`, `python main.py`.
3. Crear/validar `ml-service/requirements.txt` con dependencias mínimas:
   - `flask`, `numpy`, `scikit-learn`, `xgboost`.
4. Validar `backend/.env`:
   - `DB_HOST=db-mysql`
   - `DB_PORT=3306`
   - `DB_DATABASE=siderae_db`
   - `ML_SERVICE_URL=http://ml-engine:5000`
   - `FRONTEND_URL=http://localhost:5173`
5. Definir y probar health checks:
   - Laravel: `GET /api/health`
   - Flask: `GET /`
6. Verificar levantamiento integral con `docker compose up -d --build`.

## Dependencias de entrada
Ninguna.

## Dependencias de salida
Habilita Sprint 2.

## Criterios de aceptación
- El stack completo levanta con un solo comando.
- Laravel conecta a MySQL sin errores.
- Backend y ML responden health checks.
- Sin errores críticos de arranque.

## Entregables
- `docker-compose.yml` operativo.
- `ml-service/requirements.txt` listo.
- `backend/.env` alineado.
- Endpoints de salud funcionando.
