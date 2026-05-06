# Sprint 7.6A: Materias y cursos administrables por grado

## Objetivo

Implementar un **módulo de materias/cursos administrables por el administrador**, asociado a la **estructura académica** del colegio Blenkir, de forma que el sistema se alinee al **DRS** y a las **reglas de negocio** institucionales (**no adaptar las reglas al código existente**).

Preparar el terreno para que **el registro de notas deje de depender de texto libre** en el campo tipo `curso` que hoy existe en modelo/migración de notas (**Confirmado en código** en Sprint 7.5B/FASE 0: `Nota` con atributo `curso` string), sustituyéndolo o complementándolo con **referencia a materia real** según diseño acordado en implementación.

## Duración estimada

1 a 1,5 semanas (ajustar según carga del equipo y decisiones de modelo).

## Contexto

- **Definido en DRS:** la carga y consistencia de datos académicos es crítica para riesgo y trazabilidad (RF-01 y afines en el PDF formal).
- **Confirmado en código:** existen `Estudiante`, `Nota` con `curso` como string, permisos Spatie y `registrar_datos_academicos` para datos académicos; **no** existe modelo `Materia` ni migración de catálogo (**Pendiente de verificar** en cada release, pero **no confirmado** en el estado auditado previo a este sprint).
- **Implementado parcialmente:** registro de notas por estudiante vía API anidada (**Confirmado en código**); catálogo de materias (**Pendiente de desarrollo**).
- **Regla de negocio nueva (equipo / DRS operativo):** el administrador debe crear y gestionar materias/cursos asociados a **nivel**, **grado** y/o **año escolar** (y **sede** si aplica). El software debe reflejar eso; **no** simplificar el negocio para acomodar limitaciones técnicas sin decisión explícita.

Sprint **7.6B** dependerá de este sprint para notas masivas con **materia real**.

## Alcance

### Backend

- Definir e implementar **modelo/entidad** de materia o curso catalogado (nombre tentativo técnico: p. ej. `Materia` o nombre alineado al equipo), con persistencia MySQL mediante **migración nueva** (**Pendiente de desarrollo** hasta implementar).
- **Relación esperada en datos** con al menos:
  - **nivel** (coherente con enum o valores ya usados en `estudiantes`, p. ej. `primaria` / `secundaria` — **Confirmado en código** en estudiante migración previa).
  - **grado** (coherente con campo string `grado` de estudiantes o catálogo acordado).
  - **año escolar** (coherente con `anio_escolar` string en estudiantes y notas — **Confirmado en código**).
  - **sede** si el **DRS**/negocio exige segregación catalogada (**Confirmado en código:** enum `chilca`, `auquimarca` en estudiantes; **prototipo operativo:** foco **Chilca** — decisiones de valores por defecto deben documentarse sin **hardcodear** negocio de forma cerrada si el DRS pide soporte futuro Auquimarca).
- **CRUD** (o al menos create, read, update, list con filtros) expuesto vía **API REST** protegida con `auth:sanctum` y permiso dedicado si se adopta (**Pendiente de decisión**, ej.: `gestionar_materias` **solo administrador**, alineado a RF-15 / segregación RN-05 del DRS — **no inventar roles** fuera del esquema Spatie vigente sin actualizar seeders formales).
- **Validaciones servidor:**
  - nombre obligatorio;
  - nivel obligatorio si el modelo adoptado así lo establece en negocio;
  - grado obligatorio cuando aplique;
  - año escolar obligatorio;
  - **unicidad controlada:** evitar duplicados lógicos por combinación nivel/grado/año/sede/nombre (**definición exacta Pendiente de verificar** frente al DRS/redacción interna institucional).
- **Endpoint** (uno o más) para **listar materias filtradas** por nivel, grado, año escolar y sede cuando corresponda, pensado como insumo para el formulario masivo de notas en **7.6B**.
- **Compatibilidad con notas históricas:** estrategia explícita (p. ej. conservar registros legacy con `curso` texto, migración de datos, o periodo de convivencia) — **Pendiente de decisión**; criterio: **no romper** producción de datos académicos existentes sin plan.

### Frontend (si el alcance del sprint lo permite)

- **Pantalla o sección mínima** para el administrador: listar, crear, editar materias con los mismos criterios de validación visibles (estados vacío/carga/error).
- **No** prometer importación masiva ni campos no existentes en API.

### Pruebas

- **Tests Feature** (PHPUnit) para CRUD, permisos **403/401**, unicidad y listados filtrados.

### Documentación operativa

- Registrar en el propio PR/sprint qué quedó **Pendiente de verificar** frente al PDF DRS (REQ finos de académicos).

## Fuera de alcance

- Registro masivo de notas (**Sprint 7.6B**).
- Registro masivo de asistencia (**Sprint 7.6B**).
- Importación Excel / PDF / OCR / IA para leer archivos (**versión 2**, explícitamente excluido).
- **RF-18** reentrenamiento ML.
- **RF-19** semáforo de completitud.
- Relación **docente–aula** si no está definida y confirmada en modelo.
- Relación **directivo–sede** si no está definida y confirmada.
- Reportes avanzados, CSV, nuevos PDF.
- Cambios en **ML Service**, **Docker**, stack de despliegue.
- Rediseño visual global (mantener coherencia con guía UI existente; **no** “Sprint 7A de nuevo”).
- **Cypress** / E2E amplios.
- Configuración institucional avanzada (umbrales, parámetros globales) salvo lo estrictamente necesario para catálogo de materias si el equipo lo acota.
- Gestión completa de **periodos académicos** si no existe modelo previo y no es requerido para cerrar el catálogo mínimo (**Pendiente de verificar** en DRS).
- **Cambiar reglas de negocio** para que encajen en atajos de implementación: **prohibido**; el código debe adaptarse al DRS y reglas acordadas.

