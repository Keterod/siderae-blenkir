# Sprint 8.5C — Evaluación bimestral y nivel de logro

## Estado

**Implementado y validado manualmente.**

Este sprint amplía el módulo curricular académico para que el cálculo bimestral no dependa únicamente de los criterios de evaluación, sino de una estructura completa de evaluación por curso y bimestre.

No se integró todavía con riesgo académico. No se modificó Flask, Docker, modelo ML ni notas legacy.

---

## 1. Objetivo del sprint

Implementar la evaluación bimestral del estudiante por curso, considerando:

- Promedio de criterios de evaluación.
- Nota oral.
- Promedio ETA.
- Examen bimestral.
- Componentes personalizados configurables.
- Nivel de logro numérico del bimestre.
- Nivel literal AD / A / B / C.
- Conclusiones descriptivas opcionales.

La evaluación bimestral se calcula por estudiante, curso, bimestre, sede, grado y sección, pero la configuración de componentes se comparte por curso y bimestre.

---

## 2. Alcance funcional

### 2.1 Componentes predeterminados

Cada curso y bimestre inicia con cuatro componentes activos:

| Componente | Código estable | Peso predeterminado |
|---|---|---:|
| Promedio de criterios | `promedio_criterios` | 25 % |
| Oral | `oral` | 25 % |
| Promedio ETA | `promedio_eta` | 25 % |
| Examen bimestral | `examen_bimestral` | 25 % |

Los componentes activos siempre deben sumar 100 %.

### 2.2 Configuración por curso y bimestre

La configuración se define por:

```text
malla_curso_id + periodo_academico_id
```

No se configuró por sede en esta fase. Por tanto, Chilca y Auquimarca comparten la misma estructura de componentes y pesos para el mismo curso/bimestre.

El cálculo sí considera aula/sección/sede porque las notas dependen de estudiantes reales.

---

## 3. Reglas académicas implementadas

### 3.1 Promedio de criterios

- Se calcula desde los CE guardados en criterios de evaluación.
- Solo participan criterios con `ce_calculado`.
- Criterios sin CE no afectan el promedio.
- Si el componente está activo y no hay ningún CE, el nivel de logro queda pendiente.

### 3.2 Oral

- Es una sola nota por estudiante, curso y bimestre.
- Rango válido: 0 a 20.
- Si está activo y vacío, el nivel de logro queda pendiente.
- No se convierte automáticamente a 0.

### 3.3 ETAs

Por defecto existen:

- ETA 1
- ETA 2
- ETA 3

Reglas finales:

- ETA vacía o `null` = no cargada.
- ETA con `0` explícito = cargada.
- ETA con cualquier valor de 0 a 20 = cargada.
- ETA activa + nadie del aula tiene nota = no participa todavía.
- ETA activa + al menos un alumno del aula tiene nota = participa para todos.
- Si una ETA participa y un alumno no tiene nota en ella, para ese alumno cuenta como 0.
- ETA inactiva = no participa nunca.
- Si Promedio ETA está activo y ninguna ETA participa, el nivel queda pendiente.

La participación ETA se calcula por:

```text
malla_curso_id + periodo_academico_id + sede + grado + seccion
```

### 3.4 Examen bimestral

- Es una sola nota por estudiante, curso y bimestre.
- Rango válido: 0 a 20.
- Si está activo y vacío, el nivel de logro queda pendiente.
- No se convierte automáticamente a 0.

### 3.5 Componentes personalizados

- Se pueden agregar por curso y bimestre.
- Cada componente personalizado tiene una nota por estudiante.
- Si está activo y vacío, el nivel queda pendiente.
- Al agregar o desactivar componentes, los pesos activos se redistribuyen automáticamente.
- Luego pueden editarse manualmente, siempre respetando suma 100 %.

### 3.6 Conclusiones descriptivas

- Son opcionales.
- Se registran por estudiante, curso y bimestre.
- No afectan el cálculo del nivel de logro.
- Se editan desde modal para evitar una columna extensa en la tabla.

---

## 4. Nivel de logro bimestral

El nivel de logro numérico se calcula con los componentes activos y sus pesos.

Ejemplo con pesos 25 %:

```text
Promedio criterios = 14
Oral = 16
Promedio ETA = 15
Examen bimestral = 12

Nivel de logro = 14.25
```

El resultado se guarda como `decimal(5,2)`.

### 4.1 Nivel literal

Se implementó escala:

| Literal | Rango inicial |
|---|---:|
| AD | 18.00 - 20.00 |
| A | 14.00 - 17.99 |
| B | 11.00 - 13.99 |
| C | 0.00 - 10.99 |

---

## 5. Fases implementadas

## 5.1 Sprint 8.5C-1 — Backend base

### Migración

- `backend/database/migrations/2026_05_26_100000_create_eval_bimestral_tables.php`

### Tablas creadas

- `eval_bim_componentes`
- `eval_bim_eta_items`
- `eval_bim_notas_scalar`
- `eval_bim_notas_eta`
- `eval_bim_resultados`
- `eval_bim_escala_logro`

### Modelos

