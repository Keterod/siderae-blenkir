# Contexto DRS — requerimientos (resumen operativo para IA/Cursor)

> **Documento de contexto histórico.** Transcribe el DRS v1 PDF. La **fuente formal vigente** es [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) **versión documental 2.1** (RF-01 a RF-35). Las decisiones de alcance v2.1 (SIAGIE/Fast Test/VSE/comunicación familiar retirados) **no** están reflejadas en las tablas de este resumen v1.

Fuente formal transcrita desde **`DRS_SIDERAE_Blenkir_v1.pdf`** (Versión **1.0**, Fecha **02/04/2026**) salvo donde se indique el **estado V1** actualizado según código y DRS v2.

Este Markdown **no reemplaza** al PDF ni al DRS v2.

---

## 1. Propósito del documento

- Ofrecer a Cursor y otras IA una **consulta rápida** de RF-01–RF-20, **reglas de negocio** (RN), **requerimientos no funcionales** (RNF) y temas sensibles (**RF-14, RF-16, RF-18, RF-19**).
- **Reducir alucinaciones** mezclando alcance formal con lo implementado.
- El **código** y las pruebas siguen siendo la referencia para **Confirmado en código** / **Implementado parcialmente**; las celdas *Estado actual general* conjugan **`docs/arquitectura/resumen-arquitectura.md`** y hallazgos de contexto donde no hay auditoría de código en esta sesión.

---

## 2. Regla clave: DRS vs implementación

| Etiqueta | Uso |
|----------|-----|
| **Definido en DRS** | Texto comprometido en el PDF (obligatorio de alcance). |
| **Confirmado en código** | Evidencia en rutas, modelos, UI o pruebas del repo (auditar antes de afirmar). |
| **Implementado parcialmente** | Solo parte del RF cumple REQ del DRS. |
| **Pendiente de desarrollo** | No hay equivalencia suficiente en código según revisión previa del proyecto. |
| **Pendiente de verificar** | Contraste PDF ↔ código **no realizado aquí** o contradictorio entre docs. |

Discrepancia entre narrativas: priorizar **`docs/drs/DRS_SIDERAE_Blenkir_v2.md`** + **código** + [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) para estado V1. El PDF v1 describe alcance formal histórico.

---

## 3. Tabla RF-01 a RF-20

Origen de **nombre, actor y prioridad**: **Tabla «7. Requerimientos Funcionales»**, páginas 7–8 del DRS (resumen). *Estado actual general* orientado por `resumen-arquitectura.md` + contexto (validar en código).

| RF | Nombre resumido | Actor principal | Prioridad | Estado actual general |
|----|-----------------|-----------------|-----------|------------------------|
| RF-01 | Carga e importación de datos académicos | Docente / Administrador | Alta | **Implementado parcialmente** — plantilla Excel curricular + Excel aula (descarga) confirmados; **importación SIAGIE pendiente** |
| RF-02 | Registro digital de asistencia semanal | Docente | Alta | **Confirmado en código** (asistencia curricular) |
| RF-03 | Importación de resultados del Fast Test | Coordinador Académico | Alta | **Pendiente** |
| RF-04 | Registro digital de reportes conductuales | Psicólogo / Tutor | Alta | **Pendiente** — migración sin API |
| RF-05 | Integración de variables socioeconómicas | Administrador / Sistema | Alta | **Implementado parcialmente** — API confirmada; **UI pausada** (pestaña no expuesta) |
| RF-06 | Procesamiento multivariable y cálculo del índice de riesgo | Sistema | Alta | **Implementado parcialmente** — Laravel → Flask **determinístico**; **UI riesgo pausada**; RF/SVM/XGBoost **no implementados** |
| RF-07 | Evaluación automática del nivel de riesgo | Sistema | Alta | **Confirmado en código** — umbrales 0,70 / 0,40; configuración admin **pendiente** |
| RF-08 | Emisión de alertas tempranas accionables | Sistema | Alta | **Confirmado en código** — RN-03 completa (dos disparadores) **pendiente verificar** |
| RF-09 | Intervención preventiva del docente | Docente | Alta | **Confirmado en código** |
| RF-10 | Decisión de derivación por el directivo | Directivo | Alta | **Pendiente** |
| RF-11 | Atención psicológica preventiva con perfil integrado | Psicólogo / Tutor | Media | **Implementado parcialmente** — alertas; sin RF-10 ni perfil integrado completo |
| RF-12 | Comunicación formal y trazable con la familia | Docente / Directivo | Media | **Pendiente** |
| RF-13 | Registro de acción tomada y cierre de alerta | Docente / Directivo / Psicólogo | Alta | **Implementado parcialmente** — cierre vía intervención; derivación/comunicación familiar **pendientes** |
| RF-14 | Panel de visualización (dashboard) de riesgo | Docente / Directivo | Alta | **Implementado parcialmente** — subset REQ-14; multi-sede directivo **fuera V1** |
| RF-15 | Gestión de usuarios y control de acceso por rol | Administrador | Alta | **Confirmado en código** — register público = brecha pre-producción |
| RF-16 | Exportación de reportes en PDF | Docente / Directivo | Media | **Implementado parcialmente** — export PDF dashboard; Excel aula `.xlsx` distinto |
| RF-17 | Registro de auditoría de acciones | Sistema | Alta | **Implementado parcialmente** — activitylog parcial |
| RF-18 | Reentrenamiento del modelo ML | Administrador | Media | **Pendiente** |
| RF-19 | Semáforo de completitud de datos | Docente / Administrador | Media | **Pendiente** |
| RF-20 | Historial de riesgo por estudiante | Docente / Directivo | Media | **Implementado parcialmente** — persistencia; UI/timeline **pausada** |

