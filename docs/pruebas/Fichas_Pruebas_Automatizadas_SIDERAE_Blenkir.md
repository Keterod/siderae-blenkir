# Fichas de Pruebas Automatizadas
## Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil — SIDERAE-Blenkir

**Área:** Informática  
**Responsable:** Mg. Maglioni Arana Caparachin  
**Fecha:** 02/04/2026  
**Versión:** 1.0  
**Autores:** Diego Carhuamaca Vasquez / Ernesto Chuchon Sotelo

---

> **Leyenda de estado:** ✓ Exitoso · ✗ Fallido · N/A No aplica

---
## Distribución de ejecución por sprints

Las pruebas automatizadas definidas en este documento serán ejecutadas progresivamente según el avance de los sprints del proyecto SIDERAE-Blenkir.

La asignación es la siguiente:

- **Sprint 2**
  - RF-15: Gestión de usuarios y control de acceso

- **Sprint 3A**
  - Pruebas base de entidades (estudiantes)

- **Sprint 3B**
  - RF-01: Carga de datos académicos
  - RF-02: Registro de asistencia
  - RF-05: Variables socioeconómicas

- **Sprint 4**
  - RF-06: Procesamiento ML
  - RF-07: Clasificación de riesgo

- **Sprint 5**
  - RF-08: Alertas tempranas
  - RF-09: Intervención docente
  - RF-13: Cierre de alertas

- **Sprint 6A**
  - RF-14: Dashboard

- **Sprint 6B**
  - RF-14 (avanzado)
  - RF-16: Exportación PDF
  - RF-17: Auditoría

---


## RF-01 — Carga e importación de datos académicos

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-01 |
| **Nombre** | Carga e importación de datos académicos |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter ImportarDatosTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-01-A01 | `test_importar_xlsx_valido_vincula_notas_al_perfil` | Archivo .xlsx válido con notas dentro de rango (0–20) | Response 200. Notas vinculadas al perfil del estudiante en BD. | |
| CP-01-A02 | `test_importar_rechaza_codigo_inexistente` | Archivo .xlsx con código de estudiante inexistente en BD | Response 422. Fila rechazada con mensaje de error descriptivo. | |
| CP-01-A03 | `test_importar_rechaza_nota_fuera_de_rango` | Archivo .xlsx con nota = 25 | Response 422. Fila rechazada por valor fuera de rango. | |
| CP-01-A04 | `test_importar_registra_en_activity_log` | Importación exitosa por usuario autenticado | `activity_log` contiene registro con usuario, fecha y cantidad de registros procesados. | |

---

## RF-02 — Registro digital de asistencia semanal

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-02 |
| **Nombre** | Registro digital de asistencia semanal |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) + Cypress E2E |
| **Comando de ejecución** | `php artisan test --filter AsistenciaTest` / `npx cypress run --spec cypress/e2e/rf02_asistencia.cy.js` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-02-A01 | `test_docente_registra_asistencia_de_su_aula` | Docente autenticado; lista de estudiantes del aula; estados: Presente/Tardanza/Falta | Response 200. Asistencia guardada. Perfil de riesgo actualizado. | |
| CP-02-A02 | `test_calcula_porcentaje_inasistencias_por_bimestre` | Estudiante con 2 faltas de 8 semanas | Sistema retorna 25 % de inasistencia acumulada. | |
| CP-02-A03 | `test_bloquea_edicion_fuera_de_plazo` | Petición de edición de asistencia de semana anterior (después del viernes 23:59) | Response 403. Sistema bloquea la edición con mensaje de plazo vencido. | |

---

## RF-03 — Importación de resultados del Fast Test

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-03 |
| **Nombre** | Importación de resultados del Fast Test |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter FastTestImportTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-03-A01 | `test_importar_fast_test_valido_vincula_al_perfil_ml` | Archivo .xlsx con columnas: `código_estudiante`, `dimensión_evaluada`, `puntaje` | Response 200. Resultados vinculados al perfil y disponibles como predictores ML. | |
| CP-03-A02 | `test_notifica_registros_rechazados_con_motivo` | Archivo con 10 filas válidas y 2 con código inexistente | Respuesta incluye: 10 importados, 2 rechazados con motivo descriptivo. | |

---

## RF-04 — Registro digital de reportes conductuales

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-04 |
| **Nombre** | Registro digital de reportes conductuales |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter ReporteConductualTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-04-A01 | `test_psicologo_registra_reporte_conductual` | Psicólogo autenticado; formulario con fecha, tipo, descripción, gravedad, acción | Response 201. Reporte vinculado al perfil de riesgo. Registrado en `activity_log`. | |
| CP-04-A02 | `test_docente_no_puede_crear_reporte_conductual` | Usuario con rol Docente; petición POST al endpoint de reporte conductual | Response 403. Acceso denegado por Spatie Permission. | |

