# Plan AI-DLC — RF-06 Variables oficiales del riesgo académico V1

**Fase:** RF-06B — Planificación de variables oficiales (solo documental).  
**Fecha:** 2026-06-23  
**Fuente principal:** [`diagnostico-rf06-variables-riesgo.md`](diagnostico-rf06-variables-riesgo.md).  
**Reglas de esta fase:** no se modifica backend, frontend, Flask, Docker, migraciones, seeders, tests ni Cypress.

---

## 1. Propósito

Definir oficialmente las variables que alimentarán el cálculo de riesgo académico en **RF-06 V1**, alineando el requerimiento con el alcance real del prototipo SIDERAE-Blenkir:

- **Notas curriculares** y **asistencia curricular** serán los insumos obligatorios.
- **Reportes conductuales activos** serán agravantes opcionales.
- **Variables socioeconómicas (VSE)** y **Fast Test** quedan retirados del flujo de riesgo.
- No se afirma ML real ni se implementan modelos avanzados.

Este plan es la entrada para la siguiente fase técnica **RF-06C**.

---

## 2. Decisión oficial de variables

| Categoría | Variable | Estado V1 | Uso |
| --------- | -------- | --------- | --- |
| Obligatoria | Notas curriculares | Requerida | Promedio académico principal (evaluación bimestral o notas semanales). |
| Obligatoria | Asistencia curricular | Requerida | Porcentaje de asistencia efectiva del año escolar. |
| Opcional / agravante | Reportes conductuales activos | Opcional | Conteo de reportes activos; aumenta riesgo si existen. |
| Opcional / agravante | Gravedad máxima de reportes | Opcional | `grave`, `moderado`, `leve`; pondera el componente conductual. |
| Opcional / agravante | Cantidad de reportes graves | Opcional | Número de reportes con `nivel_gravedad = grave`. |
| Opcional / agravante | Reportes recientes | Futuro V1+ | Reportes del último mes o bimestre. |
| Opcional / agravante | Reincidencia conductual | Futuro V1+ | Repetición del mismo `tipo_conducta`. |
| Contextual | Semáforo de completitud (RF-19) | Informativo | Indica si hay datos suficientes; no altera el índice. |
| Contextual | Historial de riesgo (RF-20) | Informativo | Muestra evolución histórica; no altera el índice. |
| Excluida | Variables socioeconómicas | Retirada | No se exige ni se envía a Flask. API legacy disponible como dato aparte. |
| Excluida | Fast Test | Retirado | RF-03 eliminado del alcance vigente. |
| Excluida | SIAGIE | Fuera de alcance | Reemplazado por plantillas propias RF-32/RF-33. |
| Excluida | Comunicación familiar | Eliminada | RF-12 fuera del alcance. |
| Excluida | Texto libre de reportes (NLP/ML) | No V1 | La descripción es solo lectura humana. |
| Excluida | ML real / reentrenamiento | No V1 | RF-18 planificado para futuro con dataset histórico. |

---

## 3. Variables académicas propuestas

El riesgo académico **no debe depender solo del promedio general**. Se proponen los siguientes indicadores académicos:

| Indicador académico | Existe hoy | Uso V1 | Observación |
| ------------------- | ---------: | ------ | ----------- |
| Promedio general | Sí | Principal | Calculado desde `EvalBimResultado::nivel_logro_numerico` o `NotaSemanal::ce_calculado`. |
| Nota mínima | Sí (datos) | Futuro V1+ | Requiere agregar cálculo mínimo por estudiante/año. |
| Cantidad de cursos en riesgo | Sí (datos) | Futuro V1+ | Cursos con promedio bajo en `EvalBimResultado`. |
| Cantidad de cursos desaprobados | Sí (datos) | Futuro V1+ | Cursos bajo umbral de aprobación. |
| Cursos críticos con bajo rendimiento | Sí (datos) | Futuro V1+ | Requiere definir umbral por institución. |
| Evaluación bimestral baja | Sí (datos) | Futuro V1+ | `EvalBimResultado` por bimestre; hoy solo promedia. |
| Desempeño por competencia/capacidad | Parcial | Futuro V1+ | `detalle_json` contiene desglose; no explotado aún. |
| Tendencia de notas semanales | Sí (datos) | Futuro V1+ | Requiere ordenar `NotaSemanal` por fecha. |
| Notas faltantes o incompletas | Sí (datos) | Futuro V1+ | `NotaSemanal` con `ce_calculado` nulo. |

