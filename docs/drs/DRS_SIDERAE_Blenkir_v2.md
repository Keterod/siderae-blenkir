# Documento de Requerimientos de Software
# SIDERAE-Blenkir V2

**Versión:** 2.0 (actualización documental)  
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
| RBAC — 5 roles, 23 permisos | `PermissionsSeeder.php`, `api.php` |
| Gestión estudiantes | `EstudianteController`, `EstudiantesPanel` |
| Gestión usuarios (RF-15) | `/api/usuarios/*`, `UsuariosPanel` |
| Módulo curricular completo en menú | `/api/curricular/*`, paneles en `App.jsx` |
| Asistencia curricular (RF-02) | `/api/curricular/asistencias-diarias/*`, `AsistenciaDiariaTest` |
| Alertas, intervenciones, cierre (RF-08, RF-09) | `AlertaController`, `AlertasPanel`, `AlertaIntervencionTest` |
| Clasificación de riesgo por umbrales (RF-07) | `RiesgoAcademicoService`, `RiesgoTest` |
| Infraestructura Docker 4 servicios | `docker-compose.yml` |

### 2.2 Alcance implementado parcialmente

| Módulo / RF | Qué opera en V1 | Qué falta frente al DRS v1 |
|-------------|-----------------|---------------------------|
| **RF-01** Carga/importación datos | Notas semanales, plantilla Excel curricular (import/export), Excel aula (descarga), legacy API | Importación **SIAGIE** global; REQ-01.x completos |
| **RF-05** Variables socioeconómicas | API bajo estudiante | UI **pausada** (pestaña no expuesta) |
| **RF-06** Procesamiento ML | Laravel → Flask determinístico; comando batch | Ensemble RF/SVM/XGBoost; UI procesar riesgo en perfil |
| **RF-11** Atención psicológica | Alertas + permisos psicólogo | Perfil integrado; depende RF-10 |
| **RF-13** Cierre alerta | Cierre vía intervención | Cierre vía derivación o comunicación familiar |
| **RF-14** Dashboard | KPIs básicos, filtros parciales | Multi-sede directivo, PNG, % alertas REQ-14.5 |
| **RF-16** Exportación | PDF dashboard; Excel aula `.xlsx` | PDF individual/aula completo REQ-16.x |
| **RF-17** Auditoría | `activity_log` parcial en controladores | UI consulta logs; cobertura REQ-17.x |
| **RF-20** Historial riesgo | Persistencia `indices_riesgo` | Timeline UI; export PDF historial |

### 2.3 Alcance pendiente o no confirmado

| RF | Estado V1 |
|----|-----------|
| RF-03 Importación Fast Test | **Pendiente** — sin ruta API |
| RF-04 Reportes conductuales | **Pendiente** — migración sin API |
| RF-10 Derivación directivo | **Pendiente** — sin rutas API |
| RF-12 Comunicación familiar | **Pendiente** — tabla sin API |
| RF-18 Reentrenamiento ML | **Pendiente** — sin endpoint |
| RF-19 Semáforo completitud | **Pendiente** — sin UI/lógica explícita |
| Cypress / E2E | **No confirmado** — no existe en repo |
| Despliegue productivo | **Pendiente** — solo Docker local |
| Certificación ISO | **No aplica V1** — referencia académica únicamente |

### 2.4 Fuera del alcance V1

- Operación **multi-sede activa** (selector sedes, mapa consolidado todas las sedes).
- **Random Forest / SVM / XGBoost** entrenados y reentrenamiento automático (RF-18).
- **Importación SIAGIE** institucional automática.
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
| **VSE** | Variables socioeconómicas del estudiante |
| **SIAGIE** | Sistema de información académico institucional peruano — importación **pendiente** en V1 |
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
| **psicologo_tutor** | Seguimiento alertas e intervenciones | Alertas, intervenciones, lectura académica/asistencia | Solo módulo alertas visible | Sin RF-11 perfil integrado completo |
| **directivo** | Visión consolidada riesgo | Dashboard, alertas, lectura malla/notas/asistencia | Dashboard subset; excepción UI notas semanales | Sin mapa multi-sede REQ-14.2 |

