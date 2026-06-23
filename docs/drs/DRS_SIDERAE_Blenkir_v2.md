# Documento de Requerimientos de Software
# SIDERAE-Blenkir V2

**Versión:** 2.1 (actualización documental — reestructuración RF)  
**Fecha:** 2026-06-09  
**Tipo de documento:** Prototipo académico V1 — estado real verificado en repositorio

**Importante:** este DRS v2 **actualiza** el alcance descrito en `DRS_SIDERAE_Blenkir_v1.pdf` (v1.0, 02/04/2026) según la implementación real, la documentación técnica consolidada (Fases 1–7) y las matrices de trazabilidad. **No** constituye certificación ISO, auditoría externa ni declaración de producto listo para producción.

**Fuente formal anterior:** `DRS_SIDERAE_Blenkir_v1.pdf` — **externo al repositorio**; no modificado por esta fase.

> **Formato del documento:** fuente autoritativa en **Markdown**. La conversión a PDF o Word queda **pendiente** de revisión humana y maquetación formal en una etapa posterior; el contenido técnico vigente es este archivo `.md`.

---

## Historial de versiones

| Fecha | Versión | Autor / equipo | Descripción | Fuente base |
|-------|---------|----------------|-------------|-------------|
| 02/04/2026 | 1.0 | Equipo SIDERAE-Blenkir (Colegio Blenkir) | DRS formal original — alcance institucional completo | `DRS_SIDERAE_Blenkir_v1.pdf` (externo al repo) |
| 2026-06-09 | 2.0 | Equipo documentación SIDERAE-Blenkir (Diego Carhuamaca Vasquez, Ernesto Chuchon Sotelo) | Actualización documental V1 real: RF/RNF con estado honesto, brechas, trazabilidad a código y pruebas | Fases 1–6 + ISO + matriz RF–Sprint–Test + informe de pruebas + limitaciones |
| 2026-06-09 | 2.1 | Equipo documentación SIDERAE-Blenkir | Reestructuración de alcance: retiro Fast Test / SIAGIE / VSE / comunicación familiar del alcance vigente; ajuste RF-10/RF-11/RF-13/RF-14/RF-16/RF-18/RF-19/RF-20; incorporación RF-21–RF-35 del módulo curricular como RF oficiales | Fase 9 documental |
| 2026-06-23 | 2.2 | Equipo documentación SIDERAE-Blenkir | RF-04 cerrado V1 mínimo; RF-19 backend implementado (`CompletitudDatosService`, endpoint API, tests 11 passed); UI RF-19 pendiente | Fase 3C AI-DLC RF-19 |

---

## Información del proyecto

| Campo | Valor |
|-------|-------|
| **Organización** | Colegio Blenkir |
| **Proyecto** | SIDERAE-Blenkir (Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil) |
| **Tipo** | Prototipo académico / sistema web de apoyo a gestión curricular y alertas tempranas |
| **Sede operativa V1** | **Chilca** — única sede activa en UI, consultas por defecto y datos demo nuevos |
| **Auquimarca** | Valor histórico/local en BD auditada; **no** operación multi-sede V1. Campo `sede` conservado para compatibilidad y expansión futura |
| **Equipo** | Diego Carhuamaca Vasquez; Ernesto Chuchon Sotelo |
| **Repositorio** | Prototipo local Docker — ver [`README.md`](../../README.md) |

---

## 1. Propósito

El presente DRS v2 tiene como propósito:

1. **Actualizar** el documento de requerimientos formal (v1.0) al **estado real del prototipo V1** verificado en código, rutas, UI y pruebas.
2. **Separar** explícitamente: alcance formal institucional, implementación V1, brechas pendientes y mejoras futuras.
3. **Sustentar** la defensa académica ante jurado con evidencia trazable a documentos del repositorio.
4. **Integrar** los insumos de las fases documentales completadas: manual técnico, manual de usuario, matriz RF–Sprint–Test, informe de pruebas, seguridad roles/permisos, aula-notas-excel, limitaciones y alineación ISO progresiva.

Este documento **no reemplaza** el PDF v1.0 como archivo histórico; lo **complementa y corrige** respecto al estado V1.

---

## 2. Alcance del producto V1

SIDERAE-Blenkir V1 es un sistema web desacoplado (React + Laravel + MySQL + Flask) para gestión curricular, registro de notas y asistencia, cálculo de riesgo académico (prototipo), alertas e intervenciones, con control de acceso por roles. Opera en entorno **local Docker** con sede operativa **Chilca**.

### 2.1 Alcance confirmado

| Módulo | Evidencia principal |
|--------|---------------------|
| Autenticación Sanctum + sesión `/api/me` | `backend/routes/auth.php`, `AuthContext.jsx` |
| RBAC — 5 roles, **23 permisos implementados** | `PermissionsSeeder.php`, `api.php` |
| Gestión estudiantes | `EstudianteController`, `EstudiantesPanel` |
| Gestión usuarios (RF-15) | `/api/usuarios/*`, `UsuariosPanel` |
| Módulo curricular **RF-21–RF-35** (confirmado en código) | `/api/curricular/*`, paneles en `App.jsx`, tests `Feature/Curricular/` |
| Asistencia curricular (RF-02) | `/api/curricular/asistencias-diarias/*`, `AsistenciaDiariaTest` |
| Alertas, intervenciones, cierre (RF-08, RF-09) | `AlertaController`, `AlertasPanel`, `AlertaIntervencionTest` |
| Clasificación de riesgo por umbrales (RF-07) | `RiesgoAcademicoService`, `RiesgoTest` |
| Infraestructura Docker 4 servicios | `docker-compose.yml` |

### 2.2 Alcance implementado parcialmente

| Módulo / RF | Qué opera en V1 | Qué falta frente al DRS v1 |
|-------------|-----------------|---------------------------|
| **RF-01** Carga/importación datos | Notas semanales, plantilla Excel curricular (import/export), Excel aula (descarga), legacy API | **SIAGIE fuera del alcance actual** (decisión de alcance) |
| **RF-05** Variables socioeconómicas | API legacy bajo estudiante (código histórico) | **Retirado del flujo funcional de riesgo** — no insumo obligatorio |
| **RF-06** Procesamiento ML | Laravel → Flask determinístico; comando batch | ML real / ensemble — ver RF-18 planificado |
| **RF-11** Atención psicológica | Alertas + permisos psicólogo | Perfil integral lectura — **planificado** (RF-11 reformulado) |
| **RF-13** Cierre alerta | Cierre vía intervención | Escalamiento RF-10; **sin** cierre por comunicación familiar |
| **RF-14** Dashboard | KPIs básicos, filtros parciales | Dashboard **académico-institucional** ampliado — planificado |
| **RF-16** Reportes | PDF dashboard; Excel aula `.xlsx` | Zona **reportes de riesgo** — planificado |
| **RF-17** Auditoría | `activity_log` parcial | UI consulta logs |
| **RF-20** Historial riesgo | Persistencia `indices_riesgo` | Timeline evolutivo por periodo — planificado |

### 2.3 Alcance pendiente o no confirmado

| RF | Estado V1 |
|----|-----------|
| RF-03 Importación Fast Test | **Retirado del alcance vigente** — la institución no utiliza Fast Test |
| RF-04 Reportes conductuales | **Implementado V1 mínimo** — API + UI perfil estudiante; 8 passed Fase 2E |
| RF-10 Escalamiento directivo crítico | **Planificado** — solo casos críticos/extremos |
| RF-12 Comunicación familiar | **Eliminado del alcance** — gestión fuera del sistema |
| RF-18 Reentrenamiento ML | **Planificado** — requiere dataset histórico; no implementado |
| RF-19 Semáforo completitud | **Backend implementado V1** — API + servicio + tests; UI perfil estudiante pendiente |
| RF-20 Historial evolutivo (UI/timeline) | **Planificado** — persistencia `indices_riesgo` confirmada |
| Cypress / E2E | **No confirmado** — no existe en repo |
| Despliegue productivo | **Pendiente** — solo Docker local |
| Certificación ISO | **No aplica V1** — referencia académica únicamente |

### 2.4 Fuera del alcance V1

