# Módulo Curricular Académico — SIDERAE-Blenkir

**Versión documento:** 3.1 (reglas finales aprobadas — Sprint 8.5A Fase 2)  
**Estado en código:** Fase 2 backend (migraciones, modelos, seeders, servicios CE) en progreso; API/frontend pendiente (8.5B).

---

## 1. Objetivo del documento

Define la implementación del módulo curricular académico de SIDERAE-Blenkir:

**Año escolar → Nivel → Grado → Malla curricular → Área → Curso → Tema semanal → Competencias → Capacidades → Notas C/L/T → CE calculado.**

Sirve para:

- Registrar notas semanales ordenadas por curso y semana.
- Vincular temas con competencias y capacidades del Currículo Nacional.
- Calcular CE automáticamente y promedios por curso, área y bimestre.
- Alimentar el perfil académico y el **promedio agregado** del flujo de riesgo sin romper el sistema legacy de `notas` y `materias`.

---

## 2. Referencias y fuentes

| Fuente | Ubicación | Uso en el módulo |
|--------|-----------|------------------|
| Currículo Nacional de Educación Básica (2016) | [`docs/referencias/curriculo-nacional-2016-2.pdf`](../referencias/curriculo-nacional-2016-2.pdf) | **Áreas, competencias y capacidades** (nombres oficiales resumidos en seeders). **No** copiar estándares ni desempeños completos. |
| Boletas institucionales Blenkir (cursos por nivel) | [`docs/referencias/cursos/boletas blenkir INICIAL (1).docx`](../referencias/cursos/boletas%20blenkir%20INICIAL%20(1).docx) | Plantilla institucional **Inicial** (áreas y cursos internos). |
| | [`docs/referencias/cursos/boletas blenkir PRIMARIA.docx`](../referencias/cursos/boletas%20blenkir%20PRIMARIA.docx) | Plantilla institucional **Primaria** (todos los grados). |
| | [`docs/referencias/cursos/boletas blenkir.docx`](../referencias/cursos/boletas%20blenkir.docx) | Plantilla institucional **Secundaria** (todos los grados). |
| Excel registro auxiliar 2do | `REGISTRO_AUXILIAR_2DO_GRADO.xlsx` — **obsoleto / no usar** | Reemplazado por boletas DOCX anteriores. No implementar importación Excel en 8.5. |
| Sprint 7.6 (legacy) | `materias`, `notas`, lotes | Conviven en paralelo; UI separada. |
| Plan / sprints | `sprints/sprint 8.5A.md`, `sprints/sprint 8.5B.md` | Ejecución por fases. |

---

## 3. Fuentes de datos: Currículo Nacional vs organización del colegio

### 3.1 Del Currículo Nacional (seeders y catálogo)

- **Áreas** curriculares por nivel (Inicial, Primaria, Secundaria).
- **Competencias** por área.
- **Capacidades** por competencia.

Reglas del seeder:

- Solo esas tres entidades.
- Nombres oficiales **resumidos** (identificables, sin párrafos del PDF).
- Opcional: campo `codigo` o `referencia_cn` corto.
- **Excluir:** estándares de aprendizaje, desempeños, indicadores, textos largos.

### 3.2 Organización interna del colegio (institucional)

Los **cursos** dentro de un área son decisión del colegio, no del CN. Ejemplos validados para **Primaria** (plantilla institucional, grado 2do):

| Área (CN) | Cursos internos (colegio) |
|-----------|---------------------------|
| Matemática | Aritmética, Álgebra, Raz. Matemático, Trigonometría |
| Comunicación | Comprensión y Producción de Textos, Gramática, Raz. Verbal |
| Ciencia y Tecnología | Mundo Físico, Cuerpo Humano |
| Personal Social | Historia, Geografía, Ciudadanía |
| Educación Física | Educ. Física |
| Inglés | Inglés |
| Educación Religiosa | Educación Religiosa |
| Arte y Cultura | Taller |

### 3.3 Plantillas institucionales por nivel (boletas DOCX)

**Inicial** (`boletas blenkir INICIAL (1).docx`):