**Principio RN-05:** el backend valida permisos **antes** de procesar; el frontend solo oculta menú.

---

## 8. Requerimientos funcionales actualizados

**Criterios de estado V1:** Confirmado · Implementado parcialmente · Pendiente · No confirmado · Fuera de V1

Estados derivados de [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) (verificación código 2026-06-09).

---

### RF-01 — Carga e importación de datos académicos

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** El prototipo V1 permite carga manual de notas semanales (UI + bulk API), registro curricular completo, **plantilla Excel curricular** (descarga + importación por curso/asignación) y **Excel por aula** (descarga multi-hoja). Existe API legacy de notas/asistencia por estudiante y lote **sin menú V1**. La importación masiva **SIAGIE** (.xlsx/.csv institucional global) **no está implementada**.
- **Actor(es):** Docente, Administrador, Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** `/api/curricular/notas-semanales/*`, `GET/POST plantilla-excel` / `importar-excel`, `GET /excel-aula`, `PlantillaRegistroAuxiliarExcelService.php`, paneles curriculares en `App.jsx`
- **Evidencia de prueba:** `DatosAcademicosTest`, `PlantillaRegistroAuxiliarExcelTest`, `CurricularApiTest`, `NotasSemanales*`, `ExcelAulaTest` (8 passed @ 512M)
- **Brechas / pendientes:** SIAGIE pendiente; `ImportarDatosTest` planeado/no encontrado; Excel aula **solo descarga** (no import); suite OOM @ 128M
- **Observaciones para versión futura:** Implementar pipeline SIAGIE con validación RN-08; no confundir plantilla curricular con SIAGIE ([`aula-notas-excel.md`](../aula-notas-excel.md) §3)

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

- **Estado V1:** Pendiente
- **Descripción actualizada:** Sin ruta API dedicada ni UI visible. Requerimiento definido en DRS v1; no implementado en V1.
- **Actor(es):** Coordinador académico
- **Prioridad:** Alta
- **Evidencia de implementación:** —
- **Evidencia de prueba:** —
- **Brechas / pendientes:** Implementación completa RF-03; tests `FastTestImportTest` planeados/no encontrados
- **Observaciones para versión futura:** Backlog post-V1; vincular a RF-19 semáforo

---

### RF-04 — Registro digital de reportes conductuales

- **Estado V1:** Pendiente
- **Descripción actualizada:** Existe migración/modelo `reportes_conductuales`; **sin rutas API** ni UI en V1.
- **Actor(es):** Psicólogo / Tutor
- **Prioridad:** Alta
- **Evidencia de implementación:** Migración BD únicamente
- **Evidencia de prueba:** —
- **Brechas / pendientes:** API, UI, tests conductuales
- **Observaciones para versión futura:** Requerido para RN-02 completitud y RF-19

---

### RF-05 — Integración de variables socioeconómicas

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** API anidada `GET/POST /api/estudiantes/{id}/variables-socioeconomicas` operativa. **Pestaña UI pausada** — `EstudiantesPanel` no expone VSE al perfil.
- **Actor(es):** Administrador, Sistema
- **Prioridad:** Alta
- **Evidencia de implementación:** `VariableSocioeconomicaController`, rutas api.php
- **Evidencia de prueba:** `DatosAcademicosTest` (API)
- **Brechas / pendientes:** UI oculta; RN-02 completitud VSE no visible al docente
- **Observaciones para versión futura:** Reactivar pestaña o documentar carga solo vía API/admin

---

