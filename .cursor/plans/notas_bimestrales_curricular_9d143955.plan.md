---
name: Sprint 8.5C Evaluación bimestral
overview: Sprint 8.5C — Evaluación bimestral y nivel de logro; config por malla_curso + periodo (sin sede); participación ETA por aula (null=no cargada, 0=cargada); Promedio ETA activo sin ETA participantes ⇒ nivel pendiente; cache eval_bim_resultados con recálculo; sin implementar código en esta entrega del plan.
todos:
  - id: f0-docs
    content: Documentar Sprint 8.5C — config sin sede; ETA cargada (null≠cargada, 0–20=cargada); Promedio ETA activo sin participantes ⇒ pendiente; normalización pesos; Oral/Examen/personalizado vacío ⇒ pendiente; cache y recálculo
    status: pending
  - id: f1-migrations
    content: Migraciones eval_bim_* (componentes, etas, notas, resultados por aula, escala) + seeds — ejecutar cuando se implemente el sprint (no parte de esta entrega solo-plan)
    status: pending
  - id: f2-services
    content: Implementar servicios ETA participantes + normalización, PromedioCriterios, NivelLogro, EscalaLogro, pesos componentes/ETAs, persist resultado cache/recalc hooks
    status: pending
  - id: f3-api-config
    content: API configuración componentes/ETAs (permiso configurar_evaluacion_bimestral), sin sede en config
    status: pending
  - id: f4-api-registro
    content: Extender formulario bulk notas semanales o rutas paralelas; recalc resultado por aula ante guardados configuración/notas CE
    status: pending
  - id: f5-frontend
    content: Bloque compacto Evaluación bimestral + pantalla configuración + conclusiones modal/drawer/expansión
    status: pending
  - id: f6-tests-feature
    content: Tests obligatorios (ETA participación/normalización pendiente oral pesos resultado cache permisos)
    status: pending
isProject: false
---

# Sprint 8.5C — Evaluación bimestral y nivel de logro (plan corregido)

Este documento refleja **decisiones finales del usuario**. No incluye migraciones ni código ejecutable; es la especificación/plan maestro del sprint.

### Decisiones finales — ETA (cerradas)

**A) ¿Cuándo una ETA está “cargada” para activar participación en el aula?**

| Estado en `eval_bim_notas_eta` | ¿Cuenta como cargada? |
|--------------------------------|------------------------|
| `null` / vacía (sin fila o sin valor persistido) | **No** |
| `0` explícito | **Sí** |
| Cualquier valor entre 0 y 20 | **Sí** |

Consecuencia: si **al menos un alumno del aula** tiene, por ejemplo, **ETA 2 = 0**, entonces **ETA 2 participa para toda la aula** (los demás alumnos sin nota en esa ETA reciben **0** en ese sumando al calcular Promedio ETA).

**B) Promedio ETA activo sin ninguna ETA participante en el aula**

Si el componente **Promedio ETA** está **activo** en config, pero **ninguna** ETA activa en config cumple la regla de participación para esa aula (nadie ha cargado ninguna ETA aún):

- El **resultado bimestral** queda **`estado_calculo = pendiente`** (nivel de logro no calculado).
- **No** se convierte Promedio ETA en **0**.
- **No** se ignora el componente: bloquea el cierre del nivel hasta que exista al menos una ETA participante **o** el coordinador desactive el componente Promedio ETA.
- El componente **solo deja de participar** en el nivel si **Promedio ETA** está **desactivado** en `eval_bim_componentes` (no por “falta de ETAs participantes”).

---

## 1. Diagnóstico del estado actual

**Dominio vigente**