| Área | Cursos |
|------|--------|
| Matemática | Aritmética, Geometría, Raz. Matemático |
| Comunicación | Comunicación, Raz. Verbal |
| Ciencia y Tecnología | Ciencia y Tecnología |
| Personal Social | Personal Social |
| Psicomotricidad | Educación Física |
| Inglés | Inglés |

**Primaria** (`boletas blenkir PRIMARIA.docx`): ver tabla §3.2 (misma estructura para **todos** los grados 1ro–6to).

**Secundaria** (`boletas blenkir.docx`):

| Área | Cursos |
|------|--------|
| Matemática | Aritmética, Álgebra, Raz. Matemático, Trigonometría |
| Comunicación | Lenguaje, Literatura, Raz. Verbal |
| Ciencia y Tecnología | Biología, Física, Química |
| Desarrollo Personal, Ciudadanía y Cívica | Cívica, Psicología |
| Ciencias Sociales | Historia del Perú, Historia Universal, Geografía |
| Educación Física | Educ. Física |
| Inglés | Inglés |
| Educación para el Trabajo | Educación para el Trabajo |
| Educación Religiosa | Educación Religiosa |
| Arte y Cultura | Taller |

**Secundaria:** cursos institucionales según boleta Blenkir (§3.3). Seeders materializan la plantilla por grado; **no** agregar cursos fuera de esa lista.

**Inicial:** cursos institucionales según boleta Blenkir (§3.3). Malla configurable y registro de notas semanales (C/L/T/CE, eval bim, Excel) con `estudiantes.nivel=inicial`. Riesgo académico excluido hasta actualizar ML.

---

## 4. Alcance multi-nivel y límites de tablas legacy

### 4.1 Tres niveles curriculares

| Nivel | Grados (catálogo curricular) | En Sprint 8.5 |
|-------|------------------------------|---------------|
| `inicial` | `3 años`, `4 años`, `5 años` | Mismo flujo curricular que P/S (malla, CN, temas, notas, eval bim). **Sin** riesgo académico hasta fase ML. |
| `primaria` | `1ro` … `6to` | Malla, temas, notas semanales (con estudiantes legacy). |
| `secundaria` | `1ro` … `5to` | Malla y CN; notas si hay estudiantes legacy secundaria. |

### 4.2 Tablas legacy sin cambio de `nivel`

**Confirmado en código actual:** `estudiantes.nivel` y `materias.nivel` son `enum('primaria','secundaria')` con validaciones en Form Requests, batch y demo seed.

**Decisión cerrada:** en Sprint 8.5 **no** se migra ese enum. El valor `inicial` existe **solo** en tablas curriculares nuevas.

**Estado (Fase 3):** con `estudiantes.nivel=inicial` y equivalencias de grado, el registro de notas semanales usa el mismo flujo que Primaria/Secundaria.

### 4.3 Tabla obligatoria `equivalencias_grado`

Puente entre grado curricular y grado en `estudiantes.grado` (formato legacy con símbolo °).

| nivel | grado_curricular | grado_estudiante_legacy |
|-------|------------------|-------------------------|
| primaria | 1ro | 1° |
| primaria | 2do | 2° |
| primaria | 3ro | 3° |
| primaria | 4to | 4° |
| primaria | 5to | 5° |
| primaria | 6to | 6° |
| secundaria | 1ro | 1° |
| secundaria | 2do | 2° |
| secundaria | 3ro | 3° |
| secundaria | 4to | 4° |
| secundaria | 5to | 5° |

Toda validación docente↔estudiante debe usar `EquivalenciaGradoService`.

---

## 5. Regla principal del negocio

El sistema **no** crea la malla desde cero sin contexto.

Orden obligatorio de configuración:

1. **Año escolar**
2. **Nivel** educativo (curricular)
3. **Grado**

Al elegir **año + nivel + grado**, el sistema **obtiene o prepara automáticamente** la malla predeterminada (sin flujo visible de “importar” o “cargar plantilla” para el usuario habitual). Luego se administran cursos en pantalla (agregar / desactivar / reactivar).

---

## 6. Plantillas institucionales (DOCX)

Fuentes: §2 y §3.3. Los seeders (`PlantillasInstitucionalesSeeder`) crean plantilla por **cada grado** del nivel con `detalle_completo=true` y los cursos institucionales del catálogo.