---

## RF-05 — Integración de variables socioeconómicas

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-05 |
| **Nombre** | Integración de variables socioeconómicas |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter VariablesSocioeconómicasTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-05-A01 | `test_variables_socioeconómicas_se_almacenan_en_perfil` | Formulario de matrícula con: composición familiar, nivel socioeconómico, acceso a internet, distancia | Variables almacenadas en el perfil del estudiante como predictores activos. | |
| CP-05-A02 | `test_semáforo_muestra_verde_cuando_variables_completas` | Perfil con todas las variables socioeconómicas registradas | Semáforo retorna estado Verde para el grupo de variables socioeconómicas. | |

---

## RF-06 — Procesamiento multivariable y cálculo del índice de riesgo

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-06 |
| **Nombre** | Procesamiento multivariable y cálculo del índice de riesgo |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Unit Test + Feature Test con `Http::fake()`) |
| **Comando de ejecución** | `php artisan test --filter MotorMLTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-06-A01 | `test_envía_perfil_completo_al_microservicio_flask` | `Http::fake()` simula respuesta Flask con índice = 0.82; perfil completo del estudiante | Laravel envía JSON con todas las variables. Índice 0.82 almacenado en historial con fecha. | |
| CP-06-A02 | `test_registra_fallo_cuando_flask_no_responde` | `Http::fake()` simula timeout del microservicio Flask | Sistema registra el fallo en `activity_log` y notifica al administrador. | |
| CP-06-A03 | `test_procesamiento_completa_en_menos_de_10_segundos` | Perfil completo; Flask respondiendo con `Http::fake()` | Tiempo de procesamiento registrado < 10 segundos. | |

---

## RF-07 — Evaluación automática del nivel de riesgo

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-07 |
| **Nombre** | Evaluación automática del nivel de riesgo |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Unit Test) |
| **Comando de ejecución** | `php artisan test --filter ClasificadorRiesgoTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-07-A01 | `test_clasifica_índice_0_82_como_alto` | Índice de riesgo = 0.82; umbrales por defecto (Alto ≥ 0.70) | Nivel asignado: Alto. Semáforo dashboard actualizado a rojo. | |
| CP-07-A02 | `test_clasifica_índice_0_55_como_medio` | Índice de riesgo = 0.55 | Nivel asignado: Medio. | |
| CP-07-A03 | `test_clasifica_índice_0_25_como_bajo` | Índice de riesgo = 0.25 | Nivel asignado: Bajo. | |
| CP-07-A04 | `test_admin_puede_cambiar_umbral_alto` | Administrador actualiza umbral Alto a ≥ 0.75 | Nuevo umbral guardado. Clasificación posterior usa el umbral actualizado. | |

---

## RF-08 — Emisión de alertas tempranas accionables

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-08 |
| **Nombre** | Emisión de alertas tempranas accionables |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter AlertaTempranaTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-08-A01 | `test_genera_alerta_cuando_índice_supera_umbral_alto` | Estudiante con índice ≥ 0.70 procesado por el motor ML | Alerta creada con: nombre, índice, nivel, 3 factores principales, recomendación. Notificación enviada a docente y directivo. | |
| CP-08-A02 | `test_alerta_tiene_estado_inicial_pendiente` | Alerta recién generada | Estado de la alerta = `Pendiente`. | |
| CP-08-A03 | `test_alerta_registrada_en_activity_log` | Alerta generada automáticamente por el sistema | `activity_log` contiene registro de la alerta con fecha/hora y estudiante afectado. | |

---

## RF-09 — Intervención preventiva del docente

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-09 |
| **Nombre** | Intervención preventiva del docente |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) + Cypress E2E |
| **Comando de ejecución** | `php artisan test --filter IntervencionDocenteTest` / `npx cypress run --spec cypress/e2e/rf09_intervencion.cy.js` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-09-A01 | `test_docente_registra_intervención_y_actualiza_alerta` | Docente autenticado; alerta activa; formulario con tipo, descripción y fecha | Intervención guardada. Estado de alerta actualizado a `En atención`. Registrado en `activity_log`. | |
| CP-09-A02 | `test_perfil_muestra_datos_completos_del_estudiante` | Docente accede al perfil de estudiante con alerta activa | Respuesta incluye: notas, asistencia, reportes conductuales, variables socioeconómicas, historial de riesgo. | |

---