- **Bimestre**: `periodos_academicos` ([`create_curricular_module_tables`](backend/database/migrations/2026_05_23_120000_create_curricular_module_tables.php)): `bimestre` 1–4 y `anio_escolar`.
- **Curso en malla**: `malla_cursos` dentro de `mallas_curriculares` (año institucional, nivel y grado de la malla **sin sede**). **Sede, grado (estudiante) y sección** viven en asignación docente y en el estudiante, y definen el **aula** operativa para listados y registros masivos.
- **Criterios**: `temas_semanales` por `malla_curso_id` + `periodo_academico_id` + semana.
- **Notas semanales**: `notas_semanales` — C/L/T, `ce_calculado` persistente (`decimal`), único `(estudiante_id, tema_semanal_id)` ([`NotaSemanal`](backend/app/Models/Curricular/NotaSemanal.php)).
- **CE**: [`CeCalculatorService`](backend/app/Services/Curricular/CeCalculatorService.php) con [`PesoEvaluacionResolver`](backend/app/Services/Curricular/PesoEvaluacionResolver.php) sobre **configuración_pesos_evaluacion** (solo C/L/T, distinto del bimestre).
- **Registro tipo Excel**: [`NotaSemanalFormularioService`](backend/app/Services/Curricular/NotaSemanalFormularioService.php) + [`NotaSemanalController`](backend/app/Http/Controllers/Api/Curricular/NotaSemanalController.php); frontend [`RegistroNotasSemanalesPanel`](frontend/src/components/curricular/RegistroNotasSemanalesPanel.jsx) + [`api.js`](frontend/src/lib/api.js).

**Brecha respecto al sprint**

- No existen tablas ni API para componentes bimestrales, Oral, Examen bimestral, ETAs, componentes personalizados, nivel de logro institucional (0–20 + literal AD/A/B/C) ni conclusiones por estudiante + curso de malla + bimestre.
- [`ResumenAcademicoService`](backend/app/Services/Curricular/ResumenAcademicoService.php): `promedios_bimestrales` es promedio de CE agregados por periodo sobre **todas** las materias donde hay CE; **no** es el nivel de logro ni por curso/aula.

**Compatibilidad obligada en este sprint**

- **Solo lectura** de `notas_semanales` / CE donde haga falta (promedio de criterios). No cambiar modelo de escritura de C/L/T ni `CeCalculatorService` más allá de lo necesario para observadores de recálculo si se adoptan observers (valorar en diseño técnico al implementar).

---

## 2. Diseño de tablas definitivo

### 2.1 Configuración sin sede

- **`eval_bim_componentes`**: `malla_curso_id`, `periodo_academico_id`, `tipo` (`promedio_criterios`, `oral`, `promedio_eta`, `examen_bimestral`, `personalizado`), `codigo` estable para tipo sistema (único por par curso-periodo donde aplique), `nombre` (personalizado / display), `peso`, `orden`, `activo`, timestamps.
  - Defaults: 4 componentes sistema activos **25 % c/u**.
  - Al **agregar** componente activo → redistribuir **equitativo** entre todos los activos (ej.: 5 activos → 20 % cada uno).
  - Al **desactivar** → redistribuir entre activos; **suma de pesos de activos = 100 %** (tolerancia numérica igual que en [`PesoEvaluacionResolver`](backend/app/Services/Curricular/PesoEvaluacionResolver.php)).
  - Edición manual permitida después; validar siempre suma 100 % en activos.

- **`eval_bim_eta_items`**: ligadas al bloque ETA del mismo par `malla_curso_id`, `periodo_academico_id` (FK al registro componente tipo `promedio_eta` o duplicación mínima con `componente_id` nullable según modelo elegido — en implementación debe quedar una sola ETA “familia”). Campos: `nombre`, `peso_interno`, `orden`, `activo`.
  - Defaults: ETA 1, ETA 2, ETA 3 activas; pesos **33.33 / 33.33 / 33.34** u otra repartición exacta a 100 %.
  - Al agregar ETA activa o desactivar → redistribuir pesos entre **ETA activas**; suma 100 % sobre activas solamente.

**Ejemplo institucional (decisión final)**: para el mismo curso/bimestre, **Chilca y Auquimarca comparten idénticos componentes y pesos**. La diferencia entre sedes es solo en **quiénes son los estudiantes** y por tanto en **notas/promedio ETA participantes/recálculo**.

### 2.2 Notas (fuente del cálculo)

- **`eval_bim_notas_scalar`**: `(estudiante_id, componente_id)` uno por componente aplicable donde el tipo sea oral, examen_bimestral o personalizado. `nota` nullable 0–20; `docente_id` opcional para auditoría. No hay coercion a 0 cuando está vacío para estos tipos: **vacío ⇒ pendiente a nivel de logro** si ese componente está activo.
- **`eval_bim_notas_eta`**: `(estudiante_id, eta_item_id)` — `nota` nullable `decimal(4,2)`. **Cargada** = valor persistido en rango 0–20 (incluye **0**). **No cargada** = `null`/ausente. Tras participación del ítem ETA en el aula: alumno **no cargado** en ETA participante ⇒ efectivo **0** en el promedio ponderado de ese alumno (distinto de “no cargada” a nivel aula para activar participación).