**Decisión V1:** se usa **promedio general** como única métrica académica por simplicidad. Los indicadores avanzados quedan como mejora futura documentada.

---

## 4. Variables de asistencia propuestas

| Indicador asistencia | Existe hoy | Uso V1 | Observación |
| -------------------- | ---------: | ------ | ----------- |
| Porcentaje de asistencia | Sí | Principal | `AsistenciaDiariaResumenService::construirPorEstudiante()`. |
| Cantidad de inasistencias | Sí | Futuro V1+ | Conteo de `estado = falta`. |
| Inasistencias recientes | Sí (datos) | Futuro V1+ | Requiere ventana de fechas. |
| Inasistencias consecutivas | Sí (datos) | Futuro V1+ | Requiere algoritmo simple sobre fechas ordenadas. |
| Asistencia por bimestre/periodo | Sí (datos) | Futuro V1+ | Requiere cruzar `AsistenciaDiaria::fecha` con periodos. |

**Decisión V1:** se usa **porcentaje de asistencia efectiva** como única métrica de asistencia por simplicidad.

---

## 5. Variables conductuales propuestas

En V1 **no se procesará el texto libre** de la descripción del reporte con NLP/ML. Solo se usarán campos estructurados:

| Indicador conductual | Existe hoy | Uso V1 | Observación |
| -------------------- | ---------: | ------ | ----------- |
| Cantidad de reportes activos | Sí | Principal | `ReporteConductual::activos()->count()`. |
| Gravedad máxima | Sí | Futuro V1+ | Campo `nivel_gravedad`. |
| Cantidad de reportes graves | Sí | Futuro V1+ | Filtrar `nivel_gravedad = grave`. |
| Reportes recientes | Sí (datos) | Futuro V1+ | Filtrar por `fecha` últimos 30 días. |
| Reincidencia por tipo | Sí (datos) | Futuro V1+ | Agrupar por `tipo_conducta`. |

**Decisión V1:** se usa el **conteo de reportes activos** como único indicador conductual. Si no hay reportes, el componente conductual será neutro (`0`).

---

## 6. Peso conceptual recomendado V1

Se propone un peso simple y explícito, ajustable en fases posteriores:

| Componente | Peso conceptual | Comportamiento si falta |
| ---------- | --------------: | ----------------------- |
| Académico (promedio de notas) | 55% | Requisito obligatorio; sin notas no se procesa riesgo. |
| Asistencia (porcentaje asistencia) | 30% | Requisito obligatorio; sin asistencia no se procesa riesgo. |
| Conductual (reportes activos) | 15% | Opcional; si no hay reportes, aporta `0`. No castiga por defecto. |

**Notas:**

- Estos pesos son conceptuales para RF-06D; **no se implementan en esta fase**.
- El índice seguirá en rango `[0.0, 1.0]`.
- La clasificación Alto/Medio/Bajo mantiene umbrales actuales: `≥0.70` Alto, `0.40–0.69` Medio, `<0.40` Bajo.

### Ejemplo conceptual de fórmula determinística V1

```text
nota_riesgo  = (20 - promedio_notas) / 20        # 0..1
asis_riesgo  = (100 - porcentaje_asistencia) / 100  # 0..1
cond_riesgo  = min(reportes_activos / 5, 1)      # 0..1 (opcional)

indice = nota_riesgo * 0.55 + asis_riesgo * 0.30 + cond_riesgo * 0.15
```

Esta fórmula es solo orientativa; la fase RF-06D la concretará y validará.

---

## 7. Qué debe cambiar en RF-06C

Fase técnica inmediata: retirar dependencias obsoletas sin cambiar la lógica de cálculo todavía.

1. **Quitar VSE de `validarDatosMinimos()`** en `RiesgoAcademicoService.php`.
2. **Quitar `firstOrFail()` de VSE** en `construirPayload()`; eliminar lectura de VSE.
3. **Quitar Fast Test del payload** (`fast_test_puntaje: 0`) o dejarlo explícitamente retirado.
4. **Ajustar payload** para enviar solo:
   - `promedio_notas`
   - `porcentaje_asistencia`
   - `reportes_conductuales`
