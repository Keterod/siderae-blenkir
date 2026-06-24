# Diagnóstico AI-DLC — RF-06 Variables reales del riesgo académico

**Fase:** RF-06A — Diagnóstico documental (solo lectura, sin cambios de código).  
**Fecha:** 2026-06-23  
**Fuente principal:** código vigente del repositorio SIDERAE-Blenkir, rama `feature/rf20-historial-riesgo`.  
**Reglas de esta fase:** no se modificó backend, frontend, Flask, Docker, migraciones, seeders, tests ni Cypress.

---

## 1. Propósito

Este diagnóstico busca alinear **RF-06** (*Procesamiento multivariable e índice de riesgo*) con el alcance real del prototipo V1:

- **Notas curriculares** y **asistencia curricular** deben ser los insumos obligatorios del cálculo de riesgo.
- **Reportes conductuales activos** pueden actuar como agravante opcional.
- **Variables socioeconómicas (VSE)** fueron retiradas del flujo funcional de riesgo en DRS v2.1, pero aún aparecen como requisito obligatorio en `RiesgoAcademicoService`.
- El objetivo es documentar el estado exacto antes de proponer correcciones mínimas (RF-06B en adelante).

---

## 2. Estado actual del flujo de riesgo

| Paso | Archivo / método | Qué hace hoy |
| ---- | ---------------- | ------------ |
| 1 | `backend/routes/api.php` L115–116 | Expone `POST /api/estudiantes/{estudiante}/procesar-riesgo` protegido por `auth:sanctum` + `permission:procesar_riesgo`. |
| 2 | `backend/app/Http/Controllers/Api/ProcesarRiesgoController.php` `store()` | Valida `bimestre` opcional (`1`–`4`, default `1`), toma `anio_escolar` del estudiante e invoca `RiesgoAcademicoService::procesarEstudiante()`. |
| 3 | `backend/app/Services/RiesgoAcademicoService.php` `procesarEstudiante()` | Llama a `validarDatosMinimos()`. Si pasa, construye payload, invoca `MlRiskService::predict()`, guarda resultado en `IndiceRiesgo` y genera alerta si aplica. |
| 4 | `RiesgoAcademicoService::validarDatosMinimos()` L31–84 | Verifica nivel Inicial (rechazado), datos académicos curriculares, asistencias diarias **y variables socioeconómicas**. Sin VSE devuelve `ok: false`. |
| 5 | `RiesgoAcademicoService::construirPayload()` L163–201 | Calcula promedio de notas, porcentaje de asistencia, cuenta reportes conductuales activos y **lee VSE con `firstOrFail()`**, enviándolas a Flask. |
| 6 | `backend/app/Services/MlRiskService.php` `predict()` | POST HTTP a `ML_SERVICE_URL/predict` con el payload. |
| 7 | `ml-service/main.py` `/predict` | Fórmula determinística que combina promedio, asistencia, reportes, VSE (`nivel_socioeconomico`, `acceso_internet`, `distancia_colegio`) y Fast Test (fijo en 0). Devuelve `indice_riesgo` y `nivel_riesgo`. |
| 8 | `RiesgoAcademicoService::procesarEstudiante()` L116–158 | Persiste en `indices_riesgo` con `variables_utilizadas = payload` y `modelos_scores` si Flask lo devuelve. Llama `Alerta::crearPorRiesgoAltoSiAplica()`. |
| 9 | `frontend/src/components/estudiantes/EstudiantePerfilRiesgo.jsx` | UI en pausa: muestra mensaje informativo de rediseño; **no** muestra índice ni botón de procesar. |
| 10 | `frontend/src/components/estudiantes/EstudiantePerfilHistorialRiesgo.jsx` (RF-20) | Muestra historial de índices ya persistidos; **no** recalcula riesgo. |

---

## 3. Variables usadas actualmente

| Variable | Fuente / tabla | Obligatoria hoy | Se usa en cálculo | Observación |
| -------- | -------------- | --------------: | ----------------: | ----------- |
| Promedio de notas curriculares | `EvalBimResultado::nivel_logro_numerico` o `NotaSemanal::ce_calculado` | Sí | Sí | Fallback: primero evaluación bimestral, luego notas semanales. |
| Porcentaje de asistencia curricular | `AsistenciaDiaria` | Sí | Sí | Calculado como `(presente + tarde + justificado) / total * 100`. |
| Cantidad de reportes conductuales activos | `ReporteConductual` (`estado = activo`) | No | Sí | Solo se envía el conteo; no se usa gravedad ni recencia. |
| Variables socioeconómicas | `VariableSocioeconomica` | **Sí** | Sí | `validarDatosMinimos()` exige su existencia; `construirPayload()` hace `firstOrFail()`. |
| Fast Test | Fijo en `0` | No | No | DRS v2.1 retiró RF-03; el payload lo envía como `fast_test_puntaje: 0` y Flask le asigna peso pequeño. |
| Índice de riesgo previo | `IndiceRiesgo` | No | No | Se consulta en RF-19/RF-20, pero no influye en el nuevo cálculo. |

