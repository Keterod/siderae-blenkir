# SIDERAE-Blenkir
Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil

---

## 📌 Descripción

SIDERAE-Blenkir es un sistema web que permite:

- Autenticación y autorización por roles/permisos.
- Gestión de estudiantes.
- Catálogo institucional de materias por sede/nivel/grado/año.
- Registro individual y masivo de notas.
- Registro individual y masivo de asistencia.
- Registro de variables socioeconómicas por estudiante *(pausado en UI; ver nota al final)*.
- Procesamiento de riesgo académico (manual por estudiante, automático por lotes académicos, y comando excepcional post-importación).
- Gestión de alertas e intervenciones.
- Dashboard con filtros y export PDF (alcance parcial frente al DRS).

---

## Decisión operativa: sede única Chilca

SIDERAE-Blenkir **V1** opera únicamente con la sede **Chilca** en interfaz, filtros por defecto y datos demo nuevos.

- El campo `sede` permanece en base de datos y API (`enum` `chilca` / `auquimarca`) para compatibilidad y eventual multi-sede.
- La UI **no** expone selectores de sede; los payloads envían `sede: chilca` (helpers en `frontend/src/lib/sedeOperativa.js`).
- Los listados/dashboard aplican Chilca por defecto si no se envía `sede` (`App\Support\SedeOperativa` en backend).
- No sembrar datos demo nuevos en Auquimarca. No modificar Flask ni el flujo de riesgo académico por este criterio.

Detalle para agentes y contribuidores: `.cursorrules` (misma sección) y `AGENTS.md`.

---

## 🏗️ Arquitectura del sistema

El sistema está dividido en:

- **Frontend:** React + Vite + Tailwind
- **Backend:** Laravel 13 (API REST; ver `backend/composer.json`)
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

## Configuración y arranque local

Sigue estos pasos para clonar el repositorio y levantar el proyecto en entorno local con Docker.

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

Comandos útiles de observación:

```bash
docker compose ps
docker compose logs -f app-backend
docker compose logs -f app-frontend
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

## 📴 Apagar y volver a encender el proyecto

Sección pensada para uso diario en local. En este proyecto, la base de datos MySQL persiste en `docker/mysql_data/` (datos locales) mientras no la borres manualmente.

### Apagar sin borrar datos

Para pausar el proyecto conservando contenedores y datos:

```bash
docker compose stop
```

### Volver a encender (sin reconstruir)

Si solo quieres volver a levantar lo que ya estaba creado:

```bash
docker compose start
```

### ¿Cuándo usar `stop/start`?

- Cuando solo necesitas **pausar** y **reanudar** el entorno local rápidamente.
- Útil si no cambiaste Dockerfiles/dependencias y no necesitas recrear contenedores.

### ¿Cuándo usar `down/up`?

- Cuando quieres **apagar** y luego **levantar** de nuevo el stack (por ejemplo, si hubo problemas de red/puertos o quieres recrear el entorno).

```bash
docker compose down
docker compose up -d
```

### Qué NO usar si no quieres perder la base de datos

- No ejecutes `php artisan migrate:fresh --seed` si no quieres borrar y reconstruir la base de datos.
- No borres la carpeta `docker/mysql_data/` (ahí se guardan los datos locales de MySQL).
- Evita `docker compose down -v` si no estás seguro de los volúmenes involucrados.

### Si cambiaste código y necesitas reconstruir

Si modificaste dependencias o el build de contenedores (por ejemplo `Dockerfile`, `package.json`, `composer.json`) y necesitas reconstruir:

```bash
docker compose up -d --build
```

### Verificar que todo volvió a funcionar

Comprobaciones rápidas:

```bash
docker compose ps
docker compose logs -f app-backend
docker compose logs -f app-frontend
```

Luego abre:

- Frontend: `http://localhost:5173`
- Backend: `http://localhost:8000`

---

## 🗄️ Preparar base de datos limpia (demo)

> **Advertencia:** el siguiente comando elimina y reconstruye la base de datos del entorno local.

```bash
docker compose exec app-backend php artisan migrate:fresh --seed
```

