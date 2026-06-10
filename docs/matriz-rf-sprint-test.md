# Matriz RF–Sprint–Test — SIDERAE-Blenkir

Documento vigente (Fase 5 documental). Fecha de verificación en código: **2026-06-09**. **No** implica ejecución de pruebas en esta fase salvo lo ya registrado en [`docs/pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md).

Referencias cruzadas: [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md) · [`docs/limitaciones.md`](limitaciones.md) · [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md) · [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) · [`docs/aula-notas-excel.md`](aula-notas-excel.md) · [`docs/calidad/alineacion-iso.md`](calidad/alineacion-iso.md) · [`docs/arquitectura/contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md).

---

## 1. Propósito

Esta matriz relaciona los **requerimientos funcionales (RF-01–RF-20)** del DRS formal con:

- Sprints de planificación del repositorio (`sprints/`),
- Evidencia de **código y rutas** (`backend/routes/api.php`, UI en `frontend/src/App.jsx`),
- **Pruebas automatizadas** detectadas en `backend/tests/`,
- Estado honesto **V1** (prototipo académico, sede operativa Chilca).

Sirve para trazabilidad académica y como insumo para actualizar el DRS separando alcance formal de implementación real.

---

## 2. Criterios de estado

| Estado | Significado |
|--------|-------------|
| **Confirmado en código** | Rutas, UI o tests demuestran la funcionalidad principal del RF en V1. |
| **Implementado parcialmente** | Solo parte de los REQ del DRS está operativa o visible. |
| **Pendiente** | Definido en DRS/sprints; sin equivalencia suficiente en código. |
| **No confirmado** | Mencionado en documentación; sin verificación en esta revisión. |
| **Planeado/no encontrado** | Documento de prueba o sprint lo prevé; archivo o suite **no existe** en repo. |
| **Histórico** | Planificación o matriz anterior; no refleja solo el estado V1. |
| **No aplica V1** | Fuera del alcance operativo V1 (p. ej. multi-sede activa, certificación ISO). |

**Resultado de prueba:** solo se documenta ejecución **conocida** (Fase 1). No usar «aprobado» sin evidencia de corrida completa.

---

## 3. Fuentes utilizadas