### Detalle del payload enviado a Flask

```php
[
    'promedio_notas'        => round($promedioNotas, 4),
    'porcentaje_asistencia' => round($porcentajeAsistencia, 4),
    'reportes_conductuales' => $reportesCount,
    'fast_test_puntaje'     => 0,
    'nivel_socioeconomico'  => $vars->nivel_socioeconomico,
    'acceso_internet'       => (bool) $vars->acceso_internet,
    'distancia_colegio'     => $distancia,
]
```

### Pesos en Flask (`ml-service/main.py`)

```text
nota_riesgo  * 0.28
asis_riesgo  * 0.24
rep_riesgo   * 0.14
socio        * 0.14
dist_riesgo  * 0.09
fast_riesgo  * 0.06
internet_penalty (0.0 o 0.12)
```

---

## 4. Dependencia actual de variables socioeconómicas

| Archivo | Línea / método | Uso actual | Problema |
| ------- | -------------- | ---------- | -------- |
| `backend/app/Services/RiesgoAcademicoService.php` L51–54 | `validarDatosMinimos()` | Verifica que exista al menos un registro `VariableSocioeconomica` para el estudiante y año. | Hace que VSE sea requisito obligatorio para procesar riesgo. |
| `backend/app/Services/RiesgoAcademicoService.php` L74–78 | `validarDatosMinimos()` | Agrega error `variables_socioeconomicas` si no hay VSE. | Mensaje de error expone la dependencia al usuario. |
| `backend/app/Services/RiesgoAcademicoService.php` L182–200 | `construirPayload()` | `firstOrFail()` de VSE; usa `nivel_socioeconomico`, `acceso_internet`, `distancia_colegio_km`. | Si no hay VSE, lanza excepción antes de llamar a Flask. |
| `backend/tests/Feature/RiesgoTest.php` L51–56, L102–164, L180, L191 | `estudianteConDatosMinimos()`, fixtures | Los tests crean VSE para poder procesar riesgo. | Refuerza la dependencia en la suite. |
| `backend/routes/api.php` L111–112 | `GET/POST /estudiantes/{estudiante}/variables-socioeconomicas` | Permiso `registrar_datos_academicos`; API legacy disponible. | No es parte del flujo de riesgo, pero existe como dato aparte. |

### Conclusión sobre VSE

Las **variables socioeconómicas no deben ser requisito obligatorio para procesar riesgo en V1**. DRS v2.1 §RF-05 las retiró del flujo funcional de riesgo y `limitaciones.md` lo documenta. Sin embargo, `RiesgoAcademicoService` aún las exige y las envía a Flask, lo que genera una brecha entre decisión de alcance e implementación.

---

## 5. Variables disponibles para un riesgo V1 mejorado

### Obligatorias

- **Notas curriculares** (`NotaSemanal`, `EvalBimResultado`).
- **Asistencia curricular** (`AsistenciaDiaria`).

### Opcionales / agravantes

- **Reportes conductuales activos** (`ReporteConductual::activos()`):
  - cantidad de reportes;
  - gravedad máxima (`nivel_gravedad`: `leve`, `moderado`, `grave`);
  - cantidad de reportes graves;
  - reportes recientes (por fecha);
  - reincidencia (cantidad por tipo de conducta).

### Contextuales

- **Historial de riesgo RF-20** (`IndiceRiesgo`): evolución por bimestre/periodo.
- **Semáforo de completitud RF-19** (`CompletitudDatosService`): indica qué insumos faltan; no altera el índice pero puede advertir.

### Excluidas

- Variables socioeconómicas (retiradas del flujo de riesgo V1).
- Fast Test (RF-03 retirado del alcance).
- SIAGIE (fuera del alcance).
- Comunicación familiar (RF-12 eliminado).
- Texto libre del reporte conductual como NLP/ML.

---

## 6. Detalle académico recomendado

Actualmente el cálculo usa un **promedio simple** de notas. Datos disponibles que podrían enriquecer el análisis:

| Variable académica | ¿Existe hoy? | Fuente | Observación |
| ------------------ | ------------- | ------ | ----------- |
| Promedio general | Sí | `EvalBimResultado::nivel_logro_numerico` / `NotaSemanal::ce_calculado` | Usado actualmente. |
| Nota mínima | Futuro | Mismo origen | Requiere agregar cálculo. |
| Cursos en riesgo / desaprobados | Futuro | `EvalBimResultado` por curso | Requiere agrupar por `malla_curso_id`. |
| Evaluación bimestral baja | Parcial | `EvalBimResultado` | Ya se usa el promedio; falta detalle por bimestre. |
| Desempeño por competencia/capacidad | Futuro | `detalle_json` de evaluación bimestral | Datos estructurados posibles pero no explotados. |
| Tendencia de notas semanales | Futuro | `NotaSemanal` con `fecha_registro` | Requiere ordenar por semana/periodo. |
| Notas faltantes o incompletas | Futuro | `NotaSemanal` con `ce_calculado` nulo | Puede complementar el semáforo RF-19. |

---

## 7. Detalle asistencia recomendado

Actualmente se usa solo el **porcentaje de asistencia efectiva**.

| Variable asistencia | ¿Existe hoy? | Fuente | Observación |
| ------------------- | ------------- | ------ | ----------- |
| Porcentaje asistencia | Sí | `AsistenciaDiariaResumenService::construirPorEstudiante()` | Usado actualmente. |
| Cantidad de inasistencias | Sí | Conteo de `estado = falta` | Disponible, no usado. |
| Inasistencias recientes | Futuro | `AsistenciaDiaria::fecha` | Requiere ventana de fechas. |
| Inasistencias consecutivas | Futuro | `AsistenciaDiaria` ordenado por fecha | Requiere algoritmo simple. |
| Asistencia por bimestre/periodo | Futuro | `AsistenciaDiaria::anio_escolar` + relación con periodo | No implementado directamente. |

---

## 8. Detalle conductual recomendado

En V1 **no se procesará el texto libre** de la descripción con ML/NLP.

Campos estructurados disponibles en `ReporteConductual`:

| Campo | Tipo | Uso posible |
| ----- | ---- | ----------- |
| `estado` | `activo` / `anulado` | Scope `scopeActivos()` ya filtra activos. |
| `nivel_gravedad` | `leve`, `moderado`, `grave` | Peso agravante según gravedad. |
| `fecha` | date | Recencia del reporte. |
| `tipo_conducta` | string | Reincidencia por tipo. |
| `descripcion` | text | Solo lectura humana; no NLP en V1. |

Recomendación V1: contar reportes activos, detectar gravedad máxima y cantidad de reportes graves; opcionalmente penalizar reportes del último mes.

---

## 9. Qué se guarda actualmente en `indices_riesgo`

| Campo | Existe | Uso actual | Uso recomendado |
| ----- | -----: | ---------- | --------------- |
| `id` | Sí | PK autoincremental | Mantener. |
| `estudiante_id` | Sí | FK a estudiante | Mantener. |
| `indice` | Sí | Valor 0..1 devuelto por Flask | Mantener; posiblemente guardar versión del cálculo. |
| `nivel` | Sí | `Alto` / `Medio` / `Bajo` | Mantener. |
| `anio_escolar` | Sí | Año del procesamiento | Mantener. |
| `bimestre` | Sí | `1`–`4` | Mantener. |
| `variables_utilizadas` | Sí | JSON con payload completo enviado a Flask | Mantener como trazabilidad de insumos. |
| `modelos_scores` | Sí | JSON si Flask devuelve `modelos`/`modelos_scores` | Mantener para futuro ML real. |
| `created_at` / `updated_at` | Sí | Timestamps | Usar como fecha del registro (RF-20 lo muestra). |

---

## 10. Brechas encontradas

