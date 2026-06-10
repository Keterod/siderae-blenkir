# Hallazgos Fase 1 — Documentación

## 1. Fecha

**2026-06-09** (fecha del sistema al ejecutar la fase).

Entorno: Docker Compose levantado (`docker compose ps`: `siderae_backend`, `siderae_frontend`, `siderae_ml`, `siderae_mysql` healthy/up).

---

## 2. Comandos ejecutados

| Comando | Resultado | Observación |
|---------|-----------|-------------|
| `docker compose ps` | Exit 0 — 4 servicios Up | Backend, frontend, ML y MySQL operativos |
| `docker compose exec app-backend php artisan test` | **Exit 2** — fallo fatal | ~277 tests pasaron antes del error; ver §3 |
| `docker compose exec app-backend php -d memory_limit=512M artisan test --filter=ExcelAulaTest` | Exit 0 — 8 passed (32 assertions) | Misma clase que provocó OOM con 128M |
| `docker compose exec app-backend php artisan tinker --execute="echo 'Estudiantes total: '.App\Models\Estudiante::count();"` | Exit 0 — `449` | Solo lectura |
| `docker compose exec app-backend php artisan tinker --execute="echo 'chilca: '.App\Models\Estudiante::where('sede','chilca')->count().' auq: '.App\Models\Estudiante::where('sede','auquimarca')->count();"` | Exit 0 — `chilca: 253 auq: 196` | Solo lectura |
| `docker compose exec app-backend php artisan tinker --execute="echo 'Users: '.App\Models\User::count();"` | Exit 0 — `8` | Solo lectura |
| `docker compose exec app-backend php artisan tinker --execute="echo json_encode(DB::table('model_has_roles')...);"` | Exit 0 — JSON roles | Ver §4 |
| `docker compose exec app-backend php artisan tinker --execute="echo 'Materias: '.App\Models\Materia::count()..."` | Exit 0 — legacy en 0 | Materias/Notas/Asist/VSE/Riesgos/Alertas = 0 |
| `docker compose exec app-backend php artisan tinker --execute="echo 'NotasSemanales: '.App\Models\Curricular\NotaSemanal::count();"` | Exit 0 — `15` | Datos curriculares presentes |
| `docker compose exec app-backend php artisan tinker --execute="echo 'AsistDiaria: '.App\Models\Curricular\AsistenciaDiaria::count().' SeccionesAula: '.App\Models\Curricular\SeccionAula::count();"` | Exit 0 — `35` / `69` | Datos curriculares presentes |
| `docker compose exec app-backend php artisan tinker --execute="foreach(...Role...)"` | **Exit 1** — Parse error | Escaping `$` en PowerShell; sustituido por consulta DB |

**No ejecutado (por diseño de la fase):** `migrate:fresh --seed` (destructivo; no requerido).

---

## 3. Pruebas backend

| Campo | Valor |
|-------|-------|
| **Ejecutadas** | Sí (suite completa + reintento ExcelAulaTest) |
| **Resultado suite completa** | **Incompleta / fallo técnico** |
| **Fallos** | Fatal error memoria PHP 128M en `Tests\Feature\Curricular\ExcelAulaTest::administrador_puede_descargar_excel_aula` |
| **Evidencia textual** | `Allowed memory size of 134217728 bytes exhausted` en `vendor/maennchen/zipstream-php/src/File.php` |
| **Tests pasados antes del fallo** | ~277 assertions con ✓ (conteo en salida de ejecución) |
| **ExcelAulaTest aislado (512M)** | **8 passed**, duración ~136 s |
| **Cypress** | **No ejecutado** — no existe suite en repo |

**Interpretación:** la funcionalidad Excel aula está cubierta por tests que **requieren más memoria** que el límite por defecto del contenedor PHP. Para informe formal conviene documentar `php -d memory_limit=512M artisan test` o subir `memory_limit` en entorno de pruebas.

---

## 4. Conteos demo

Consulta sobre **base de datos actual del entorno local** (no necesariamente igual a un `migrate:fresh --seed` limpio).

> **Nota sobre conteos Fase 1 (BD local auditada):** Estos conteos pertenecen al entorno local auditado. La presencia de registros con sede Auquimarca no implica operación multi-sede en V1; la decisión vigente de V1 es sede operativa **Chilca** ([`AGENTS.md`](../../AGENTS.md)). Los seeders demo actuales no crean nuevos datos en Auquimarca.

| Métrica | Valor | Fuente | Estado |
|---------|-------|--------|--------|
| Estudiantes total | **449** | `Estudiante::count()` vía tinker | Confirmado |
| Estudiantes sede `chilca` | **253** | `where('sede','chilca')` | Confirmado |
| Estudiantes sede `auquimarca` | **196** | `where('sede','auquimarca')` | Confirmado en BD auditada — **no** implica operación V1 multi-sede (ver nota §4) |
| Usuarios total | **8** | `User::count()` | Confirmado |
| Rol `administrador` | **2** | `model_has_roles` + `roles` | Confirmado |
| Rol `docente` | **3** | idem | Confirmado |
| Rol `coordinador_academico` | **1** | idem | Confirmado |
| Rol `psicologo_tutor` | **1** | idem | Confirmado |
| Rol `directivo` | **1** | idem | Confirmado |
| Materias (legacy) | **0** | `Materia::count()` | Confirmado — legacy vacío en BD actual |
| Notas (legacy) | **0** | `Nota::count()` | Confirmado |
| Asistencias (legacy) | **0** | `Asistencia::count()` | Confirmado |
| Variables socioeconómicas | **0** | `VariableSocioeconomica::count()` | Confirmado |
| Índices de riesgo | **0** | `IndiceRiesgo::count()` | Confirmado |
| Alertas | **0** | `Alerta::count()` | Confirmado |
| Notas semanales curriculares | **15** | `Curricular\NotaSemanal::count()` | Confirmado |
| Asistencias diarias curriculares | **35** | `Curricular\AsistenciaDiaria::count()` | Confirmado |
| Secciones/aulas | **69** | `Curricular\SeccionAula::count()` | Confirmado |
| README: conteos demo desalineados con BD auditada | — | [`README.md`](../../README.md) §5.5 | **Documentado** — conteos Fase 1 son del entorno local, no constantes de producto |
| README: “220 estudiantes” (tinker esperado) | — | [`README.md`](../../README.md) (versiones anteriores) | **Desalineado** — BD auditada tiene 449 total |