---

## 4. Reglas de negocio principales

Transcripción resumida de la **Sección 8. Reglas de Negocio** del DRS (sin sustituir el PDF).

| ID | Regla | Resumen operativo |
|----|-------|---------------------|
| **RN-01** | Umbrales de riesgo | Clasificación: **Alto ≥ 0,70**; **Medio 0,40–0,69**; **Bajo < 0,40**. Umbrales **configurables por el administrador**. |
| **RN-02** | Completitud mínima para procesamiento ML | Perfil debe tener completas como mínimo **asistencia**, **notas bimestrales** y **datos socioeconómicos** para procesar. El semáforo (RF-19) **bloquea** si faltan variables críticas. |
| **RN-03** | Generación de alertas | Alerta automática si el índice supera umbral **Alto (≥ 0,70)** **o** si el estudiante **asciende de Bajo a Medio en dos bimestres consecutivos**. |
| **RN-04** | Cierre de alerta | Cierre solo si existe al menos **intervención (RF-09)** o **derivación (RF-10)**. Registro en **activity_log** (fecha, usuario, acción). *Nota:* el detalle **RF-13 REQ-13.1** también admite **comunicación familiar (RF-12)** como prerrequisito — al implementar, cruzar **RF-13** completo en PDF. |
| **RN-05** | Segregación de acceso por rol | Ningún usuario accede a módulos fuera de su rol. **Backend Laravel** valida con **Spatie Permission** **antes** de procesar cada solicitud, **independiente** del frontend. |
| **RN-06** | Reentrenamiento ML | Solo al **inicio del año escolar** o por **autorización explícita del directivo**. Solo **Administrador** ejecuta (RF-18). |
| **RN-07** | Trazabilidad ISO 9001 | Acciones que modifican datos (carga, alerta, intervención, cierre, reentrenamiento, etc.) en **activity_log**. **No** eliminar ni modificar registros del log. |
| **RN-08** | Importación de datos externos | Archivos **.xlsx** o **.csv**; rechazo si formato incorrecto u obligatorios vacíos, con **mensaje descriptivo**. |

**Privacidad (DRS sección 11.2):** datos personales/académicos sensibles; acceso restringido por rol (coherente con RN-05); cumplimiento **Ley N.º 29733** y reglamento **D.S. 003-2013-JUS**; **activity_log** mínimo **5 años** (también citado en RF-17 / RNF-07).

---

## 5. Requerimientos no funcionales principales

Resumen de la **Sección 10** del DRS (RNF-01 a RNF-10), **sin** desarrollar marco ISO ni citas académicas.

