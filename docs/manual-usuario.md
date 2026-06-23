# Manual de usuario — SIDERAE-Blenkir

Guía de uso del sistema para personal institucional. Versión **V1** (prototipo académico). Fecha de verificación en interfaz: **2026-06-09**.

**Fuentes de permisos y pantallas:** [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md) · [`frontend/src/App.jsx`](../frontend/src/App.jsx).

---

## 1. Propósito del manual

Este manual explica **cómo usar SIDERAE-Blenkir según su rol** en la versión V1 del prototipo: ingreso al sistema, menú lateral, pantallas visibles y acciones permitidas (consultar, registrar, exportar, intervenir).

Está escrito para usuarios finales (administradores, docentes, coordinación académica, psicología/tutoría y dirección), no para programadores. Para detalle técnico, consulte [`docs/manual-tecnico.md`](manual-tecnico.md).

---

## 2. Alcance del manual

| Criterio | Descripción |
|----------|-------------|
| Sede operativa V1 | **Chilca** — la interfaz no ofrece selector de sede; los listados usan Chilca por defecto |
| Pantallas documentadas | Solo módulos **visibles en el menú lateral** y acciones confirmadas en la UI |
| Requerimientos del DRS | Algunos pueden estar **parciales o pendientes** — ver §15 |
| Multi-sede | **No activa** en V1; registros Auquimarca en bases locales de desarrollo son históricos, no operación normal |
| Certificación ISO | **No incluida** — el sistema es un prototipo académico |

---

## 3. Requisitos para usar el sistema

| Requisito | Detalle |
|-----------|---------|
| Navegador web | Chrome, Firefox, Edge o equivalente actualizado |
| Acceso al sistema | En entorno local de desarrollo: **http://localhost:5173** (ver [`README.md`](../README.md)) |
| Credenciales | Correo y contraseña **asignados por el administrador** o el equipo técnico |
| Rol y permisos | El menú y las acciones dependen del rol; el sistema carga su perfil al iniciar sesión |

**Entorno local (solo pruebas):** tras ejecutar el seed de datos demo, el [`README.md`](../README.md) §8 indica usuarios de ejemplo por rol y la contraseña común de prototipo. **No use esas credenciales ni datos ficticios en operación real.**

---

## 4. Ingreso al sistema

### Pantalla de login

Al abrir la aplicación sin sesión activa verá la pantalla **«Ingresar a SIDERAE-Blenkir»** con:

- Campo **Correo institucional**
- Campo **Contraseña**
- Botón **Iniciar sesión**

### Ingresar

1. Escriba su correo y contraseña.
2. Pulse **Iniciar sesión**.
3. Si las credenciales son correctas, el sistema valida su sesión y muestra el **panel principal** con menú lateral y encabezado.

El sistema identifica su **rol** y **permisos** al cargar la sesión (equivalente a consultar su perfil en el servidor). No necesita elegir rol manualmente.

### Credenciales inválidas

- Mensaje habitual: **«Credenciales inválidas.»**
- Verifique mayúsculas, correo completo y contraseña.
- Si el problema continúa, solicite restablecimiento al **administrador** (la opción «¿Olvidó su contraseña?» en pantalla está **pendiente de desarrollo**).

### Cierre de sesión

En la parte superior derecha pulse **Cerrar sesión**. La sesión se cierra en el servidor y volverá a la pantalla de login.

### Sesión expirada o no válida

Si la sesión caduca o no puede validarse, el sistema puede mostrar de nuevo el login o el mensaje **«No se pudo validar la sesión.»** — vuelva a iniciar sesión.

---

## 5. Navegación general

### Menú lateral (sidebar)

A la izquierda aparecen los **módulos permitidos para su rol**, agrupados así:

| Grupo | Módulos posibles |
|-------|------------------|
| Inicio | Dashboard |
| Gestión académica | Estudiantes, Notas semanales, Excel por aula, Asistencia, Alertas |
| Gestión docente y aulas | Secciones / Aulas, Asignación docente |
| Configuración curricular | Malla curricular, Criterios de evaluación, Componentes de calificación, Configuración bimestral |
| Configuración avanzada | Competencias y capacidades, Periodos académicos |
| Administración | Usuarios |

Solo verá los ítems para los que su cuenta tiene permiso. En pantallas pequeñas use el botón **Menú** del encabezado.

### Encabezado (header)

Muestra su **perfil** (p. ej. «Docente», «Coordinación académica»), su **correo** y el botón **Cerrar sesión**.

### Cambio de módulo