- Operación **multi-sede activa** (selector sedes, mapa consolidado todas las sedes).
- **Importación SIAGIE** institucional — **fuera del alcance actual** (decisión de alcance; plantillas propias en su lugar).
- **Fast Test** — retirado del alcance vigente.
- **Variables socioeconómicas** como insumo obligatorio de riesgo — retiradas del flujo funcional.
- **Comunicación formal con familia** dentro del sistema — eliminada del alcance.
- **Random Forest / SVM / XGBoost** entrenados y reentrenamiento automático (RF-18 planificado, no implementado).
- **Auditoría externa**, pentest, certificación ISO/SGSI.
- **Cypress** obligatorio como gate de cierre.
- Eliminación de estudiantes (política README: no eliminar).

---

## 3. Definiciones, acrónimos y abreviaturas

| Término | Definición |
|---------|------------|
| **SIDERAE** | Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil |
| **RF** | Requerimiento funcional |
| **RNF** | Requerimiento no funcional |
| **DRS** | Documento de Requerimientos de Software |
| **ML** | Machine Learning — en V1: servicio Flask **determinístico/prototipo** |
| **API** | Interfaz REST Laravel (`/api/*`) |
| **RBAC** | Control de acceso basado en roles (Spatie Permission) |
| **VSE** | Variables socioeconómicas — **retiradas del flujo funcional de riesgo**; API legacy puede existir |
| **SIAGIE** | Sistema de información académico institucional peruano — **fuera del alcance actual** |
| **ISO** | Normas de referencia académica (25010, 27000, 9001) — **sin certificación** |
| **V1** | Versión prototipo académica actual del repositorio |
| **Chilca** | Sede operativa única V1 |
| **Auquimarca histórico/local** | Registros en BD local auditada; no operación V1 |
| **Malla curricular** | Estructura de cursos, competencias y capacidades por nivel |
| **Aula / sección** | Agrupación de estudiantes (`secciones_aulas`) |
| **Plantilla Excel curricular** | Registro auxiliar por curso — descarga + **importación** vía `importar-excel` |
| **Excel aula** | Archivo multi-hoja por aula — **solo descarga** (`GET /excel-aula`) |

---

## 4. Referencias documentales

| Documento | Ruta | Uso en DRS v2 |
|-----------|------|---------------|
| DRS v1 (PDF original) | `DRS_SIDERAE_Blenkir_v1.pdf` (externo) | Alcance formal histórico; no modificado |
| Resumen DRS para IA | [`docs/arquitectura/contexto-drs-requerimientos.md`](../arquitectura/contexto-drs-requerimientos.md) | Transcripción RF/RN/RNF v1 |
| README | [`README.md`](../../README.md) | Visión, stack, equipo, alcance resumido |
| Arquitectura | [`ARCHITECTURE.md`](../../ARCHITECTURE.md) | Diagrama y capas |
| Resumen arquitectura | [`docs/arquitectura/resumen-arquitectura.md`](../arquitectura/resumen-arquitectura.md) | Matriz rápida DRS ↔ código |
| Manual técnico | [`docs/manual-tecnico.md`](../manual-tecnico.md) | Stack, pruebas, servicios |
| Manual usuario | [`docs/manual-usuario.md`](../manual-usuario.md) | Flujos por rol |
| Matriz RF–Sprint–Test | [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) | Trazabilidad RF → código → test |
| Informe pruebas | [`docs/pruebas/informe-pruebas.md`](../pruebas/informe-pruebas.md) | Evidencias PHPUnit, OOM, Cypress |
| Aula, notas, Excel | [`docs/aula-notas-excel.md`](../aula-notas-excel.md) | RF-01/02/16 Excel vs SIAGIE |
| Seguridad roles permisos | [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) | RF-15, RN-05, 401/403 |
| Limitaciones | [`docs/limitaciones.md`](../limitaciones.md) | Alcance real vs formal |
| API | [`docs/api.md`](../api.md) | Catálogo endpoints |
| Módulo curricular | [`docs/analisis/modulo-curricular-academico.md`](../analisis/modulo-curricular-academico.md) | Análisis curricular V1 |
| Alineación ISO | [`docs/calidad/alineacion-iso.md`](../calidad/alineacion-iso.md) | Referencia académica ISO |
| Matriz ISO 25010 | [`docs/calidad/matriz-iso-25010.md`](../calidad/matriz-iso-25010.md) | Calidad producto |
| Matriz seguridad 27000 | [`docs/calidad/matriz-seguridad-iso-27000.md`](../calidad/matriz-seguridad-iso-27000.md) | Activos y controles |
| Trazabilidad ISO 9001 | [`docs/calidad/trazabilidad-iso-9001.md`](../calidad/trazabilidad-iso-9001.md) | Documentación y mejora |
| No conformidades | [`docs/calidad/no-conformidades-y-mejora.md`](../calidad/no-conformidades-y-mejora.md) | Registro NC-01…NC-15 |
| Hallazgos Fase 1 | [`docs/pruebas/hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md) | Auditoría técnica |
| AGENTS.md | [`AGENTS.md`](../../AGENTS.md) | Decisión sede Chilca V1 |
| Índice documentación | [`INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md) | Paquete Fase 8 |
| Entrada `docs/` | [`README.md`](../README.md) | Cierre Markdown |

---

## 5. Descripción general del sistema

### 5.1 Visión general

SIDERAE-Blenkir integra gestión académica curricular (malla, notas semanales, asistencia, evaluación bimestral) con un flujo de **detección temprana de riesgo académico**: el backend Laravel consolida datos del estudiante, invoca un microservicio ML (prototipo determinístico en V1), persiste índices de riesgo, genera alertas y permite intervenciones docentes con trazabilidad parcial.

### 5.2 Usuarios principales

Administrador, docente, coordinador académico, psicólogo/tutor y directivo — ver §7.

### 5.3 Arquitectura general

```
Usuario → React (5173) → Laravel API (8000) → MySQL (3307→3306)
                              ↓
                         Flask ML (5000)
```

- El **frontend no llama directamente** a Flask.
- **Flask no accede** directamente a MySQL.
- Laravel orquesta el riesgo vía `MlRiskService` → `POST /predict`.

### 5.4 Módulos funcionales V1 (menú visible)

Dashboard, estudiantes, usuarios (admin), alertas, módulos curriculares (malla, criterios, componentes, configuración bimestral, secciones/aulas, asignación docente, periodos, notas semanales, Excel aula, asistencia). Legacy materias/lotes **sin menú**.

### 5.5 Sede operativa V1

**Chilca** en UI, filtros y seeders demo nuevos. Registros Auquimarca en BD local = histórico; no selector multi-sede.

---

## 6. Arquitectura del sistema

| Capa | Tecnología | Evidencia |
|------|------------|-----------|
| Frontend | React 18 + Vite + Tailwind | `frontend/package.json`, `App.jsx` |
| Backend | PHP 8.3, **Laravel ^13** | `backend/composer.json` |
| Autenticación | Sanctum + Breeze | `routes/auth.php` |
| Autorización | Spatie Permission | `PermissionsSeeder.php` |
| Base de datos | MySQL 8 | `docker-compose.yml` |
| ML Service | Flask (determinístico) | `ml-service/main.py` |
| PDF | DomPDF | export dashboard |
| Excel | Maatwebsite Excel + servicio curricular | `PlantillaRegistroAuxiliarExcelService.php` |
| Auditoría | Spatie Activitylog (parcial) | `ActivityLogTest.php` |
| Infraestructura | Docker Compose | 4 servicios |

### 6.1 Servicios y puertos

| Servicio Compose | Contenedor | Puerto host | Función |
|------------------|------------|-------------|---------|
| `app-frontend` | siderae_frontend | **5173** | SPA React |
| `app-backend` | siderae_backend | **8000** | API Laravel |
| `ml-engine` | siderae_ml | **5000** | ML Flask `/predict` |
| `db-mysql` | siderae_mysql | **3307** → 3306 | MySQL `siderae_db` |

### 6.2 Flujos de comunicación

| Origen | Destino | Protocolo | Notas |
|--------|---------|-----------|-------|
| Frontend | Laravel | HTTP JSON REST + cookies Sanctum | `frontend/src/lib/api.js` |
| Laravel | MySQL | SQL vía Eloquent | — |
| Laravel | Flask | HTTP interno Docker | `ML_SERVICE_URL` |
| Frontend | Flask | — | **No permitido** en V1 |
| Flask | MySQL | — | **No implementado** |