5. **Actualizar tests** `RiesgoTest` para que ya no requieran VSE como dato mínimo.
6. **Documentar** en manuales que VSE no es requisito para procesar riesgo.

---

## 8. Qué debe cambiar en RF-06D

Fase técnica posterior: mejorar el cálculo determinístico con las variables oficiales.

1. **Académico no solo promedio:** incluir nota mínima y cursos en riesgo si se decide.
2. **Asistencia:** usar porcentaje y, opcionalmente, inasistencias recientes/consecutivas.
3. **Conductual:** usar gravedad máxima, reportes graves y recencia.
4. **Guardar `variables_utilizadas` claro:** JSON que indique exactamente qué variables se usaron.
5. **Mantener historial** en `indices_riesgo` para RF-20.
6. **Ajustar `ml-service/main.py`** para reflejar nuevo payload y pesos si aplica.

---

## 9. Fuera del alcance

Queda explícitamente excluido de RF-06 V1:

- ML real o ensemble entrenado (RF-18).
- Reentrenamiento de modelos.
- Cambios avanzados en Flask más allá de ajustar fórmula determinística.
- Procesamiento de lenguaje natural (NLP) sobre descripciones conductuales.
- Dashboard académico-institucional (RF-14).
- Zona de reportes de riesgo (RF-16).
- Escalamiento directivo (RF-10).
- Perfil integral psicólogo/tutor (RF-11).
- Variables socioeconómicas como requisito.
- SIAGIE.
- Fast Test.
- Comunicación familiar (RF-12).
- Selector de sede.
- Certificación ISO.

---

## 10. Criterios de aceptación futuros

Para considerar cerrada la corrección de RF-06:

1. El riesgo puede procesarse **sin variables socioeconómicas**.
2. **Notas curriculares** y **asistencia curricular** son los datos mínimos requeridos.
3. Los **reportes conductuales** son opcionales y no castigan cuando no existen.
4. Los tests actualizados pasan (`RiesgoTest` y `HistorialRiesgoTest`).
5. Flask no exige VSE ni Fast Test.
6. `variables_utilizadas` registra claramente qué variables se usaron.
7. Se mantiene historial en `indices_riesgo`.
8. Documentación (manuales, matriz, API, limitaciones) refleja el nuevo alcance.
9. No se afirma ML real.

---

## 11. Conclusión

**Sí se puede avanzar a RF-06C.**

El plan define variables oficiales coherentes con el alcance V1: notas y asistencia obligatorias, conducta opcional, VSE/Fast Test retirados. La corrección es técnicamente mínima y no requiere cambios de arquitectura.

### Validación de alcance de esta fase

- ✅ Solo se creó documentación (`plan-rf06-variables-riesgo-v1.md`).
- ✅ No se modificó backend, frontend, Flask, Docker, migraciones, seeders, tests ni Cypress.
- ✅ No se cambió fórmula de riesgo.
- ✅ No se recalculó riesgo automáticamente.
- ✅ No se usaron variables socioeconómicas como requisito.
- ✅ No se implementó RF-10, RF-11, RF-14, RF-16 ni RF-18.
- ✅ No se creó selector de sede.
- ✅ No se afirmó ML real ni ISO certificado.

---

## Apéndice: RF-06C ejecutado (2026-06-23)

**RF-06C completado.** Todos los cambios del plan §7 se implementaron:

1. ✅ VSE retirado de `validarDatosMinimos()` — solo notas + asistencia como mínimos.
2. ✅ `firstOrFail()` de VSE eliminado de `construirPayload()`; payload reducido a `promedio_notas`, `porcentaje_asistencia`, `reportes_conductuales`.
3. ✅ Fast Test retirado del payload.
4. ✅ Flask recalibrado con pesos RF-06C: notas 55%, asistencia 30%, reportes 15%.
5. ✅ Tests actualizados: `RiesgoTest` 20 passed; `RiesgoCurricularFixtures` sin VSE en datos mínimos.
6. ✅ Documentación actualizada (api, limitaciones, manual-tecnico, matriz, NC-12 cerrada, informe-pruebas).

**Próximo:** RF-06D — mejorar cálculo académico/asistencia/conductual.

---

*Plan AI-DLC Fase RF-06B — 2026-06-23.*