Pulse un ítem del menú lateral. El contenido central cambia al módulo seleccionado. Si intenta acceder a un módulo no permitido, el sistema le redirige al **primer módulo disponible** para su rol.

### Estados habituales

| Estado | Qué verá |
|--------|----------|
| Carga | Mensajes como «Validando sesión…», «Cargando módulo…» o «Cargando listado…» |
| Vacío | Avisos del tipo «No hay registros» o instrucciones para aplicar filtros |
| Error | Recuadros rojos con el problema (red, permisos, validación) |
| Sin permisos | **«Sin módulos asignados»** — contacte al administrador |

### V1 Chilca

Todos los filtros y registros académicos operan sobre la sede **Chilca**. No hay selector de sede en la interfaz V1.

---

## 6. Roles del sistema

Resumen basado en [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md).

| Rol | Qué puede hacer en V1 (menú visible) | Qué no tiene o no debe usar | Observaciones |
|-----|--------------------------------------|-----------------------------|---------------|
| **Administrador** | Todos los módulos del menú: dashboard académico-institucional, estudiantes, curricular completo (RF-21–RF-35), alertas, usuarios, Excel por aula | — | Acceso total a los **23 permisos implementados** (+ 8 sugeridos/planificados — ver [`seguridad-roles-permisos.md`](seguridad-roles-permisos.md) §16) |
| **Docente** | Dashboard, estudiantes, malla (consulta), notas semanales, asistencia, alertas e intervenciones | Configuración curricular avanzada, usuarios, Excel por aula, procesar riesgo desde pantalla | Puede registrar notas y asistencia de sus aulas asignadas |
| **Coordinador académico** | Dashboard, estudiantes, configuración curricular, asignaciones, notas (**consulta institucional**), asistencia, Excel por aula, alertas (solo lectura de intervención) | Usuarios; registrar intervenciones/cierre de alertas | Puede **procesar riesgo** vía sistema backend; **no hay botón visible** en perfil de estudiante (§15) |
| **Psicólogo / tutor** | Alertas (ver e intervenir), asistencia (**consulta**) | Dashboard, estudiantes, notas, configuración curricular | **Planificado RF-11:** perfil integral del estudiante en **modo lectura** (notas, asistencia, riesgo, conductuales) — hoy solo alertas |
| **Directivo** | Dashboard, alertas e intervenciones, malla (consulta), notas (**solo lectura institucional**), asistencia (consulta) | Estudiantes, configuración, usuarios, Excel por aula; **no** es actor inicial de todas las alertas | **Planificado RF-10:** intervención solo en casos **críticos/extremos** escalados |

---

## 7. Manual por rol: Administrador

Módulos visibles: **todos** los del menú lateral.

### 7.1 Ingreso y navegación

- **Objetivo:** Acceder al sistema con cuenta de administrador.
- **Navegación:** Pantalla de login → panel principal.
- **Pasos:** Ingrese correo y contraseña → **Iniciar sesión**.
- **Resultado esperado:** Menú completo según §5.
- **Errores comunes:** «Credenciales inválidas.» — ver §4.
- *Permiso:* sesión autenticada.

### 7.2 Consultar Dashboard

- **Objetivo:** Ver indicadores académicos e institucionales (estudiantes, riesgo, alertas, subset académico) de la sede Chilca.
- **Navegación:** Menú → **Dashboard**.
- **Pasos:** Ajuste filtros (nivel, grado, sección, riesgo) si lo desea → aplique → revise tarjetas y barras.
- **Resultado esperado:** Resumen numérico y gráficos simples según filtros.
- **Errores comunes:** «No se pudo cargar el dashboard.» — reintente; verifique conexión.
- *Permiso:* `ver_dashboard`.

### 7.3 Exportar reporte PDF del Dashboard

- **Objetivo:** Descargar un PDF con el resumen del dashboard (antecedente parcial; zona de **reportes de riesgo** RF-16 planificada).
- **Navegación:** **Dashboard** → botón **Exportar PDF**.
- **Pasos:** Configure filtros → **Exportar PDF** → espere la descarga.
- **Resultado esperado:** Archivo PDF en su equipo.
- **Errores comunes:** «No se pudo exportar el PDF.» — reintente; filtros sin datos pueden dar archivo vacío o error.
- *Permiso:* `ver_dashboard`.

### 7.4 Gestionar estudiantes (listar, crear, editar, perfil)

