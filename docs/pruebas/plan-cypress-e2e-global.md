# Plan Cypress E2E global — SIDERAE-Blenkir

## 1. Propósito

Este plan define una estrategia gradual para ampliar Cypress desde el smoke mínimo RF-04 hacia una cobertura E2E de las funcionalidades principales de SIDERAE-Blenkir. La intención es validar flujos críticos desde navegador, con datos y usuarios controlados, sin convertir Cypress en una prueba exhaustiva de toda la lógica interna.

Aclaraciones de alcance:

- Cypress no reemplaza PHPUnit ni los Feature Tests Laravel.
- Cypress no reemplaza el smoke manual por rol ni la revisión humana de sustentación.
- Cypress debe validar flujos visibles de usuario final: login, navegación, módulos principales, permisos visibles y operaciones críticas.
- No se probará cada detalle interno con Cypress si ya está cubierto por PHPUnit o pruebas unitarias.
- La prioridad es estabilidad: datos semilla conocidos, usuarios E2E definidos, permisos claros y selectores consistentes.
- No se debe afirmar cobertura global hasta que existan specs implementadas y corridas con resultado real.

## 2. Estado actual de Cypress

Fase 2F introdujo Cypress como base mínima para RF-04. No existe todavía una suite Cypress global del sistema.

| Elemento | Estado | Observación |
| -------- | ------ | ----------- |
| Dependencia Cypress | Configurada | `cypress` en `frontend/package.json` como dev dependency |
| Scripts npm | Configurados | `npm run cy:open` y `npm run cy:run` |
| Configuración | Configurada | `frontend/cypress.config.js` con `baseUrl` por `CYPRESS_BASE_URL` o `http://localhost:5173` |
| Support file | Configurado | `frontend/cypress/support/e2e.js` |
| Comandos reutilizables | Configurados base | `visitApp`, `getByTestId`, `requireE2ECredentials`, `loginAsE2EUser`, `logout`, `openModule`, `openRf04StudentProfile` |
| Spec RF-04 | Creado | `frontend/cypress/e2e/rf04-reportes-conductuales.cy.js` |
| Specs auth/logout | Creados | `auth-login.cy.js` y `logout.cy.js` en Fase 2H |
| Variables requeridas | Definidas | `CYPRESS_E2E_EMAIL`, `CYPRESS_E2E_PASSWORD`, `CYPRESS_E2E_STUDENT_TEXT` opcional |
| Resultado de ejecución | Parcial | Fase 2H: `npm run cy:run` detectó 3 specs; 2 tests auth públicos passed; login/logout/RF-04 pendientes por falta de credenciales E2E |
| Alcance actual | Infraestructura auth/logout + RF-04 | No cubre dashboard, curricular ni RBAC completo |
| Limitación principal | Datos/credenciales E2E | Falta usuario E2E documentado con variables de entorno definidas en ejecución |

## 3. Principios de cobertura E2E

- Mantener un spec por módulo o flujo funcional, evitando specs gigantes.
- Preferir pruebas cortas, deterministas y con una intención clara.
- Usar datos demo/seed cuando sea posible.
- Usar textos únicos para datos creados durante la prueba, por ejemplo `Texto E2E ${Date.now()}`.
- No depender del orden frágil de tablas; buscar por texto, `data-testid` o filtros estables.
- No duplicar en Cypress validaciones profundas ya cubiertas por PHPUnit.
- No guardar credenciales reales en el repositorio.
- No hardcodear contraseñas reales en specs, docs o configuración.
- Mantener V1 como sede operativa Chilca; no mezclar sedes ni crear selector de sede.
- No afirmar cobertura global hasta implementar y ejecutar los specs correspondientes.
- Tratar Cypress como smoke de confianza funcional, no como certificación total del sistema.

## 4. Estrategia de datos y usuarios E2E

Variables mínimas:

```bash
CYPRESS_E2E_EMAIL=
CYPRESS_E2E_PASSWORD=
CYPRESS_E2E_STUDENT_TEXT=
```

Variables opcionales futuras para RBAC:

```bash
CYPRESS_E2E_NO_PERMISSION_EMAIL=
CYPRESS_E2E_NO_PERMISSION_PASSWORD=
CYPRESS_E2E_DIRECTIVO_EMAIL=
CYPRESS_E2E_DIRECTIVO_PASSWORD=
```

Recomendaciones:

- Definir un usuario E2E con permisos amplios, idealmente `administrador` o `coordinador_academico`, para smoke institucional.
- Definir un usuario sin permisos o con permisos limitados para validar RBAC visible, por ejemplo ausencia de módulos restringidos.
- Definir un usuario `directivo` si se desea validar lectura donde la UI lo permita.
- Mantener al menos un estudiante Chilca existente y estable, referenciable por `CYPRESS_E2E_STUDENT_TEXT`.
- No guardar credenciales reales en el repositorio.
- No crear endpoints de test backend en esta fase.
- No depender de datos Auquimarca como operación V1.
- Para creación de datos desde UI, usar textos únicos y limpiar mediante flujos funcionales existentes cuando aplique, por ejemplo anulación RF-04.

## 5. Inventario de módulos a cubrir

| Módulo / funcionalidad | Estado funcional | Prioridad Cypress | Tipo de prueba |
| ---------------------- | ---------------- | ----------------- | -------------- |
| Login / autenticación | Confirmado en código | Alta | Smoke login correcto/error |
| Cierre de sesión | Confirmado en código | Alta | Smoke logout |
| Menú lateral y permisos | Confirmado en código | Alta | Navegación y visibilidad RBAC |
| Dashboard | Implementado parcialmente | Alta | Carga, KPIs, filtros mínimos |
| Usuarios / roles | Confirmado en código | Media | Listado y acciones no destructivas |
| Estudiantes | Confirmado en código | Alta | Listado, búsqueda, abrir perfil |
| Perfil estudiante | Confirmado en código | Alta | Datos, riesgo, resumen y bloques visibles |
| Riesgo académico actual | Implementado parcialmente | Media | Lectura del bloque y estado visible; no ML interno |
| Alertas | Confirmado en código | Alta | Listado y apertura de detalle |
| Intervenciones | Confirmado en código | Media | Registro mínimo si hay alerta disponible |
| Reportes conductuales RF-04 | Implementado V1 mínimo | Alta | Ya existe spec mínimo; completar ejecución |
| Malla curricular | Confirmado en código | Media | Carga y navegación básica |
| Competencias y capacidades | Confirmado en código | Media | Carga y búsqueda/expansión básica |
| Temas semanales / criterios | Confirmado en código | Media | Carga y filtros básicos |
| Componentes de calificación | Confirmado en código | Media | Carga y validación visual mínima |
| Configuración bimestral | Confirmado en código | Media | Carga por nivel/grado si hay datos |
| Secciones / aulas | Confirmado en código | Media | Listado y filtros básicos |
| Asignación docente | Confirmado en código | Media | Listado y filtros básicos |
| Registro de notas semanales | Confirmado en código | Alta | Carga de matriz/formulario y edición controlada si hay datos |
| Consulta de notas | Parcial según rol | Media | Lectura por rol permitido |
| Asistencia curricular | Confirmado en código | Alta | Carga y registro/lectura controlada |
| Excel aula / descarga | Confirmado en código | Media | Verificar botón/descarga sin validar contenido interno profundo |
| Calendario académico | Confirmado en código | Media | Carga de periodos |
| Seguridad/RBAC visible | Implementado parcialmente | Alta | Usuario limitado no ve módulos restringidos |
| RF-10 escalamiento directivo | Planificado | Fuera de Cypress por ahora | Pendiente de implementación |
| RF-11 perfil integral psicólogo | Planificado / parcial por alertas | Fuera de Cypress por ahora | No marcar como implementado |
| RF-16 zona reportes dedicada | Planificado | Fuera de Cypress por ahora | PDF dashboard es antecedente parcial |
| RF-18 ML real / reentrenamiento | Planificado | Fuera de Cypress | No validar ML interno con Cypress |
| RF-19 semáforo completitud | Implementado V1 | Fuera de Cypress hasta definición de suite global | Backend probado (`SemaforoCompletitudTest` 11 passed); UI en perfil estudiante build OK; E2E UI pendiente |
| RF-20 historial evolutivo | Planificado / persistencia parcial | Fuera de Cypress por ahora | No hay timeline completo |
| SIAGIE | Fuera del alcance vigente | No aplica | Plantillas propias RF-32/RF-33 |
| Fast Test | Retirado del alcance | No aplica | No crear spec |
| Comunicación familiar | Eliminado del alcance | No aplica | No crear spec |
| VSE en riesgo | Retirado del flujo | No aplica | No validar como insumo RF-06 |

## 6. Matriz de cobertura propuesta

