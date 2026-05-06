# Sprint 7.6B: Registro masivo de asistencia y notas

## Objetivo

Implementar **flujos de registro académico masivo** para **asistencia** y **notas**, mejorando la usabilidad del sistema frente al flujo actual **Confirmado en código:** un POST por estudiante anidado a `/api/estudiantes/{id}/…` (**Implementado parcialmente** para operación institucional de aula/sección).

El usuario debe poder **seleccionar un grupo académico** (filtros coherentes con campos existentes en `Estudiante`: **sede**, **nivel**, **grado**, **sección**, **anio_escolar**) y registrar datos para **varios estudiantes** de forma agrupada, con persistencia defendible (**transacciones**, **endpoint batch**) y **trazabilidad** en **`activity_log`** donde corresponda (alineado a **RF-17 / RN-07** definidos en DRS — **Implementado parcialmente** en backend según documentación de **Sprint 7.5A**).

**El software se adapta al DRS y a las reglas de negocio; no al revés.**

## Duración estimada

1,5 a 2 semanas (según volumen de pruebas y refinamiento de reglas de duplicidad).

## Contexto

- **Dependencia obligatoria:** **Sprint 7.6A** completado o, como mínimo, API de **materias reales** y estrategia de enlace en notas (**Pendiente de desarrollo** hasta ejecutar 7.6A). Si **7.6A** no está listo: **bloquear** la parte masiva de **notas por materia catalogada**; la parte de **asistencia masiva** podría en teoría avanzar con modelo actual (**Confirmado:** asistencias sin FK a materias), pero el equipo debe evitar paralelización que genere inconsistencia de sprint — **decisión de gestión**.
- **Confirmado en código (pre-7.6):** modelo `Asistencia` con `estado` `presente|tardanza|falta`, `semana_inicio` fecha, `anio_escolar`, `bimestre`; modelo `Nota` con `curso` string, `nota`, `nota_conducta` nullable.
- **Pendiente de verificar antes de codificar:**
  - si el registro de asistencia masiva debe anclarse a **fecha puntual**, a **semana_inicio única**, o ambos (**contrastar DRS RF-02** y uso real Chilca);
  - si `nota_conducta`/“observación” debe mostrarse en UI masiva **solo si** el campo existe en modelo real (**confirmar** `notas` migración antes de exponer nuevos controles ficticios).

## Alcance

### A. Asistencia masiva

#### Backend

- **Endpoint batch** para crear o actualizar asistencias de **múltiples estudiantes** en **una solicitud**, con validación por estudiante dentro del payload.
- Uso preferente de **transacción única** para el lote: **commit** todo o **rollback** ante fallo no parcial (**Pendiente de decisión** si se admite modo “partial success” documentado frente UX).
- Reglas contra **duplicidad** coherentes con negocio (p. ej. mismo estudiante + misma ventana temporal + mismo bimestre — **definición exacta Pendiente de verificar** contra DRS; hoy migración legacy **no confirmó** unique compuesto — **documentar en implementación**).
- Registrar **auditoría** (`activity_log`) de forma defendible (**evento único por lote** con propiedades anexas vs evento por fila — **Pendiente de decisión** institucional/ISO mencionado en contexto proyecto).

#### Frontend

- **Elemento principal en sidebar:** entrada **«Asistencia»** navegable.
- Selector de filtros alineados a datos reales de estudiante:
  - sede (**Chilca** como foco prototipo si solo hay uso real de una sede; **No confirmado** uso masivo Auquimarca),
  - nivel,
  - grado,
  - sección,
  - **año escolar**,
  - **bimestre**,
  - **fecha o semana** según modelo acordado con **equipo técnico y DRS** (**Pendiente de verificar** conciliación UI “fecha” vs campo `semana_inicio`).
- Obtener estudiantes: vía **`GET /api/estudiantes`** con filtros **en servidor preferible** (**Pendiente de desarrollo**: hoy índice **sin query params** — **Confirmado** en FASE 0; si no hay filtro server, cliente filtra provisionalmente — **Implementado parcialmente** con riesgo de rendimiento hasta evolucionar API estudiantes).
- Tabla o equivalente por estudiante: presente / tardanza / falta.
- Una acción **Guardar masivo** disparando **un** llamado batch.
- Estados UX: cargando; sin estudiantes; error; guardando; éxito; errores por validación servidor.

---

### B. Notas masivas

#### Backend