- `EvalBimComponente`
- `EvalBimEtaItem`
- `EvalBimNotaScalar`
- `EvalBimNotaEta`
- `EvalBimResultado`
- `EvalBimEscalaLogro`

### Enums / DTOs

- `EvalBimComponenteTipo`
- `EvalBimEstadoCalculo`
- `AulaEvaluacionContext`
- `NivelLogroBimestralResultado`

### Servicios base

- `EvaluacionComponentesResolver`
- `EvaluacionBimestralConfiguracionService`
- `PesosComponentesService`
- `PesosEtaInternosService`
- `EtaParticipacionPorAulaService`
- `PromedioCriteriosService`
- `PromedioEtaService`
- `NivelLogroBimestralService`
- `EscalaLogroService`
- `EvalBimResultadoPersistService`

### Seeder

- `EvalBimEscalaLogroSeeder`
- Integrado en `CurricularModuleSeeder`

---

## 5.2 Sprint 8.5C-2 — API evaluación bimestral

### Controlador

- `backend/app/Http/Controllers/Api/Curricular/EvaluacionBimestralController.php`

### Requests

- `EvaluacionBimestralConfigQueryRequest`
- `StoreEvalBimComponenteRequest`
- `UpdateEvalBimComponenteRequest`
- `StoreEvalBimEtaRequest`
- `UpdateEvalBimEtaRequest`
- `BulkEvaluacionBimestralRequest`

### Servicios API

- `EvaluacionBimestralFormularioService`
- `EvaluacionBimestralBulkService`

### Rutas agregadas

Prefijo:

```text
/api/curricular/evaluacion-bimestral
```

| Método | Ruta | Uso |
|---|---|---|
| GET | `/config` | Consultar/crear configuración |
| GET | `/resultados` | Consultar resultados bimestrales |
| POST | `/componentes` | Agregar componente personalizado |
| PATCH | `/componentes/{id}` | Actualizar componente |
| POST | `/etas` | Agregar ETA |
| PATCH | `/etas/{id}` | Actualizar ETA |
| GET | `/formulario` | Cargar formulario bimestral |
| POST | `/bulk` | Guardar evaluación bimestral |

### Permiso agregado

- `configurar_evaluacion_bimestral`

Asignado a:

- administrador
- coordinador_academico

---

## 5.3 Sprint 8.5C-3 — Frontend registro bimestral

### Archivos creados

- `frontend/src/lib/evaluacionBimestral.js`
- `frontend/src/components/curricular/notas/bimestral/evaluacionBimestralUtils.js`
- `frontend/src/components/curricular/notas/bimestral/EvalBimInputCell.jsx`
- `frontend/src/components/curricular/notas/bimestral/EvalBimReadonlyCell.jsx`
- `frontend/src/components/curricular/notas/bimestral/ConclusionDescriptivaModal.jsx`
- `frontend/src/components/curricular/notas/bimestral/EvaluacionBimestralAlumnoRow.jsx`
- `frontend/src/components/curricular/notas/bimestral/EvaluacionBimestralTable.jsx`
- `frontend/src/components/curricular/notas/bimestral/EvaluacionBimestralBlock.jsx`
- `frontend/src/components/curricular/notas/bimestral/EvaluacionBimestralEstudianteCard.jsx`

### Archivo modificado

- `frontend/src/components/curricular/RegistroNotasSemanalesPanel.jsx`

### Funcionalidad

Se agregó bloque compacto **Evaluación bimestral** debajo de la tabla de criterios.

Campos editables por docente:

- Oral
- ETAs activas
- Examen bimestral
- Componentes personalizados
- Conclusiones descriptivas

Campos no editables:

- Promedio de criterios
- Promedio ETA
- Nivel de logro numérico
- Nivel literal AD/A/B/C
- Estado completo/pendiente

Modo consulta global para administrador, coordinador y directivo: solo lectura.

---

## 5.4 Previsualización en vivo

Se agregó preview frontend para:

- Promedio ETA
- Nivel de logro numérico
- Nivel literal AD/A/B/C
- Estado completo/pendiente

El backend sigue siendo fuente oficial al guardar.

### Archivos principales

- `evaluacionBimestralUtils.js`
- `EvalBimPreviewValue.jsx`
- `EvaluacionBimestralTable.jsx`
- `EvaluacionBimestralAlumnoRow.jsx`
- `EvaluacionBimestralEstudianteCard.jsx`
- `EvaluacionBimestralBlock.jsx`
- `RegistroNotasSemanalesPanel.jsx`

### Regla

Si el valor mostrado es preview y difiere del persistido, aparece indicador naranja de “previsualización / sin guardar”.

---

## 5.5 Corrección de Promedio de criterios

Se corrigió que `Prom. crit.` apareciera vacío aunque existieran CE.

### Causa

`eval_bim_resultados` no se recalculaba al guardar notas C/L/T/CE.

### Corrección

Al guardar notas semanales:

```text
NotaSemanalBulkService
→ guarda CE
→ construye AulaEvaluacionContext
→ llama EvalBimResultadoPersistService::recalcularAula()
```

Además, el frontend recarga evaluación bimestral después de **Guardar notas**.

