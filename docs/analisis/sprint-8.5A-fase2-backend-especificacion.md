# Sprint 8.5A — Fase 2: especificación de implementación backend

Documento operativo para ejecutar **solo Fase 2** (sin API, sin frontend, sin riesgo).  
Generado tras reglas finales aprobadas v3.1.

## Comandos (tras implementar archivos PHP)

```bash
cd backend
php artisan migrate
php artisan db:seed --class=CurricularModuleSeeder
php artisan test --filter=Curricular
```

En Docker:

```bash
docker compose exec app-backend php artisan migrate
docker compose exec app-backend php artisan db:seed --class=CurricularModuleSeeder
docker compose exec app-backend php artisan test --filter=Curricular
```

---

## 1. Archivos de documentación actualizados (Fase 1)

| Archivo |
|---------|
| `docs/analisis/modulo-curricular-academico.md` (v3.1) |
| `sprints/sprint 8.5A.md` |
| `sprints/sprint 8.5B.md` |
| `sprints/sprint 9.md` (dependencia 8.5B) |
| `docs/analisis/sprint-8.5A-fase2-backend-especificacion.md` (este archivo) |

---

## 2. Migración nueva (única)

**Archivo:** `backend/database/migrations/2026_05_23_120000_create_curricular_module_tables.php`

**Tablas:** `equivalencias_grado`, `areas`, `cursos_catalogo`, `competencias`, `capacidades`, `plantillas_curriculares`, `plantilla_cursos`, `mallas_curriculares`, `malla_cursos`, `periodos_academicos`, `semanas_academicas`, `temas_semanales`, `tema_competencias`, `tema_capacidades`, `configuracion_pesos_evaluacion`, `docente_curso_aulas`, `notas_semanales`.

**Puntos clave:**

- `nivel` curricular: `inicial|primaria|secundaria` solo en tablas nuevas.
- `notas_semanales`: `nota_cuaderno`, `nota_libro`, `nota_tarea` **nullable**; `ce_calculado` **decimal(5,2)** NOT NULL.
- `docente_curso_aulas`: **unique** `(anio_escolar, nivel, grado, seccion, sede, malla_curso_id)` — impide dos docentes en la misma asignación (activar/desactivar vía `activo`, no segunda fila).
- `temas_semanales`: **unique** `(malla_curso_id, periodo_academico_id, semana_academica_id)`.
- **No** modificar migraciones de `estudiantes`, `materias`, `notas`.

---

## 3. Modelos Laravel nuevos

Ruta: `backend/app/Models/Curricular/`

| Modelo | Tabla |
|--------|-------|
| `EquivalenciaGrado` | equivalencias_grado |
| `Area` | areas |
| `CursoCatalogo` | cursos_catalogo |
| `Competencia` | competencias |
| `Capacidad` | capacidades |
| `PlantillaCurricular` | plantillas_curriculares |
| `PlantillaCurso` | plantilla_cursos |
| `MallaCurricular` | mallas_curriculares |
| `MallaCurso` | malla_cursos |
| `PeriodoAcademico` | periodos_academicos |
| `SemanaAcademica` | semanas_academicas |
| `TemaSemanal` | temas_semanales |
| `ConfiguracionPesoEvaluacion` | configuracion_pesos_evaluacion |
| `DocenteCursoAula` | docente_curso_aulas |
| `NotaSemanal` | notas_semanales |

Relaciones estándar Eloquent + casts (`activo` bool, pesos decimal, `pesos_usados_json` array en `NotaSemanal`).

---

## 4. Servicios nuevos

Ruta: `backend/app/Services/Curricular/`

### `CatalogoNivelGrado.php`

Constantes de grados: Inicial `3 años`,`4 años`,`5 años`; Primaria `1ro`–`6to`; Secundaria `1ro`–`5to`.

### `EquivalenciaGradoService.php`

- `aLegacy(string $nivel, string $gradoCurricular): ?string`
- `aCurricular(string $nivel, string $gradoLegacy): ?string`
- Solo `primaria` y `secundaria`.

### `PesoEvaluacionResolver.php`

- Default: `['cuaderno' => 33.33, 'libro' => 33.33, 'tarea' => 33.34]`
- `validarSuma100(array $pesos): void` — cada peso ≥ 0, suma = 100 (tolerancia 0.01)
- `resolver(?...scopes)` — Fase 2 puede devolver solo default; scopes en 8.5B

### `CeCalculatorService.php`