Unicidad estándar e índices por `componente_id` / `eta_item_id` y `estudiante_id` para carga masiva.

### 2.3 Resultado persistido opción **B** (cache)

**`eval_bim_resultados`**: tratado como **resultado/cache** a recalcular; no fuente autoritativa de notas de ingreso.

**Clave lógica** (una fila por estudiante dentro de un mismo **contexto de aula evaluable** junto al curso de malla y bimestre):

- `estudiante_id`
- `malla_curso_id`
- `periodo_academico_id`
- `sede`
- `grado` *(grado estudiante/contexto sección tal como opera hoy DocenteCursoAula / listado estudiantes)*
- `seccion`

Campos persistidos solicitados:

- `promedio_criterios` nullable `decimal(5,2)` — snapshot último cálculo
- `oral` nullable
- `promedio_eta` nullable — ya con participación ETA y ponderación efectiva aplicada
- `examen_bimestral` nullable
- `nivel_logro_numerico` nullable `decimal(5,2)` — 0–20
- `nivel_logro_literal` nullable (`AD`|`A`|`B`|`C`)
- `conclusion_descriptiva` nullable `text`
- `estado_calculo` enum **`completo` | `pendiente`**
- `detalle_json` — desglose: componentes activos, pesos efectivos ETA, etiquetas ETA participantes, contribuciones parciales, flags de incompletitud útiles para UI y soporte (evitar repetir lógica en frontend).
- `calculado_en` nullable timestamp

**Índices**: único recomendado `(estudiante_id, malla_curso_id, periodo_academico_id, sede, grado, seccion)` (ajustar longitud/normalización sede igual que enums existentes).

**Columnas opcionales** para valores de componentes personalizados si el número es variable:

- Preferido: **solo** `detalle_json` + columnas núcleo solicitadas por negocio; si se necesita reporting SQL puro sobre “Proyecto”, valorar segunda tabla de snapshots por componente (fuera alcance minimal si JSON basta para sprint).

### 2.4 Escala cualitativa

- **`eval_bim_escala_logro`** (o nombre institucional unificado): `codigo_literal`, `orden`, `nota_min`, `nota_max`, `activo`. Seed inicial: AD 18–20; A 14–17.99; B 11–13.99; C 0–10.99 (definir límites inclusivos/exclusivos en implementación compatible con decimals).

---

## 3. Servicios definitivos

**Nomenclatura orientativa Laravel** (`App\Services\Curricular\*`):

1. **`EvalBimComponentesResolver`** — Lista componentes ETA + escalar activos por `malla_curso_id` + `periodo_academico_id`; integridad de un solo bloque ETA lógico; no depende de sede.

2. **`PesosComponentesActivoService`** — Redistribución equitativa al altas/básico toggles activo; validación suma 100 % en componentes activos.

3. **`PesosEtaInternosActivosService`** — Redistribución entre **solo** ETA con `activo=true` en config; suma 100 %.

4. **`EtaParticipacionPorAulaService`** (núcleo regla ETA) — Ámbito: `malla_curso_id` + `periodo_academico_id` + **sede** + **grado** + **sección**; estudiantes del aula según formulario masivo actual.
   - ETA **inactiva en config** → no participa nunca.
   - ETA **activa en config** + **ningún alumno del aula** con esa ETA **cargada** (`null`/vacía en todos) → **no participa** aún.
   - ETA **activa en config** + **≥1 alumno** con ETA **cargada** (incluye **0** explícito o cualquier 0–20) → **participa para todos** los alumnos del aula; alumno con ETA **no cargada** en ítem participante → **0** en ese sumando.
   - **Normalización**: pesos internos solo de ETAs **activas en config** que **participan**; renormalizar a 100 % entre ellas antes del promedio por estudiante.
   - Helper recomendado: `etaEstaCargada(?float $nota): bool` — `true` si `nota !== null` y 0 ≤ nota ≤ 20 (validar en persistencia).

5. **`PromedioCriteriosBimestralService`** — Media de CE de temas evaluados: temas activos periodo+malla_curso con fila en `notas_semanales`; sin CE (sin fila) no entran; si ninguno cuenta y componente promedio criterios **activo** → **pendiente** a nivel de logro.