- **Objetivo:** Mantener el padrón de estudiantes de Chilca.
- **Navegación:** Menú → **Estudiantes**.
- **Pasos (listar):** Use búsqueda y filtros (nivel, grado, sección, año) → **Buscar** → pulse un estudiante para ver perfil.
- **Pasos (crear):** **Nuevo estudiante** → complete formulario → **Guardar estudiante**.
- **Pasos (editar):** En perfil → **Editar estudiante** → modifique → **Guardar cambios**.
- **Pasos (reportes conductuales):** En perfil → bloque **Reportes conductuales** → consulte listado; con permiso de registro use **Registrar reporte** o **Anular** (confirmación previa).
- **Resultado esperado:** Listado paginado; ficha con datos generales, bloque de riesgo (informativo), reportes conductuales (si aplica) y resumen curricular si aplica.
- **Errores comunes:** Campos obligatorios resaltados; «No se pudo guardar el estudiante.» — revise validaciones.
- *Permiso:* `gestionar_estudiantes` (listado y ficha); reportes conductuales: `ver_reportes_conductuales` / `registrar_reportes_conductuales`.

### 7.5 Gestionar usuarios

- **Objetivo:** Crear cuentas, asignar rol, activar/desactivar y restablecer contraseña.
- **Navegación:** Menú → **Usuarios**.
- **Pasos:** Liste con filtros → **Nuevo usuario** o edite existente → asigne rol → guarde; use acciones de activar/desactivar o restablecer contraseña según pantalla.
- **Resultado esperado:** Usuarios con rol Spatie correspondiente.
- **Errores comunes:** Correo duplicado; contraseña no coincide en confirmación.
- *Permiso:* `gestionar_usuarios`.

### 7.6 Configurar malla curricular

- **Objetivo:** Definir áreas, cursos y estructura por nivel/año.
- **Navegación:** **Configuración curricular** → **Malla curricular**.
- **Pasos:** Seleccione año escolar y nivel → revise áreas → agregue o edite cursos según la pantalla.
- **Resultado esperado:** Malla guardada para el contexto seleccionado.
- **Errores comunes:** Sin calendario activo — configure **Periodos académicos** primero.
- *Permiso:* `gestionar_malla_curricular`.

### 7.7 Configurar criterios de evaluación (temas semanales)

- **Objetivo:** Definir criterios por competencia/capacidad y semana.
- **Navegación:** **Criterios de evaluación**.
- **Pasos:** Elija contexto (nivel, área, curso, bimestre/semana) → agregue o edite criterios.
- **Resultado esperado:** Criterios disponibles para registro de notas.
- *Permiso:* `gestionar_temas_semanales`.

### 7.8 Configurar componentes de calificación

- **Objetivo:** Definir componentes de evaluación por nivel.
- **Navegación:** **Componentes de calificación**.
- **Pasos:** Seleccione nivel → gestione componentes según formulario.
- **Resultado esperado:** Componentes usados en notas semanales y evaluación bimestral.
- *Permiso:* `gestionar_componentes_calificacion`.

### 7.9 Configuración bimestral

- **Objetivo:** Configurar etapas, componentes y pesos de evaluación bimestral.
- **Navegación:** **Configuración bimestral**.
- **Pasos:** Seleccione contexto académico → configure tablas de etapas y componentes → guarde.
- **Resultado esperado:** Configuración lista para calificación bimestral en **Notas semanales**.
- *Permiso:* `configurar_evaluacion_bimestral`.

### 7.10 Gestionar secciones y aulas

- **Objetivo:** Catálogo de secciones por nivel/grado.
- **Navegación:** **Gestión docente y aulas** → **Secciones / Aulas**.
- **Pasos:** Filtre y cree o edite secciones según pantalla.
- **Resultado esperado:** Secciones disponibles en asignación y registros.
- *Permiso:* `gestionar_secciones_aulas`.

### 7.11 Asignación docente

- **Objetivo:** Vincular docente con aula, curso y malla.
- **Navegación:** **Asignación docente**.
- **Pasos:** Cree o edite asignaciones para el año escolar vigente.
- **Resultado esperado:** Docentes ven sus aulas en notas y asistencia.
- *Permiso:* `gestionar_asignaciones_docente`.

### 7.12 Periodos académicos

- **Objetivo:** Definir años escolares y bimestres/periodos.
- **Navegación:** **Configuración avanzada** → **Periodos académicos**.
- **Pasos:** Gestione años y periodos; marque vigencia según pantalla.
- **Resultado esperado:** Calendario activo para filtros de notas, asistencia y Excel.
- *Permiso:* `gestionar_calendario_academico`.

### 7.13 Competencias y capacidades