### RF-06 — Procesamiento multivariable e índice de riesgo

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** Laravel invoca Flask vía `POST /api/estudiantes/{id}/procesar-riesgo` y `MlRiskService`. El servicio ML V1 es **determinístico** (fórmula ponderada), **no** ensemble Random Forest/SVM/XGBoost del DRS v1. Comando batch `DemoProcesarRiesgosCommand` disponible. **UI perfil riesgo en pausa** — sin botón procesar visible.
- **Actor(es):** Sistema, Coordinador académico (permiso `procesar_riesgo`)
- **Prioridad:** Alta
- **Evidencia de implementación:** `RiesgoAcademicoService`, `ml-service/main.py`, `EstudiantePerfilRiesgo.jsx` (pausado)
- **Evidencia de prueba:** `RiesgoTest`, `DemoProcesarRiesgosCommandTest` (no re-ejecutados Fase 5)
- **Brechas / pendientes:** No RF/SVM/XGBoost; UI procesar ausente; RN-02 bloqueo semáforo no confirmado
- **Observaciones para versión futura:** Sustituir prototipo determinístico por pipeline ML real (fuera V1)

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

### RF-10 — Decisión de derivación por el directivo

- **Estado V1:** Pendiente
- **Descripción actualizada:** Sin rutas API ni UI. RF-11 y cierre RF-13 completo dependen parcialmente de este flujo.
- **Actor(es):** Directivo
- **Prioridad:** Alta
- **Evidencia de implementación:** —
- **Evidencia de prueba:** —
- **Brechas / pendientes:** Implementación completa RF-10
- **Observaciones para versión futura:** Backlog prioritario para cierre alertas DRS

---

### RF-11 — Atención psicológica preventiva con perfil integrado

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** Psicólogo accede a **Alertas** e intervenciones con permisos dedicados. **No** existe perfil estudiante integrado ni flujo derivación (RF-10).
- **Actor(es):** Psicólogo / Tutor
- **Prioridad:** Media
- **Evidencia de implementación:** Permisos psicólogo, `AlertasPanel`
- **Evidencia de prueba:** `AlertaIntervencionTest` (parcial)
- **Brechas / pendientes:** RF-10; perfil integrado REQ-11.x
- **Observaciones para versión futura:** Módulo psicología con vista 360° estudiante

---

### RF-12 — Comunicación formal y trazable con la familia

- **Estado V1:** Pendiente
- **Descripción actualizada:** Tabla `comunicaciones_familiares` en migraciones; **sin API** ni UI.
- **Actor(es):** Docente, Directivo
- **Prioridad:** Media
- **Evidencia de implementación:** Esquema BD únicamente
- **Evidencia de prueba:** —
- **Brechas / pendientes:** RF-12 completo; prerrequisito cierre RF-13 según DRS v1
- **Observaciones para versión futura:** —

---

### RF-13 — Registro de acción tomada y cierre de alerta

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** Cierre vía `POST /api/alertas/{id}/cerrar` tras intervención confirmada en tests. DRS v1 admite cierre también por derivación o comunicación familiar — **no implementados**.
- **Actor(es):** Docente, Directivo, Psicólogo
- **Prioridad:** Alta
- **Evidencia de implementación:** Rutas alertas, `AlertasPanel`
- **Evidencia de prueba:** `AlertaIntervencionTest`
- **Brechas / pendientes:** RF-10, RF-12 como vías de cierre; RN-04 completa
- **Observaciones para versión futura:** Ampliar reglas cierre alineadas a RN-04/REQ-13.1

---

### RF-14 — Panel de visualización (dashboard) de riesgo

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** Dashboard con KPIs de riesgo y filtros básicos (`GET /api/dashboard`). **No** equivale a REQ-14.1–14.5 completos: sin mapa multi-sede directivo, export PNG, % alertas activas/cerradas.
- **Actor(es):** Docente, Directivo
- **Prioridad:** Alta
- **Evidencia de implementación:** `DashboardController`, `DashboardPanel`
- **Evidencia de prueba:** `DashboardTest`
- **Brechas / pendientes:** Multi-sede fuera V1; REQ-14.4 PNG; REQ-14.5 % alertas
- **Observaciones para versión futura:** Dashboard por sede cuando multi-sede esté activo

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