- **Primaria:** estructura §3.2 para grados 1ro–6to.
- **Inicial:** estructura §3.3 Inicial para 3/4/5 años.
- **Secundaria:** estructura §3.3 Secundaria para 1ro–5to.

API `GET /api/curricular/mallas/grado` materializa la malla del año escolar sin duplicar cursos al repetir la consulta.

---

## 7. Nivel Inicial — flujo curricular operativo (notas); riesgo pendiente

**Sí existe en el diseño curricular** (tablas nuevas):

- Nivel `inicial`.
- Grados: `3 años`, `4 años`, `5 años`.
- Áreas, competencias y capacidades base desde el Currículo Nacional (nombres resumidos).

**No forzar en esta fase:**

- ~~Registro de notas para estudiantes de Inicial.~~ **Implementado** (notas semanales + eval bim + Excel).
- Procesamiento de riesgo para Inicial (pendiente ML / Fase 5).
- Modificación de `estudiantes.nivel` (legacy sigue `primaria|secundaria` únicamente).

Inicial opera el flujo curricular completo (estudiantes, notas, eval bim, Excel). La exclusión de **riesgo académico** permanece hasta actualizar el modelo ML.

---

## 8. Nivel Secundaria — plantilla institucional

- Seed: áreas CN + **cursos institucionales** según boleta `boletas blenkir.docx` (§3.3).
- **No** inventar cursos adicionales fuera de esa lista.
- Misma lógica de malla predeterminada por grado que Primaria e Inicial.

---

## 9. Roles y responsabilidades

### Administrador

- Todo el módulo curricular.
- Asignación de docentes.
- Acceso al bloque **legacy** (Materias, notas masivas) en menú separado.

### Coordinador académico / Tutora

| Acción | Permitido |
|--------|-----------|
| Configurar mallas (agregar / desactivar / reactivar cursos del grado) | Sí |
| Crear y editar temas semanales | Sí |
| Asignar docentes a curso + sección | Sí |
| Configurar pesos C/L/T | Sí |
| Consultar notas / resumen académico | Sí |
| **Registrar notas semanales (C/L/T)** | **No** en 8.5 |

Un permiso especial futuro (`registrar_notas_semanales_coordinador` o similar) queda **fuera de alcance** hasta decisión explícita.

### Docente

| Acción | Permitido |
|--------|-----------|
| Ver cursos/aulas **asignados** | Sí |
| Seleccionar bimestre y semana configurados | Sí |
| Registrar **solo** Cuaderno, Libro, Tarea | Sí |
| Ver CE calculado | Sí |
| Crear áreas, cursos, temas, competencias, capacidades | **No** |

### Directivo / Psicólogo

- Consulta según `ver_notas_academicas` / lectura de malla.
- No edición de notas.

---

## 10. Temas semanales y secciones

### 10.1 Alcance del tema

- El tema pertenece a la **malla del grado** (año + nivel + grado + curso + bimestre + semana).
- **No** lleva `seccion`.
- **2do A** y **2do B** comparten el **mismo** tema semanal.
- Cada **docente** registra notas **solo** para estudiantes de **su sección** asignada (`docente_curso_aulas.seccion`).

### 10.2 Unicidad (obligatoria en 8.5)

**Un solo tema activo** por combinación:

`(malla_curso_id, periodo_academico_id, semana_academica_id)`

Segundo tema para el mismo curso + bimestre + semana → **422** (validación + índice único en BD).

**Mejora futura (backlog):** permitir dos temas activos en la misma semana; temas distintos por sección (`temas_semanales.seccion`).

### 10.3 Flujo coordinador — temas

Campos: año, nivel, grado, bimestre, semana, área, curso, título, descripción opcional, competencias (≥1), capacidades (≥1). Competencias filtradas por área; capacidades por competencia seleccionada.

---

## 11. Semanas académicas

- Entidades `periodos_academicos` y `semanas_academicas`.
- Número de semanas **configurable** por periodo (`semanas_planificadas` y/o altas de semanas).
- **Seed demo:** año 2026, 4 bimestres, **4 semanas por bimestre** (16 semanas). Valor **demostrativo**; el colegio puede ajustar la cantidad real después sin cambiar el modelo.
- Coordinador puede activar/desactivar semanas vía API/UI (8.5B).

