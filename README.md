# SIDERAE-Blenkir
Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil

---

## 📌 Descripción

SIDERAE-Blenkir es un sistema web que permite:

- Gestionar estudiantes
- Registrar datos académicos (notas, asistencia, variables socioeconómicas)
- Detectar riesgo académico mediante Machine Learning
- Gestionar alertas e intervenciones sobre estudiantes en riesgo

---

## 🏗️ Arquitectura del sistema

El sistema está dividido en:

- **Frontend:** React + Vite + Tailwind
- **Backend:** Laravel 11 (API REST)
- **Base de datos:** MySQL 8
- **ML Service:** Python (Flask)
- **Infraestructura:** Docker + Docker Compose

---

## 🚀 Tecnologías utilizadas

- PHP 8.3 / Laravel
- React 18
- MySQL 8
- Python 3.11 (Flask)
- Docker

---

## 📂 Estructura del proyecto
Proyecto/
│
├── backend/ → API Laravel
├── frontend/ → Aplicación React
├── ml-service/ → Servicio de Machine Learning
├── docker/ → Configuración de contenedores
├── sprints/ → Documentación por fases
└── docker-compose.yml

---

## ⚙️ Requisitos

- Docker Desktop instalado
- Git instalado

---

## Configuración del entorno local

Sigue estos pasos para clonar el repositorio y levantar todo el proyecto con Docker.

### 1. Clonar el repositorio

```bash
git clone https://github.com/Keterod/siderae-blenkir.git
cd siderae-blenkir
```

(Si el remoto cambia en el futuro, usa la URL que te dé el equipo o la que figure en tu plataforma Git.)

### 2. Crear los archivos `.env` desde los `.env.example`

Cada carpeta tiene una plantilla. Copiala con:

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
cp ml-service/.env.example ml-service/.env
```

Luego revisa `backend/.env` en particular (credenciales de base de datos y URLs) para que coincida con tu uso local o Docker.

### 3. Levantar el proyecto con Docker

```bash
docker compose up -d --build
```

### 4. Generar `APP_KEY` de Laravel si es necesario

Si el backend avisa que falta `APP_KEY`, genera una clave dentro del contenedor:

```bash
docker compose exec app-backend php artisan key:generate
```

### 5. Verificar servicios

Con los contenedores en marcha puedes abrir:

- **Frontend:** http://localhost:5173
- **Backend (API Laravel):** http://localhost:8000
- **ML Service:** http://localhost:5000
- **MySQL (desde tu máquina, host puerto expuesto por Compose):** `localhost:3307`

### 6. Archivos `.env` y seguridad en GitHub

Los archivos `.env` reales **no** deben subirse al repositorio: contienen datos sensibles locales (clave de aplicación, contraseñas, etc.). El proyecto los ignora con `.gitignore` para esa razón.

### 7. Archivos `.env.example`

Las plantillas **`.env.example`** **sí** se suben porque no incluyen secretos reales y sirven de referencia para que cada persona copie los nombres de variables correctos después de clonar.

---

## ▶️ Cómo ejecutar el proyecto

Si ya completaste [Configuración del entorno local](#configuración-del-entorno-local) (clonar, crear `.env` y `docker compose up -d --build`), pasa directamente a preparar el backend (**paso 3**).

### 1. Clonar repositorio

```bash
git clone https://github.com/Keterod/siderae-blenkir.git
cd siderae-blenkir
```
### 2. Levantar servicios
```bash
docker compose up -d --build
```

### 3. Preparar backend
```bash
docker compose exec app-backend php artisan migrate --seed
docker compose exec app-backend php artisan optimize:clear
```

4. Acceder al sistema
Frontend:
```bash
http://localhost:5173
```
Backend:
```bash
http://localhost:8000
```

## 🔐 Credenciales de prueba
```bash
Email: test@example.com
Password: password
```

## 📊 Funcionalidades implementadas
✔ Sprint 1
- Infraestructura Docker
- Servicios levantados

✔ Sprint 2
- Autenticación con Laravel Sanctum
- Roles y permisos (Spatie)
- Endpoint /api/me

✔ Sprint 3A
- CRUD de estudiantes
- Validaciones
- Pruebas automatizadas

✔ Sprint 3B
- Registro de:
-- Notas
-- Asistencia
-- Variables socioeconómicas
- Integración en perfil de estudiante
- Pruebas Feature

✔ Sprint 4

Integración con Machine Learning (Flask)
Procesamiento de riesgo académico
Cálculo de índice de riesgo (Alto / Medio / Bajo)
Persistencia del riesgo en base de datos
Visualización en el perfil del estudiante
Pruebas automatizadas

✔ Sprint 5

Generación automática de alertas
Listado y detalle de alertas
Registro de intervenciones
Cambio de estado: pendiente → en atención → cerrada
Validaciones de cierre de alertas
Pruebas automatizadas

## 🧪 Pruebas automatizadas

Ejecutar:
```bash
docker compose exec app-backend php artisan test
```

## 🧠 Estado del proyecto
✔ Sistema funcional
✔ Base de datos estructurada
✔ Sistema inteligente completo funcionando
✔ Machine Learning integrado
✔ Alertas e intervenciones operativas

## 🚧 Próximos desarrollos
- Sprint 6: Dashboard y visualización
## 👥 Equipo
- Diego Carhuamaca Vasquez
- Ernesto Chuchon Sotelo
## 📌 Notas
- No eliminar estudiantes (integridad de datos)
- Uso de permisos para control de acceso
- Datos académicos estructurados para análisis predictivo

---

## 🎯 Qué logra esto

```text
✔ Tu grupo entiende cómo ejecutar
✔ Orden académico
✔ Documentación profesional
✔ Listo para presentación