El comando `migrate:fresh --seed` ejecuta el demo curricular oficial (`DemoCurricularOperativoSeeder`), coherente con malla, notas bimestrales y asistencia diaria vigentes.

Seeders legacy (solo si necesitas datos antiguos de materias/notas semanales):

```bash
docker compose exec app-backend php artisan db:seed --class=DemoAcademicDataSeeder
```

---

## Asignación docente (malla curricular)

La asignación docente usa el **año escolar activo** y los **cursos de malla curricular** (`malla_curso_id`); no usa materias legacy ni filtro por bimestre.

Los **pesos C/L/T** (cuaderno, libro, tarea) se administran por alcance y se resuelven con prioridad: curso → área → nivel/grado → global.

---

## 👤 Gestión de usuarios (RF-15)

El módulo **Usuarios** en el frontend (menú lateral) permite al rol **administrador** crear y administrar cuentas del sistema (`gestionar_usuarios`): datos básicos, un rol por usuario, activar/desactivar y restablecer contraseña.

API: `GET/POST/PATCH /api/usuarios` y acciones `activar`, `desactivar`, `restablecer-contrasena` (requieren sesión Sanctum y permiso `gestionar_usuarios`).

**Pendiente de hardening (producción):** la ruta pública `POST /register` de Laravel Breeze sigue disponible; conviene restringirla o deshabilitarla antes de un despliegue real.

---