| Spec Cypress | Funcionalidad | Flujo cubierto | Usuario requerido | Estado |
| ------------ | ------------- | -------------- | ----------------- | ------ |
| `auth-login.cy.js` | Autenticación | Login correcto, error credenciales, sesión inicial | Usuario E2E válido | Creado en 2H; ejecución pendiente de credenciales |
| `navigation-rbac.cy.js` | Menú / permisos visibles | Módulos permitidos, módulos restringidos, sin selector sede | Usuario amplio y usuario limitado | Propuesto |
| `dashboard.cy.js` | Dashboard | Carga de KPIs, filtros mínimos, export visible si aplica | Usuario con `ver_dashboard` | Propuesto |
| `estudiantes.cy.js` | Estudiantes | Listado, búsqueda, abrir perfil | Usuario con `gestionar_estudiantes` | Propuesto |
| `perfil-estudiante.cy.js` | Perfil estudiante | Datos, resumen, riesgo visible y bloques principales | Usuario con acceso a estudiantes | Propuesto |
| `riesgo-alertas-intervenciones.cy.js` | Riesgo / alertas / intervenciones | Lectura riesgo, listado alertas, detalle/intervención si hay alerta | Usuario con permisos de riesgo/alertas | Propuesto |
| `rf04-reportes-conductuales.cy.js` | RF-04 | Ver bloque, registrar, validar, anular, sin multi-sede | Usuario con permisos RF-04 | Existente mínimo; ejecución funcional pendiente |
| `curricular-malla.cy.js` | Malla curricular | Carga de malla, navegación por áreas/cursos | Usuario curricular permitido | Propuesto |
| `curricular-competencias-capacidades.cy.js` | Competencias/capacidades | Carga, expansión/consulta básica | Usuario curricular permitido | Propuesto |
| `curricular-temas-semanales.cy.js` | Temas/criterios | Carga y filtros básicos | Usuario curricular permitido | Propuesto |
| `curricular-configuracion-bimestral.cy.js` | Configuración bimestral | Carga por filtros y validación visual | Usuario curricular permitido | Propuesto |
| `secciones-aulas.cy.js` | Secciones/aulas | Listado y filtros | Usuario con gestión aulas | Propuesto |
| `asignacion-docente.cy.js` | Asignación docente | Listado y filtros | Usuario con gestión asignaciones | Propuesto |
| `notas-semanales.cy.js` | Notas semanales | Carga matriz/formulario y guardado controlado si aplica | Docente o coordinador | Propuesto |
| `asistencia-curricular.cy.js` | Asistencia curricular | Carga, filtros y registro/lectura controlada | Docente o coordinador | Propuesto |
| `excel-aula.cy.js` | Excel aula | Verificar acceso y descarga iniciada | Usuario con `descargar_excel_aula` | Propuesto |
| `calendario-academico.cy.js` | Calendario académico | Carga periodos y filtros | Usuario con gestión calendario | Propuesto |
| `logout.cy.js` | Cierre sesión | Logout y retorno a login | Usuario E2E válido | Creado en 2H; ejecución pendiente de credenciales |

## 7. Flujos mínimos por spec

### `auth-login.cy.js`

- Login correcto con `CYPRESS_E2E_EMAIL` y `CYPRESS_E2E_PASSWORD`.
- Error visible con credenciales inválidas de prueba.
- Tras login, se muestra `workspace-main` y no la pantalla de login.

### `navigation-rbac.cy.js`

- Menú muestra módulos permitidos para usuario amplio.
- Usuario sin permiso no ve módulo restringido.
- No aparece selector de sede ni opción operativa Auquimarca.
- Validar que el módulo por defecto corresponde a permisos del usuario.

### `dashboard.cy.js`

- Carga dashboard sin error.
- Verifica presencia de tarjetas/KPIs principales.
- Aplica un filtro básico si la UI lo permite.
- Verifica que la exportación PDF visible no se confunda con zona RF-16 completa.

### `estudiantes.cy.js`

- Carga listado de estudiantes Chilca.
- Usa búsqueda por `CYPRESS_E2E_STUDENT_TEXT` o primer estudiante visible.
- Abre perfil del estudiante.
- Verifica ausencia de selector multi-sede operativo.

### `perfil-estudiante.cy.js`

- Abre perfil desde listado.
- Verifica bloque de datos del estudiante.
- Verifica bloque de riesgo/resumen cuando esté visible.
- Verifica estados de carga/vacío sin romper navegación.

### `riesgo-alertas-intervenciones.cy.js`

- Verifica lectura del estado de riesgo visible en perfil o dashboard.
- Abre módulo Alertas.
- Si hay alertas, abre detalle.
- Si hay permisos y datos disponibles, registra una intervención mínima con texto único.
- No valida fórmula ML interna ni reentrenamiento.

