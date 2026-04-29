# SIDERAE-Blenkir
Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil

---

## 📌 Descripción

SIDERAE-Blenkir es un sistema web que permite:

- Gestionar estudiantes
- Registrar datos académicos (notas, asistencia, variables socioeconómicas)
- Detectar riesgo académico mediante Machine Learning
- Gestionar alertas e intervenciones (futuro)

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

## ▶️ Cómo ejecutar el proyecto

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

## 🧪 Pruebas automatizadas

Ejecutar:
```bash
docker compose exec app-backend php artisan test
```

## 🧠 Estado del proyecto
✔ Sistema funcional
✔ Base de datos estructurada
✔ Machine Learning integrado y funcionando
## 🚧 Próximos desarrollos
- Sprint 5: Alertas e intervenciones
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