- **Objetivo:** Mantener catálogo de competencias y capacidades curriculares.
- **Navegación:** **Competencias y capacidades**.
- **Pasos:** Filtre por nivel/área → cree o edite registros.
- **Resultado esperado:** Competencias enlazables a criterios de evaluación.
- *Permiso:* `gestionar_competencias_capacidades`.

### 7.14 Registrar notas semanales

- **Objetivo:** Registrar calificaciones semanales por aula o consulta global.
- **Navegación:** **Notas semanales**.
- **Pasos:** Seleccione año, periodo, contexto (aula o consulta global) → cargue formulario → ingrese notas → **Guardar**; opcionalmente descargue/importe plantilla Excel desde la barra de herramientas.
- **Resultado esperado:** Notas registradas; mensaje de éxito.
- **Errores comunes:** Notas fuera de rango; calendario no activo; «No tiene permiso para registrar.»
- *Permiso:* `registrar_notas_semanales` (el administrador puede registrar también en modo consulta global).

### 7.15 Registrar asistencia curricular

- **Objetivo:** Tomar asistencia diaria por aula.
- **Navegación:** **Asistencia**.
- **Pasos:** Elija fecha, nivel, grado, sección → cargue listado → marque estados → **Guardar asistencia**.
- **Resultado esperado:** Asistencia guardada por estudiante.
- *Permiso:* `registrar_asistencia_curricular`.

### 7.16 Descargar Excel por aula

- **Objetivo:** Obtener archivo Excel consolidado del aula.
- **Navegación:** **Excel por aula**.
- **Pasos:** Complete año, nivel, grado, sección y bimestre → **Descargar Excel**.
- **Resultado esperado:** Archivo `.xlsx` descargado.
- **Errores comunes:** Filtros incompletos — botón deshabilitado; error de descarga — reintente.
- *Permiso:* `descargar_excel_aula`.

### 7.17 Gestionar alertas e intervenciones

- **Objetivo:** Revisar alertas de riesgo, registrar intervenciones y cerrar casos.
- **Navegación:** **Alertas**.
- **Pasos:** Liste alertas → abra detalle → registre **Intervención** (tipo, descripción, fecha) → si corresponde, **Cerrar alerta** con resultado.
- **Resultado esperado:** Alerta actualizada; intervenciones en historial.
- *Permisos:* `ver_alertas`, `registrar_intervencion`.

### 7.18 Reportes conductuales (perfil de estudiante)

- **Objetivo:** Consultar y, si corresponde, registrar o anular incidencias conductuales del estudiante en la sede Chilca.
- **Navegación:** Menú → **Estudiantes** → abra perfil → bloque **Reportes conductuales** (no hay menú global ni selector de sede).
- **Pasos (consultar):** Revise la tabla (fecha, tipo, gravedad, descripción, acción inmediata, registrado por).
- **Pasos (registrar):** **Registrar reporte** → complete fecha, tipo, gravedad (leve/moderado/grave), descripción y acción inmediata opcional → **Guardar reporte**.
- **Pasos (anular):** **Anular** en la fila → confirme en el diálogo; el reporte deja de listarse (anulación lógica en servidor).
- **Resultado esperado:** Solo reportes **activos** visibles; mensaje de vacío si no hay registros.
- **Errores comunes:** «Sin permiso para esta acción» (403); validaciones de campos obligatorios (422); «No se pudo cargar…» ante fallo de red.
- **Importante:** No sustituye **comunicación formal con familia** (RF-12 eliminado del alcance); es registro institucional interno.
- *Permisos:* `ver_reportes_conductuales` (listado); `registrar_reportes_conductuales` (crear y anular). **Directivo:** solo lectura en backend, pero en V1 **no tiene menú Estudiantes** — no accede al bloque desde UI habitual.

### 7.19 Semáforo de completitud de datos (perfil de estudiante)

- **Objetivo:** Indicar si hay datos suficientes para interpretar el riesgo académico del estudiante.
- **Navegación:** Menú → **Estudiantes** → abra perfil → bloque **Completitud de datos**, debajo de **Riesgo académico**.
- **Significado de colores:**
  - **Verde:** el estudiante tiene notas curriculares y asistencia curricular del periodo. Interpretación más confiable.
  - **Amarillo:** faltan algunos datos académicos, pero existe al menos una fuente (notas, asistencia, reportes conductuales activos o índice de riesgo). Interpretar con advertencia.
  - **Rojo:** no hay datos suficientes; no se recomienda interpretar el riesgo académico con la información actual.