| ID | Categoría | Idea operativa |
|----|-----------|----------------|
| RNF-01 | Rendimiento | Dashboard **&lt; 3 s** con datos ya procesados; **hasta 10 s** para ML en background; escenario **hasta 50 usuarios** concurrentes en red local. |
| RNF-02 | Disponibilidad | **≥ 99%** en horario escolar (lun–sáb **7:00–18:00**); recuperación máx. **2 h**. |
| RNF-03 | Seguridad | **HTTPS/TLS 1.2+**; contraseñas **bcrypt**; acceso verificado con **Spatie Permission**. |
| RNF-04 | Usabilidad | UI **responsiva** (desde **768 px**); flujos principales **≤ 5 pasos** desde menú. |
| RNF-05 | Mantenibilidad | **PSR-12** (PHP/Laravel) y guías React; cobertura pruebas **PHPUnit + Jest ≥ 80%** en módulos críticos (RF-06, RF-07, RF-08, RF-15, RF-17). |
| RNF-06 | Escalabilidad | Arquitectura desacoplada (**Docker Compose**); nuevas sedes sin rediseño base; **ML reemplazable** sin rediseño Laravel. |
| RNF-07 | Trazabilidad y auditoría | Acciones relevantes en **activity_log** (usuario, fecha/hora, dato afectado); **ISO 9001** referida en descripción formal. |
| RNF-08 | Compatibilidad de navegadores | Últimas versiones **Chrome** y **Firefox**; sin IE ni Edge Legacy. |
| RNF-09 | Portabilidad del entorno | Despliegue reproducible con **Docker**: `docker-compose up -d` sin configuración manual adicional (según DRS). |
| RNF-10 | Integridad de datos | Validar integridad referencial antes de ML; datos críticos faltantes **bloqueados** y comunicados vía semáforo (RF-19). |

**Interfaces:** el DRS indica comunicación **frontend ↔ Laravel** JSON REST; **Laravel ↔ Flask** HTTP interna Docker; actualizaciones de dashboard:** sin WebSockets en v1.0** — recarga manual o **polling configurable**.

---

## 6. Sección especial — RF-14 Dashboard

### Definido en DRS

- Dashboard **interactivo** con estado de riesgo por **aula** y **nivel educativo**; **gráficos exportables**.
- Actores:** Docente / Directivo**; prioridad **Alta**.
- **Docente:** distribución de riesgo de **su aula** (cantidades Alto/Medio/Bajo), gráfico barras o torta (**REQ-14.1**).
- **Directivo:** mapa de riesgo **consolidado de todas las sedes**; **filtros** por sede y nivel educativo (**REQ-14.2**).
- **Actualización automática** al procesarse un **nuevo índice** (**REQ-14.3**).
- Exportar gráficos en **PDF (RF-16)** e imagen **PNG** (**REQ-14.4**).
- Mostrar **porcentaje de alertas** activas (pendientes/atención) y cerradas (**REQ-14.5**).

### Contraste implementación

| Dimensión | Clasificación sugerida |
|-----------|-------------------------|
| Visión alcance REQ-14 | **Definido en DRS** (completo arriba) |
| API/panel dashboard mín. | **Implementado parcialmente** frente REQ-14 |
| Solo aula docente vs todas las sedes directivo | **Pendiente de verificar** (relación usuario–aula en código) |
| Gráficos + export PNG + PDF desde dashboard | **Pendiente de desarrollo / Pendiente de verificar** vs REQ-14.4–16 |
| Filtros sede/nivel tipo REQ-14.2 | **`sprint 6B`** típicamente; **Pendiente de verificar** según codebase |
| Rendimiento tipo RNF-01 | **Pendiente de verificar** |

**Sprint 6A** puede cubrir solo un **subset** demo; según alcance oficial del equipo **no** equivale a **RF-14 cerrado**.

---

## 7. Sección especial — RF-16 Exportación

### Definido en DRS

- Reportes **riesgo individual**, **aula** y **dashboard** en **PDF** (Barryvdh DomPDF citado).
- Actores:** Docente / Directivo**; prioridad **Media**.
- REQ-16 incluye contenido del PDF (factores, historial, intervenciones, logo institucional, fecha, usuario generator, registro **activity_log**).

### Contraste implementación

| Dimensión | Clasificación |
|-----------|---------------|
| Alcance REQ-16 | **Definido en DRS** |
| Codificación real PDF | **Pendiente de verificar** — dependencia existe en docs proyecto |
| **Sprint 6A** incluye RF-16 | **No** (plan equipo: orientación Sprint **6B** export básico) |