- **Endpoint batch** de notas vinculado a **`materia_id` / identificador** de catálogo creado en **7.6A** (**Confirmado esperado tras 7.6A**, **Pendiente** hasta ese sprint).
- Validación robusta por fila (`nota` rango definido por reglas institucionales y validación Laravel existente p. ej. `0–20` — **Confirmado** parcialmente en `StoreNotaRequest` actual).
- **Transacciones** donde aplique y política ante fallo intermedio (**idempotencia** opcionalmente **fuera de alcance** inicial — documentar si se aplaza).
- **Observaciones / conducta:** incluir **`nota_conducta`** únicamente si **Persistido en modelo** después de revisión (**Confirmado nullable**); **No inventar campos**.
- Prevención de **notas duplicadas** mismo estudiante/bimestre/año/**materia** según definición institucional (**Pendiente de verificar**: hoy permite múltiples filas igual combinación sobre `curso` texto si no hay unique).
- **activity_log**: según mismo criterio que asistencia (lote vs granular).

#### Frontend

- **Elemento principal en sidebar:** entrada **«Notas»**.
- Selectores coherentes:
  - sede (según mismo criterio Chilca),
  - nivel, grado, sección,
  - año escolar, bimestre,
  - **materia** cargada desde **API 7.6A** (**no texto libre** para operación oficial masiva tras catálogo).
- Listado estudiantes y campos por fila (**nota** obligatoria; conducta opcional solo si modelo lo soporta).
- **Guardado masivo** vía endpoint batch único.

---

### C. Menú principal (frontend)

Menú esperado después de esta entrega (**objetivo de producto prototipo**, sujeto a permisos reales):

- Dashboard  
- Estudiantes  
- Asistencia  
- Notas  
- Alertas  

**Ocultar** como entradas de primer nivel (**no crear módulos ficticios**, solo retirar o condicionar visibilidad por permiso/vista útil):

- Intervenciones (flujo **real** desde detalle de alerta — **Confirmado** en flujo vigente).
- Reportes (**PDF desde Dashboard** — **Confirmado** en prototipo Sprint 6B).
- Configuración (**Pendiente de desarrollo**; no prometer función ocultando sin mensaje donde el equipo requiera explicativo — **microcopy Pendiente de decisión**).

**Permisos:** visibilidad de **Asistencia** y **Notas** debe alinearse a quien registra datos académicos (p. ej. `registrar_datos_academicos` más matriz Sprint 8) — **Pendiente de verificar**.

## Fuera de alcance

- Importación Excel / PDF / OCR / IA / **carga masiva desde archivo** (**versión 2**, explícitamente fuera).
- **Reportes nuevos**, CSV, PDF adicionales.
- Cambios en **ML Service**, **Docker**, stack de infra.
- **RF-18** reentrenamiento; **RF-19** semáforo.
- Relación **docente–aula** si **no confirmada en código**.
- Derivación **directivo**; comunicación familiar; atención psicológica ampliada.
- **Cypress** y rediseño visual global (**no** segunda ola Sprint 7A).
- Configuración institucional avanzada parametrizada.
- **Cambiar reglas de negocio** solo para hacer encajar el desarrollo rápido: **no permitido**.

## Actividades

1. Confirmar disponibilidad de **7.6A** (API materias).
2. Definir contratos batch (JSON entrada/salida, códigos `422`/errores por fila).
3. Migraciones necesarias sólo si 7.6A no cubrió vínculo nota→materia (coordinación con 7.6A para no duplicar trabajo).
4. Implementar **`EstudianteController::index`** con filtros query opcional **o** endpoint dedicado de “listado contextual” (**Pendiente de decisión**).
5. Implementar **`AsistenciaBatchController`** (nombre ilustrativo) y **`NotaBatchController`** según estándares del proyecto.
6. Instrumentar **`activity_log`** acordado.
7. Frontend: vistas Asistencia/Notas, integración **`api.js`**, permisos en `moduloPermitido`/`AuthContext`.
8. Ajustar **Sidebar**/`App.jsx` según menú esperado (**sin rutas falsas**).
9. Tests Feature PHPUnit para batch permisados y rechazados; regresión dashboard/riesgo (**Pendiente de verificar** integración tras cambios payloads).
10. QA manual checklist.

## Dependencias de entrada

- **Sprint 7.6A cerrado** o criterios mínimos de API materias (**dependencia fuerte para notas masivas con materia real**).
- Permisos y roles establecidos (**Confirmados en código** base; puede requerir ampliación acordada en **Sprint 8** en paralelo).
- Guía UI `docs/ui/mockups/guia-ui-siderae.md` solo como **referencia visual** (**no datos reales mockups**).

## Dependencias de salida