- **Resultado esperado:** Se muestra color, etiqueta, mensaje corto y lista de insumos presentes/ausentes.
- **Errores comunes:** «Sin permiso para ver el semáforo» (403); «No se pudo cargar» ante fallo de red. El error se muestra aislado y no rompe el resto del perfil.
- **Importante:** El semáforo **no es el nivel de riesgo** (bajo/medio/alto); solo indica la completitud de los datos. No bloquea el procesamiento de riesgo. No aparece selector de sede. V1: solo sede Chilca.
- *Permiso:* `ver_semaforo_completitud` (asignado en V1 a administrador, docente y coordinador académico).

### 7.20 Historial de riesgo evolutivo (perfil de estudiante)

- **Objetivo:** Ver la evolución del índice de riesgo académico del estudiante por periodo.
- **Navegación:** Menú → **Estudiantes** → abra perfil → bloque **Historial de riesgo académico**, debajo de **Completitud de datos**.
- **Datos mostrados:** fecha, año escolar, bimestre, índice, nivel (Alto/Medio/Bajo) y variables utilizadas cuando existen.
- **Resultado esperado:** Tabla simple ordenada del registro más reciente al más antiguo. Mensaje de vacío si no hay registros.
- **Errores comunes:** «Sin permiso para ver el historial de riesgo» (403); «No se pudo cargar» ante fallo de red. El error se muestra aislado y no rompe el resto del perfil.
- **Importante:** El historial **muestra registros existentes** generados previamente; **no recalcula** el riesgo, **no predice** ni entrena modelos. No aparece selector de sede. V1: solo sede Chilca.
- *Permiso:* `ver_historial_riesgo` (asignado en V1 a administrador, docente y coordinador académico).

---

## 8. Manual por rol: Docente

### Módulos visibles

Dashboard, Estudiantes, Malla curricular (consulta), Notas semanales, Asistencia, Alertas.

**No ve:** Usuarios, Excel por aula, configuración curricular (criterios, componentes, bimestral, secciones, asignación, periodos, competencias).

### 8.1 Ingreso

Igual que §7.1. Tras login, suele abrirse **Dashboard** o **Notas semanales** según configuración de permisos.

### 8.2 Dashboard

Igual que §7.2–7.3 si tiene `ver_dashboard` (confirmado en seed para docente).

### 8.3 Estudiantes y perfil

- Como §7.4 y §7.18 — puede **consultar, registrar y anular** reportes conductuales en el perfil.
- *Permisos:* `gestionar_estudiantes`, `ver_reportes_conductuales`, `registrar_reportes_conductuales`.

- **Objetivo:** Consultar y, si corresponde, actualizar datos de estudiantes; ver resumen académico en perfil.
- **Navegación:** **Estudiantes**.
- **Pasos:** Busque estudiante → abra perfil → revise datos, resumen curricular y asistencia; **Editar estudiante** si tiene permiso de gestión.
- **Nota:** La sección **Riesgo académico** en perfil muestra aviso de **actualización pendiente** — no hay botón «Procesar riesgo» en pantalla (§15). Junto a riesgo se muestran el **Semáforo de completitud de datos** (RF-19) y el **Historial de riesgo académico** (RF-20) cuando el rol tiene permiso.
- *Permisos:* `gestionar_estudiantes`, `ver_notas_academicas`, `registrar_asistencia_curricular` / `ver_asistencia_curricular`.

### 8.4 Registrar notas semanales

- **Objetivo:** Calificar a estudiantes de **sus aulas asignadas**.
- **Navegación:** **Notas semanales**.
- **Pasos:** Seleccione su asignación (aula/curso) y periodo → ingrese notas → guarde; puede alternar vista por aula o por estudiante según toolbar.
- **Resultado esperado:** Calificaciones registradas solo en sus cursos asignados.
- **Errores comunes:** Sin asignaciones — contacte coordinación; validación de rangos numéricos.
- *Permiso:* `registrar_notas_semanales`.

### 8.5 Registrar asistencia

- **Objetivo:** Marcar asistencia del día para su aula.
- **Navegación:** **Asistencia**.
- **Pasos:** Elija fecha y aula asignada → complete tabla → guarde.
- *Permiso:* `registrar_asistencia_curricular`.

### 8.6 Alertas, intervenciones y cierre

- **Objetivo:** Atender alertas de estudiantes bajo su tutela o derivadas del sistema.
- **Navegación:** **Alertas**.
- **Pasos:** Igual que §7.17 — puede **registrar intervención** y **cerrar alerta**.
- *Permisos:* `ver_alertas`, `registrar_intervencion`.

### 8.7 Consultar malla curricular

- **Navegación:** **Malla curricular** — solo **lectura** (no edita estructura).
- *Permiso:* `ver_malla_curricular`.

