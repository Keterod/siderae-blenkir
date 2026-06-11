# SIDERAE-Blenkir

Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil — **prototipo académico V1**.

Documentación ampliada: [`docs/README.md`](docs/README.md) · [`docs/INDICE_DOCUMENTACION.md`](docs/INDICE_DOCUMENTACION.md) · [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](docs/drs/DRS_SIDERAE_Blenkir_v2.md) · [`docs/limitaciones.md`](docs/limitaciones.md) · [`docs/instalacion-docker.md`](docs/instalacion-docker.md) · [`docs/manual-tecnico.md`](docs/manual-tecnico.md) · [`docs/manual-usuario.md`](docs/manual-usuario.md) · [`docs/aula-notas-excel.md`](docs/aula-notas-excel.md) · [`docs/matriz-rf-sprint-test.md`](docs/matriz-rf-sprint-test.md) · [`docs/pruebas/informe-pruebas.md`](docs/pruebas/informe-pruebas.md) · [`docs/calidad/alineacion-iso.md`](docs/calidad/alineacion-iso.md) · [`docs/api.md`](docs/api.md)

---

## 1. Descripción

SIDERAE-Blenkir es un sistema web para gestión académica institucional y detección temprana de riesgo académico/deserción. **V1** opera como prototipo local con Docker: frontend React, API Laravel, MySQL y un microservicio Flask para cálculo de riesgo.

El alcance formal del proyecto se documenta en [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](docs/drs/DRS_SIDERAE_Blenkir_v2.md) (versión documental **2.1**, RF-01 a RF-35). El PDF histórico (`DRS_SIDERAE_Blenkir_v1.pdf`) **no está en este repositorio**.

**Decisión operativa V1:** sede única **Chilca** en UI y consultas por defecto ([`AGENTS.md`](AGENTS.md)). El campo `sede` se conserva en BD/API para compatibilidad futura.

---

## 2. Alcance actual del prototipo

Resumen basado en [`docs/limitaciones.md`](docs/limitaciones.md). Detalle completo en ese documento.

### Confirmado en código

| Área | Evidencia |
|------|-----------|
| Autenticación Sanctum + `/api/me` | `backend/routes/auth.php`, `frontend/src/context/AuthContext.jsx` |
| Roles/permisos (5 roles, **23 permisos** en `PermissionsSeeder`) | `backend/database/seeders/PermissionsSeeder.php` — ver también 8 permisos **sugeridos/planificados** en [`docs/seguridad-roles-permisos.md`](docs/seguridad-roles-permisos.md) §16 |
| Estudiantes, usuarios (RF-15) | `backend/routes/api.php`, `frontend/src/App.jsx` |
| Módulo curricular **RF-21–RF-35** | `/api/curricular/*`, paneles en `App.jsx` |
| Import Excel **plantilla curricular** | `POST /api/curricular/notas-semanales/importar-excel` |
| Riesgo académico (Laravel → Flask) | `MlRiskService.php`, `ml-service/main.py` |
| Alertas, intervenciones, cierre | `AlertaController`, `AlertasPanel` |
| Dashboard + export PDF dashboard | `GET /api/dashboard`, `/export` |
| Docker local (4 servicios) | `docker-compose.yml` |

### Implementado parcialmente

| Área | Nota |
|------|------|
| RF-01 carga de datos | Manual + Excel curricular RF-32; **SIAGIE fuera del alcance** |
| RF-14 / RF-16 dashboard y reportes | Subset operativo; zona reportes riesgo **planificada** |
| RF-17 auditoría | `activity_log` parcial — apoya alineación ISO progresiva |
| RF-20 historial de riesgo | Persistencia sí; historial evolutivo UI **planificado** |

### Retirado del alcance vigente (v2.1)

SIAGIE, Fast Test (RF-03), variables socioeconómicas en flujo de riesgo (RF-05), comunicación familiar (RF-12).

### Planificado (por implementar en código)

Reportes conductuales (RF-04), escalamiento directivo crítico (RF-10), perfil integral psicólogo (RF-11), reentrenamiento ML (RF-18), semáforo completitud (RF-19), reportes de riesgo (RF-16), historial evolutivo (RF-20).

### Otros pendientes

Cypress/E2E, modelos ML entrenados, despliegue productivo, certificación ISO.

---

## 3. Arquitectura resumida

```
Usuario → React (5173) → Laravel API (8000) → MySQL (3307→3306)
                              ↓
                         Flask ML (5000)
```

| Servicio | URL / puerto (host) |
|----------|---------------------|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000 |
| ML Service | http://localhost:5000 |
| MySQL | `localhost:3307` (contenedor `3306`, BD `siderae_db`) |

Diagrama y detalle: [`ARCHITECTURE.md`](ARCHITECTURE.md) · [`docs/arquitectura/resumen-arquitectura.md`](docs/arquitectura/resumen-arquitectura.md)

**ML:** integración confirmada; el servicio Flask usa un **prototipo determinístico**, no Random Forest/SVM/XGBoost del DRS.

---