## Actividades

1. **Auditoría de código:** confirmar ausencia o presencia de entidad materia/curso (**Pendiente de verificar** hasta el día de implementación).
2. Alinear con **DRS** y negocio Blenkir: granularidad (¿“curso” = materia curricular única? ¿misma materia en varias secciones por año?).
3. Diseñar esquema relacional y migraciones; revisar impacto en tabla `notas` (FK nullable vs migración de datos).
4. Implementar modelo, factory si aplica, políticas o validación en Form Requests.
5. Exponer rutas en `api.php` con middleware de permisos coherente con **Sprint 8** futuro (matriz rol–permiso).
6. Actualizar o crear **PermissionsSeeder** solo con decisión explícita del equipo (`gestionar_materias` u homólogo).
7. Frontend admin mínimo (si se incluye en el sprint).
8. Tests Feature y regresión sobre estudiantes/notas existentes.
9. Documentar **pendientes** y riesgos residuales.

## Dependencias de entrada

- **Sprint 7.5A** y **7.5B** completados o estables (prototipo visual y trazabilidad base).
- Acceso a **DRS_SIDERAE_Blenkir_v1.pdf** para contrastar REQ de datos académicos.
- Roles y permisos base **Confirmados en código** (Spatie); matriz ampliada **Pendiente de verificar** hasta Sprint 8.

## Dependencias de salida

- Habilita **Sprint 7.6B** (notas masivas con selección de materia catalogada).
- Insumo para futura importación **versión 2** (fuera de alcance).

## Criterios de aceptación

- El administrador (o rol definido con permiso explícito) puede **crear, listar y actualizar** materias con las validaciones de negocio acordadas.
- Listado filtrable por **nivel, grado, año escolar** (y **sede** si se modela).
- **Duplicados** controlados según regla definida en implementación.
- **Tests automatizados** cubren casos felices y **403** sin permiso.
- **No se rompe** el flujo actual de estudiantes; impacto en notas documentado (convivencia o migración).
- **No** se introducen importaciones Excel/PDF ni RF-18/RF-19.

## Entregables

- Migraciones y modelo de materias/cursos.
- API CRUD + listado filtrado.
- (Opcional acotado) UI administración materias.
- Tests Feature PHPUnit.
- Nota breve de decisiones de diseño (unicidad, sedes, compatibilidad con `curso` legacy).

## Pruebas asociadas

### Pruebas automatizadas

- `php artisan test` (o suite filtrada por módulo materias una vez existan tests).
- Casos sugeridos: crear materia; editar materia; listar por nivel/grado/año; usuario sin permiso **403**; intento de duplicado rechazado; regresión en rutas `/api/estudiantes` y notas existentes.

### Pruebas manuales

- Flujo administrador en UI/API: crear materia válida y verla en listado filtrado.
- Verificación de mensajes de error de validación.
- Comprobación visual de que no se muestran datos tomados de **mockups** como reales (**mockups solo referencia**).

## Criterios de validación

- El catálogo es **consistente** con la jerarquía académica definida por el colegio.
- Separación clara entre **dato institucional** (materia) y **texto libre** previo donde aún aplique (**Implementado parcialmente** hasta migración de notas en 7.6B).

## Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Modelo académico demasiado grande | Retrasa sprint | Mantener MVP: materia + claves nivel/grado/año/sede opcional según decisión |
| Mezclar materias con “configuración global” indefinida | Confusión de módulos | Scope explícito: solo catálogo académico de materias |
| Romper **notas** existentes con `curso` texto | Alto | Plan de compatibilidad y migración antes de hacer obligatorio FK |
| Permisos sin matriz (Sprint 8) | Deuda seguridad | Documentar decisión temporal; minimizar superficie |
| **Hardcodear** Chilca como única sede en reglas servidor | Incorrecto futuro Auquimarca | Default UI ≠ regla de negocio cerrada si DRS contempla sedes |
| Inventar **relaciones docente–aula** | Alcance ilegítimo | No implementar fuera del diseño |

## Reglas para Cursor

- No afirmar **Confirmado en código** sin revisar archivo concreto en el momento de implementar.
- Priorizar **DRS_SIDERAE_Blenkir_v1.pdf** y reglas negocio sobre conveniencias técnicas.
- No inventar endpoints, migraciones ni permisos fuera del sprint definido aquí sin aprobación.
- No implementar importación desde archivos (**fuera de alcance** explícito).
- No ejecutar trabajo de **Sprint 7.6B** dentro de este sprint.
- Relacionar trabajo con **`docs/arquitectura/`** cuando se precise contexto (**no alterar negocio**).

---

### Recordatorio de principios compartidos (7.6A y 7.6B)

- **No adaptar las reglas de negocio al software** ni al código legado como fuente de verdad normativa.
- **No inventar funcionalidades** ni relaciones docente–aula / directivo–sede no definidas.
- **No asumir implementación** solo porque aparece como RF en el DRS; validar código y pruebas.
- **No** importación PDF/Excel en esta línea de sprints.
- **No** usar contenido de **mockups** como datos reales.
- **No** mezclar **Sprint 8**, **Sprint 9**, **Cypress**, **RF-18**, **RF-19**, cambios **ML Service** ni **Docker**.
- **No** rediseño visual global obligatorio dentro de estos sprints.