---

## 9. Manual por rol: Coordinador académico

### Módulos visibles

Dashboard, Estudiantes, Notas semanales (**consulta institucional, solo lectura**), Excel por aula, Asistencia, Alertas (**sin** registrar intervención), toda la **configuración curricular** y **gestión docente y aulas**. **No ve:** Usuarios.

### 9.1 Dashboard y export PDF

Como §7.2–7.3.

### 9.2 Estudiantes

Como §7.4 y §7.18 (gestión de padrón + reportes conductuales en perfil).

### 9.3 Configuración curricular

Puede ejecutar las acciones de §7.6 a §7.13 (malla, criterios, componentes, bimestral, secciones, asignación, periodos, competencias).

### 9.4 Notas semanales (solo consulta)

- **Objetivo:** Revisar calificaciones de cualquier aula en modo institucional.
- **Navegación:** **Notas semanales**.
- **Comportamiento:** El sistema abre en **consulta global**; los campos de nota están en **solo lectura** (no puede guardar calificaciones).
- **Resultado esperado:** Visualización de registros existentes.
- *Permisos:* `gestionar_asignaciones_docente`, `ver_notas_academicas` — **no** tiene `registrar_notas_semanales`.

### 9.5 Asistencia

Puede **registrar** y **consultar** asistencia (modo global disponible para coordinación).

### 9.6 Excel por aula

Como §7.16.

### 9.7 Alertas

- **Solo lectura de acciones:** puede **ver** listado y detalle; **no** puede registrar intervenciones ni cerrar alertas (sin `registrar_intervencion`).

### 9.8 Procesar riesgo

Tiene permiso `procesar_riesgo` en backend, pero **no hay acción visible** en el perfil del estudiante en V1 (§15). El procesamiento masivo es tarea técnica (comando de consola), no flujo de usuario habitual.

---

## 10. Manual por rol: Psicólogo / tutor

### Módulos visibles

**Alertas**, **Asistencia** (consulta).

**No ve:** Dashboard, Estudiantes, Notas, configuración curricular, usuarios, Excel.

### 10.1 Alertas

- **Objetivo:** Seguimiento psicopedagógico vía alertas.
- **Navegación:** **Alertas** (módulo principal de este rol).
- **Pasos:** Revise listado → abra detalle → **registre intervención** → **cierre** cuando el caso esté resuelto.
- *Permisos:* `ver_alertas`, `registrar_intervencion`.

### 10.2 Asistencia (consulta)

- **Objetivo:** Consultar asistencia de estudiantes como apoyo al seguimiento.
- **Navegación:** **Asistencia**.
- **Comportamiento:** Formulario en **solo lectura** si el servidor no concede registro.
- *Permiso:* `ver_asistencia_curricular`.

### 10.3 Estudiantes y perfil

**No tiene** menú Estudiantes. No accede al padrón desde la UI V1.

### 10.4 Variables socioeconómicas

**No disponibles** en interfaz — pestaña pausada (§15).

---

## 11. Manual por rol: Directivo

### Módulos visibles

Dashboard, Alertas, Malla curricular (consulta), **Notas semanales** (visualización institucional), Asistencia (consulta).

**No ve:** Estudiantes, configuración curricular de edición, usuarios, Excel por aula.

### 11.1 Dashboard y export PDF

Como §7.2–7.3.

### 11.2 Alertas e intervenciones

Como §7.17 — puede intervenir y cerrar alertas.

### 11.3 Notas semanales (visualización institucional)

- **Objetivo:** Supervisar calificaciones sin registrar notas.
- **Navegación:** **Notas semanales** (visible por rol directivo aunque no tenga permiso de registro).
- **Comportamiento:** **Solo lectura** — no puede guardar ni importar plantillas.
- **Importante:** Ver el menú **no** implica permiso de calificar; el servidor bloquea escritura.
- *Permisos efectivos:* `ver_notas_academicas`; **sin** `registrar_notas_semanales`.

### 11.4 Malla y asistencia

- **Malla curricular:** consulta de estructura curricular.
- **Asistencia:** consulta de registros (solo lectura si no tiene permiso de registro).

### 11.5 Limitaciones propias del rol

- No gestiona estudiantes ni usuarios desde menú.
- No descarga Excel por aula.
- No configura malla, criterios ni bimestres.
- V1 opera solo en **Chilca** — no hay vista multi-sede para comparar sedes.

---

## 12. Flujos principales del sistema

### 12.1 Flujo de gestión curricular

Orden recomendado para coordinación o administración:

1. **Periodos académicos** — definir año y bimestres vigentes.
2. **Malla curricular** — áreas y cursos.
3. **Competencias y capacidades** — catálogo curricular.
4. **Criterios de evaluación** — temas/criterios por semana.
5. **Componentes de calificación** — tipos de nota por nivel.
6. **Configuración bimestral** — etapas y pesos bimestrales.
7. **Secciones / Aulas** — secciones por grado.
8. **Asignación docente** — docente ↔ aula ↔ curso.

Después, docentes pueden operar **Notas semanales** y **Asistencia**.

### 12.2 Flujo de registro académico

1. Verifique **calendario activo** y su **asignación** (docente) o contexto institucional (coordinación/directivo en lectura).
2. En **Notas semanales** o **Asistencia**, seleccione año, periodo, nivel, grado, sección y (para notas) curso.
3. Cargue el formulario → ingrese datos → **Guarde**.
4. Revise mensaje de éxito o errores de validación.
5. Opcional: descargue **plantilla Excel** o **Excel por aula** (roles con permiso).

### 12.3 Flujo de riesgo académico

1. El sistema dispone de **notas, asistencia y evaluación curricular** como insumo.
2. El **cálculo de riesgo** se procesa mediante un **servicio configurado** (Laravel invoca microservicio ML en prototipo).
3. En V1, la sección **Riesgo académico** del perfil de estudiante muestra **aviso de actualización pendiente** — no es el flujo operativo principal para usuarios.
4. Cuando exista índice de riesgo y reglas institucionales, puede generarse una **alerta** consultable en **Alertas**.
5. Procesamiento masivo post-importación es responsabilidad del **equipo técnico** (comando de consola), no del usuario de aula.

### 12.4 Flujo de alertas e intervención

1. Usuario con permiso abre **Alertas**.
2. Selecciona una alerta **pendiente** o **en atención**.
3. Registra **intervención** (tipo académica/psicosocial/etc., descripción, fecha).
4. Realiza seguimiento con nuevas intervenciones si es necesario.
5. Cuando el caso concluye, **cierra la alerta** indicando resultado (roles con `registrar_intervencion`).

### 12.5 Flujo de descarga Excel por aula

1. Menú → **Excel por aula** (administrador o coordinador académico).
2. Seleccione año escolar, nivel, grado, sección y bimestre.
3. Pulse **Descargar Excel**.
4. Abra el archivo en Excel/LibreOffice según procedimiento institucional.

Detalle técnico y diferencia con plantilla/import curricular: [`docs/aula-notas-excel.md`](aula-notas-excel.md) §11.

**Plantilla de registro auxiliar** (import/export de notas por curso): disponible desde la barra de **Notas semanales** para roles con permiso de registro — sustituye SIAGIE en alcance actual; no confundir con «Excel por aula».

**Fuera del sistema en V1:** comunicación formal con familias (RF-12), Fast Test (RF-03), variables socioeconómicas en el flujo de riesgo (RF-05).

---

## 13. Mensajes comunes y solución

| Mensaje / situación | Posible causa | Qué debe hacer el usuario |
|---------------------|---------------|---------------------------|
| Credenciales inválidas | Correo o contraseña incorrectos | Verifique datos; contacte administrador |
| No se pudo iniciar sesión | Servidor o red no disponible | Reintente; avise a soporte técnico |
| No se pudo validar la sesión | Sesión expirada o cookies bloqueadas | Cierre pestaña, vuelva a entrar; permita cookies del sitio |
| Sin permiso para ver alertas / 403 | Rol sin permiso | Solicite permiso al administrador |
| Sin módulos asignados | Cuenta sin rol/permisos | Contacte administrador |
| No hay registros / listado vacío | Filtros restrictivos o BD sin datos | Amplíe filtros; confirme año escolar |
| No se pudo cargar el dashboard / listado | Error de red o backend caído | Reintente; verifique que Docker/servicios estén activos (entorno local) |
| Campos obligatorios / 422 | Formulario incompleto o inválido | Complete campos marcados; revise rangos de notas |
| Riesgo académico pendiente de actualización | Módulo de riesgo en pausa en UI | Use flujo curricular y alertas; consulte coordinación |
| Sin calendario activo | Periodos no configurados | Administrador/coordinador debe configurar **Periodos académicos** |
| Descarga Excel fallida | Filtros incompletos o error servidor | Complete todos los filtros; reintente |
| No se pudo registrar la intervención | Validación o alerta ya cerrada | Revise mensaje detallado; actualice página |

---

## 14. Buenas prácticas de uso