## RF-10 — Decisión de derivación por el directivo

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-10 |
| **Nombre** | Decisión de derivación por el directivo |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) + Cypress E2E |
| **Comando de ejecución** | `php artisan test --filter DerivacionDirectivoTest` / `npx cypress run --spec cypress/e2e/rf10_derivacion.cy.js` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-10-A01 | `test_directivo_deriva_estudiante_al_psicólogo` | Directivo autenticado; alerta activa; acción = `Derivar a psicólogo` | Notificación enviada al psicólogo. Perfil del estudiante actualizado a estado `Derivado`. Registrado en `activity_log`. | |
| CP-10-A02 | `test_filtro_por_sede_y_nivel_en_listado_de_alertas` | Directivo filtra por sede = `Chilca` y nivel = `Primaria` | Respuesta contiene solo estudiantes de esa sede y nivel con alertas activas. | |

---

## RF-11 — Atención psicológica preventiva con perfil integrado

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-11 |
| **Nombre** | Atención psicológica preventiva con perfil integrado |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter AtencionPsicologicaTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-11-A01 | `test_psicólogo_accede_al_perfil_integrado_del_derivado` | Psicólogo autenticado; estudiante con estado `Derivado` | Respuesta incluye: datos académicos, conductuales, socioeconómicos e historial de intervenciones. | |
| CP-11-A02 | `test_psicólogo_agrega_nota_de_atención_al_perfil` | Psicólogo redacta nota de atención y envía | Nota vinculada al perfil del estudiante. Registrada en `activity_log`. | |

---

## RF-12 — Comunicación formal y trazable con la familia

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-12 |
| **Nombre** | Comunicación formal y trazable con la familia |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter ComunicacionFamiliaTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-12-A01 | `test_registra_comunicacion_familiar_y_vincula_al_perfil` | Docente autenticado; formulario con tipo = presencial, fecha, participantes, resumen | Comunicación vinculada al perfil del estudiante y a la alerta activa. Registrada en `activity_log`. | |

---

## RF-13 — Registro de acción tomada y cierre de alerta

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-13 |
| **Nombre** | Registro de acción tomada y cierre de alerta |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter CierreAlertaTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-13-A01 | `test_cierra_alerta_con_intervención_previa_registrada` | Alerta en estado `En atención` con intervención asociada; usuario ingresa descripción del resultado | Alerta marcada como `Cerrada`. Registrado en `activity_log` con fecha y resultado. | |
| CP-13-A02 | `test_bloquea_cierre_sin_intervención_previa` | Alerta activa sin ninguna intervención, derivación o comunicación familiar asociada | Response 422. Sistema bloquea el cierre con mensaje descriptivo. | |
| CP-13-A03 | `test_alerta_cerrada_no_puede_reabrirse` | Alerta con estado `Cerrada`; intento de reapertura | Response 403 o 422. No se permite la reapertura. | |

---

## RF-14 — Panel de visualización (dashboard) de riesgo

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-14 |
| **Nombre** | Panel de visualización (dashboard) de riesgo |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | Cypress E2E + Jest (componentes React) |
| **Comando de ejecución** | `npx cypress run --spec cypress/e2e/rf14_dashboard.cy.js` / `npx jest --testPathPattern=Dashboard` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-14-A01 | `test_docente_ve_solo_su_aula_en_dashboard` | Docente autenticado accede al dashboard | Dashboard muestra distribución de riesgo únicamente del aula asignada al docente. | |
| CP-14-A02 | `test_directivo_ve_todas_las_sedes_en_dashboard` | Directivo autenticado accede al dashboard | Dashboard muestra mapa de riesgo consolidado de todas las sedes con filtros activos. | |
| CP-14-A03 | `test_dashboard_actualiza_al_procesar_nuevo_índice` | Se procesa un nuevo índice de riesgo para un estudiante | Dashboard refleja el nuevo estado de riesgo sin recarga manual. | |

---

## RF-15 — Gestión de usuarios y control de acceso por rol

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-15 |
| **Nombre** | Gestión de usuarios y control de acceso por rol |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter GestionUsuariosTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-15-A01 | `test_admin_crea_usuario_con_rol_docente` | Administrador autenticado; datos del nuevo usuario y rol = Docente | Response 201. Usuario creado con permisos del rol Docente. Registrado en `activity_log`. | |
| CP-15-A02 | `test_admin_desactiva_cuenta_de_usuario` | Administrador selecciona usuario activo y lo desactiva | Usuario desactivado. No puede iniciar sesión. Registrado en `activity_log`. | |
| CP-15-A03 | `test_backend_bloquea_acceso_sin_permiso_vía_api` | Usuario con rol Docente; petición directa al endpoint de gestión de usuarios | Response 403 Forbidden independientemente del frontend. | |

---