6. **`PromedioEtaPonderadoAulaService`** — Orquestación: llamar participación por aula + pesos efectivos renormarlos → un escalar por estudiante (solo si componente Promedio ETA activo).

7. **`NivelLogroBimestralService`** — Reglas por tipo si el componente está **activo** en config:

   - **Promedio de criterios**: hace falta al menos **un CE** válido entre criterios contados por el servicio de promedio; si no ⇒ **pendiente**.
   - **Oral**, **Examen bimestral**, **personalizado**: nota **registrada** (no puede inferirse 0 desde null); falta ⇒ **pendiente**.
   - **Promedio ETA** (componente **activo** en config):
     - Con **≥1 ETA participante** en el aula: calcular `promedio_eta` por estudiante (ponderación + ceros por alumno sin nota en ETA participante).
     - Con **0 ETAs participantes** (ninguna ETA activa en config tiene ≥1 alumno con ETA cargada): **no** calcular valor sustituto; **no** usar 0 ni ignorar el componente → **`estado_calculo = pendiente`** a nivel resultado bimestral (y `nivel_logro_numerico` / literal null hasta completar o desactivar Promedio ETA).
     - Si el componente **Promedio ETA** está **desactivado** en config → no entra en el nivel (no aplica esta regla de pendiente).

   **Cierre del nivel**: sólo si **ningún** componente activo está en estado pendiente según reglas anteriores: `nivel_logro_numerico` = suma ponderada de valores con **los pesos configurados de componentes activos** (suma 100 %). Si **cualquier** activo obligatorio está pendiente ⇒ `estado_calculo = pendiente` y `nivel_logro_numerico` / literal en null; las columnas snapshot (`oral`, `promedio_criterios`, etc.) pueden igual guardar valores parciales calculables para la UI o quedar null según conveniencia implementación (documentar en código).

   **Aclaración**: “participación” ETA es por **aula**: `malla_curso_id` + `periodo_academico_id` + **sede** + **grado** + **sección** como listó el usuario.

8. **`EscalaLogroService`** — Traduce `nivel_logro_numerico` a literal leyendo filas ordenadas en escala tabla; valores fuera tabla opcional tratamiento configurable (fallback null + log).

9. **`EvalBimResultadoPersistService`** (o método en servicio aplicación) — Upsert resultado cache con columnas solicitadas + `detalle_json`.

10. **`EvalBimInvalidacionServicio`** (nombre orientativo) — Lista central de causas para **refrescar** filas `eval_bim_resultados` del aula afectada (y quizá masivo curso si cambia config global):
    - Cambios en **notas** que afectan CE (`notas_semanales`)
    - Cambios en oral, ETAs, examen, personalizados
    - Cambios en **componentes activos** o **pesos**
    - Cambios en **ETAs activas** o **pesos internos**
    - Cambios en **componentes personalizados** (alta/baja lógica)
    - Tras guardar conclusión no recalcula numérico salvo que se desee invalidar por consistencia de UI (conclusión no afecta nota; puede actualizarse solo campo texto)

**Orden operativo sugerido**: persistir notas → calcular servicios 4–7 → escala → persist resultado.

---

## 4. Endpoints definitivos

**Configuración** (admin/coordinador, permiso `configurar_evaluacion_bimestral` o equivalente; docente **sin** acceso):

- `GET /api/curricular/eval-bim/componentes?malla_curso_id=&periodo_academico_id=` — asegurar seed idempotente de 4+3 ETA al primer GET si no existen.
- `PATCH /api/curricular/eval-bim/componentes/{id}` — peso, activo; opción `redistribuir_activos=true`.
- `POST /api/curricular/eval-bim/componentes` — personalizado.
- `POST /api/curricular/eval-bim/componentes/redistribuir` — equitativo activos.
- CRUD analogues bajo `/api/curricular/eval-bim/etas/...` (crear, patch, desactivar, redistribuir).

**Registro y lectura en aula** (docente con asignación; consulta con `ver_notas_academicas` + contexto global existente):