- **No comparta** su contraseña con otros usuarios.
- **Cierre sesión** en equipos compartidos (laboratorio, dirección).
- **Revise contexto** (año, grado, sección, curso, semana) antes de guardar notas o asistencia.
- **No registre datos de prueba** en un entorno que se use como referencia operativa.
- **Verifique el estudiante** antes de calificar o intervenir en una alerta.
- Ante errores repetidos, anote hora, pantalla y mensaje, y **reporte al administrador o equipo técnico**.
- En V1, asuma que todos los registros corresponden a la sede **Chilca**.

---

## 15. Limitaciones y funciones no disponibles en V1

| Limitación | Detalle |
|------------|---------|
| Sede única Chilca | Sin selector de sede; multi-sede no operativa |
| Auquimarca en BD local | Dato histórico de desarrollo; no uso normal V1 |
| Variables socioeconómicas | API existe; **pestaña no visible** en perfil de estudiante |
| Reportes conductuales | Solo en **perfil de estudiante**; sin menú global ni listado por grado/sección |
| Semáforo de completitud | En **perfil de estudiante**; indica completitud de datos, **no** nivel de riesgo |
| Historial de riesgo | En **perfil de estudiante**; muestra evolución del índice de riesgo por periodo, **sin** recalcular |
| Riesgo en perfil de estudiante | Mensaje de **pausa/rediseño**; sin botón «Procesar riesgo» en UI |
| Procesar riesgo manual | Permiso backend para admin/coordinador; **sin acción de pantalla** habitual |
| Módulos legacy | Materias, notas masivas y asistencia masiva **sin menú** — fuera del flujo V1 |
| Pesos C/L/T | Módulo **oculto** en menú (transición curricular) |
| Recuperación de contraseña | Enlace visual **pendiente de desarrollo** |
| Cypress / E2E | No afecta al usuario final; no hay suite automatizada documentada |
| Reentrenamiento ML | **No disponible** — servicio ML es prototipo determinístico |
| Modelos RF/SVM/XGBoost (DRS) | **No confirmados** en el microservicio actual |
| Certificación ISO | **No incluida** — referencia académica únicamente |
| Eliminación de estudiantes | Política del sistema: **no eliminar** estudiantes desde UI |
| Registro público de cuentas | Existe en backend de prototipo; **no es flujo institucional** — use cuentas asignadas |

---

## 16. Glosario breve

| Término | Significado en SIDERAE-Blenkir |
|---------|--------------------------------|
| **Riesgo académico** | Nivel calculado (bajo/medio/alto) a partir de desempeño y asistencia, usado para detección temprana |
| **Alerta** | Aviso generado cuando un estudiante requiere atención según reglas del sistema |
| **Intervención** | Acción registrada (académica, psicológica, etc.) para atender una alerta |
| **Malla curricular** | Estructura de áreas y cursos por nivel y año escolar |
| **Tema semanal / Criterio de evaluación** | Criterio o capacidad evaluada en una semana del bimestre |
| **Componente de evaluación** | Tipo de calificación (p. ej. capacidad, actitud) usado en registro de notas |
| **Configuración bimestral** | Parámetros y etapas para la evaluación por bimestre |
| **Aula / sección** | Grupo de estudiantes (grado + sección) en la sede Chilca |
| **Permiso** | Autorización concreta (p. ej. registrar notas) asignada a su cuenta |
| **Rol** | Perfil institucional (docente, coordinador, etc.) que agrupa permisos |
| **Sede operativa** | En V1: **Chilca** — campus donde opera el prototipo |

---

## Documentación relacionada

| Documento | Uso |
|-----------|-----|
| [`docs/seguridad-roles-permisos.md`](seguridad-roles-permisos.md) | Matriz técnica rol–permiso–pantalla |
| [`docs/limitaciones.md`](limitaciones.md) | Alcance real vs DRS |
| [`README.md`](../README.md) | Instalación local y usuarios demo |
| [`docs/manual-tecnico.md`](manual-tecnico.md) | Stack y pruebas (equipo técnico) |
| [`docs/matriz-rf-sprint-test.md`](matriz-rf-sprint-test.md) | Trazabilidad RF–Sprint–Test |
| [`docs/pruebas/informe-pruebas.md`](pruebas/informe-pruebas.md) | Informe de pruebas V1 |
| [`docs/aula-notas-excel.md`](aula-notas-excel.md) | Módulo curricular: aula, notas, Excel |

---

*Documento generado en Fase 4 del plan de actualización documental SIDERAE-Blenkir. RF-19 cerrado V1 Fase 3E — 2026-06-23.*