### RF-16 — Exportación de reportes en PDF

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** Export PDF del **dashboard** vía `GET /api/dashboard/export` (DomPDF). **Excel por aula** (`GET /excel-aula`) genera `.xlsx` — **distinto** de PDF REQ-16 (reporte riesgo individual/aula). No confundir formatos.
- **Actor(es):** Docente, Directivo
- **Prioridad:** Media
- **Evidencia de implementación:** `DashboardController` export, `PlantillaRegistroAuxiliarExcelService` (Excel)
- **Evidencia de prueba:** `DashboardTest`, `ExcelAulaTest`, `ActivityLogTest` (registro export)
- **Brechas / pendientes:** PDF individual/aula REQ-16.x; logo institucional completo
- **Observaciones para versión futura:** Ver [`aula-notas-excel.md`](../aula-notas-excel.md) §11

---

### RF-17 — Registro de auditoría de acciones

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** `spatie/laravel-activitylog` registra acciones en controladores críticos (estudiantes, riesgo, alertas, export PDF, curricular parcial). **Sin UI** de consulta de logs.
- **Actor(es):** Sistema
- **Prioridad:** Alta
- **Evidencia de implementación:** Activitylog en controladores; tabla `activity_log`
- **Evidencia de prueba:** `ActivityLogTest` (parcial)
- **Brechas / pendientes:** Cobertura REQ-17.x; retención 5 años no verificada operativamente; RN-07 completa
- **Observaciones para versión futura:** UI auditoría admin; extender logging

---

### RF-18 — Reentrenamiento del modelo ML

- **Estado V1:** Pendiente
- **Descripción actualizada:** Sin endpoints en Laravel ni Flask para reentrenamiento. RN-06 no aplicable en V1. Prototipo ML determinístico no entrenable.
- **Actor(es):** Administrador
- **Prioridad:** Media
- **Evidencia de implementación:** —
- **Evidencia de prueba:** —
- **Brechas / pendientes:** RF-18 completo; métricas accuracy/precision/recall/F1
- **Observaciones para versión futura:** Fuera alcance V1; requiere pipeline ML real

---

### RF-19 — Semáforo de completitud de datos

- **Estado V1:** Pendiente
- **Descripción actualizada:** Sin componente UI ni lógica explícita de semáforo verde/amarillo/rojo. Bloqueo de procesamiento ML por datos faltantes (REQ-19.3) **no confirmado**.
- **Actor(es):** Docente, Administrador
- **Prioridad:** Media
- **Evidencia de implementación:** —
- **Evidencia de prueba:** —
- **Brechas / pendientes:** RF-19 completo; RN-02/RNF-10 bloqueo
- **Observaciones para versión futura:** Dependiente de RF-03, RF-04, RF-05 UI

---

### RF-20 — Historial de riesgo por estudiante

- **Estado V1:** Implementado parcialmente
- **Descripción actualizada:** Persistencia en tabla `indices_riesgo`. Perfil riesgo **UI pausado** — sin timeline ni export PDF de historial.
- **Actor(es):** Docente, Directivo
- **Prioridad:** Media
- **Evidencia de implementación:** Migración `indices_riesgo`, `ResumenAcademicoTest`, `ActivoUniqueKeyHistorialTest`
- **Evidencia de prueba:** `RiesgoTest` (persistencia)
- **Brechas / pendientes:** Timeline UI; export PDF historial REQ-20
- **Observaciones para versión futura:** Reactivar `EstudiantePerfilRiesgo.jsx`

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
| Integridad datos (RNF-10) | Validación pre-ML | Validaciones API parciales | Evidencia parcial | RF-19 semáforo pendiente |

---

## 10. Reglas de negocio

Transcripción/resumen DRS v1 con **estado V1** ([`contexto-drs-requerimientos.md`](../arquitectura/contexto-drs-requerimientos.md) §4).