```php
// Entrada: ?float $cuaderno, ?float $libro, ?float $tarea, array $pesos ['cuaderno'=>, 'libro'=>, 'tarea'=>]
// 1. Filtrar presentes (no null)
// 2. Si count === 0 → throw NotasCurricularesVaciasException
// 3. Validar cada nota entre 0 y 20
// 4. Si pesos son default iguales → promedio aritmético de presentes
// 5. Si no → CE = sum(nota_i * peso_i) / sum(peso_i) solo para claves presentes
// 6. return round(ce, 2)
```

### `DocenteCursoAulaValidator.php`

- Antes de crear/activar: no existe otro `activo=true` con misma clave `(anio, nivel, grado, seccion, sede, malla_curso_id)`.

### Excepciones

- `App\Exceptions\Curricular\NotasCurricularesVaciasException`
- `App\Exceptions\Curricular\PesosEvaluacionInvalidosException`

---

## 5. Seeders nuevos

Ruta: `backend/database/seeders/Curricular/`

| Seeder | Clase |
|--------|-------|
| Equivalencias | `EquivalenciasGradoSeeder` — 6 primaria + 5 secundaria |
| CN base | `CurriculoNacionalBaseSeeder` — áreas/competencias/capacidades resumidas; 3 niveles; **sin** desempeños |
| Plantilla Primaria 2do | `PlantillaPrimariaInstitucionalSeeder` — cursos §3.2 análisis, `detalle_completo=true` solo 2do |
| Plantilla Inicial | `PlantillaInicialBaseSeeder` — CN sin cursos institucionales masivos |
| Plantilla Secundaria | `PlantillaSecundariaBaseSeeder` — solo CN, **sin** cursos inventados |
| Periodos demo | `PeriodosSemanasDemoSeeder` — 2026, 4 bimestres × 4 semanas |
| Orquestador | `CurricularModuleSeeder` — llama todos los anteriores |

**No** incluir en Fase 2: `notas_semanales` demo, asignaciones docente demo (opcional mínimo comentado en 8.5B).

---

## 6. Tests nuevos

| Archivo | Casos |
|---------|-------|
| `tests/Unit/Curricular/CeCalculatorServiceTest.php` | C+L+T; C+T; solo L; pesos 50/20; vacío → excepción; fuera 0–20 |
| `tests/Unit/Curricular/PesoEvaluacionResolverTest.php` | default suma 100; suma 99 → excepción |
| `tests/Unit/Curricular/EquivalenciaGradoServiceTest.php` | 2do↔2°; 1ro↔1° |
| `tests/Feature/Curricular/CurricularSeedersTest.php` | migrate+seed; cuenta equivalencias; plantilla 2do tiene cursos; secundaria sin cursos institucionales |

Filtro PHPUnit: `--filter=Curricular`

---

## 7. Fase 2 prohibida (checklist)

- [ ] `backend/routes/api.php`
- [ ] `app/Http/Controllers/Api/*Curricular*`
- [ ] `frontend/**`
- [ ] `RiesgoAcademicoService.php`
- [ ] `ml-service/**`
- [ ] `docker-compose.yml`
- [ ] Alter `estudiantes` / `materias` / `notas` migrations

---

## 8. Advertencias / pendientes

| Tema | Estado |
|------|--------|
| Ejecución PHP bloqueada en modo plan | Implementar al activar modo agente |
| Excel registro auxiliar 2do | Pendiente en repo; nombres según análisis §3.2 |
| Replicar plantilla 2do a otros grados | No automático |
| Inicial: notas/riesgo/estudiantes | Backlog explícito |
| Permiso coordinador registrar notas | Backlog |
| `docente_curso_aulas` / `temas_semanales` historial activo | **Fase 2.1:** columna `activo_unique_key` (1 si activo, null si inactivo) + unique compuesto; trait `SyncActivoUniqueKey` en modelos |
| `docente_curso_aulas.user_id` → `cascadeOnDelete` | **Pendiente confirmación:** borrar usuario elimina asignaciones e historial; valorar migración a `restrictOnDelete` en 8.5B o posterior |
| Migración temas: índice auxiliar en `malla_curso_id` | Requerido en MySQL antes de `dropUnique` porque la FK reutilizaba el índice compuesto |

---

## 9. Criterios de aceptación Fase 2

1. `php artisan migrate` sin tocar tablas legacy.
2. `CurricularModuleSeeder` completa sin error.
3. `php artisan test --filter=Curricular` en verde.
4. CE y pesos cumplen reglas §13 del análisis.
5. Equivalencias 11 filas pobladas.