**Pendiente de confirmar:** conteos exactos tras un `migrate:fresh --seed` en entorno limpio (no ejecutado en esta fase para no borrar datos).

---

## 5. Hallazgos relevantes para README

- Conteos demo del README (**196** y **220** en versiones anteriores) **no coinciden** con la BD auditada (**449** total; **253** Chilca / **196** Auquimarca). Los conteos Fase 1 documentan el entorno local medido; Auquimarca en BD **no** implica operación multi-sede en V1.
- Existen **196 estudiantes Auquimarca** en la BD auditada por historial local previo; la decisión V1 es sede operativa Chilca en UI, consultas por defecto y seeders demo nuevos ([`AGENTS.md`](../../AGENTS.md)).
- Tablas legacy (materias, notas, asistencias, VSE) en **0** en BD actual; el flujo documentado como demo principal es **curricular**.
- `docker compose up` **no ejecuta seed** — solo migrate ([`docker-compose.yml`](../../docker-compose.yml)); README debe explicitarlo antes de Fase 2.
- Sprints **8.5A/B/C** y módulos UI curriculares ausentes en sección “Funcionalidades implementadas”.
- Suite de tests: posible fallo OOM en Excel sin `memory_limit` elevado.

---

## 6. Hallazgos relevantes para arquitectura

- Prefijo `/api/curricular/*` extenso confirmado en [`backend/routes/api.php`](../../backend/routes/api.php) — debe reflejarse en `ARCHITECTURE.md` y contextos.
- ML sigue siendo **determinístico** ([`ml-service/main.py`](../../ml-service/main.py)); no RF/SVM/XGBoost.
- Sin healthcheck en backend/frontend/ml en Compose.
- Backend no declara `depends_on` hacia `ml-engine`; fallo ML es en runtime.
- Migraciones para `reportes_conductuales` y `comunicaciones_familiares` sin rutas API — arquitectura “datos preparados, flujo pendiente”.

---

## 7. Hallazgos relevantes para manual técnico

- Stack Laravel **^13** ([`backend/composer.json`](../../backend/composer.json)); Plan de Pruebas matizado post-Fase 8 (histórico/parcial — ver banner en [`Plan_de_Pruebas_SIDERAE_Blenkir.md`](Plan_de_Pruebas_SIDERAE_Blenkir.md)).
- Variables `.env.example`: [`backend/.env.example`](../../backend/.env.example), [`frontend/.env.example`](../../frontend/.env.example), [`ml-service/.env.example`](../../ml-service/.env.example).
- Comando pruebas recomendado documentar con nota de memoria para tests Excel.
- Dependencias: DomPDF, Maatwebsite Excel, Activitylog — instaladas; no equivalen a todos los RF del DRS.
- `POST /register` activo ([`RegistrationTest.php`](../../backend/tests/Feature/Auth/RegistrationTest.php)).

---

## 8. Hallazgos relevantes para matriz RF–Sprint–Test

- **`ImportarDatosTest` no existe** — Fichas automatizadas desactualizadas; usar `PlantillaRegistroAuxiliarExcelTest`, `DatosAcademicosTest`, etc.
- Tests curriculares abundantes en [`backend/tests/Feature/Curricular/`](../../backend/tests/Feature/Curricular/) — mapear en matriz RF–01 parcial, módulo 8.5.
- **Cypress: N/A** — sin carpeta en repo.
- RF-18, RF-19, RF-10–12: sin tests de aceptación equivalentes detectados.
- Suite completa: resultado **no binario** (fallo infra memoria, no fallo funcional en ExcelAulaTest con 512M).

---

## 9. Pendientes antes de Fase 2

1. Decidir conteo demo **oficial** (ejecutar `migrate:fresh --seed` en entorno de referencia o documentar que conteos dependen del historial local).
2. Actualizar README con conteos verificados y nota seed manual.
3. Documentar límite memoria PHP para suite de tests en manual técnico / informe de pruebas.
4. Crear `docs/seguridad-roles-permisos.md` (23 permisos; matriz Sprint 8 obsoleta).
5. No afirmar Cypress, SIAGIE, ML ensemble ni ISO certificada en ningún entregable.
6. Archivar [`matriz-control-accesos-sprint8.md`](../arquitectura/matriz-control-accesos-sprint8.md) con banner histórico al crear matriz vigente (Fase 3 del plan).

---

## Verificación estructura documental (tarea 1)

| Ruta | Estado |
|------|--------|
| `docs/` | Existe |
| `docs/pruebas/` | Existe |
| `docs/arquitectura/` | Existe |
| `docs/analisis/` | Existe |

No fue necesario crear carpetas. **No se reorganizó** la estructura de `docs/`.

**Archivos creados en Fase 1:**

- [`docs/limitaciones.md`](../limitaciones.md)
- `docs/pruebas/hallazgos-fase1-documentacion.md` (este archivo)

**Archivos no modificados** (según instrucciones): README, ARCHITECTURE, documentos históricos, fichas, plan de pruebas, etc.