| Ruta | Uso |
|------|-----|
| `DRS_SIDERAE_Blenkir_v1.pdf` | **No presente en el repositorio** — nombres RF vía [`contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md) |
| [`docs/limitaciones.md`](limitaciones.md) | Alcance V1 vs DRS |
| [`docs/pruebas/hallazgos-fase1-documentacion.md`](pruebas/hallazgos-fase1-documentacion.md) | Comandos y conteos Fase 1 |
| [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md) | Permisos, 401/403 |
| [`docs/manual-usuario.md`](manual-usuario.md) | Flujos visibles por rol |
| [`docs/api.md`](api.md) | Catálogo endpoints |
| [`backend/routes/api.php`](../backend/routes/api.php) | Evidencia rutas |
| [`backend/tests/`](../backend/tests/) | Pruebas PHPUnit |
| [`frontend/src/App.jsx`](../frontend/src/App.jsx) | Módulos UI |
| [`ml-service/main.py`](../ml-service/main.py) | ML determinístico |
| [`docker-compose.yml`](../docker-compose.yml) | Infra local |
| `sprints/sprint 1.md` … `sprints/sprint 10.md` (+ sub-sprints 3A/B, 6A/B, 7.x, 8.5x) | Planificación |

---

## 4. Advertencias de lectura

- **V1 Chilca:** operación documentada con sede única; sin selector multi-sede en UI.
- **Auquimarca:** registros en BD local auditada = **histórico/local**; no operación multi-sede V1.
- **BD auditada Fase 1:** conteos **no** equivalen a seed oficial limpio (`migrate:fresh --seed` no ejecutado en Fase 1).
- **Suite PHPUnit completa:** falló por **OOM** con `memory_limit=128M` en `ExcelAulaTest` (Fase 1).
- **`ExcelAulaTest` aislado:** **8 passed**, 32 assertions con `memory_limit=512M` (Fase 1).
- **Cypress:** **no existe** en el repositorio.
- **ML:** servicio Flask **determinístico**; no RF/SVM/XGBoost entrenados; no reentrenamiento (RF-18).
- **ISO:** referencia académica orientativa únicamente; **sin certificación**.

---

## 5. Matriz principal RF–Sprint–Código–Prueba

Nombres RF según DRS (tabla §3 de [`contexto-drs-requerimientos.md`](arquitectura/contexto-drs-requerimientos.md)).

| RF | Nombre / funcionalidad | Sprint relacionado | Estado funcional V1 | Evidencia código/ruta | Evidencia UI/manual | Prueba automatizada | Resultado conocido | Observación |
|----|------------------------|-------------------|---------------------|----------------------|---------------------|---------------------|-------------------|-------------|
| RF-01 | Carga e importación de datos académicos | 3B, 7.6B, 8.5B | Implementado parcialmente | `/api/notas/*`, `/api/asistencias/*`, `/api/curricular/notas-semanales/*`, `importar-excel`, `GET /excel-aula` | Notas/asistencia curricular; plantilla Excel curso (import); Excel aula (solo descarga); legacy sin menú | `DatosAcademicosTest`, `PlantillaRegistroAuxiliarExcelTest`, `CurricularApiTest`, `NotasSemanales*`, `ExcelAulaTest` | Parcial — `ExcelAulaTest` 8 passed @ 512M; suite OOM @ 128M | **SIAGIE** pendiente; ver [`aula-notas-excel.md`](aula-notas-excel.md). Import curricular ≠ SIAGIE |
| RF-02 | Registro digital de asistencia semanal | 3B, 8.5B | Confirmado en código (curricular) | `/api/curricular/asistencias-diarias/*` | Menú **Asistencia** | `AsistenciaDiariaTest` | No ejecutado en Fase 5; existe test | Legacy lote en API sin menú V1 |
| RF-03 | Importación resultados Fast Test | — | Pendiente | Sin ruta API dedicada | No visible | — | — | Planeado en DRS; no implementado |
| RF-04 | Registro reportes conductuales | — | Pendiente | Migración `reportes_conductuales`; sin API | No visible | — | — | Implementado sin prueba automatizada confirmada |
| RF-05 | Integración variables socioeconómicas | 3B | Implementado parcialmente | `/api/estudiantes/{id}/variables-socioeconomicas` | **Pestaña pausada** en perfil | `DatosAcademicosTest` (API) | Parcial | UI oculta — ver [`manual-usuario.md`](manual-usuario.md) §15 |
| RF-06 | Procesamiento multivariable e índice de riesgo | 4, 8.5B | Implementado parcialmente | `POST …/procesar-riesgo`, `MlRiskService`, `ml-service` `/predict` | Perfil riesgo **en pausa** (sin botón procesar) | `RiesgoTest`, `DemoProcesarRiesgosCommandTest` | `RiesgoTest` no re-ejecutado Fase 5 | ML **determinístico**; no ensemble DRS |
| RF-07 | Evaluación automática nivel de riesgo | 4, 5 | Confirmado en código (parcial DRS) | Umbrales en servicio riesgo | Dashboard KPIs riesgo | `RiesgoTest` | No re-ejecutado Fase 5 | REQ configurables admin: pendiente verificar |
| RF-08 | Emisión alertas tempranas | 5 | Confirmado en código | `/api/alertas`, generación post-riesgo | **Alertas** | `RiesgoTest`, `AlertaIntervencionTest` | No re-ejecutado Fase 5 | RN-03 completa (dos disparadores): pendiente verificar |
| RF-09 | Intervención preventiva docente | 5 | Confirmado en código | `POST /api/alertas/{id}/intervenciones` | Alertas → detalle | `AlertaIntervencionTest` | No re-ejecutado Fase 5 | — |
| RF-10 | Decisión derivación directivo | — | Pendiente | Sin rutas API | No visible | — | — | RF-11 depende de esto |
| RF-11 | Atención psicológica perfil integrado | 5, 8 | Implementado parcialmente | Alertas + permisos psicólogo | Solo **Alertas** + asistencia consulta | `AlertaIntervencionTest` (parcial) | — | Sin perfil estudiante integrado para psicólogo |
| RF-12 | Comunicación formal con familia | — | Pendiente | Tabla `comunicaciones_familiares`; sin API | No visible | — | — | — |
| RF-13 | Registro acción y cierre alerta | 5 | Implementado parcialmente | `POST …/cerrar` | Alertas | `AlertaIntervencionTest` | No re-ejecutado Fase 5 | DRS admite cierre vía derivación/comunicación; solo intervención confirmada |
| RF-14 | Dashboard de riesgo | 6A, 6B, 7A | Implementado parcialmente | `GET /api/dashboard` | **Dashboard** | `DashboardTest` | No re-ejecutado Fase 5 | Sin multi-sede directivo; sin PNG; subset REQ-14 |
| RF-15 | Gestión usuarios y control acceso | 2, 8 | Confirmado en código | `/api/usuarios`, Spatie, `/api/me` | **Usuarios** (admin) | `GestionUsuariosTest`, `AuthenticationTest`, múltiples 403 | Parcial 401 usuarios | Matriz: [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) |
| RF-16 | Exportación reportes PDF | 6B | Implementado parcialmente | `GET /api/dashboard/export` | Botón **Exportar PDF** dashboard | `DashboardTest`, `ActivityLogTest` | No re-ejecutado Fase 5 | PDF dashboard parcial REQ-16; **Excel aula** (`GET /excel-aula`) es `.xlsx` distinto — [`aula-notas-excel.md`](aula-notas-excel.md) |
| RF-17 | Log auditoría / trazabilidad | 7.5A, 8 | Implementado parcialmente | `spatie/laravel-activitylog` | Sin UI consulta logs | `ActivityLogTest` | No re-ejecutado Fase 5 | Cobertura parcial; no auditoría completa |
| RF-18 | Reentrenamiento modelo ML | — | Pendiente | Sin endpoint ML/Laravel | No visible | — | — | No aplica prototipo V1 actual |
| RF-19 | Semáforo completitud datos | — | Pendiente | Sin componente UI/lógica explícita | No visible | — | — | Bloqueo ML por semáforo: no confirmado |
| RF-20 | Historial riesgo / completitud (DRS) | 4, 6A | Implementado parcialmente | Tabla `indices_riesgo` | Perfil riesgo **pausado** | `RiesgoTest` (persistencia) | — | Sin timeline/export PDF historial |

---

## 6. Matriz por Sprint

Incluye sub-sprints documentados en `sprints/` vinculados al sprint principal. Estado = implementación + tests **detectados**, no cierre formal de sprint.

| Sprint | Objetivo documentado | Funcionalidad asociada | Evidencia implementación | Tests asociados | Estado | Observación |
|--------|----------------------|------------------------|--------------------------|-----------------|--------|-------------|
| **1** | Docker + health checks | Compose 4 servicios, `/api/health`, ML `/` | `docker-compose.yml`, `api.php` L34–38 | `ExampleTest` | Confirmado en código | Healthcheck solo MySQL; backend/ML sin healthcheck Compose |
| **2** | Login + `/api/me` + RBAC mínimo | Sanctum, Breeze, Spatie seed | `auth.php`, `AuthContext`, seeders | `AuthenticationTest`, `RegistrationTest`, `EmailVerificationTest`, `PasswordResetTest` | Confirmado en código | `POST /register` público — brecha prototipo |
| **3A** | CRUD estudiantes + perfil | Estudiantes API/UI | `EstudianteController`, `EstudiantesPanel` | `EstudianteTest`, `EstudianteInicialTest` | Confirmado en código | V1 Chilca default |
| **3B** | Datos base + validaciones | Notas/asistencia legacy, VSE API | Rutas legacy L98–111 | `DatosAcademicosTest` | Implementado parcialmente | Legacy sin menú V1 |
| **4** | Laravel → ML + persistencia riesgo | `procesar-riesgo`, Flask | `MlRiskService`, `RiesgoTest` | `RiesgoTest`, `DemoProcesarRiesgosCommandTest` | Implementado parcialmente | UI procesar riesgo ausente |
| **5** | Alertas + intervención + cierre | Alertas API/UI | `AlertaController`, `AlertasPanel` | `AlertaIntervencionTest` | Confirmado en código | Sin derivación RF-10 |
| **6A** | Dashboard mínimo KPIs | Dashboard API/UI | `DashboardController`, `DashboardPanel` | `DashboardTest` | Implementado parcialmente | Subset RF-14 |
| **6B** | Filtros + export PDF + rol | Export dashboard | `GET /api/dashboard/export` | `DashboardTest` | Implementado parcialmente | Parcial RF-16 |
| **7A** | Rediseño UI/UX | Layout, sidebar, tokens | `AppLayout`, `Sidebar`, `App.jsx` | — | Confirmado en código | Sin tests E2E |
| **7B** | Pantallas según mockups | Paneles módulos | `frontend/src/components/*` | — | Implementado parcialmente | Mockups legacy ≠ curricular completo |
| **7.5A/B** | Correcciones P0 / visual | Fixes funcionales | Código disperso | Tests regresión existentes | Parcial | Documentado en sprints |
| **7.6A/B** | Materias admin + registro masivo | Legacy materias/lotes | API; UI sin menú | `MateriaTest`, `DatosAcademicosTest` | Implementado parcialmente | Fuera flujo visible V1 |
| **8** | Seguridad RBAC + 401/403 | Middleware `permission:*` | `api.php`, `PermissionsSeeder` | `DashboardTest`, `EstudianteTest`, `GestionUsuariosTest`, `CurricularApiTest`, etc. | Implementado parcialmente | Matriz vigente: `seguridad-roles-permisos.md`; Sprint 8 matriz: histórico |
| **8.5A** | Backend curricular + seeders | `/api/curricular/*`, migraciones | Rutas L128–282 | `CurricularSeedersTest`, `CurricularApiTest` | Confirmado en código | — |
| **8.5B** | API/UI curricular + asignación | Notas, asistencia, malla UI | Paneles `curricular/*` | `AsistenciaDiariaTest`, `AsignacionDocenteValidacionesTest`, `NotasSemanales*` | Confirmado en código | — |
| **8.5C** | Evaluación bimestral | Config bimestral, CE | `EvaluacionBimestral*` | `EvaluacionBimestralApiTest`, `EvaluacionBimestralTest` | Confirmado en código | — |
| **9** | Pruebas integrales + regresión | Campaña pytest + Cypress planeado | — | Ejecución Fase 1 documentada | Implementado parcialmente | **Cypress planeado/no encontrado**; suite OOM 128M |
| **10** | Documentación + cierre calidad | Manuales, matriz RF, informe | `docs/*` Fases 1–5 | Informe consolidado Fase 5 | En progreso | ISO solo referencia; sin certificación |

---

## 7. Matriz de pruebas backend detectadas

Archivos en [`backend/tests/`](../backend/tests/) (49 archivos `.php` detectados). **Resultado conocido** solo si Fase 1 lo registró.

### Auth

| Archivo test | Casos relevantes | RF | Módulo | Estado | Observación |
|--------------|------------------|-----|--------|--------|-------------|
| `Feature/Auth/AuthenticationTest.php` | login, logout | RF-15 | Auth | Detectado | No re-ejecutado Fase 5 |
| `Feature/Auth/RegistrationTest.php` | registro público | RF-15 | Auth | Detectado | Brecha producción |
| `Feature/Auth/EmailVerificationTest.php` | verificación email | RF-15 | Auth | Detectado | — |
| `Feature/Auth/PasswordResetTest.php` | reset password | RF-15 | Auth | Detectado | UI recuperación pendiente |

### Estudiantes y académico legacy

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `Feature/EstudianteTest.php` | RF-01, RF-15 | Estudiantes | Detectado | 401/403 confirmados en doc seguridad |
| `Feature/EstudianteInicialTest.php` | RF-01 | Estudiantes inicial | Detectado | — |
| `Feature/DatosAcademicosTest.php` | RF-01, RF-05 | Legacy notas/asist/VSE | Detectado | API legacy |
| `Feature/MateriaTest.php` | RF-01 | Materias legacy | Detectado | Sin menú V1 |

### Dashboard, riesgo, alertas

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `Feature/DashboardTest.php` | RF-14, RF-16 | Dashboard | Detectado | Export PDF |
| `Feature/RiesgoTest.php` | RF-06, RF-07, RF-08, RF-20 | Riesgo | Detectado | — |
| `Feature/AlertaIntervencionTest.php` | RF-08, RF-09, RF-13 | Alertas | Detectado | — |
| `Feature/DemoProcesarRiesgosCommandTest.php` | RF-06 | Comando batch | Detectado | Operación técnica, no UI |
| `Feature/ActivityLogTest.php` | RF-17 | Auditoría | Detectado | Parcial |

### Usuarios y seguridad

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `Feature/GestionUsuariosTest.php` | RF-15 | Usuarios | Detectado | 403 sí; 401 pendiente |

### Curricular (`Feature/Curricular/`)

| Archivo test | RF | Módulo | Estado | Observación |
|--------------|-----|--------|--------|-------------|
| `CurricularApiTest.php` | RF-01, RF-15 | API curricular | Detectado | 401/403 |
| `AsistenciaDiariaTest.php` | RF-02 | Asistencia | Detectado | — |
| `ExcelAulaTest.php` | RF-01, RF-16 | Excel aula | Detectado | **8 passed @ 512M** Fase 1; OOM @ 128M en suite |
| `PlantillaRegistroAuxiliarExcelTest.php` | RF-01 | Import plantilla | Detectado | Memoria elevada posible |
| `EvaluacionBimestralApiTest.php` | RF-01 | Eval bimestral | Detectado | — |
| `EvaluacionBimestralTest.php` | RF-01 | Eval bimestral | Detectado | — |
| `NotasSemanalesInicialTest.php` | RF-01 | Notas | Detectado | — |
| `NotasSemanalesComponentesDinamicosTest.php` | RF-01 | Notas dinámicas | Detectado | — |
| `AsignacionDocenteValidacionesTest.php` | RF-01 | Asignación | Detectado | — |
| `SeccionesAulasTest.php` | RF-01 | Secciones | Detectado | — |
| `CalendarioAcademicoTest.php` | RF-01 | Calendario | Detectado | — |
| `CompetenciaCapacidadCrudTest.php` | RF-01 | Competencias | Detectado | — |
| `ComponentesCalificacionNivelTest.php` | RF-01 | Componentes | Detectado | — |
| `ConfiguracionPesoEvaluacionTest.php` | RF-01 | Pesos (UI oculta) | Detectado | — |
| `ConfiguracionBimestralGradoTest.php` | RF-01 | Config bimestral | Detectado | — |
| `ConfiguracionBimestralDefaultsTest.php` | RF-01 | Config bimestral | Detectado | — |
| `ResumenAcademicoTest.php` | RF-01, RF-20 | Resumen | Detectado | — |
| `CurricularSeedersTest.php` | — | Seeders | Detectado | — |
| `ActivoUniqueKeyHistorialTest.php` | RF-20 | Historial | Detectado | — |

### Seeders y Unit

| Archivo test | RF | Estado | Observación |
|--------------|-----|--------|-------------|
| `Feature/Seeders/DemoEstudiantesCurricularesSeederTest.php` | RF-01 | Detectado | Solo Chilca V1 |
| `Feature/Seeders/DemoCurricularOperativoSeederTest.php` | RF-01 | Detectado | — |
| `Feature/Seeders/InicialIIBimestre2026SeederTest.php` | RF-01 | Detectado | — |
| `Feature/Seeders/CriteriosEvaluacionInicialSeederTest.php` | RF-01 | Detectado | — |
| `Unit/Curricular/*` | RF-01, RF-06 | Detectado | Servicios CE, pesos, equivalencias |
| `Feature/ExampleTest.php`, `Unit/ExampleTest.php` | — | Detectado | Smoke |

---

## 8. Pruebas planeadas pero no encontradas

| Caso / documento | RF | Archivo esperado | Estado | Observación |
|------------------|-----|------------------|--------|-------------|
| [`Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md`](pruebas/Fichas_Pruebas_Automatizadas_SIDERAE_Blenkir.md) | RF-01 | `ImportarDatosTest` | **Planeado/no encontrado** | Usar `PlantillaRegistroAuxiliarExcelTest`, `DatosAcademicosTest` |
| [`sprint 9.md`](../sprints/sprint%209.md) | Varios | Suite **Cypress** | **Planeado/no encontrado** | Sin carpeta `cypress/` |
| Plan de pruebas / Sprint 9 | RF-01 | Importación **SIAGIE** | **Pendiente** | Sin ruta ni test |
| Sprint 8 / seguridad | RF-15 | 401 en **todas** rutas `/api/curricular/*` | **Parcial** | Ver [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) §12 |
| DRS RF-03 | RF-03 | `FastTestImportTest` o similar | **Planeado/no encontrado** | — |
| DRS RF-04 | RF-04 | Tests reportes conductuales | **Planeado/no encontrado** | Solo migración |
| DRS RF-10–12 | RF-10–12 | Tests derivación/comunicación | **Planeado/no encontrado** | — |
| DRS RF-18 | RF-18 | Tests reentrenamiento ML | **Planeado/no encontrado** | — |
| DRS RF-19 | RF-19 | Tests semáforo completitud | **Planeado/no encontrado** | — |
| RNF-05 Jest frontend | — | Tests Jest/React | **No confirmado** | Sin suite frontend detectada |

---

## 9. Brechas de trazabilidad

| Brecha | RF afectado | Impacto | Recomendación | Prioridad |
|--------|-------------|---------|---------------|-----------|
| DRS PDF fuera del repo | Todos | Tribunal no contrasta desde repo | Mantener `contexto-drs-requerimientos.md` actualizado | Media |
| Brecha SIAGIE vs plantilla curricular | RF-01 | Confusión documental/tribunal | [`aula-notas-excel.md`](aula-notas-excel.md) §3 y §11 | Alta |
| Excel aula solo descarga; import solo plantilla curso | RF-01, RF-16 | Usuario espera import aula | Documentar en manual y DRS | Media |
| Cypress ausente | Varios UI | Sin E2E automatizado | Smoke manual por rol ([`manual-usuario.md`](manual-usuario.md)) | Media |
| Suite OOM 128M | RF-01, RF-16 | CI/local falla antes de terminar | `memory_limit=512M` o ajuste php.ini tests | Alta |
| RF-06–07 UI pausada | RF-06, RF-07, RF-20 | Usuario no procesa riesgo desde perfil | Alinear UI o documentar comando técnico | Media |
| RF-10–12 sin API | RF-10–13 | Cierre alerta incompleto vs DRS | Backlog explícito en DRS actualizado | Alta |
| Activity log parcial | RF-17 | Trazabilidad incompleta | Extender logging + tests | Media |
| Seed oficial no definido | RF-01 | Conteos demo inconsistentes | Entorno referencia `migrate:fresh --seed` | Media |
| 401/403 incompletos | RF-15 | Riesgo seguridad no medido | Ampliar Feature tests | Media |

---

## 10. Uso para actualización del DRS

El DRS v2 consolidado está en [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](drs/DRS_SIDERAE_Blenkir_v2.md). Esta matriz sigue siendo la trazabilidad RF–Sprint–Test de soporte.

Al mantener el DRS (PDF/Word formal opcional):

1. **Separar** requerimientos **objetivo institucional** vs **estado V1 prototipo** (columna «Estado funcional V1» de §5).
2. Marcar como **futuro / fuera V1**: multi-sede operativa, RF-18, ensemble ML, SIAGIE, Cypress obligatorio, certificación ISO.
3. **Consolidar** RF parciales con sub-notas (RF-14 dashboard subset, RF-16 solo export dashboard, RF-13 sin derivación).
4. Enlazar cada RF cerrado en V1 a **test + ruta + pantalla** usando esta matriz y [`informe-pruebas.md`](pruebas/informe-pruebas.md).
5. **No** elevar a «implementado» los RF con solo migración o API sin UI cuando el manual V1 excluye el flujo.

---

*Documento generado en Fase 5 del plan de actualización documental SIDERAE-Blenkir.*