### Archivos modificados

- `backend/app/Services/Curricular/NotaSemanalBulkService.php`
- `frontend/src/components/curricular/RegistroNotasSemanalesPanel.jsx`
- `backend/tests/Feature/Curricular/CurricularApiTest.php`

---

## 5.6 Sprint 8.5C-4 — UI configuración bimestral

### Pantalla nueva

Menú curricular:

```text
Configuración bimestral
```

Ruta interna:

```text
curricular_eval_bim
```

### Archivos creados

- `frontend/src/components/curricular/configuracion-bimestral/ConfiguracionBimestralPanel.jsx`
- `frontend/src/components/curricular/configuracion-bimestral/ConfiguracionBimestralFiltros.jsx`
- `frontend/src/components/curricular/configuracion-bimestral/ComponentesEvaluacionTable.jsx`
- `frontend/src/components/curricular/configuracion-bimestral/ComponenteEvaluacionRow.jsx`
- `frontend/src/components/curricular/configuracion-bimestral/EtasConfigTable.jsx`
- `frontend/src/components/curricular/configuracion-bimestral/EtaConfigRow.jsx`
- `frontend/src/components/curricular/configuracion-bimestral/PesosResumen.jsx`
- `frontend/src/components/curricular/configuracion-bimestral/configuracionBimestralUtils.js`

### Archivos modificados

- `frontend/src/App.jsx`
- `frontend/src/lib/evaluacionBimestral.js`

### Funcionalidad

Permite a administrador/coordinador:

- Ver componentes activos/inactivos.
- Activar/desactivar componentes.
- Editar pesos.
- Agregar componentes personalizados.
- Ver ETAs activas/inactivas.
- Agregar ETAs.
- Activar/desactivar ETAs.
- Editar pesos internos de ETAs.

### Restricciones

- No se permite borrado físico.
- La suma de pesos activos debe ser 100 %.
- La suma de pesos de ETAs activas debe ser 100 %.
- Al activar/desactivar/agregar, los pesos se redistribuyen automáticamente.

---

## 6. Permisos finales del sprint

| Permiso | Uso |
|---|---|
| `configurar_evaluacion_bimestral` | Configurar componentes, pesos y ETAs |
| `ver_notas_academicas` | Consultar notas y resultados |
| `registrar_notas_semanales` | Registrar notas como docente asignado |

### Roles

| Rol | Configura evaluación | Consulta notas | Edita notas |
|---|---:|---:|---:|
| Administrador | Sí | Sí | No por defecto |
| Coordinador académico | Sí | Sí | No por defecto |
| Directivo | No | Sí | No |
| Docente | No | Solo asignadas | Sí, solo asignadas |
| Psicólogo/tutor | No | Resumen/perfil si corresponde | No |

---

## 7. Resultados de pruebas reportados

### 8.5C-1

```text
EvaluacionBimestral: 24 passed (80 assertions)
Curricular: 125 passed (436 assertions)
```

### 8.5C-2

```text
EvaluacionBimestral: 40 passed (138 assertions)
Curricular: 141 passed (494 assertions)
```

### Corrección Prom. crit.

```text
EvaluacionBimestral: 40 passed (138 assertions)
Curricular: 144 passed (507 assertions)
npm run build: OK
```

### 8.5C-3 / 8.5C-4 frontend

```text
npm run build: OK
```

---

## 8. Comandos importantes

### Ejecutar migraciones

```bash
docker compose exec app-backend php artisan migrate
```

### Seed permisos

```bash
docker compose exec app-backend php artisan permission:cache-reset
docker compose exec app-backend php artisan db:seed --class=PermissionsSeeder
docker compose exec app-backend php artisan optimize:clear
```

### Tests backend

```bash
docker compose exec app-backend php artisan test --filter=EvaluacionBimestral
docker compose exec app-backend php artisan test --filter=Curricular
```

### Reiniciar frontend

```bash
docker compose restart app-frontend
```

---

## 9. Exclusiones

No se implementó en este sprint:

- Integración con riesgo académico.
- Modificación de Flask.
- Modificación de Docker.
- Modelo ML.
- PDF.
- Importación Excel.
- Reportes finales.
- Boletas oficiales.
- Integración de riesgo con nivel de logro bimestral.

---

## 10. Pendientes posteriores

1. Resumen académico bimestral en perfil del estudiante.
2. Promedio por curso, área y bimestre usando `eval_bim_resultados`.
3. Visualización de conclusiones descriptivas en resumen académico.
4. Integración posterior con riesgo académico usando resultados bimestrales.
5. Recalculo automático vía observers/hooks cuando cambien configuraciones o notas de criterios.
6. Reportes/boletas.
7. Exportación futura PDF/Excel.

---

## 11. Estado final

Sprint 8.5C queda implementado y validado manualmente en sus bloques principales:

- Backend base.
- API.
- Registro bimestral en notas.
- Previsualización en vivo.
- Configuración bimestral.
- Recalculo de promedio de criterios al guardar notas.

Siguiente paso recomendado:

```text
Resumen académico bimestral
```

Luego:

```text
Integración mínima con RiesgoAcademicoService
```