## 🔐 Usuarios demo (solo local)

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@siderae.test` | `password` |
| Docente | `docente@siderae.test` | `password` |
| Docente (secundario demo) | `docente2@siderae.test` | `password` |
| Docente adicional | `docente3@siderae.test` | `password` |
| Coordinador académico | `coordinador@siderae.test` | `password` |
| Psicólogo/Tutor | `psicologo@siderae.test` | `password` |
| Directivo | `directivo@siderae.test` | `password` |

---

## 📚 Datos demo académicos (solo ficticios)

El demo principal (`migrate:fresh --seed`) carga:

- **196 estudiantes** curriculares (`DemoEstudiantesCurricularesSeeder`): inicial, primaria y secundaria en sede `chilca`.
- **Mallas curriculares** provisionadas para primaria `2do`, secundaria `1ro` e inicial `3 años`.
- **Aula operativa demo**: primaria `2°` sección `A`, sede `chilca`, año `2026`, bimestre `1`.
- **Asignaciones docentes**, criterios (temas semanales), notas semanales/bimestrales y asistencia diaria curricular.
- **Usuarios demo** por rol (ver tabla anterior).

`DemoAcademicDataSeeder` (legacy, no ejecutado por defecto) poblaba materias, notas con `materia_id` y asistencia semanal antigua.

Todos los nombres, correos y códigos usados para demo son ficticios.

---

## ⚠️ Procesamiento masivo de riesgo post-importación

Existe un comando operativo **excepcional** para procesar riesgos en lote sobre datos ya cargados:

```bash
docker compose exec app-backend php artisan demo:procesar-riesgos --sede=chilca --anio=2026 --bimestre=1 --confirmar-post-import
```

También puedes forzar reprocesamiento:

```bash
docker compose exec app-backend php artisan demo:procesar-riesgos --sede=chilca --anio=2026 --bimestre=1 --confirmar-post-import --force
```

Uso correcto del comando:

- Solo para post-importación, post-seed o carga masiva inicial.
- No es flujo normal diario de la aplicación.
- No se ejecuta automáticamente.
- Puede generar carga alta (invoca procesamiento ML por estudiante).
- Requiere confirmación explícita con `--confirmar-post-import`.

---

## 🔎 Validación rápida de conteos demo (opcional)

```bash
docker compose exec app-backend php artisan tinker --execute="echo 'Estudiantes: '.App\Models\Estudiante::count().PHP_EOL; echo 'Materias: '.App\Models\Materia::count().PHP_EOL; echo 'Notas: '.App\Models\Nota::count().PHP_EOL; echo 'Asistencias: '.App\Models\Asistencia::count().PHP_EOL; echo 'VSE: '.App\Models\VariableSocioeconomica::count().PHP_EOL; echo 'Riesgos: '.App\Models\IndiceRiesgo::count().PHP_EOL; echo 'Alertas: '.App\Models\Alerta::count().PHP_EOL;"
```

Conteos esperados aproximados tras seed demo:

- Estudiantes: `220`
- Materias: `44`
- Notas: `880`
- Asistencias: `440`
- Variables socioeconómicas: `220`
- Riesgos: `0` antes del comando post-importación
- Riesgos: `> 0` después del comando, si el ML responde correctamente

---

## 📊 Funcionalidades implementadas

### Sprint 1
- Infraestructura Docker y servicios base.

### Sprint 2
- Autenticación con Laravel Sanctum.
- Roles y permisos con Spatie.
- Endpoint `/api/me` para roles/permisos efectivos.

### Sprint 3A
- CRUD de estudiantes con validaciones.

### Sprint 3B
- Registro individual de notas, asistencias y variables socioeconómicas.
- Integración de datos académicos en perfil de estudiante.

### Sprint 4
- Integración Laravel → Flask (`/predict`) para riesgo.
- Persistencia de índice de riesgo.
- Procesamiento manual por estudiante (`POST /api/estudiantes/{id}/procesar-riesgo`).

### Sprint 5
- Alertas e intervenciones.
- Cierre de alertas.

### Sprint 6A / 6B (estado operativo)
- Dashboard y filtros base.
- Export PDF del dashboard.
- Implementación parcial respecto al alcance completo del DRS.

### Sprint 7.6A
- Catálogo de materias por sede/nivel/grado/año.
- Activación/desactivación y uso en notas.

### Sprint 7.6B
- Registro masivo de notas por lote (`POST /api/notas/lote`).
- Registro masivo de asistencia por lote (`POST /api/asistencias/lote`).

### Sprint 8
- Matriz de control de accesos por rol/permiso.
- Refuerzo de autorización backend (401/403) y coherencia UI por permiso.
- Usuarios demo por rol para entorno local.

### Ajustes recientes de UX y operación
- Filtros de estudiantes con aplicación explícita.
- Búsqueda por código/nombre/apellido en estudiantes.
- Grados dependientes del nivel (primaria/secundaria) en módulos académicos.
- Año escolar automático por defecto en formularios/filtros relevantes.
- Formulario de materias prellenado con filtros aplicados.
- Comando excepcional post-importación para procesar riesgos demo.

## 🧪 Pruebas automatizadas

Comandos generales:

```bash
docker compose exec app-backend php artisan test
docker compose exec app-frontend npm run build
```

Comandos útiles por módulo:

```bash
docker compose exec app-backend php artisan test --filter=DatosAcademicos
docker compose exec app-backend php artisan test --filter=Riesgo
docker compose exec app-backend php artisan test --filter=Estudiante
docker compose exec app-backend php artisan test --filter=DemoProcesarRiesgosCommand
```

---

## 🔐 Roles y permisos (resumen operativo)

Fuente real de autorización:

- Backend (`backend/routes/api.php`) con `auth:sanctum` + middleware `permission:*`.
- Seeder de permisos/roles (`backend/database/seeders/PermissionsSeeder.php`).

| Rol | Acceso principal |
|---|---|
| `administrador` | Acceso completo a dashboard, estudiantes, materias, notas, asistencia, riesgo, alertas e intervenciones. |
| `docente` | Dashboard, gestión de estudiantes, registro de datos académicos, visualización de alertas e intervenciones. |
| `coordinador_academico` | Dashboard, gestión de estudiantes, registro de datos académicos, procesamiento de riesgo y visualización de alertas. |
| `psicologo_tutor` | Visualización de alertas y registro de intervenciones/cierre. |
| `directivo` | Dashboard, alertas e intervención/cierre según permisos vigentes de Sprint 8. |

Notas:

- El backend es la fuente real de autorización.
- El frontend adapta visibilidad de módulos/acciones, pero no reemplaza la validación del servidor.
- La matriz completa rol–permiso–pantalla–endpoint se mantiene en `docs/arquitectura/matriz-control-accesos-sprint8.md`.

---

## 🧠 Estado del proyecto (prototipo académico)

- Sistema **funcional** en flujos principales (login, estudiantes, datos académicos, riesgo, alertas, dashboard y export PDF básicos).
- Base de datos estructurada con migraciones del repo.
- **ML:** integración Laravel → Flask **confirmada**; modelo en Flask es **prototipo determinístico**, no equivalente a pipelines RF/SVM/XGBoost del DRS hasta que exista evidencia en código.
- **Dashboard y export PDF:** **implementados parcialmente** frente al DRS (ver `docs/arquitectura/resumen-arquitectura.md`).
- **RF-18** (reentrenamiento) y **RF-19** (semáforo de completitud): **pendientes de desarrollo**.
- **Auditoría:** `spatie/laravel-activitylog` con tabla `activity_log`; desde Sprint **7.5A** se registran acciones críticas en controladores API (crear/editar estudiante, notas, asistencia, variables, riesgo, alerta automática, intervención, cierre, export PDF dashboard). La cobertura frente a todo el RF-17 del DRS sigue siendo **parcial**.

## 🚧 Próximos desarrollos (orientación)

### Transición curricular: evaluación por criterios y bimestre

**Flujo funcional acordado:**

1. **Componentes para criterios por nivel/grado** *(fase posterior)* — Cuaderno / Libro / Tarea (C/L/T) con predeterminados: Inicial → Cuaderno 100 %; Primaria y Secundaria → C/L/T en partes iguales (33,33 / 33,33 / 33,34). Regla: para ese nivel/grado, todos los criterios se evalúan con esos componentes.
2. **Nota de cada criterio** — CE semanal calculado con esos pesos.
3. **Promedio de criterios** — agregación hacia la evaluación bimestral.
4. **Configuración bimestral** — fórmula final del bimestre: Promedio de criterios, Oral, Promedio ETA, Examen bimestral (+ personalizados). Las ETAs se configuran en bloque aparte.
5. **Nota bimestral final** — nivel de logro.

**Estado actual en código:**

- **UI:** *Pesos evaluación* (C/L/T) está **oculto del menú**; backend legacy (`/api/curricular/pesos*`, `PesoEvaluacionResolver`) sigue activo para el CE semanal.
- **Configuración bimestral** mantiene los **cuatro componentes bimestrales** clásicos; **no** incluye Cuaderno/Libro/Tarea.
- **Pendiente:** módulo «Componentes para criterios por nivel/grado» y migración del motor de notas semanales desde `PesoEvaluacionResolver`.

- Pruebas integrales y de regresión ampliadas.
- Fortalecimiento documental final de arquitectura, seguridad y operación.
- Mejoras futuras fuera del alcance actual (por ejemplo importaciones avanzadas de archivos) según backlog formal.
- Evolución del componente ML (incluido reentrenamiento) cuando exista alcance técnico aprobado.
- Mejoras adicionales de UX transversal/búsqueda global según priorización.

*(El roadmap operativo se mantiene en `sprints/`; este README describe el estado vigente del prototipo en código.)*

---

## ⚠️ Advertencias importantes

- No subir `.env` reales al repositorio.
- Los usuarios demo son solo para entorno local/demo.
- Los datos demo son ficticios.
- Los códigos tipo DNI usados en demo son ficticios.
- El comando de riesgo masivo no debe usarse como flujo normal diario.
- Este README no afirma certificación ISO.
- El DRS (`DRS_SIDERAE_Blenkir_v1.pdf`) sigue siendo la fuente formal de alcance; este README resume el estado operativo actual del prototipo.

---

## 👥 Equipo
- Diego Carhuamaca Vasquez
- Ernesto Chuchon Sotelo

---

## 📌 Notas
- No eliminar estudiantes (integridad de datos)
- Uso de permisos para control de acceso
- Datos académicos estructurados para análisis predictivo
- **Variables socioeconómicas:** las variables socioeconómicas actuales quedan pausadas y ocultas hasta el rediseño del módulo de riesgo académico. El backend se conserva; no se reconecta la UI ni se agregan seeders en este ciclo.

---