### `rf04-reportes-conductuales.cy.js`

- Ya existente como smoke mínimo RF-04.
- Completar ejecución con `CYPRESS_E2E_EMAIL` y `CYPRESS_E2E_PASSWORD`.
- Registrar reporte con texto único.
- Validar formulario incompleto.
- Anular el reporte creado.

### `curricular-malla.cy.js`

- Abre Malla curricular desde menú.
- Verifica carga de áreas/cursos o estado vacío documentado.
- Aplica navegación o filtro básico si existe.

### `curricular-competencias-capacidades.cy.js`

- Abre Competencias y capacidades.
- Verifica listado o estado vacío.
- Expande/consulta un elemento existente si la UI lo permite.

### `curricular-temas-semanales.cy.js`

- Abre Criterios de evaluación.
- Verifica carga inicial.
- Aplica filtros básicos por nivel/grado/curso si existen.

### `curricular-configuracion-bimestral.cy.js`

- Abre Configuración bimestral.
- Selecciona filtros mínimos disponibles.
- Verifica tabla o mensaje de configuración pendiente.

### `secciones-aulas.cy.js`

- Abre Secciones / Aulas.
- Verifica listado.
- Aplica búsqueda o filtro básico.

### `asignacion-docente.cy.js`

- Abre Asignación docente.
- Verifica listado/formulario de asignaciones.
- Aplica filtro básico sin crear datos frágiles.

### `notas-semanales.cy.js`

- Abre Notas semanales.
- Selecciona filtros requeridos usando datos demo.
- Verifica matriz/formulario o estado vacío.
- Si se registra nota, usar valor controlado y no depender de orden de filas.

### `asistencia-curricular.cy.js`

- Abre Asistencia.
- Selecciona filtros requeridos y fecha válida.
- Verifica estudiantes listados o estado vacío.
- Si se registra asistencia, usar flujo mínimo reversible o aceptado por datos demo.

### `excel-aula.cy.js`

- Abre Excel por aula.
- Selecciona filtros mínimos.
- Verifica botón de descarga.
- Valida inicio de descarga de archivo, sin inspeccionar internamente todo el Excel.

### `calendario-academico.cy.js`

- Abre Periodos académicos.
- Verifica listado o estado vacío.
- Aplica filtro o consulta básica si la UI lo permite.

### `logout.cy.js`

- Login con usuario E2E.
- Ejecuta cierre de sesión desde el header.
- Verifica retorno a pantalla de login.
- Verifica que no queda `workspace-main` visible.

## 8. Fases de implementación Cypress

### Fase 2H — Infraestructura Cypress global

Estado: **configurada**. `npm run cy:run` fue ejecutado sin credenciales E2E: 2 tests públicos de login pasaron; los flujos que requieren usuario E2E quedaron pendientes por `CYPRESS_E2E_EMAIL` / `CYPRESS_E2E_PASSWORD`.

Actualización Fase 2H.1: se corrigieron helpers de sesión y fallback de selector de navegación, pero la ejecución con credenciales temporales sigue sin verde por fallas de sesión/layout y una corrida aislada de auth quedó bloqueada antes de iniciar tests. No se guardaron credenciales.

- Mejorar comandos de login. **Hecho**
- Agregar helpers de navegación por `data-testid`. **Hecho**
- Definir patrón para usuarios E2E y variables. **Hecho**
- Revisar selectores estables de header/sidebar sin modificar comportamiento. **Hecho**
- Crear specs `auth-login.cy.js` y `logout.cy.js`. **Hecho**
- Documentar resultado real de ejecución. **Registrado parcialmente; pendiente corrida con credenciales**

### Fase 2I — Smoke institucional base

- Cubrir dashboard.
- Cubrir menú lateral y RBAC visible.
- Cubrir estudiantes.
- Cubrir perfil estudiante.
- Cubrir riesgo/alertas/intervenciones con alcance de UI y datos disponibles.

### Fase 2J — Smoke curricular

- Cubrir malla curricular.
- Cubrir competencias y capacidades.
- Cubrir temas semanales/criterios.
- Cubrir configuración bimestral.
- Cubrir secciones/aulas.
- Cubrir asignación docente.

### Fase 2K — Smoke aula académica

- Cubrir notas semanales.
- Cubrir consulta de notas según rol.
- Cubrir asistencia curricular.
- Cubrir Excel aula.
- Cubrir calendario académico.

### Fase 2L — Cierre Cypress global