| ID | Brecha | Evidencia | Impacto |
| -- | ------ | --------- | ------- |
| BR-06-01 | `validarDatosMinimos()` exige VSE como dato mínimo | `RiesgoAcademicoService.php` L51–78 | Contradice DRS v2.1 / `limitaciones.md`; bloquea procesamiento sin VSE. |
| BR-06-02 | `construirPayload()` usa `firstOrFail()` de VSE | `RiesgoAcademicoService.php` L182–186 | Si se relaja la validación pero no el payload, falla con excepción. |
| BR-06-03 | El cálculo académico es un promedio simple | `resolverPromedioNotas()` L212–220 | No distingue cursos críticos, nota mínima ni tendencia. |
| BR-06-04 | Conducta solo aporta conteo de reportes activos | `construirPayload()` L177–180 | No se usa gravedad, recencia ni reincidencia. |
| BR-06-05 | Fast Test sigue en payload aunque RF-03 está retirado | `construirPayload()` L196; `ml-service/main.py` L41, L58–69 | Peso pequeño pero código huérfano; confunde trazabilidad. |
| BR-06-06 | UI de riesgo activada V1 (NC-11 cerrada); botón “Procesar/Actualizar riesgo” con permiso `procesar_riesgo` | `EstudiantePerfilRiesgo.jsx` | RF-06 operativo para usuarios con permiso; no requiere comando técnico. |
| BR-06-07 | Frontend no muestra resultado del riesgo recién calculado | Solo historial RF-20 | El usuario ve historial, pero no el índice actual en el perfil. |
| BR-06-08 | Tests de `RiesgoTest` asumen VSE obligatoria | `RiesgoTest.php` L123, L155, L164, L180, L191 | Refuerzan la dependencia que se quiere retirar. |
| BR-06-09 | `ml-service/main.py` aún pondera VSE | `main.py` L43–51, L63–71 | Si se quita VSE del payload, Flask recibiría valores nulos y asignaría defaults fijos. |

---

## 11. Propuesta de fases siguientes

| Fase | Objetivo | Entregables esperados |
| ---- | -------- | --------------------- |
| **RF-06B** | Plan de variables oficiales del riesgo V1 | Documento aprobado con lista obligatoria/opcional/excluida. |
| **RF-06C** | Retirar dependencia obligatoria de VSE | Modificar `validarDatosMinimos()` y `construirPayload()`; ajustar `ml-service/main.py` para defaults neutros; actualizar tests. |
| **RF-06D** | Ajustar cálculo determinístico con notas, asistencia y conducta | Payload enriquecido: promedio, asistencia, reportes con gravedad; pesos actualizados; sin VSE. |
| **RF-06E** | Pruebas, documentación y smoke manual | Tests backend verdes, build frontend, manuales actualizados, cierre NC-11/NC-12. |

---

## 12. Conclusión

**Sí se puede avanzar a RF-06B.**

El flujo de riesgo está funcionalmente operativo, pero presenta una dependencia obsoleta con variables socioeconómicas que contradice el alcance V1 aprobado. Las variables reales y disponibles hoy son:

1. **Notas curriculares** (evaluación bimestral / notas semanales).
2. **Asistencia curricular diaria**.
3. **Reportes conductuales activos** (como agravante opcional).

Con una intervención mínima en `RiesgoAcademicoService`, `ml-service/main.py` y `RiesgoTest`, es posible dejar a RF-06 alineado con DRS v2.1 sin introducir ML real ni cambiar la arquitectura general.

### Validación de alcance de esta fase

- ✅ Solo se creó documentación (`diagnostico-rf06-variables-riesgo.md`).
- ✅ No se modificó backend.
- ✅ No se modificó frontend.
- ✅ No se modificó Flask.
- ✅ No se cambió fórmula de riesgo.
- ✅ No se recalculó riesgo automáticamente.
- ✅ No se crearon migraciones.
- ✅ No se tocaron seeders ni tests.
- ✅ No se usaron variables socioeconómicas como requisito nuevo.
- ✅ No se implementó RF-10, RF-11, RF-14, RF-16 ni RF-18.
- ✅ No se creó selector de sede.
- ✅ No se afirmó ML real ni ISO certificado.

---

## Apéndice: estado post RF-06C (2026-06-23)

Las brechas identificadas en este diagnóstico fueron corregidas en **RF-06C**:

| Brecha | Estado RF-06C |
| ------ | -------------- |
| BR-06-01 — `validarDatosMinimos()` exige VSE | **Corregido:** ya no verifica VSE |
| BR-06-02 — `firstOrFail()` de VSE en `construirPayload()` | **Corregido:** eliminado; payload solo incluye `promedio_notas`, `porcentaje_asistencia`, `reportes_conductuales` |
| BR-06-05 — Fast Test en payload y Flask | **Corregido:** retirado del payload y de la fórmula Flask |
| BR-06-08 — Tests asumen VSE obligatoria | **Corregido:** `RiesgoTest` actualizado (20 tests), `RiesgoCurricularFixtures` sin VSE en datos mínimos |
| BR-06-09 — Flask pondera VSE | **Corregido:** Flask RF-06C usa pesos notas 55%, asistencia 30%, conducta 15%; VSE/Fast Test ignorados |

Pendiente para **RF-06D**: BR-06-03 (cálculo académico simple) y BR-06-04 (conducta solo conteo).

---

*Documento generado en Fase RF-06A — diagnóstico sin modificación de código.*