---

## 7. Clases de usuario y roles

Basado en [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md).

| Rol | Propósito | Permisos principales | Alcance V1 | Restricciones |
|-----|-----------|---------------------|------------|---------------|
| **administrador** | Configuración total del prototipo | Los 23 permisos | Gestión usuarios, malla, curricular, riesgo | No equivale a superusuario producción hardening |
| **docente** | Registro académico y alertas de su ámbito | Estudiantes, notas/asistencia curricular, alertas, intervenciones, lectura malla | Flujo curricular Chilca | Sin gestión usuarios ni malla escritura completa |
| **coordinador_academico** | Configuración curricular y riesgo | Malla, asignaciones, calendario, Excel aula, procesar riesgo | Operación Chilca | Sin gestión usuarios |
| **psicologo_tutor** | Seguimiento alertas e intervenciones; perfil integral lectura (planificado RF-11) | Alertas, intervenciones | Perfil 360° lectura académica — **planificado** |
| **directivo** | Visión consolidada; escalamiento casos críticos (planificado RF-10) | Dashboard, alertas, lectura malla/notas/asistencia | **No** actor inicial de todas las alertas; solo críticos/extremos |

**Principio RN-05:** el backend valida permisos **antes** de procesar; el frontend solo oculta menú.

---

## 8. Requerimientos funcionales actualizados

**Criterios de estado V1:** Confirmado · Implementado parcialmente · Pendiente · No confirmado · Fuera de V1

Estados derivados de [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) (verificación código 2026-06-09).

---