## RF-16 — Exportación de reportes en PDF

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-16 |
| **Nombre** | Exportación de reportes en PDF |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter ExportacionPDFTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-16-A01 | `test_genera_pdf_reporte_individual_con_datos_completos` | Docente autenticado; estudiante con historial de riesgo e intervenciones | PDF generado con: índice, nivel, factores, historial, intervenciones, logo, fecha y usuario. | |
| CP-16-A02 | `test_genera_pdf_reporte_de_aula` | Docente autenticado; aula con estudiantes clasificados | PDF incluye distribución de riesgo y listado de estudiantes por nivel. | |
| CP-16-A03 | `test_pdf_registrado_en_activity_log` | Exportación de cualquier tipo de PDF | `activity_log` registra: usuario, fecha y tipo de reporte generado. | |

---

## RF-17 — Registro de auditoría de acciones

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-17 |
| **Nombre** | Registro de auditoría de acciones |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) |
| **Comando de ejecución** | `php artisan test --filter AuditoriaActivityLogTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-17-A01 | `test_carga_de_datos_registrada_en_activity_log` | Docente importa archivo .xlsx de notas | `activity_log` contiene: usuario, acción, modelo afectado, datos anteriores/nuevos, fecha/hora. | |
| CP-17-A02 | `test_ningún_rol_puede_eliminar_registros_del_log` | Administrador intenta petición DELETE sobre `activity_log` | Response 403. El registro permanece intacto en BD. | |
| CP-17-A03 | `test_admin_filtra_activity_log_por_usuario_y_fecha` | Administrador aplica filtros: usuario y fecha | Respuesta contiene solo los registros que coinciden con los filtros. | |

---

## RF-18 — Reentrenamiento del modelo ML

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-18 |
| **Nombre** | Reentrenamiento del modelo ML |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test con `Http::fake()`) |
| **Comando de ejecución** | `php artisan test --filter ReentrenamientoMLTest` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-18-A01 | `test_admin_inicia_reentrenamiento_y_recibe_métricas` | `Http::fake()` simula respuesta Flask con métricas; administrador autenticado | Sistema muestra: accuracy, precision, recall, F1 del nuevo modelo. Registrado en `activity_log`. | |
| CP-18-A02 | `test_admin_confirma_activación_y_conserva_modelo_anterior` | Administrador confirma activación del nuevo modelo | Nuevo modelo activo. Modelo anterior conservado como respaldo por 30 días. | |
| CP-18-A03 | `test_docente_no_puede_iniciar_reentrenamiento` | Usuario con rol Docente; petición al endpoint de reentrenamiento | Response 403 Forbidden. | |

---

## RF-19 — Semáforo de completitud de datos

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-19 |
| **Nombre** | Semáforo de completitud de datos |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Unit Test) + Jest (componente React) |
| **Comando de ejecución** | `php artisan test --filter SemaforoCompletitudTest` / `npx jest --testPathPattern=Semaforo` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-19-A01 | `test_semáforo_verde_para_perfil_con_todas_las_variables` | Perfil con notas, asistencia, Fast Test, variables socioeconómicas y reportes conductuales completos | Semáforo retorna Verde en todas las variables. Botón `Procesar ML` habilitado. | |
| CP-19-A02 | `test_semáforo_rojo_y_bloquea_ml_cuando_faltan_notas` | Perfil sin notas bimestrales registradas | Semáforo retorna Rojo en `notas`. Botón `Procesar ML` deshabilitado. | |
| CP-19-A03 | `test_semáforo_actualiza_automáticamente_al_cargar_datos` | Semáforo en Rojo por asistencia faltante; docente registra asistencia | Semáforo actualiza automáticamente la variable de asistencia a Verde. | |

---

## RF-20 — Historial de riesgo por estudiante

| Campo | Valor |
|---|---|
| **Proyecto** | SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín |
| **ID Requerimiento** | RF-20 |
| **Nombre** | Historial de riesgo por estudiante |
| **Tipo de prueba** | Automatizada |
| **Herramienta** | PHPUnit (Feature Test) + Jest (componente React) |
| **Comando de ejecución** | `php artisan test --filter HistorialRiesgoTest` / `npx jest --testPathPattern=HistorialRiesgo` |
| **Tester responsable** | |

| ID Caso | Nombre del Test | Precondición / Datos de entrada | Assertion esperada | Estado |
|---|---|---|---|---|
| CP-20-A01 | `test_historial_almacena_índice_con_fecha_y_nivel_por_bimestre` | Estudiante con 3 bimestres de índices de riesgo calculados | Historial contiene: índice, bimestre, nivel asignado y variables utilizadas para cada entrada. | |
| CP-20-A02 | `test_historial_muestra_intervenciones_asociadas_por_bimestre` | Estudiante con intervenciones registradas en bimestres anteriores | Historial muestra intervenciones y derivaciones en el bimestre correspondiente. | |