---

## 12. Flujo del docente — registro de notas

1. Ingresa a **Notas semanales** (no al módulo legacy masivo).
2. El sistema lista solo asignaciones activas (`docente_curso_aulas`).
3. Selecciona bimestre y semana.
4. Selecciona curso asignado.
5. Ve el tema semanal único del grado (compartido entre secciones).
6. Lista estudiantes de **su sección** que coincidan con la asignación en: `anio_escolar`, `nivel`, `grado` (vía `equivalencias_grado`), `seccion`, `sede`.
7. Registra **una o más** de C, L, T (0–20 cada una presente); CE se calcula al guardar.
8. No puede guardar sin tema, sin asignación, sin al menos una nota C/L/T, ni si curso/tema están inactivos.

---

## 13. Tipos de nota, pesos y CE

| Código | Significado | Obligatorio | Editable |
|--------|-------------|-------------|----------|
| C | Cuaderno | No* | Sí |
| L | Libro | No* | Sí |
| T | Tarea | No* | Sí |
| CE | Evaluación semanal calculada | Calculado | **No** |

\*Debe existir **al menos una** nota entre C, L y T. Si las tres están vacías: no guardar y mostrar error.

### 13.1 Cálculo de CE (solo notas presentes)

**Pesos iguales (por defecto):**

| Notas presentes | Fórmula |
|-----------------|---------|
| C, L y T | CE = promedio(C, L, T) |
| C y T | CE = promedio(C, T) |
| Solo L | CE = L |

**Pesos personalizados:** usar solo pesos de las notas presentes y **normalizar** sobre la suma de esos pesos.

Ejemplo: C=50, L=30, T=20. Si solo hay C y T:

`CE = ((C × 50) + (T × 20)) / (50 + 20)`

### 13.2 Pesos configurables

- Cada peso ≥ 0.
- La suma de los tres pesos configurados debe ser **exactamente 100**.
- Por defecto: Cuaderno **33.33**, Libro **33.33**, Tarea **33.34**.
- Si un peso es 0, solo válido si fue configurado explícitamente (no por omisión).
- Precedencia de resolución: curso → área → grado/nivel → global.

### 13.3 Almacenamiento y UI de CE

- `ce_calculado`: `decimal(5,2)` en BD.
- UI: mostrar con 1 o 2 decimales.
- No editable manualmente.

### 13.4 Columnas de notas en BD

`nota_cuaderno`, `nota_libro`, `nota_tarea`: nullable `decimal(4,2)`; al menos una no nula al persistir.

---

## 14. Promedios requeridos

- Semanal = CE del tema.
- Bimestral por curso = promedio de CE de semanas del bimestre.
- Por área = promedio de cursos activos del área.
- Para riesgo (8.5): agregado compatible con `promedio_notas` actual (bimestral general desde CE o fallback `notas` legacy).

Etiquetas visuales de bimestres: B.I, II.B, III.B, IV.B, P.F.

---

## 15. Convivencia con módulo legacy (Sprint 7.6)

| Aspecto | Legacy | Curricular |
|---------|--------|------------|
| Tablas | `materias`, `notas` | Tablas `mallas_*`, `notas_semanales`, etc. |
| UI coordinador | Oculto del menú principal | Malla, Temas, Pesos, Asignación, Consulta notas |
| UI admin | Grupo «Datos académicos (legacy)» al final | Igual bloque curricular que coordinador |
| UI docente | Notas masivas ocultas | Notas semanales |
| Riesgo | `avg(nota)` | CE agregado si existe; si no, legacy |

**No** modificar contratos de `/api/materias`, `/api/notas`, `/api/notas/lote`.

---

## 16. Modelo de base de datos

### 16.1 `equivalencias_grado` (obligatoria)

| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigint PK | |
| nivel | enum/string | `primaria`, `secundaria` |
| grado_curricular | string | `1ro`…`6to` / `1ro`…`5to` |
| grado_estudiante_legacy | string | `1°`…`6°` / `1°`…`5°` |
| timestamps | | |
| **unique** | | `(nivel, grado_curricular)` |

### 16.2 Catálogo CN e institucional

**`areas`:** id, nombre, nivel (`inicial|primaria|secundaria`), activo, timestamps.