## 4. Requisitos

- **Docker Desktop** (Compose v2)
- **Git**
- Recursos recomendados: **4 GB RAM** mínimo para el stack; **8 GB** recomendado si se ejecutan tests Excel
- **Nota pruebas:** la suite PHPUnit completa puede agotar `memory_limit` PHP **128M** en tests de descarga Excel; usar `memory_limit=512M` (ver §6)

---

## 5. Instalación local

Pasos detallados: [`docs/instalacion-docker.md`](docs/instalacion-docker.md)

### 5.1 Clonar y configurar `.env`

```bash
git clone https://github.com/Keterod/siderae-blenkir.git
cd siderae-blenkir

cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
cp ml-service/.env.example ml-service/.env
```

Use los valores de las plantillas `.env.example` (sin secretos reales). En Docker, `backend/.env` debe apuntar a `DB_HOST=db-mysql` y `ML_SERVICE_URL=http://ml-engine:5000`.

### 5.2 Levantar servicios

```bash
docker compose up -d --build
```

**Importante:** el arranque del backend ejecuta `migrate --force`, **no** `--seed`. Tras el primer `up`, la base puede estar **vacía de datos demo** hasta que ejecute el seed manualmente (§5.4).

Espere a que `app-backend` termine `composer install` y migraciones antes de abrir la UI.

### 5.3 Verificar servicios

- Frontend: http://localhost:5173  
- Backend health: http://localhost:8000/api/health  
- ML health: http://localhost:5000/

### 5.4 Datos demo (opcional, destructivo si usa `fresh`)

Para cargar roles, usuarios demo, catálogo curricular y estudiantes de prueba:

```bash
docker compose exec app-backend php artisan migrate:fresh --seed
```

> **Advertencia:** `migrate:fresh --seed` **borra y reconstruye** la base local. No ejecutarlo si desea conservar datos en `docker/mysql_data/`.

Seed principal: [`backend/database/seeders/DatabaseSeeder.php`](backend/database/seeders/DatabaseSeeder.php) → `DemoUsersSeeder`, `CurricularModuleSeeder`, `DemoEstudiantesCurricularesSeeder`, `DemoCurricularOperativoSeeder`.

Legacy opcional (no incluido en seed por defecto):

```bash
docker compose exec app-backend php artisan db:seed --class=DemoAcademicDataSeeder
```

### 5.5 Conteos de datos demo

Los conteos **dependen del historial de su BD local** y **no son constantes universales** del producto.

En la auditoría Fase 1 (2026-06-09), sobre una BD local **sin** `migrate:fresh` en esa sesión, se midió:

| Métrica | Valor observado |
|---------|-----------------|
| Estudiantes total | 449 |
| Sede `chilca` | 253 |
| Sede `auquimarca` | 196 |

> **Nota sobre conteos Fase 1 (BD local auditada):** Estos conteos pertenecen al entorno local auditado. La presencia de registros con sede Auquimarca **no** implica operación multi-sede en V1; la decisión vigente de V1 es sede operativa **Chilca** ([`AGENTS.md`](AGENTS.md)).

Versiones anteriores de este README citaban **196** y **220** estudiantes; esas cifras **no coincidían** con la BD auditada. Tras un `migrate:fresh --seed` limpio, los conteos pueden diferir; **pendiente de confirmar** en entorno de referencia dedicado.

Consulta no destructiva (ejemplo):

```bash
docker compose exec app-backend php artisan tinker --execute="echo App\Models\Estudiante::count();"
```

---

## 6. Comandos útiles

```bash
docker compose up -d --build
docker compose ps
docker compose logs --tail=120 app-backend
docker compose logs --tail=120 app-frontend
docker compose logs --tail=120 ml-engine

docker compose stop
docker compose start

docker compose exec app-backend php artisan test
docker compose exec app-backend php -d memory_limit=512M artisan test --filter=ExcelAulaTest
docker compose exec app-frontend npm run build
```

**Pruebas (Fase 1):** `php artisan test` con límite 128M quedó **incompleta** (OOM en `ExcelAulaTest`); la misma clase pasó con **512M**. Ver [`docs/pruebas/hallazgos-fase1-documentacion.md`](docs/pruebas/hallazgos-fase1-documentacion.md).

Filtros útiles:

```bash
docker compose exec app-backend php artisan test --filter=Curricular
docker compose exec app-backend php artisan test --filter=Riesgo
docker compose exec app-backend php artisan test --filter=GestionUsuarios
```

---

## 7. Módulos UI vigentes (menú lateral)

Definidos en [`frontend/src/App.jsx`](frontend/src/App.jsx):

| Grupo | Módulos |
|-------|---------|
| Inicio | Dashboard |
| Gestión académica | Estudiantes, Notas semanales, Excel por aula, Asistencia, Alertas |
| Gestión docente y aulas | Secciones/Aulas, Asignación docente |
| Configuración curricular | Malla, Criterios, Componentes calificación, Configuración bimestral |
| Configuración avanzada | Competencias/capacidades, Periodos académicos |
| Administración | Usuarios |