- Extender **`GET /api/curricular/notas-semanales/formulario`** con bloque `evaluacion_bimestral` calculado **por el aula del request** (sede, grado, sección, `malla_curso_id`, periodo): columnas editables/inactivas según config; mostrar qué ETAs son “participantes” según regla aula (útil para UI).
- **Escritura**: o extensión de `POST /api/curricular/notas-semanales/bulk` con subobjeto bimestral **o** `POST /api/curricular/eval-bim/notas/bulk` con mismas garantías `asignacion_docente_id` que hoy bulk — decisión técnica al implementar; contrato estable: una transacción que persiste escalares ETA + consecuencias + recalculo cache.

**Consulta**

- Coordinador/directivo: mismos filtros globales ya usados (`consulta_global` query path). Endpoints resultado pueden apoyarse en `eval_bim_resultados` solo lectura o extensión eventual de resumen estudiante.

**Permisos (decisión usuario, alinear con [`PermissionsSeeder`](backend/database/seeders/PermissionsSeeder.php) al implementar)**

- **Administrador / coordinador**: configuran componentes, ETAs y pesos (`configurar_evaluacion_bimestral`); consultan notas y nivel; **no registran notas por defecto** (coherente con ausencia típica de `registrar_notas_semanales` en rol coordinador; administrador suele tener stack completo — si debe quedar igual que coordinador sin registro masivo, ajustar mape rol-permiso en seeder como decisión institucional).
- **Docente**: registra criterios (flujo ya existente), oral, ETAs, examen bimestral, personalizados y conclusiones; **solo asignaciones activas**.
- **Directivo**: consulta.
- **Psicólogo/tutor**: consulta resumen académico si ya tiene permiso (`ver_notas_academicas` u otro acordado); **sin registro masivo**.

---

## 5. Cambios frontend definitivos

- **Sin alterar la tabla Excel de C/L/T/CE**: mantener estructura actual de criterios.
- **Bloque final compacto titulado “Evaluación bimestral”** dentro de vista aula/estudiante:
  - No editables: promedio de criterios, promedio ETA, nivel numérico, literal AD/A/B/C.
  - Editables donde componente ETA activo (mostrar ETA 1/2/3 o nombres custom); columnas ETA **desactivadas en configuración**: no editable (ocultos o sólo historia según necesidad segunda iteración — sprint mínimo: ocultos si inactivos).
  - Oral, Examen bimestral, personalizados activos editables si permiso docente.
  - Estado **pendiente** visible (badge texto) cuando `estado_calculo = pendiente`.
- **Conclusiones descriptivas**: **no** columna anchura enorme — **modal, drawer o fila/expansión por estudiante** con textarea; mismo dato opcional editable docente visible en modo consulta.
- **Nueva sección navegable “Configuración de evaluación bimestral”**: filtros año, nivel, grado, sede (solo navegación a curso institucional), curso (`malla_curso_id`), bimestre; lista componentes toggles pesos ETAs igual que especificación usuario; solo roles con permiso config.

Archivos tácticos esperados mismos ya citados antes: [`RegistroNotasSemanalesPanel.jsx`](frontend/src/components/curricular/RegistroNotasSemanalesPanel.jsx), subcomponentes bajo [`notas/`](frontend/src/components/curricular/notas/), [`api.js`](frontend/src/lib/api.js), routing en [`App.jsx`](frontend/src/App.jsx).

---

## 6. Fases de implementación recomendadas

| Orden | Fase | Alcance principal |
|------:|------|-------------------|
| **F0** | Documentación técnico-funcional en repo | Reglas ETA participación/normalización Oral≠0 pendiente resultado cache triggers |
| **F1** | Migraciones modelos enums | Todas las tablas §2 + escala seed |
| **F2** | Servicios §3 sin HTTP | Cobertura unitaria tests §7 ETA/pesos/normalización |
| **F3** | Endpoints configuración | Permiso + policies admin/coordinador |
| **F4** | Integración formulario bulk + observers/jobs invalidación resultado | Una transacción coherencia notas resultado |
| **F5** | UI configuración + bloque bimestral + UX conclusión | Compacto modo consulta/read-only |
| **F6** | Feature tests sprint + revisión perf payload | Perf batch recalc opcional diferido job si lento |

---

## 7. Tests obligatorios

**Backend (PHPUnit), no exhaustivo pero sí casos siguiente:**