- Ejecutar `npm run cy:run`.
- Documentar specs ejecutados, aprobados, fallidos y pendientes.
- Actualizar matriz RF–Sprint–Test.
- Actualizar informe de pruebas.
- Marcar explícitamente qué cobertura existe y qué queda fuera.
- No declarar suite global verde si hay specs pendientes o no ejecutados.

## 9. Reglas para implementación futura

- No modificar backend para satisfacer Cypress salvo bug real confirmado.
- No modificar Flask ni lógica ML.
- No modificar migraciones, seeders ni permisos sin fase aprobada.
- No usar datos reales sensibles.
- No hardcodear credenciales.
- No crear selector de sede.
- No agregar sleeps arbitrarios; preferir esperas por estado visible, request o `data-testid`.
- Preferir selectores estables (`data-testid`, labels, roles/textos visibles).
- Si hacen falta `data-testid`, agregarlos de forma mínima, documentada y sin cambiar comportamiento funcional.
- No usar Cypress para validar fórmulas internas de ML, ranking de riesgo o lógica ya cubierta por PHPUnit.
- No marcar un spec como verde si no se ejecutó.
- Mantener capturas/videos solo cuando aporten evidencia; evitar artefactos innecesarios en repo.

## 10. Comandos de ejecución

Desde `frontend/`:

```bash
npm run cy:open
npm run cy:run
```

Con variables Linux/Git Bash:

```bash
CYPRESS_E2E_EMAIL="..." CYPRESS_E2E_PASSWORD="..." npm run cy:run
```

Con PowerShell:

```powershell
$env:CYPRESS_E2E_EMAIL="..."
$env:CYPRESS_E2E_PASSWORD="..."
npm run cy:run
```

Variable opcional para seleccionar estudiante:

```bash
CYPRESS_E2E_STUDENT_TEXT="..."
```

## 11. Documentación a actualizar en fases futuras

- `docs/pruebas/informe-pruebas.md`
- `docs/matriz-rf-sprint-test.md`
- `docs/limitaciones.md`
- `docs/manual-tecnico.md`
- `docs/pruebas/cypress-rf04.md`
- `docs/pruebas/plan-cypress-e2e-global.md`

## 12. Riesgos

| Riesgo | Impacto | Mitigación |
| ------ | ------- | ---------- |
| Tests frágiles por selectores | Falsos negativos | Usar `data-testid` mínimo y textos visibles estables |
| Falta de usuario E2E estable | Specs no ejecutables | Definir usuarios por rol y variables locales |
| Datos semilla incompletos | Flujos sin registros | Documentar precondiciones y usar estados vacíos como resultado válido cuando aplique |
| Dependencia de estado previo | Intermitencia | Crear datos con texto único o usar datos demo conocidos |
| Diferencia Docker vs host | Fallos por entorno | Documentar comando host y Docker; registrar entorno usado |
| Cypress headless limitado | Fallos de navegador | Verificar `npx cypress verify` y dependencias gráficas si aplica |
| Cubrir demasiado en una fase | Suite lenta y frágil | Dividir en fases 2H–2L |
| Documentar Cypress como completo sin ejecución | Riesgo académico y técnico | Registrar solo resultados reales por spec |
| Mezclar módulos planificados | Falsas afirmaciones | Marcar RF-10, RF-11, RF-16, RF-18, RF-19 y RF-20 como planificados/parciales según evidencia |
| Datos multi-sede históricos | Confusión de alcance | Mantener V1 Chilca y no operar Auquimarca desde Cypress |

## 13. Criterios de aceptación del plan

El plan queda aceptado si:

- Lista los módulos implementados principales del sistema.
- Distingue implementado, parcial, planificado, retirado y fuera de alcance.
- Propone specs por módulo o flujo funcional.
- Define variables, usuarios y datos E2E necesarios.
- Propone fases de implementación gradual.
- No afirma ejecución inexistente.
- No afirma Cypress global implementado.
- Mantiene V1 Chilca y evita selector de sede.
- No propone modificar backend, Flask, Docker, migraciones, seeders ni permisos en esta fase.

## 14. Conclusión

Con Fase 2H, el proyecto cuenta con infraestructura Cypress reutilizable para auth/logout, navegación base y RF-04. El siguiente paso recomendado es **Fase 2I — Smoke institucional base**, siempre que antes de ejecutar los specs se definan credenciales E2E por variables de entorno y un estudiante Chilca estable. Cypress global permanece **en construcción** hasta que se implementen los specs de módulos y se registren ejecuciones reales.