---

## 8. Sección especial — RF-18 Reentrenamiento ML

### Definido en DRS

- **Administrador** reentrena modelos con datos acumulados **inicio cada año escolar**.
- Flujo hacia **Flask**; métricas **accuracy, precision, recall, F1**; aprobación explícita antes de producción; modelo anterior respaldo **≥ 30 días**; registro en **activity_log** (REQ-18.x).

### Contraste implementación

| Dimensión | Clasificación |
|-----------|---------------|
| RF-18 | **Definido en DRS** |
| Endpoints/UI reentrenamiento | **Pendiente de desarrollo** (docs arquitectura) |
| Modelos RF/SVM/XGBoost en código | **Pendiente de verificar** — no afirmar alineados con REQ-06.2 / RF-18 sin evidencia |

---

## 9. Sección especial — RF-19 Semáforo de completitud

### Definido en DRS

- Indicador **Docente / Administrador**; prioridad **Media**.
- Estado por variable: **verde (completa)**, **amarillo (parcial)**, **rojo (faltante)**.
- Variables mencionadas en REQ-19:** notas, asistencia, Fast Test, variables socioeconómicas, reportes conductuales**.
- Bloquear **Procesar ML** si hay críticas faltantes (notas, asistencia) (**REQ-19.3**).
- Actualización automática al cargar nueva información (**REQ-19.4**).

### Contraste implementación

| Dimensión | Clasificación |
|-----------|---------------|
| RF-19 | **Definido en DRS** |
| UI semáforo + bloqueo ML según REQ-19 | **Pendiente de desarrollo** (`resumen-arquitectura`) |
| Evidencia en código actual | **Pendiente de verificar** antes de declarar cualquier nivel |

---

## 10. Advertencias para Cursor

1. **No afirmar** un RF como implementado solo por figurar en el **DRS**.
2. **Sprint 6A ≠ RF-14 completo** (REQ-14.1–14.5 siguen mayormente **Pendiente de verificar** en código).
3. **No ubicar RF-16** como obligación de Sprint **6A**; plan típico **6B**.
4. **No inventar** Random Forest / SVM / XGBoost entrenados o pipeline **RF-18** sin **evidencia** en **`ml-service` y Laravel**.
5. **No asumir** importación SIAGIE / Excel / CSV completa (**RF-01**, **RF-03**) sin rutas/controladores revisados.
6. **No asumir** restricción **docente–aula** ni **mapa multi-sede** para **RF-14** sin modelo/relación **confirmada en código**.
7. **RN-03** tiene **dos** disparadores de alerta (Alto **o** transición Bajo→Medio dos bimestres); no reducir solo a umbral Alto sin verificar código.
8. **Mockups** son referencia visual; **no** son datos reales.
9. Antes de implementar: **`docs/drs/DRS_SIDERAE_Blenkir_v2.md`** + **código** + [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) + contextos `docs/arquitectura/`.

---

## Nota operativa repositorio (no altera el DRS formal)

- Tras **Sprint 7.5A**, el backend registra en la tabla `activity_log` (paquete Spatie) varias acciones críticas de la API (estudiantes, datos académicos, riesgo, alertas, intervenciones, cierre, export PDF del dashboard). Detalle en `docs/arquitectura/contexto-backend-laravel.md`.
- **RF-17** del PDF sigue siendo el requisito formal; la implementación en código es **parcial** (sin UI de auditoría ni cobertura explícita de todos los REQ del PDF).

---

## Referencias locales

| Documento | Uso |
|-----------|-----|
| [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) | **DRS vigente — estado V1** |
| `DRS_SIDERAE_Blenkir_v1.pdf` | Fuente formal histórica |
| `.cursorrules` | Jerarquía y anti-alucinación |
| `docs/arquitectura/resumen-arquitectura.md` | Matriz rápida DRS ↔ código |
| [`docs/matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md) | Trazabilidad RF–test |

---

## Control de contenido del resumen RF/RN/RNF

- **Nombre, actor, prioridad** RF-01–RF-20: extraídos de la tabla resumen **sección 7** del DRS PDF.
- **Detalle REQ** y texto largo:** consultar páginas 9–18** del mismo PDF si se necesita implementación campo a campo.