- Prototipo con registro institucional de aula menos fragmentado (**mejor defensa ante DRS** operación docente cotidiana).
- Base estable para mejoras posteriores (importación archivo **versión 2**).

## Criterios de aceptación

- **Dos endpoints batch** (asistencias y notas) con **sanctum** + permisos válidos (**403**/ **401** verificados en tests donde aplique).
- **Transaccionalidad** acordada documentada en código/comentarios mínimos o ADR corto (**sin documentación extensa fuera alcance equipo** opcionalmente en README sprint).
- Frontend: entradas **Asistencia** y **Notas** en sidebar; menú ya **sin** Intervenciones / Reportes / Configuración **como se pidió**, salvo que el equipo exija vistas informativas de un clic (**Pendiente de decisión**) — objetivo textual: **ocultos**.
- Sin datos ficticios tipo mockup; errores claros ante filas inválidas.
- **Sin regresión** conocida en: login, dashboard, export PDF, estudiantes, alertas (****Pendiente de verificar** en CI al implementar).

## Entregables

- Rutas nuevas batch documentadas brevemente.
- Migraciones sólo si faltaban tras 7.6A para FK notas (**coordinación**).
- Frontend dos pantallas funcionales más nav actualizado.
- Suite de tests ampliada (Feature).
- Lista de **Pendiente de verificar** post-merge (rendimiento, duplicados exactos).

## Pruebas asociadas

### Pruebas automatizadas

- `docker compose exec app-backend php artisan test` (o equivalente en entorno del equipo).
- Casos: batch asistencia con N estudiantes válidos; estado inválido rechazado; batch notas con **materia_id** válido; materia inexistente **422**; permiso insuficiente **403**; **activity_log** contiene entradas esperadas según diseño; regresión `DashboardTest` / `DatosAcademicosTest` según impacto (**Pendiente de verificar** lista exacta al implementar).

### Pruebas manuales

- Flujo docente/admin: filtrar Chilca + grado + sección + año + bimestre; marcar asistencias; guardar una vez.
- Mismo flujo notas con materia del catálogo.
- Verificar estados vacíos y mensajes.
- Verificar **no** aparición de datos de ejemplo de mockups.

## Criterios de validación

- El registro masivo refleja **operación real** por grupo académico sin obligar navegación perfil alumno por alumno para el caso institucional planteado.
- **Duplicidades** tratadas explicitamente (**éxito/409/422** según política definida).

## Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Resolver masivo sólo con N POST individuales | Alto (lento, logs masivos, UX mala) | **Exigir** endpoint batch este sprint |
| **7.6A** incompleto | Bloqueo notas con materia real | Gestión dependencias / scope reducido |
| Duplicidad asistencias/notas | Datos inconsistentes | Unique/compuesto migración tras regla cerrada |
| Rendimiento **GET estudiantes** sin filtro server | Lentitud | Paginación o filtros servidor **Pendiente de desarrollo** en mismo sprint o spike previo corto (**decisión**) |
| Lote falla a mitad sin política | Usuario confuso | Transacción o respuesta errores por fila acordados |
| Permisos desalineados vs Sprint 8 | Deuda técnica seguridad | Mínimos + documentar matriz siguiente |
| Adelantar **docente–aula** | Alcance ilegítimo | No implementar |

## Reglas para Cursor

- Distinguir en comentarios y PR: **DRS**, **confirmado**, **parcial**, **pendiente desarrollo**, **pendiente verificar**.
- No implementar importación archivo ni RF fuera del sprint.
- No afirmar existencia batch/materias **antes de merge** correspondiente (**no alucinar**).
- Coordinar cambios **`api.php`** pruebas y front sin romper contratos públicos ya dependidos sin deprecación (**Pendiente de decisión** versión API).
- Respetar **`.cursorrules`** y **prioridad DRS**.
- Mantener alcance cerrado contra **fuera de alcance** listado.

---

### Recordatorio de principios compartidos (7.6A y 7.6B)

- **No adaptar las reglas de negocio al software.**
- **No inventar funcionalidades** ni relaciones no definidas (**docente–aula**, **directivo–sede** fuera del alcance hasta existan en modelo).
- **No asumir implementación** por estar en el DRS sin evidencia en código y pruebas.
- **No** importación PDF/Excel en **7.6A** ni **7.6B**.
- **No** usar mockups como fuente de datos reales.
- **No** mezclar **Sprint 8**, **Sprint 9**, **Cypress**, **RF-18**, **RF-19**, **ML Service**, **Docker**.
- **No** rediseño visual global dentro de estos sprints.