1. Promedio criterios omite temas sin `notas_semanales`; sin ningún CE y componente activo → resultado `pendiente`.
2. ETA config activa, nadie con ETA cargada → **no participa**; primer alumno registra **ETA = 0** → ETA **participa** para toda la aula; resto sin nota ⇒ **0** en esa ETA.
3. ETA activa dos alumnos: ejemplo (18/20 vs 20/vacío ⇒ 19 y 10) con pesos equitativos entre participantes; caso alterno si nadie tiene ETA 2 cargada → ETA 2 no participa (promedios solo con ETA 1).
4. **Promedio ETA activo** + ninguna ETA participante en el aula → `estado_calculo = pendiente`, `nivel_logro_numerico` null, **sin** `promedio_eta = 0` artificial.
5. ETA activa dos alumnos con pesos internos desiguales: renorm solo entre participantes (**normalizar antes** del promedio por alumno).
6. ETA inactiva en config: nunca participa aunque existan notas históricas.
7. Pesos internos ETA: renorm dentro conjunto participante (solo B participa ⇒ B **100 %** efectivo en ese cálculo).
8. Oral/Examen/personalizado activo vacío → `pendiente` (nivel null; **no** valor 0).
9. Redistribución componentes: 5 activos ~20 % c/u; desactivar uno → resto suma 100 %.
10. Redistribución pesos ETA al agregar/desactivar solo entre ETA **activas en config**.
11. Validación rechaza suma ≠ 100 % tras edición manual (componentes y ETAs).
12. Persistencia resultado: recálculo tras cambio relevante; ejemplo nivel 14.25 con cuatro pesos 25 %.
13. Permisos: docente no configura; coordinador consulta sin registrar por defecto.
14. Conclusión: opcional; no altera nivel numérico.

---

## 8. Riesgos técnicos

- **Recalc volumétrico**: cambiar pesos globales fuerza recomputación muchas filas `eval_bim_resultados`; mitigación Job cola después commit transacción + feedback UI asíncrono opcional primera versión síncrono si tamaño institución pequeña.
- **Condición concurrencia** docente anota ETA y coordinador desactiv peso mismo tiempo: resultado puede parpadear inconsistencia breve; mitigaciones transacción DB nivel fila estudiante resultado + orden locks o version token config.
- **Null vs 0 en ETA** (cerrado): implementar `etaEstaCargada` de forma unívoca; tests deben cubrir **0 activa participación**; evitar confundir “0 efectivo por alumno sin nota” con “0 cargado por alguien del aula”.
- **`ResumenAcademicoService`** legacy sigue exponiendo métricas distintos de nivel logro nueva — riesgo confusión directivos; etiquetado API o nueva clave nivel logro institucional separada en respuesta estudiante siguiente iteración menor.

---

## 9. Preguntas pendientes (mínimas)

**Resueltas (no reabrir en implementación):**

- ETA **0 explícita** = ETA **cargada** ⇒ puede activar participación del ítem para toda el aula.
- **Promedio ETA activo** + **0 ETAs participantes** en el aula ⇒ **nivel de logro pendiente** (no 0, no omitir componente).

**Pendiente menor (fuera alcance sprint, ya contemplado en modelo):**

- Reportes que agreguen varias secciones del mismo curso: `eval_bim_resultados` ya está particionado por **sede + grado + sección**; definir agregación en sprint posterior si hace falta.

---

## 10. Recomendación final

Proceder con **config única sin sede**, **notas y cache por aula**, **`EtaParticipacionPorAulaService`** con semántica **cargada** (`null` ≠ cargada; **0–20** = cargada, **0 activa el aula**), y **`NivelLogroBimestralService`** que deje el resultado **pendiente** cuando **Promedio ETA** está activo pero aún no hay ETAs participantes (sin sustituto numérico). Mantener **pendiente por vacío** en Oral/Examen/personalizado/criterios; **0 por alumno** solo en ETAs **participantes** sin nota individual. Persistir **opción B** + invalidación sistemática. Legado/riesgo/ML fuera de alcance.

---

## Alcance fuera de Sprint 8.5C (decisión usuario)

No modificar en este sprint: **`RiesgoAcademicoService`**, **Flask**, **Docker**, **modelo ML**, **notas legacy**, **endpoints legacy**. No alterar la **estructura de escritura** de `notas_semanales` (salvo **lectura** y mecanismos de **recálculo** del cache `eval_bim_resultados` cuando cambien los CE, si se implementan hooks/listeners).