**Ocultos en menú (código legacy):** Pesos evaluación (`visible: false`); paneles `MateriasPanel`, `NotasMasivasPanel`, `AsistenciaMasivaPanel` existen pero **no** están en el sidebar.

Matriz de permisos vigente: [`docs/seguridad-roles-permisos.md`](docs/seguridad-roles-permisos.md). Referencia histórica Sprint 8: [`docs/arquitectura/matriz-control-accesos-sprint8.md`](docs/arquitectura/matriz-control-accesos-sprint8.md).

---

## 8. Usuarios demo (solo local)

Contraseña común tras seed: **`password`** (definida en [`DemoUsersSeeder.php`](backend/database/seeders/DemoUsersSeeder.php)).

| Rol | Correo |
|-----|--------|
| Administrador | `admin@siderae.test` |
| Docente | `docente@siderae.test` |
| Docente adicional | `docente2@siderae.test`, `docente3@siderae.test` |
| Coordinador académico | `coordinador@siderae.test` |
| Psicólogo/Tutor | `psicologo@siderae.test` |
| Directivo | `directivo@siderae.test` |

También se crea `test@example.com` (administrador) vía `DatabaseSeeder`.

---

## 9. Procesamiento masivo de riesgo (excepcional)

Comando operativo post-importación / post-seed (no flujo diario):

```bash
docker compose exec app-backend php artisan demo:procesar-riesgos --sede=chilca --anio=2026 --bimestre=1 --confirmar-post-import
```

Requiere `--confirmar-post-import`. Puede generar carga alta (invoca ML por estudiante).

---

## 10. Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Frontend | React 18, Vite, Tailwind |
| Backend | PHP 8.3, Laravel ^13, Sanctum, Spatie Permission/Activitylog |
| BD | MySQL 8 |
| ML | Python 3, Flask (determinístico) |
| Infra | Docker Compose |

---

## 11. Estructura del repositorio

```
Proyecto/
├── backend/          → API Laravel
├── frontend/         → SPA React
├── ml-service/       → Flask /predict
├── docker/           → Datos MySQL persistentes
├── docs/             → Documentación técnica y académica
├── sprints/          → Planificación histórica por fase
├── docker-compose.yml
├── ARCHITECTURE.md
└── README.md
```

---

## 12. Advertencias

- No subir `.env` reales al repositorio.
- Usuarios y datos demo son **ficticios** y solo para local.
- `POST /register` (Breeze) sigue **público** — restringir antes de producción.
- **No** se afirma certificación ISO; normas ISO se usan solo como referencia orientativa académica ([`docs/limitaciones.md`](docs/limitaciones.md) §9).
- Cypress/E2E: **no confirmado** (sin carpeta `cypress/`).
- No eliminar estudiantes (integridad referencial).

---

## 13. Equipo

- Diego Carhuamaca Vasquez  
- Ernesto Chuchon Sotelo

---

## 14. Documentación relacionada (Fase 2)

| Documento | Contenido |
|-----------|-----------|
| [`docs/limitaciones.md`](docs/limitaciones.md) | Alcance real vs DRS |
| [`docs/instalacion-docker.md`](docs/instalacion-docker.md) | Arranque reproducible |
| [`docs/manual-tecnico.md`](docs/manual-tecnico.md) | Stack, pruebas, servicios |
| [`docs/manual-usuario.md`](docs/manual-usuario.md) | Guía de uso por rol (V1) |
| [`docs/matriz-rf-sprint-test.md`](docs/matriz-rf-sprint-test.md) | Trazabilidad RF–Sprint–Test |
| [`docs/aula-notas-excel.md`](docs/aula-notas-excel.md) | Aula, notas semanales y Excel curricular |
| [`docs/pruebas/informe-pruebas.md`](docs/pruebas/informe-pruebas.md) | Informe de pruebas V1 |
| [`docs/ml-service.md`](docs/ml-service.md) | Contrato `/predict` |
| [`docs/api.md`](docs/api.md) | Catálogo de endpoints |
| [`docs/pruebas/hallazgos-fase1-documentacion.md`](docs/pruebas/hallazgos-fase1-documentacion.md) | Auditoría técnica Fase 1 |
| [`docs/calidad/alineacion-iso.md`](docs/calidad/alineacion-iso.md) | Alineación ISO (referencia académica; sin certificación) |
| [`docs/calidad/no-conformidades-y-mejora.md`](docs/calidad/no-conformidades-y-mejora.md) | Registro de brechas NC-01… |
| [`docs/README.md`](docs/README.md) | Entrada principal a la carpeta `docs/` |
| [`docs/INDICE_DOCUMENTACION.md`](docs/INDICE_DOCUMENTACION.md) | Índice maestro del paquete documental |
| [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](docs/drs/DRS_SIDERAE_Blenkir_v2.md) | DRS vigente — estado V1 real (Markdown) |

**Formato de entrega:** paquete documental en **Markdown** listo para revisión humana. Conversión a PDF/Word = etapa posterior opcional.