**`cursos_catalogo`:** id, area_id FK, nombre, es_institucional (bool), activo, timestamps.

**`competencias`:** id, area_id FK, nombre, descripcion (corta), codigo nullable, activo, timestamps.

**`capacidades`:** id, competencia_id FK, nombre, descripcion (corta), activo, timestamps.

### 16.3 Plantilla y malla

**`plantillas_curriculares`:** id, nivel, grado, nombre, activo, detalle_completo (bool, default false para no-2do), timestamps.

**`plantilla_cursos`:** id, plantilla_curricular_id, area_id, curso_catalogo_id, orden, activo, timestamps.

**`mallas_curriculares`:** id, anio_escolar, nivel, grado, estado (borrador|activa), plantilla_curricular_id nullable, timestamps. **unique** `(anio_escolar, nivel, grado)`.

**`malla_cursos`:** id, malla_curricular_id, area_id, curso_catalogo_id, orden, activo, timestamps.

### 16.4 Calendario

**`periodos_academicos`:** id, anio_escolar, bimestre (1–4), fecha_inicio, fecha_fin, semanas_planificadas (int, default 4), activo, timestamps.

**`semanas_academicas`:** id, periodo_academico_id, numero_semana, fecha_inicio, fecha_fin, activo, timestamps. **unique** `(periodo_academico_id, numero_semana)`.

### 16.5 Temas y notas

**`temas_semanales`:** id, malla_curso_id, periodo_academico_id, semana_academica_id, titulo, descripcion nullable, creado_por (user_id), activo, timestamps. **Sin seccion.** **unique** `(malla_curso_id, periodo_academico_id, semana_academica_id)` donde activo.

**`tema_competencias`:** tema_semanal_id, competencia_id.

**`tema_capacidades`:** tema_semanal_id, competencia_id, capacidad_id.

**`configuracion_pesos_evaluacion`:** id, nivel nullable, grado nullable, area_id nullable, curso_catalogo_id nullable, peso_cuaderno, peso_libro, peso_tarea, activo, timestamps.

**`docente_curso_aulas`:** id, user_id, malla_curso_id, anio_escolar, nivel, grado, seccion, sede, activo, timestamps. **Regla:** como máximo **un docente activo** por `(anio_escolar, nivel, grado, seccion, sede, malla_curso_id)` — validación en dominio + índice único en combinación activa.

**`notas_semanales`:** id, estudiante_id, tema_semanal_id, docente_id, `nota_cuaderno` nullable, `nota_libro` nullable, `nota_tarea` nullable, `ce_calculado` decimal(5,2), pesos_usados_json, fecha_registro, timestamps. **unique** `(estudiante_id, tema_semanal_id)`.

### 16.7 Desactivación y borrado

- **No eliminar físicamente** `malla_cursos`, `temas_semanales`, `docente_curso_aulas`, `configuracion_pesos_evaluacion` si tienen relaciones o historial: usar `activo = false`.
- Desactivar curso o tema **bloquea registros futuros**; las notas ya guardadas se conservan.

### 16.6 Modelos Laravel (definitivos)

`EquivalenciaGrado`, `Area`, `CursoCatalogo`, `Competencia`, `Capacidad`, `PlantillaCurricular`, `PlantillaCurso`, `MallaCurricular`, `MallaCurso`, `PeriodoAcademico`, `SemanaAcademica`, `TemaSemanal`, `ConfiguracionPesoEvaluacion`, `DocenteCursoAula`, `NotaSemanal`.

Servicios: `CatalogoNivelGrado`, `EquivalenciaGradoService`, `CeCalculatorService`, `PesoEvaluacionResolver`, `PromedioAcademicoAggregator`.

---

## 17. Permisos (definitivos)

| Permiso | Descripción |
|---------|-------------|
| `ver_malla_curricular` | Lectura malla, temas, catálogo |
| `gestionar_malla_curricular` | CRUD malla y cursos de malla |
| `gestionar_temas_semanales` | CRUD temas y semanas |
| `configurar_pesos_evaluacion` | CRUD pesos |
| `gestionar_asignaciones_docente` | CRUD asignaciones docente–curso–sección |
| `ver_notas_academicas` | Consulta notas, resumen, formulario lectura |
| `registrar_notas_semanales` | **Solo docente** — bulk C/L/T |