### RF-01 — Carga e importación de datos académicos

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** El prototipo V1 cubre la importación académica mediante **registro manual curricular**, **plantilla Excel curricular propia** (descarga + importación por curso/asignación), **Excel por aula** (descarga multi-hoja) y API legacy de notas/asistencia por estudiante y lote **sin menú V1**. **SIAGIE no se implementará** en este alcance: no se cuenta con formato SIAGIE institucional real y el proyecto prioriza plantillas propias controladas.
- **Actor(es):** Docente, Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/notas-semanales/*`, `GET/POST plantilla-excel` / `importar-excel`, `GET /excel-aula`, `PlantillaRegistroAuxiliarExcelService.php`, paneles curriculares en `App.jsx`
- **Evidencia de prueba:** `DatosAcademicosTest`, `PlantillaRegistroAuxiliarExcelTest`, `CurricularApiTest`, `NotasSemanales*`, `ExcelAulaTest`
- **Brechas / pendientes:** SIAGIE **fuera del alcance actual** (no pendiente obligatorio); Excel aula **solo descarga** (no import); suite OOM @ 128M
- **Observaciones para versión futura:** SIAGIE permanece fuera de alcance por decisión institucional; no confundir plantilla curricular con SIAGIE ([`aula-notas-excel.md`](../aula-notas-excel.md) §3). Ver RF-21–RF-35 para módulo curricular oficial.

---

### RF-02 — Registro digital de asistencia semanal

- **Estado V1:** Confirmado
- **Descripción actualizada:** Asistencia curricular diaria operativa vía `/api/curricular/asistencias-diarias/*` y menú **Asistencia**. Flujo legacy por lote existe en API sin menú V1.
- **Actor(es):** Docente
- **Prioridad:** Alta
- **Evidencia de implementación:** Rutas curriculares asistencia, `AsistenciaPanel` (curricular)
- **Evidencia de prueba:** `AsistenciaDiariaTest` (detectado; no re-ejecutado Fase 5)
- **Brechas / pendientes:** Legacy lote sin menú; no re-ejecución Fase 5
- **Observaciones para versión futura:** Unificar legacy bajo flujo curricular o deprecar

---

### RF-03 — Importación de resultados del Fast Test

- **Estado V1:** **Retirado del alcance vigente**
- **Descripción actualizada:** RF-03 se retira del alcance vigente porque la institución no cuenta con Fast Test como insumo operativo. El cálculo de riesgo se apoyará en notas, asistencia, reportes conductuales y variables institucionales disponibles. Se conserva como referencia histórica del DRS v1; **no** es requisito funcional futuro obligatorio.
- **Actor(es):** — (no aplica en alcance vigente)
- **Prioridad:** — (retirado)
- **Evidencia de implementación:** —
- **Evidencia de prueba:** —
- **Brechas / pendientes:** N/A — fuera de alcance
- **Observaciones para versión futura:** No planificar implementación salvo cambio institucional de insumos

---

### RF-04 — Registro digital de reportes conductuales

- **Estado V1:** **Implementado V1 mínimo**
- **Descripción actualizada:** Parte del flujo de riesgo. Permite registrar reportes conductuales por estudiante (estudiante, fecha, tipo, gravedad, descripción, usuario registrador, estado). Actores V1: docente, coordinador académico, administrador. UI en perfil estudiante (`EstudiantePerfilReportesConductuales.jsx`). Anulación lógica mediante `PATCH /api/reportes-conductuales/{id}/anular`. Sin módulo global ni listado por grado/sección en V1.
- **Actor(es):** Docente, Psicólogo / Tutor, Coordinador académico; Directivo (lectura crítica)
- **Prioridad:** Alta
- **Evidencia de implementación:** `ReporteConductualController`, rutas API, `EstudiantePerfilReportesConductuales.jsx`
- **Evidencia de prueba:** `ReporteConductualTest` — 8 passed, 26 assertions (Fase 2E, 2026-06-10)
- **Brechas / pendientes:** Sin módulo global por grado/sección; sin alertas automáticas RF-10; directivo sin menú habitual
- **Observaciones para versión futura:** Integración con RF-19 completitud de datos; posible módulo global

---

### RF-05 — Integración de variables socioeconómicas

- **Estado V1:** **Retirado del flujo funcional de riesgo**
- **Descripción actualizada:** RF-05 se retira del flujo funcional vigente. Las variables socioeconómicas no serán usadas como insumo obligatorio del cálculo de riesgo. El sistema podrá operar con datos académicos, asistencia, reportes conductuales y variables institucionales disponibles. La API legacy `GET/POST /api/estudiantes/{id}/variables-socioeconomicas` puede permanecer como código histórico/técnico; **no** debe presentarse como requisito funcional vigente ni dependencia del riesgo.
- **Actor(es):** — (no actor principal en flujo de riesgo vigente)
- **Prioridad:** — (retirado del flujo de riesgo)
- **Evidencia de implementación:** `VariableSocioeconomicaController` (API legacy; UI pausada)
- **Evidencia de prueba:** `DatosAcademicosTest` (API legacy)
- **Brechas / pendientes:** N/A en flujo de riesgo — retirado por decisión de alcance
- **Observaciones para versión futura:** No reactivar como insumo obligatorio de RF-06 salvo nueva decisión institucional

---

### RF-06 — Procesamiento multivariable e índice de riesgo

- **Estado V1:** Implementado parcialmente (prototipo determinístico)
- **Descripción actualizada:** **Estado actual:** Laravel invoca Flask vía `POST /api/estudiantes/{id}/procesar-riesgo` y `MlRiskService`. El servicio ML V1 es **determinístico** (fórmula o reglas ponderadas), **no** ensemble Random Forest/SVM/XGBoost entrenado. Comando batch `DemoProcesarRiesgosCommand` disponible. **UI perfil riesgo en pausa.** **Evolución planificada:** transición a ML real cuando exista dataset histórico suficiente (RF-18). Si no existen todas las variables (notas, asistencia, reportes conductuales, etc.), el cálculo debe operar con datos disponibles y advertir mediante RF-19 semáforo de completitud.
- **Actor(es):** Sistema, Coordinador académico (permiso `procesar_riesgo`)
- **Prioridad:** Alta
- **Evidencia de implementación:** `RiesgoAcademicoService`, `ml-service/main.py`, `EstudiantePerfilRiesgo.jsx` (pausado)
- **Evidencia de prueba:** `RiesgoTest`, `DemoProcesarRiesgosCommandTest`
- **Brechas / pendientes:** No ML real entrenado; UI procesar ausente; RF-04 implementado V1 mínimo; RF-19 backend implementado; operación con datos parciales por definir
- **Observaciones para versión futura:** RF-18 vincula evolución a ML real; no afirmar reentrenamiento implementado

---

### RF-07 — Evaluación automática del nivel de riesgo

- **Estado V1:** Confirmado (parcial frente a REQ configurables)
- **Descripción actualizada:** Clasificación Alto ≥ 0,70; Medio 0,40–0,69; Bajo &lt; 0,40 en servicio de riesgo. Dashboard muestra KPIs por nivel.
- **Actor(es):** Sistema
- **Prioridad:** Alta
- **Evidencia de implementación:** `RiesgoAcademicoService`, umbrales en código
- **Evidencia de prueba:** `RiesgoTest`
- **Brechas / pendientes:** REQ-07.2 umbrales configurables por administrador — **no confirmado**
- **Observaciones para versión futura:** Panel admin configuración RN-01

---

### RF-08 — Emisión de alertas tempranas

- **Estado V1:** Confirmado
- **Descripción actualizada:** Generación de alertas post-procesamiento de riesgo; listado y detalle en UI **Alertas**.
- **Actor(es):** Sistema
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/alertas`, `AlertasPanel`, generación en servicio riesgo
- **Evidencia de prueba:** `RiesgoTest`, `AlertaIntervencionTest`
- **Brechas / pendientes:** RN-03 segundo disparador (ascenso Bajo→Medio dos bimestres) — **pendiente verificar**
- **Observaciones para versión futura:** Validar ambos disparadores RN-03 en tests

---

### RF-09 — Intervención preventiva del docente

- **Estado V1:** Confirmado
- **Descripción actualizada:** Registro de intervenciones vía `POST /api/alertas/{id}/intervenciones` desde detalle de alerta.
- **Actor(es):** Docente
- **Prioridad:** Alta
- **Evidencia de implementación:** `AlertaController`, UI alertas
- **Evidencia de prueba:** `AlertaIntervencionTest`
- **Brechas / pendientes:** No re-ejecutado Fase 5
- **Observaciones para versión futura:** —

---

### RF-10 — Escalamiento directivo de casos críticos

- **Estado V1:** **Planificado / Se implementará**
- **Descripción actualizada:** El directivo **no** es actor inicial de toda alerta. Solo interviene cuando el caso llega a nivel **crítico/extremo**. Escalamiento posible por: riesgo alto crítico, reincidencia, falta de respuesta, reporte conductual grave, alerta no resuelta. El directivo puede revisar, validar gravedad, disponer atención institucional o derivar a psicología/tutoría (RF-11). Sin rutas API ni UI en V1.
- **Actor(es):** Directivo
- **Prioridad:** Alta
- **Evidencia de implementación:** —
- **Evidencia de prueba:** —
- **Brechas / pendientes:** Implementación completa RF-10; integración con RF-13 estados escalada/derivada
- **Observaciones para versión futura:** Prioritario para cierre de ciclo alertas en casos críticos

---

### RF-11 — Atención psicológica/tutorial con perfil integral

- **Estado V1:** Implementado parcialmente — **planificado perfil integral**
- **Descripción actualizada:** **Estado actual:** Psicólogo accede a **Alertas** e intervenciones con permisos dedicados. **Alcance planificado:** el psicólogo/tutor podrá ver el **perfil completo del estudiante** en modo lectura: datos generales, notas, asistencia, historial de riesgo, alertas, intervenciones, reportes conductuales. **No** puede editar notas ni modificar configuración académica. **Sí** puede registrar intervenciones psicológicas/tutoriales y seguimiento. Puede recibir casos derivados o escalados según RF-10.
- **Actor(es):** Psicólogo / Tutor
- **Prioridad:** Media
- **Evidencia de implementación:** Permisos psicólogo, `AlertasPanel`
- **Evidencia de prueba:** `AlertaIntervencionTest` (parcial)
- **Brechas / pendientes:** RF-10; perfil integral lectura académica; reportes conductuales RF-04
- **Observaciones para versión futura:** Módulo psicología con vista 360° estudiante en lectura

---

### RF-12 — Comunicación formal y trazable con la familia

- **Estado V1:** **Eliminado del alcance / No se implementará**
- **Descripción actualizada:** La comunicación con familias se gestionará **fuera de SIDERAE-Blenkir**. No habrá API, pantalla ni trazabilidad interna para comunicación familiar en este alcance. Tabla `comunicaciones_familiares` en migraciones es esquema histórico; **no** es requisito vigente ni dependencia de RF-13.
- **Actor(es):** — (no aplica en alcance vigente)
- **Prioridad:** — (eliminado)
- **Evidencia de implementación:** Esquema BD histórico únicamente
- **Evidencia de prueba:** —
- **Brechas / pendientes:** N/A — fuera de alcance
- **Observaciones para versión futura:** No planificar salvo cambio de alcance institucional

---

### RF-13 — Registro de acción tomada y cierre de alerta

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** El cierre de alerta será **únicamente por intervención registrada**. **No** habrá cierre por comunicación familiar (RF-12 eliminado). **No** habrá cierre automático sin acción. Si existe derivación/escalamiento (RF-10), la alerta **no** se cierra solo por derivar; se cierra cuando se registre intervención y resultado. Estados sugeridos: pendiente, en atención, escalada/derivada, cerrada. Implementado parcialmente: cierre vía `POST /api/alertas/{id}/cerrar` tras intervención en tests.
- **Actor(es):** Docente, Directivo, Psicólogo
- **Prioridad:** Alta
- **Evidencia de implementación:** Rutas alertas, `AlertasPanel`
- **Evidencia de prueba:** `AlertaIntervencionTest`
- **Brechas / pendientes:** RF-10 escalamiento; estados intermedios completos; RN-04 ampliada
- **Observaciones para versión futura:** Ampliar reglas cierre alineadas a RN-04 sin vía familiar

---

### RF-14 — Dashboard académico e institucional

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** Dashboard **general académico-institucional** (el riesgo es una sección, no todo el dashboard). Debe mostrar indicadores de: estudiantes, notas/resumen académico, asistencia, alertas, riesgo académico, reportes conductuales (cuando RF-04 exista), avance curricular por periodo. **Estado actual:** KPIs de riesgo y filtros básicos (`GET /api/dashboard`). Sede **Chilca**; sin multi-sede V1. **No** equivale a vista multi-sede directivo, export PNG ni % alertas REQ-14.5 completos.
- **Actor(es):** Docente, Directivo, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `DashboardController`, `DashboardPanel`
- **Evidencia de prueba:** `DashboardTest`
- **Brechas / pendientes:** Indicadores académicos ampliados; reportes conductuales; multi-sede fuera V1
- **Observaciones para versión futura:** Evolucionar dashboard hacia visión académica-institucional integral

---

### RF-15 — Gestión de usuarios y control de acceso por rol

- **Estado V1:** Confirmado
- **Descripción actualizada:** CRUD usuarios admin, 5 roles, 23 permisos Spatie, middleware en rutas API, menú usuarios para administrador.
- **Actor(es):** Administrador
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/usuarios/*`, `PermissionsSeeder`, `UsuariosPanel`
- **Evidencia de prueba:** `GestionUsuariosTest`, `AuthenticationTest`, tests 403 múltiples
- **Brechas / pendientes:** `POST /register` **público** (guest) — brecha producción; 401 no exhaustivo en todas rutas curriculares
- **Observaciones para versión futura:** Deshabilitar registro público antes producción

---

### RF-16 — Generación de reportes de riesgo académico

- **Estado V1:** Implementado parcialmente — **planificado zona de reportes**
- **Descripción actualizada:** Zona específica de **reportes de riesgo** para informar riesgos académicos. Reportes posibles: por estudiante, aula/sección, grado, periodo/bimestre. Deben incluir según disponibilidad: riesgo actual, historial de riesgo, notas, asistencia, alertas, intervenciones, reportes conductuales. **Estado actual:** export PDF del **dashboard** vía `GET /api/dashboard/export` (DomPDF) — antecedente parcial, **no** cumplimiento completo. Excel por aula (`GET /excel-aula`) es export curricular, distinto de reportes de riesgo.
- **Actor(es):** Docente, Directivo, Coordinador académico
- **Prioridad:** Media
- **Evidencia de implementación:** `DashboardController` export PDF; `PlantillaRegistroAuxiliarExcelService` (Excel curricular)
- **Evidencia de prueba:** `DashboardTest`, `ExcelAulaTest`, `ActivityLogTest`
- **Brechas / pendientes:** Zona reportes de riesgo; reportes por estudiante/aula/grado/periodo
- **Observaciones para versión futura:** Ver [`aula-notas-excel.md`](../aula-notas-excel.md) §11 — no confundir Excel curricular con reportes de riesgo

---

### RF-17 — Registro de auditoría de acciones

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** `spatie/laravel-activitylog` registra acciones en controladores críticos. **No** existe porque «ISO lo exige directamente»; se mantiene porque aporta trazabilidad, control de acciones y evidencia para **alineación progresiva** con ISO/IEC 27000 e ISO 9001. **Sin certificación ISO** ni auditoría externa. **Sin UI** de consulta de logs.
- **Actor(es):** Sistema, Administrador (consulta planificada)
- **Prioridad:** Alta
- **Evidencia de implementación:** Activitylog en controladores; tabla `activity_log`
- **Evidencia de prueba:** `ActivityLogTest` (parcial)
- **Brechas / pendientes:** Cobertura ampliada; UI auditoría admin; retención operativa no verificada
- **Observaciones para versión futura:** UI auditoría admin; extender logging como evidencia parcial de calidad

---

### RF-18 — Reentrenamiento del modelo ML

- **Estado V1:** **Planificado / Se implementará cuando exista dataset suficiente**
- **Descripción actualizada:** RF-18 se mantiene como requisito planificado para la evolución hacia un **modelo ML real**. No forma parte del estado implementado actual hasta contar con dataset histórico y pipeline de entrenamiento. Depende de: dataset histórico, variable objetivo definida, entrenamiento, métricas, versión de modelo, activación de modelo, trazabilidad del reentrenamiento. Vinculado a ML real, **no** al modelo determinístico actual.
- **Actor(es):** Administrador
- **Prioridad:** Media
- **Evidencia de implementación:** —
- **Evidencia de prueba:** —
- **Brechas / pendientes:** Pipeline ML real completo; endpoints Laravel/Flask; métricas accuracy/precision/recall/F1
- **Observaciones para versión futura:** No afirmar implementado; requiere evolución desde RF-06 determinístico

---

### RF-19 — Semáforo de completitud de datos

- **Estado V1:** **Backend implementado; UI pendiente**
- **Descripción actualizada:** Indica si existen datos suficientes para interpretar el riesgo académico. Estados V1: **verde** (notas curriculares **y** asistencia curricular presentes), **amarillo** (al menos uno entre notas, asistencia, reportes conductuales activos o índice de riesgo del periodo), **rojo** (sin ninguno de los anteriores). Insumos V1: notas curriculares (`NotaSemanal` / `EvalBimResultado`), asistencia curricular (`AsistenciaDiaria`), reportes conductuales activos (RF-04) e índice de riesgo (`IndiceRiesgo`) como dato complementario. El semáforo es **informativo** en V1: no bloquea el procesamiento ML, no recalcula riesgo y no llama a Flask. UI en perfil estudiante pendiente Fase 3D.
- **Actor(es):** Docente, Administrador, Coordinador académico
- **Prioridad:** Media
- **Evidencia de implementación:** `CompletitudDatosService`, `SemaforoCompletitudController`, `GET /api/estudiantes/{estudiante}/semaforo-completitud`, permiso `ver_semaforo_completitud`
- **Evidencia de prueba:** `SemaforoCompletitudTest` — 11 passed, 55 assertions (Fase 3C, 2026-06-23)
- **Brechas / pendientes:** UI perfil estudiante (Fase 3D); integración con RF-06 operación con datos parciales
- **Observaciones para versión futura:** Dependiente de RF-04 reportes conductuales para completitud plena; posible historial evolutivo con RF-20

---

### RF-20 — Historial de riesgo por estudiante

- **Estado V1:** Implementado parcialmente — **planificado historial evolutivo**
- **Descripción actualizada:** **No** significa solo guardar el último índice. Debe mostrar **evolución por periodo/bimestre** y permitir ver si el estudiante mejora, empeora o se mantiene. Relaciona: índice de riesgo, nivel de riesgo, notas, asistencia, reportes conductuales, alertas, intervenciones. Puede alimentar reportes RF-16. **Estado actual:** persistencia en tabla `indices_riesgo`; perfil riesgo **UI pausado** — sin timeline ni export.
- **Actor(es):** Docente, Directivo, Psicólogo / Tutor (lectura)
- **Prioridad:** Media
- **Evidencia de implementación:** Migración `indices_riesgo`, `ResumenAcademicoTest`, `ActivoUniqueKeyHistorialTest`
- **Evidencia de prueba:** `RiesgoTest` (persistencia)
- **Brechas / pendientes:** Timeline evolutivo UI; correlación con RF-04 y RF-16
- **Observaciones para versión futura:** Reactivar `EstudiantePerfilRiesgo.jsx` con vista evolutiva

---

### 8.21 Requerimientos funcionales nuevos del módulo curricular y académico

Los requisitos RF-21 a RF-35 formalizan el módulo curricular que antes estaba implícito en RF-01. **No** renumeran RF-01 a RF-20. Estados derivados de código y [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md).

| RF | Nombre | Estado V1 |
|----|--------|-----------|
| RF-21 | Gestión de periodos académicos | Confirmado / implementado |
| RF-22 | Gestión de malla curricular | Confirmado / implementado |
| RF-23 | Gestión de competencias y capacidades | Confirmado / implementado |
| RF-24 | Gestión de criterios o temas semanales | Confirmado / implementado |
| RF-25 | Gestión de componentes de calificación | Confirmado / implementado |
| RF-26 | Configuración bimestral | Confirmado / implementado |
| RF-27 | Gestión de secciones y aulas | Confirmado / implementado |
| RF-28 | Asignación docente por aula y curso | Confirmado / implementado |
| RF-29 | Registro de notas semanales curriculares | Confirmado / implementado |
| RF-30 | Consulta institucional de notas | Confirmado / parcial según rol |
| RF-31 | Registro de asistencia curricular diaria | Confirmado / implementado |
| RF-32 | Plantilla Excel curricular: descarga e importación | Confirmado / implementado |
| RF-33 | Excel por aula: descarga multi-hoja | Confirmado / implementado |
| RF-34 | Evaluación bimestral | Confirmado / implementado |
| RF-35 | Resumen académico curricular del estudiante | Confirmado / parcial según UI |

#### RF-21 — Gestión de periodos académicos

- **Estado V1:** Confirmado / implementado
- **Descripción:** CRUD de periodos académicos (año escolar, bimestres activos).
- **Actor(es):** Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/periodos-academicos/*`, `PeriodosAcademicosPanel`
- **Evidencia de prueba:** `PeriodoAcademicoTest`, `CurricularApiTest`
- **Observaciones:** Base temporal para notas, asistencia y evaluación bimestral.

#### RF-22 — Gestión de malla curricular

- **Estado V1:** Confirmado / implementado
- **Descripción:** Administración de malla, áreas, cursos y orden curricular por nivel/grado.
- **Actor(es):** Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/malla/*`, `MallaCurricularPanel`
- **Evidencia de prueba:** `MallaCurricularTest`
- **Observaciones:** Sustituye dependencia implícita de SIAGIE para estructura curricular.

#### RF-23 — Gestión de competencias y capacidades

- **Estado V1:** Confirmado / implementado
- **Descripción:** Catálogo de competencias y capacidades asociadas a cursos de la malla.
- **Actor(es):** Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/competencias/*`, `/api/curricular/capacidades/*`
- **Evidencia de prueba:** Tests curriculares en `Feature/Curricular/`
- **Observaciones:** Alimenta registro de notas semanales por competencia.

#### RF-24 — Gestión de criterios o temas semanales

- **Estado V1:** Confirmado / implementado
- **Descripción:** Definición de criterios/temas semanales por curso y periodo.
- **Actor(es):** Docente, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/criterios-semanales/*`, `CriteriosSemanalesPanel`
- **Evidencia de prueba:** Tests curriculares
- **Observaciones:** Vinculado a RF-29 y plantilla Excel RF-32.

#### RF-25 — Gestión de componentes de calificación

- **Estado V1:** Confirmado / implementado
- **Descripción:** Componentes de evaluación (capacidades, pesos) por curso/bimestre.
- **Actor(es):** Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/componentes-calificacion/*`, `ComponentesCalificacionPanel`
- **Evidencia de prueba:** Tests curriculares
- **Observaciones:** Base para evaluación bimestral RF-34.

#### RF-26 — Configuración bimestral

- **Estado V1:** Confirmado / implementado
- **Descripción:** Configuración de bimestres activos, fechas y parámetros de evaluación.
- **Actor(es):** Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/configuracion-bimestral/*`, `ConfiguracionBimestralPanel`
- **Evidencia de prueba:** Tests curriculares
- **Observaciones:** Condiciona RF-29, RF-31, RF-34.

#### RF-27 — Gestión de secciones y aulas

- **Estado V1:** Confirmado / implementado
- **Descripción:** Creación y administración de secciones/aulas por periodo y nivel.
- **Actor(es):** Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/secciones-aulas/*`, `SeccionesAulasPanel`
- **Evidencia de prueba:** `SeccionAulaTest`
- **Observaciones:** Requerido para Excel por aula RF-33.

#### RF-28 — Asignación docente por aula y curso

- **Estado V1:** Confirmado / implementado
- **Descripción:** Asignación de docentes a aula–curso–periodo para registro de notas y asistencia.
- **Actor(es):** Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/asignaciones-docente/*`, `AsignacionDocentePanel`
- **Evidencia de prueba:** `AsignacionDocenteTest`
- **Observaciones:** Restringe registro docente en RF-29 y RF-31.

#### RF-29 — Registro de notas semanales curriculares

- **Estado V1:** Confirmado / implementado
- **Descripción:** Registro semanal de notas por competencia/capacidad en UI y bulk API.
- **Actor(es):** Docente
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/notas-semanales/*`, `RegistroNotasSemanalesPanel`
- **Evidencia de prueba:** `NotasSemanalesTest`, `NotasSemanalesBulkTest`
- **Observaciones:** Insumo principal de RF-06 y RF-01.

#### RF-30 — Consulta institucional de notas

- **Estado V1:** Confirmado / parcial según rol
- **Descripción:** Consulta de notas por rol (docente asignado, coordinador, directivo lectura, admin).
- **Actor(es):** Docente, Coordinador académico, Directivo, Administrador
- **Prioridad:** Alta
- **Evidencia de implementación:** Endpoints GET curriculares, paneles según permisos
- **Evidencia de prueba:** Tests 403 por rol en curricular
- **Observaciones:** Directivo accede por excepción UI en notas semanales.

#### RF-31 — Registro de asistencia curricular diaria

- **Estado V1:** Confirmado / implementado
- **Descripción:** Registro diario de asistencia por aula/curso/fecha en flujo curricular.
- **Actor(es):** Docente
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/asistencias-diarias/*`, `AsistenciaPanel`
- **Evidencia de prueba:** `AsistenciaDiariaTest`
- **Observaciones:** Insumo de RF-06 y RF-19.

#### RF-32 — Plantilla Excel curricular: descarga e importación

- **Estado V1:** Confirmado / implementado
- **Descripción:** Plantilla Excel **propia del sistema** por curso/asignación: descarga vacía o con datos, importación con validación. **Sustituye SIAGIE** en alcance actual.
- **Actor(es):** Docente, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `GET/POST plantilla-excel` / `importar-excel`, `PlantillaRegistroAuxiliarExcelService`
- **Evidencia de prueba:** `PlantillaRegistroAuxiliarExcelTest`
- **Observaciones:** Ver [`aula-notas-excel.md`](../aula-notas-excel.md).

#### RF-33 — Excel por aula: descarga multi-hoja

- **Estado V1:** Confirmado / implementado
- **Descripción:** Descarga Excel multi-hoja por aula (hoja ESTUDIANTES + una hoja por curso activo). **Solo descarga**; sin importación aula en V1.
- **Actor(es):** Administrador, Coordinador académico (permiso `descargar_excel_aula`)
- **Prioridad:** Media
- **Evidencia de implementación:** `GET /api/curricular/excel-aula`, `ExcelPorAulaPanel`
- **Evidencia de prueba:** `ExcelAulaTest`
- **Observaciones:** Registro auxiliar institucional; distinto de SIAGIE.

#### RF-34 — Evaluación bimestral

- **Estado V1:** Confirmado / implementado
- **Descripción:** Cálculo y registro de evaluación bimestral a partir de notas semanales y componentes.
- **Actor(es):** Docente, Sistema
- **Prioridad:** Alta
- **Evidencia de implementación:** Endpoints evaluación bimestral curricular
- **Evidencia de prueba:** Tests curriculares de bimestre
- **Observaciones:** Alimenta resumen académico RF-35.

#### RF-35 — Resumen académico curricular del estudiante

- **Estado V1:** Confirmado / parcial según UI
- **Descripción:** Vista consolidada de desempeño académico del estudiante por periodo/bimestre.
- **Actor(es):** Docente, Coordinador académico, Directivo (lectura)
- **Prioridad:** Media
- **Evidencia de implementación:** `ResumenAcademicoTest`, endpoints resumen
- **Evidencia de prueba:** `ResumenAcademicoTest`
- **Observaciones:** Base para RF-14 dashboard académico e insumo de RF-06.

---

## 9. Requerimientos no funcionales actualizados

Estados: **Evidencia confirmada** · **Evidencia parcial** · **Pendiente** · **No aplica V1**

Basado en DRS v1 (RNF-01–RNF-10), [`docs/calidad/matriz-iso-25010.md`](../calidad/matriz-iso-25010.md) y [`docs/calidad/matriz-seguridad-iso-27000.md`](../calidad/matriz-seguridad-iso-27000.md).

### 9.1 Seguridad (RNF-03)

| Aspecto | Descripción | Evidencia actual | Estado | Brecha |
|---------|-------------|------------------|--------|--------|
| Autenticación | Sanctum + Breeze, CSRF SPA | `auth.php`, `api.js` | Evidencia confirmada | `POST /register` público |
| Autorización | Spatie 23 permisos, middleware | `api.php` | Evidencia confirmada | 401/403 no exhaustivos |
| Contraseñas | bcrypt Laravel | Framework default | Evidencia confirmada | — |
| HTTPS/TLS producción | TLS 1.2+ | — | Pendiente | Solo HTTP local Docker |
| Privacidad Ley 29733 | Datos sensibles por rol | RBAC parcial | Evidencia parcial | Sin DPIA ni certificación |

### 9.2 Usabilidad (RNF-04)

| Aspecto | Descripción | Evidencia | Estado | Brecha |
|---------|-------------|---------|--------|--------|
| UI responsiva | Tailwind, layout AppLayout | `App.jsx`, Sprint 7A | Evidencia parcial | No medido 768px formalmente |
| Flujos ≤ 5 pasos | Manual usuario por rol | `manual-usuario.md` | Evidencia parcial | Módulos curriculares más pasos |
| Estados carga/error | Componentes UI | Paneles React | Evidencia parcial | Sin E2E Cypress |

### 9.3 Mantenibilidad (RNF-05)

| Aspecto | Descripción | Evidencia | Estado | Brecha |
|---------|-------------|---------|--------|--------|
| Arquitectura desacoplada | Docker, capas separadas | `ARCHITECTURE.md` | Evidencia confirmada | — |
| Documentación | Manuales, matrices, DRS v2 | `docs/*` | Evidencia confirmada | — |
| Cobertura tests ≥ 80% | PHPUnit críticos | 49 archivos tests | Evidencia parcial | Sin métrica cobertura; Jest no confirmado |
| PSR-12 / guías React | Estándares código | Convenciones Laravel | Evidencia parcial | No auditado formalmente |

### 9.4 Portabilidad (RNF-09)

| Aspecto | Descripción | Evidencia | Estado | Brecha |
|---------|-------------|---------|--------|--------|
| Docker Compose | 4 servicios reproducibles | `docker-compose.yml` | Evidencia confirmada | Instala deps en arranque (lento) |
| Sin config manual extra | `docker compose up` | `README.md`, `instalacion-docker.md` | Evidencia parcial | Seed manual; `.env` implícito |

### 9.5 Rendimiento (RNF-01)

| Aspecto | Descripción | Evidencia | Estado | Brecha |
|---------|-------------|---------|--------|--------|
| Dashboard &lt; 3 s | KPIs precalculados | No benchmark formal | No confirmado | Sin prueba carga |
| ML ≤ 10 s background | Flask determinístico | Integración síncrona API | Evidencia parcial | No medido 50 usuarios |
| Tests Excel memoria | Descarga multi-hoja | OOM @ 128M | Evidencia parcial | Requiere 512M |

### 9.6 Fiabilidad (RNF-02)

| Aspecto | Descripción | Evidencia | Estado | Brecha |
|---------|-------------|---------|--------|--------|
| Disponibilidad 99% | Horario escolar | Solo local | No aplica V1 | Sin SLA producción |
| Recuperación 2 h | Continuidad | — | Pendiente | Sin backups confirmados |

### 9.7 Trazabilidad (RNF-07)

| Aspecto | Descripción | Evidencia | Estado | Brecha |
|---------|-------------|---------|--------|--------|
| Activity log | Acciones críticas | `ActivityLogTest` | Evidencia parcial | RF-17 incompleto |
| Matriz RF–Sprint–Test | Trazabilidad académica | `matriz-rf-sprint-test.md` | Evidencia confirmada | — |
| ISO 9001 referencia | Mejora continua | `trazabilidad-iso-9001.md` | Evidencia parcial | Sin SGC certificado |

### 9.8 Compatibilidad (RNF-08)

| Aspecto | Descripción | Evidencia | Estado | Brecha |
|---------|-------------|---------|--------|--------|
| Chrome / Firefox | Navegadores modernos | Desarrollo Vite | Evidencia parcial | Sin matriz compatibilidad ejecutada |
| Integridad datos (RNF-10) | Validación pre-ML | Validaciones API parciales; semáforo RF-19 backend implementado | Evidencia parcial | UI semáforo pendiente |

---

## 10. Reglas de negocio

Transcripción/resumen DRS v1 con **estado V1** ([`contexto-drs-requerimientos.md`](../arquitectura/contexto-drs-requerimientos.md) §4).

| ID | Regla | Estado V1 |
|----|-------|-----------|
| **RN-01** | Umbrales Alto ≥ 0,70; Medio 0,40–0,69; Bajo &lt; 0,40; configurables por admin | Umbrales en código **confirmados**; configuración admin **pendiente** |
| **RN-02** | Completitud mínima (asistencia, notas, reportes conductuales) para interpretar riesgo; semáforo RF-19 | **Parcial** — notas/asistencia confirmadas; reportes conductuales RF-04 implementados V1 mínimo; semáforo RF-19 backend implementado (UI pendiente); **VSE retiradas del flujo** |
| **RN-03** | Alerta si índice ≥ Alto **o** ascenso Bajo→Medio dos bimestres | Umbral Alto **confirmado**; segundo disparador **pendiente verificar** |
| **RN-04** | Cierre alerta **solo** con intervención registrada; activity_log | Intervención/cierre **confirmado**; escalamiento RF-10 **planificado**; **sin** cierre por comunicación familiar |
| **RN-05** | Segregación acceso por rol; backend valida Spatie | **Confirmado** |
| **RN-06** | Reentrenamiento ML inicio año escolar; solo admin | **Planificado** (RF-18) — requiere ML real y dataset |
| **RN-07** | Trazabilidad acciones en activity_log; no eliminar logs | **Parcial** (RF-17) — apoya alineación progresiva, no certificación ISO |
| **RN-08** | Import .xlsx/.csv con validación y mensajes | Plantilla curricular **confirmada**; SIAGIE **fuera del alcance actual** |

**Reglas operativas V1 adicionales:**

- Sede operativa **Chilca**; campo `sede` conservado; **no** multi-sede activa.
- Excel aula = **descarga**; import curricular = **plantilla por curso** — **no SIAGIE**.
- RF vigentes **RF-01 a RF-35**; RF-03, RF-05 (flujo riesgo), RF-12 **retirados** del alcance vigente.
- Riesgo académico **no sustituye** juicio pedagógico (prototipo académico).
- ML V1 = **determinístico**; no ensemble DRS v1.
- **No eliminar estudiantes** (política README).
- Frontend **no** llama Flask directamente.

---

## 11. Interfaces externas

| Interfaz | Tipo | Estado V1 | Notas |
|----------|------|-----------|-------|
| Interfaz web React | SPA HTTP | Confirmado | Puerto 5173 |
| API REST Laravel | JSON `/api/*` | Confirmado | Puerto 8000; Sanctum |
| MySQL | SQL | Confirmado | Puerto host 3307 |
| ML Flask `/predict` | HTTP interno | Confirmado | Determinístico; puerto 5000 |
| Archivos Excel curriculares | Upload/download | Confirmado | Plantilla import + Excel aula export |
| SIAGIE automático | Import institucional | **Fuera del alcance actual** | RF-01 — plantillas propias en su lugar |
| Servicios externos (email prod, etc.) | — | No confirmado | Breeze email en dev |
| WebSockets dashboard | — | Fuera V1 | Recarga manual/polling según DRS v1 |

---

## 12. Modelo de datos conceptual

Descripción basada en migraciones y modelos detectados — **sin diagrama ER formal** en repositorio.

| Entidad | Descripción breve | Estado V1 |
|---------|-------------------|-----------|
| **usuarios** | Credenciales, roles Spatie | Confirmado |
| **roles / permisos** | RBAC 5+23 | Confirmado |
| **estudiantes** | Datos personales, sede, nivel | Confirmado |
| **periodos_academicos** | Años/bimestres | Confirmado |
| **malla_curricular / cursos / áreas** | Estructura curricular | Confirmado |
| **competencias / capacidades** | Catálogo curricular | Confirmado |
| **secciones_aulas** | Aulas por periodo/nivel | Confirmado |
| **asignaciones_docente** | Docente–aula–curso | Confirmado |
| **notas_semanales** | Registro semanal por competencia | Confirmado |
| **asistencias_diarias / curriculares** | Asistencia por fecha | Confirmado |
| **variables_socioeconomicas** | VSE por estudiante | API legacy; **retiradas del flujo de riesgo** |
| **indices_riesgo** | Historial scores ML | Confirmado |
| **alertas** | Alertas tempranas | Confirmado |
| **intervenciones** | Acciones docentes sobre alertas | Confirmado |
| **reportes_conductuales** | Esquema BD | RF-04 **planificado** |
| **comunicaciones_familiares** | Esquema BD histórico | **Fuera del alcance** (RF-12 eliminado) |
| **activity_log** | Auditoría Spatie | Parcial |

---

## 13. Seguridad y control de acceso

Consolidado de [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md).

| Elemento | Detalle V1 |
|----------|------------|
| Autenticación | Laravel Sanctum + Breeze; CSRF cookie SPA |
| Autorización | Spatie Permission — **5 roles**, **23 permisos implementados** (+ 8 sugeridos/planificados — ver [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) §16) |
| Middleware | `auth:sanctum` + `permission:*` en `api.php` |
| Pruebas seguridad | 401/403 en subset Feature tests — **parciales** |
| Registro público | `POST /register` guest — **brecha producción**; OK prototipo local |
| Activity log | Parcial — RF-17 |
| ISO / auditoría | **Alineación progresiva** únicamente; **sin certificación** ni auditoría externa |
| Datos demo | Sintéticos; no datos reales producción |

---

## 14. Pruebas y evidencias

Basado en [`docs/pruebas/informe-pruebas.md`](../pruebas/informe-pruebas.md) y Fase 1.

| Aspecto | Estado |
|---------|--------|
| Framework | PHPUnit Feature/Unit — **49 archivos** en `backend/tests/` |
| Suite completa @ 128M | **Incompleta** — OOM en `ExcelAulaTest` (Fase 1) |
| `ExcelAulaTest` aislado @ 512M | **8 passed**, 32 assertions (Fase 1) |
| Tests previos a OOM | ~277 passed (conteo salida Fase 1) |
| Cypress / E2E | **No existe** en repositorio |
| Pruebas 401/403 | **Parciales** — ver matriz seguridad §12 |
| Re-ejecución Fase 7 | **No realizada** — solo documentación |
| Conteos BD tinker Fase 1 | 449 estudiantes, 253 Chilca / 196 Auquimarca, 8 usuarios — **evidencia local auditada, no seed oficial** |
| Pruebas manuales por rol | **Recomendadas** — no registradas |

**Advertencia:** los conteos de BD **no** representan un entorno de referencia único (`migrate:fresh --seed` no ejecutado en auditoría Fase 1).

---

## 15. Alineación ISO progresiva

Referencia académica **sin certificación** ([`docs/calidad/alineacion-iso.md`](../calidad/alineacion-iso.md)).

| Norma / referencia | Evidencia principal | Estado | Brecha |
|--------------------|---------------------|--------|--------|
| ISO/IEC 25010 | Matriz calidad producto | Evidencia parcial | No conformidad total producto |
| ISO/IEC 27000 | Matriz seguridad activos | Evidencia parcial | Sin SGSI; register público |
| ISO 9001 | Trazabilidad documental Fases 1–7 | Evidencia parcial | Sin SGC certificado |
| Mejora continua | `no-conformidades-y-mejora.md` | Evidencia confirmada | NC abiertas |

**Redacción obligatoria para sustentación:** el proyecto mantiene **alineación progresiva** con criterios inspirados en ISO como **referencia académica**. **No** hay certificación ISO ni auditoría externa.

---

## 16. Limitaciones del sistema V1

| Limitación | Detalle |
|------------|---------|
| Sede única operativa | **Chilca** — Auquimarca histórico/local en BD |
| SIAGIE | **Fuera del alcance actual** — plantillas Excel propias |
| Fast Test | **Retirado del alcance vigente** |
| VSE en riesgo | **Retiradas del flujo funcional** |
| Comunicación familiar | **Eliminada del alcance** |
| ML | **Determinístico** — sin RF/SVM/XGBoost entrenado |
| Reentrenamiento | **Planificado** — RF-18; no implementado |
| Cypress | **Ausente** |
| Suite PHPUnit | OOM @ **128M**; Excel @ **512M** |
| Seed oficial | **Pendiente** — conteos locales no oficiales |
| VSE | **Retiradas del flujo de riesgo**; API legacy puede existir |
| UI riesgo | Perfil **pausado**; sin botón procesar |
| Activity log | **Parcial** |
| Register guest | **Brecha** pre-producción |
| Multi-sede | **Fuera de V1** |
| DRS v1 PDF | Histórico; este v2 corrige estado real |

Detalle: [`docs/limitaciones.md`](../limitaciones.md).

---

## 17. Brechas y plan de mejora

Basado en [`docs/calidad/no-conformidades-y-mejora.md`](../calidad/no-conformidades-y-mejora.md).

| ID | Brecha | RF/RNF | Prioridad | Acción recomendada |
|----|--------|--------|-----------|-------------------|
| NC-01 | DRS v1 PDF vs código | Todos | Alta | **Este documento v2** + revisión humana |
| NC-02 | SIAGIE **fuera del alcance actual** | RF-01 | Alta | Decisión documentada; plantillas propias RF-32/RF-33 | Documentación | Documentada v2.1 |
| NC-03 | ML **determinístico** vs ensemble DRS v1 | RF-06, RF-18 | Alta | RF-06 parcial; RF-18 planificado ML real | Equipo ML / doc | Documentada v2.1 |
| NC-04 | RF-18 reentrenamiento ML **planificado** | RF-18 | Media | Implementar cuando exista dataset histórico | Equipo ML | Planificada |
| NC-05 | Sin Cypress | RNF-04/05 | Media | Smoke manual; Cypress opcional |
| NC-06 | Suite OOM 128M | RF-01, RF-16 | Media | `memory_limit=512M` en CI |
| NC-07 | Seed oficial | RF-01 | Media | Entorno referencia documentado |
| NC-08 | Activity log parcial | RF-17 | Media | Extender logging + UI |
| NC-09 | Register público | RF-15 | Alta (prod) | Deshabilitar pre-producción |
| NC-10 | Multi-sede no activa | RF-14 | Media | Documentado — Chilca V1 |
| NC-11 | UI riesgo pausada | RF-06, RF-20 | Media | Reactivar UI o comando técnico |
| NC-12 | **VSE retiradas** del flujo de riesgo | RF-05 | Media | Documentado v2.1; no insumo obligatorio RF-06 | Producto | Documentada v2.1 |
| NC-13 | RF-04 **implementado V1 mínimo**; RF-10 **planificado**; RF-19 **backend implementado**; RF-03/RF-12 **retirados** | RF-04–13, RF-19 | Alta (RF-10) / Media (RF-04/RF-19 UI) | Backlog explícito RF-10; RF-19 UI Fase 3D | Documentación | Documentada v2.1; RF-04 cerrado V1 mínimo; RF-19 backend cerrado |
| NC-16 | RF-16 reportes de riesgo **planificados** | RF-16 | Media | Zona reportes por estudiante/aula/grado | Backend + doc | Abierta |
| NC-17 | RF-20 historial evolutivo **planificado** | RF-20 | Media | Timeline por periodo/bimestre | Frontend | Abierta |
| NC-18 | RF-21–RF-35 curriculares documentados | RF-21–35 | Media | Matriz RF–Sprint–Test actualizada | Documentación | Documentada v2.1 |
| NC-14 | 401/403 incompletos | RF-15 | Media | Ampliar Feature tests |
| NC-15 | Sin auditoría ISO | RNF | No aplica V1 | Declarar en sustentación |

---

## 18. Criterios de aceptación V1

Criterios **honestos** para cierre académico del prototipo (no producción):

1. **Stack Docker** levanta con 4 servicios Up (`docker compose ps`).
2. **Login** Sanctum operativo; roles y permisos aplican en API (403 confirmados en subset).
3. **Módulos curriculares confirmados** accesibles según rol: malla, notas, asistencia, Excel aula, plantilla import.
4. **Notas semanales y asistencia curricular** registrables vía UI documentada en manual usuario.
5. **Excel aula** descarga verificable (`ExcelAulaTest` @ 512M); **plantilla curricular** import/export según `aula-notas-excel.md`.
6. **Alertas e intervenciones** operativas según matriz RF (tests detectados).
7. **Dashboard** muestra KPIs subset RF-14; export PDF dashboard disponible.
8. **Riesgo académico** procesable vía API/comando; ML determinístico documentado.
9. **RF-19 semáforo backend** operativo: endpoint protegido, tests `SemaforoCompletitudTest` pasan; UI en perfil estudiante pendiente (Fase 3D).
9. **Pruebas documentadas** con limitaciones conocidas (OOM, sin Cypress, BD no seed oficial).
10. **Documentación consolidada** Fases 1–9 incluyendo este DRS v2.1, matrices ISO progresivas y registro NC.
11. **No se exige** certificación ISO, SIAGIE, Fast Test, VSE en riesgo, comunicación familiar, ensemble ML, multi-sede ni suite PHPUnit 100% verde @ 128M.
12. **RF vigentes RF-01 a RF-35**; módulo curricular RF-21–RF-35 confirmado en código según matriz.

---

## 19. Anexos

Enlaces a documentación de soporte — **no se reproduce el contenido completo**.

| Anexo | Documento | Ruta |
|-------|-----------|------|
| **A** | Matriz RF–Sprint–Test | [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) |
| **B** | Informe de pruebas | [`docs/pruebas/informe-pruebas.md`](../pruebas/informe-pruebas.md) |
| **C** | Seguridad, roles y permisos | [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) |
| **D** | Aula, notas y Excel | [`docs/aula-notas-excel.md`](../aula-notas-excel.md) |
| **E** | Alineación ISO progresiva | [`docs/calidad/alineacion-iso.md`](../calidad/alineacion-iso.md) |
| **F** | Limitaciones V1 | [`docs/limitaciones.md`](../limitaciones.md) |
| **G** | Índice documentación | [`INDICE_DOCUMENTACION.md`](../INDICE_DOCUMENTACION.md) |
| **H** | Entrada carpeta `docs/` | [`README.md`](../README.md) |
| **I** | DRS v1 original (PDF) | `DRS_SIDERAE_Blenkir_v1.pdf` — externo al repositorio |

---

*Documento actualizado en Fase 9 — reestructuración RF V2.1. Fecha de referencia técnica: 2026-06-09.*