| ID | Regla | Estado V1 |
|----|-------|-----------|
| **RN-01** | Umbrales Alto ≥ 0,70; Medio 0,40–0,69; Bajo &lt; 0,40; configurables por admin | Umbrales en código **confirmados**; configuración admin **pendiente** |
| **RN-02** | Completitud mínima (asistencia, notas bimestrales, VSE) para ML; semáforo bloquea | **Parcial** — VSE API sí, UI no; semáforo RF-19 pendiente |
| **RN-03** | Alerta si índice ≥ Alto **o** ascenso Bajo→Medio dos bimestres | Umbral Alto **confirmado**; segundo disparador **pendiente verificar** |
| **RN-04** | Cierre alerta con intervención **o** derivación; activity_log | Intervención/cierre **confirmado**; derivación **pendiente** |
| **RN-05** | Segregación acceso por rol; backend valida Spatie | **Confirmado** |
| **RN-06** | Reentrenamiento ML inicio año escolar; solo admin | **Pendiente** (RF-18) |
| **RN-07** | Trazabilidad acciones en activity_log; no eliminar logs | **Parcial** (RF-17) |
| **RN-08** | Import .xlsx/.csv con validación y mensajes | Plantilla curricular **confirmada**; SIAGIE **pendiente** |

**Reglas operativas V1 adicionales:**

- Sede operativa **Chilca**; campo `sede` conservado; **no** multi-sede activa.
- Excel aula = **descarga**; import curricular = **plantilla por curso** — no SIAGIE.
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
| SIAGIE automático | Import institucional | **Pendiente** | RF-01 brecha |
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
| **variables_socioeconomicas** | VSE por estudiante | API confirmada; UI pausada |
| **indices_riesgo** | Historial scores ML | Confirmado |
| **alertas** | Alertas tempranas | Confirmado |
| **intervenciones** | Acciones docentes sobre alertas | Confirmado |
| **reportes_conductuales** | Esquema BD | Pendiente API |
| **comunicaciones_familiares** | Esquema BD | Pendiente API |
| **activity_log** | Auditoría Spatie | Parcial |

---

## 13. Seguridad y control de acceso

Consolidado de [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md).

| Elemento | Detalle V1 |
|----------|------------|
| Autenticación | Laravel Sanctum + Breeze; CSRF cookie SPA |
| Autorización | Spatie Permission — **5 roles**, **23 permisos** |
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
| SIAGIE | **Pendiente** — no confundir con plantilla Excel |
| ML | **Determinístico** — sin RF/SVM/XGBoost |
| Reentrenamiento | **Pendiente** — RF-18 |
| Cypress | **Ausente** |
| Suite PHPUnit | OOM @ **128M**; Excel @ **512M** |
| Seed oficial | **Pendiente** — conteos locales no oficiales |
| VSE | UI **pausada** |
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
| NC-02 | SIAGIE pendiente | RF-01 | Alta | Backlog; documentar diferencia Excel |
| NC-03 | ML determinístico vs ensemble | RF-06, RF-18 | Alta | DRS futuro; pipeline ML real |
| NC-04 | Reentrenamiento ML | RF-18 | Media | Post-V1 |
| NC-05 | Sin Cypress | RNF-04/05 | Media | Smoke manual; Cypress opcional |
| NC-06 | Suite OOM 128M | RF-01, RF-16 | Media | `memory_limit=512M` en CI |
| NC-07 | Seed oficial | RF-01 | Media | Entorno referencia documentado |
| NC-08 | Activity log parcial | RF-17 | Media | Extender logging + UI |
| NC-09 | Register público | RF-15 | Alta (prod) | Deshabilitar pre-producción |
| NC-10 | Multi-sede no activa | RF-14 | Media | Documentado — Chilca V1 |
| NC-11 | UI riesgo pausada | RF-06, RF-20 | Media | Reactivar UI o comando técnico |
| NC-12 | VSE pausada | RF-05 | Media | Reactivar pestaña |
| NC-13 | RF-10, RF-12, RF-19 | RF-10–13, RF-19 | Alta | Backlog explícito |
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
9. **Pruebas documentadas** con limitaciones conocidas (OOM, sin Cypress, BD no seed oficial).
10. **Documentación consolidada** Fases 1–7 incluyendo este DRS v2, matrices ISO progresivas y registro NC.
11. **No se exige** certificación ISO, SIAGIE, ensemble ML, multi-sede ni suite PHPUnit 100% verde @ 128M.

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

*Documento generado en Fase 7 del plan de actualización documental SIDERAE-Blenkir. Fecha de referencia técnica: 2026-06-09.*