**No asignar** `registrar_notas_semanales` a coordinador en `PermissionsSeeder` de 8.5B.

---

## 18. Endpoints API (definitivos)

Prefijo `/api`, middleware `auth:sanctum` + `permission:*`. Lista completa en [`sprints/sprint 8.5B.md`](../../sprints/sprint%208.5B.md).

Grupos: catálogo CN, mallas, periodos/semanas, temas, pesos, asignaciones docente, notas semanales, resumen estudiante, docente/aulas-cursos.

---

## 19. UI/UX

### Pantallas nuevas (`frontend/src/components/curricular/`)

1. Malla curricular por grado  
2. Temas semanales  
3. Configuración de pesos  
4. **Asignación docente** (obligatoria)  
5. Registro semanal de notas (docente)  
6. Consulta de notas académicas (coordinador)  
7. Resumen académico en perfil estudiante  

### Menú

- **Coordinador:** curricular únicamente en flujo principal (sin Materias/Notas masivas).  
- **Administrador:** curricular + legacy al final.  
- **Docente:** Notas semanales.  

Coherencia con `docs/ui/mockups/guia-ui-siderae.md`. Sin botones muertos.

---

## 20. Integración con riesgo académico

- **No** modificar Flask ni payload de `/predict`.
- `RiesgoAcademicoService`: si hay `notas_semanales` con CE en el año → `promedio_notas` desde `PromedioAcademicoAggregator`; si no → `Nota::avg('nota')`.
- Validación datos mínimos: aceptar cualquiera de las dos fuentes de notas del año.

---

## 21. Restricciones para implementación

- No romper: login, Sanctum, estudiantes legacy, asistencia, VSE, riesgo, alertas, dashboard, Docker.  
- No importación Excel en 8.5.  
- No reportes PDF nuevos en 8.5.  
- No roles nuevos.  
- No migrar enum `estudiantes`/`materias` en 8.5.  
- No estándares/desempeños CN en seed.  
- No cursos Secundaria inventados.  
- Notas Inicial: mismo flujo que P/S (sin riesgo académico).  
- No coordinador registrando notas semanales.  

---

## 22. Pruebas obligatorias

### 8.5A — PHPUnit unitario

- CE y pesos (iguales y personalizados; suma ≠ 100 %).  
- Equivalencias grado.  

### 8.5B — Feature (T1–T12)

Ver tabla en `sprint 8.5B.md` (403, plantilla 2do, unicidad tema, docente sin asignación, CE, coordinador sin POST notas, riesgo CE/legacy, Inicial notas en `NotasSemanalesInicialTest`).

### Sprint 9

Regresión integral + smoke curricular en Cypress si aplica.

---

## 23. Criterios de aceptación finales del módulo

1. Al elegir año + nivel + grado, la malla predeterminada se obtiene o prepara automáticamente (§6) con cursos institucionales DOCX, sin temas.  
2. Primaria, Inicial y Secundaria: plantilla por grado con catálogo institucional (§3.2–3.3).  
3. Secundaria: solo cursos de boleta institucional; no inventar adicionales.  
4. Inicial: configuración y registro de notas de estudiantes (sin riesgo académico).  
5. `equivalencias_grado` poblada y usada en registro docente.  
6. Tema único por curso + bimestre + semana; 2do A/B comparten tema.  
7. Docente solo registra en asignaciones y su sección.  
8. Coordinador asigna docentes por UI; no registra C/L/T.  
9. CE automático; 0–20; no editable.  
10. Pesos suman 100 %.  
11. Semanas demo 4/bimestre; estructura ampliable.  
12. Menú sin mezcla visual legacy/coordinador.  
13. Riesgo con CE o fallback legacy.  
14. Tests T1–T12 en verde.  
15. Endpoints legacy intactos.  

---

## 24. Backlog explícito (post-8.5)

- Estudiantes nivel Inicial en `estudiantes`.  
- Temas por sección.  
- Dos temas activos misma semana/c curso.  
- Permiso coordinador para registrar notas.  
- Plantilla Secundaria institucional validada.  
- ~~Importación registro auxiliar Excel~~ (obsoleto; fuentes DOCX en §2).
