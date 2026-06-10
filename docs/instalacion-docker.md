# Instalación local con Docker — SIDERAE-Blenkir

Guía de arranque reproducible del prototipo académico V1. Alcance y limitaciones: [`limitaciones.md`](limitaciones.md).

---

## 1. Requisitos

| Requisito | Detalle |
|-----------|---------|
| Docker Desktop | Compose v2 |
| Git | Clonar repositorio |
| RAM | Mínimo 4 GB; **8 GB** recomendado para tests Excel |
| Puertos libres | 5173, 8000, 5000, 3307 |

---

## 2. Clonar repositorio

```bash
git clone https://github.com/Keterod/siderae-blenkir.git
cd siderae-blenkir
```

(Ajuste la URL si el remoto del equipo difiere.)

---

## 3. Archivos de entorno

Copie las plantillas (**no** contienen secretos de producción):

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
cp ml-service/.env.example ml-service/.env
```

### Variables clave (plantillas)

**Backend** (`backend/.env.example`):

| Variable | Valor ejemplo Docker |
|----------|----------------------|
| `APP_URL` | `http://localhost:8000` |
| `DB_HOST` | `db-mysql` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `siderae_db` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | `secret` |
| `ML_SERVICE_URL` | `http://ml-engine:5000` |
| `FRONTEND_URL` | `http://localhost:5173` |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost,localhost:5173,...` |

**Frontend** (`frontend/.env.example`):

| Variable | Valor |
|----------|-------|
| `VITE_API_URL` | `http://localhost:8000` |

**ML** (`ml-service/.env.example`):

| Variable | Valor |
|----------|-------|
| `PORT` | `5000` |

No suba `.env` reales al repositorio.

---

## 4. Levantar servicios

```bash
docker compose up -d --build
```

### Qué hace cada servicio al arrancar

| Servicio | Comando de arranque (resumen) |
|----------|-------------------------------|
| `db-mysql` | MySQL 8, healthcheck |
| `app-backend` | `composer install`, `key:generate`, **`migrate --force`**, `artisan serve :8000` |
| `app-frontend` | `npm install`, `npm run dev :5173` |
| `ml-engine` | `pip install`, `python main.py :5000` |

Fuente: [`docker-compose.yml`](../docker-compose.yml).

### Migraciones vs seed

- **Automático al `up`:** migraciones (`migrate --force`).
- **No automático:** `--seed`. Tras el primer arranque la BD puede estar **sin datos demo** hasta ejecutar §6.

Espere 1–3 minutos la primera vez (instalación de dependencias).

---

## 5. Verificación

```bash
docker compose ps
```

URLs:

| Servicio | URL |
|----------|-----|
| Frontend | http://localhost:5173 |
| Backend health | http://localhost:8000/api/health |
| ML health | http://localhost:5000/ |

Logs:

```bash
docker compose logs --tail=120 app-backend
docker compose logs --tail=120 app-frontend
docker compose logs --tail=120 ml-engine
```

Si el backend falla al inicio, espere a que MySQL esté `healthy` y las migraciones terminen.

---

## 6. Datos demo (opcional)

### Seed completo (destructivo)

```bash
docker compose exec app-backend php artisan migrate:fresh --seed
```

**Advertencia:** borra todos los datos en la BD local (`docker/mysql_data/`).

Ejecuta [`DatabaseSeeder.php`](../backend/database/seeders/DatabaseSeeder.php): roles, permisos, usuarios demo, catálogo curricular, estudiantes y demo operativo.

### Usuarios demo

Contraseña: `password` (ver [`DemoUsersSeeder.php`](../backend/database/seeders/DemoUsersSeeder.php)).

| Correo | Rol |
|--------|-----|
| `admin@siderae.test` | administrador |
| `docente@siderae.test` | docente |
| `coordinador@siderae.test` | coordinador_academico |
| `psicologo@siderae.test` | psicologo_tutor |
| `directivo@siderae.test` | directivo |

### Conteos de datos

Los conteos **dependen del historial de su BD** y no son constantes del producto.

En auditoría Fase 1 (2026-06-09), **sin** `migrate:fresh` en esa sesión, se observó: 449 estudiantes (253 Chilca / 196 Auquimarca). Tras seed limpio los valores pueden diferir — **pendiente de confirmar** en entorno de referencia.

> **Nota sobre conteos Fase 1 (BD local auditada):** Estos conteos pertenecen al entorno local auditado. La presencia de registros con sede Auquimarca no implica operación multi-sede en V1; la decisión vigente de V1 es sede operativa **Chilca** ([`../AGENTS.md`](../AGENTS.md)).

Consulta no destructiva:

```bash
docker compose exec app-backend php artisan tinker --execute="echo App\Models\Estudiante::count();"
```

---

## 7. Operación diaria

```bash
# Pausar
docker compose stop

# Reanudar
docker compose start

# Apagar contenedores (datos persisten en docker/mysql_data/)
docker compose down

# Reconstruir tras cambios en Dockerfile/deps
docker compose up -d --build
```

**No ejecutar** `docker compose down -v` ni borrar `docker/mysql_data/` si desea conservar datos.

**No ejecutar** `migrate:fresh --seed` salvo que acepte perder la BD local.

---

## 8. Pruebas backend

```bash
docker compose exec app-backend php artisan test
```

**Limitación conocida (Fase 1):** con `memory_limit` PHP 128M la suite puede fallar por OOM en `ExcelAulaTest`. Workaround:

```bash
docker compose exec app-backend php -d memory_limit=512M artisan test --filter=ExcelAulaTest
docker compose exec app-backend php -d memory_limit=512M artisan test
```

Detalle: [`pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md).

---

## 9. Troubleshooting

| Problema | Acción |
|----------|--------|
| Backend 500 al inicio | Revisar logs; esperar migrate |
| Frontend no carga API | Verificar `VITE_API_URL` y CORS/Sanctum domains |
| ML no responde | `docker compose logs ml-engine`; verificar `ML_SERVICE_URL` |
| Login falla sin seed | Ejecutar seed §6 |
| Tests Excel OOM | Aumentar `memory_limit` §8 |

---

## 10. Documentos relacionados

- [`../README.md`](../README.md)
- [`manual-tecnico.md`](manual-tecnico.md)
- [`limitaciones.md`](limitaciones.md)
- [`../ARCHITECTURE.md`](../ARCHITECTURE.md)

---

*Fase 2 documental — 2026-06-09.*
