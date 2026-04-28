# Fichas de Pruebas Manuales
## Sistema Inteligente de Detección Temprana de Riesgo Académico y Deserción Estudiantil — SIDERAE-Blenkir

**Proyecto:** SIDERAE-Blenkir — Colegio Blenkir, Huancayo, Junín  
**Tipo de prueba:** Manual — Ficha de observación / Checklist

---

> **Leyenda de estado:** ✓ Exitoso · ✗ Fallido · N/A No aplica

---

## RF-01 — Carga e importación de datos académicos

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-01 |
| **Nombre** | Carga e importación de datos académicos |
| **Actor(es)** | Docente / Administrador |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-01-01 | Importar archivo .xlsx válido con notas | Archivo .xlsx con columnas: `código_estudiante`, `bimestre`, `nota_curso`, `nota_conducta` | Sistema importa y vincula notas al perfil del estudiante. Muestra confirmación. | | | |
| CP-01-02 | Importar archivo con código de estudiante inexistente | Archivo .xlsx con código que no existe en BD | Sistema rechaza la fila y muestra mensaje de error descriptivo. | | | |
| CP-01-03 | Importar archivo con nota fuera de rango (ej: 25) | Archivo .xlsx con nota = 25 | Sistema rechaza la fila y notifica el error de rango. | | | |
| CP-01-04 | Importar archivo con campos obligatorios vacíos | Archivo .xlsx con columna `bimestre` vacía | Sistema rechaza la fila y muestra mensaje descriptivo. | | | |
| CP-01-05 | Carga manual de nota individual desde formulario web | Datos ingresados en formulario: código, bimestre, nota | Sistema registra la nota y la vincula al perfil. Se registra en `activity_log`. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-02 — Registro digital de asistencia semanal

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-02 |
| **Nombre** | Registro digital de asistencia semanal |
| **Actor(es)** | Docente |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-02-01 | Registrar asistencia completa de un aula | Docente autenticado selecciona aula y semana; marca Presente/Tardanza/Falta para cada estudiante | Sistema guarda el registro y actualiza el perfil de riesgo de cada estudiante. | | | |
| CP-02-02 | Editar registro de asistencia dentro del plazo (antes del viernes 23:59) | Registro de asistencia ya guardado; docente modifica un estado | Sistema permite la edición y actualiza el perfil de riesgo. | | | |
| CP-02-03 | Intentar editar asistencia fuera del plazo (después del viernes 23:59) | Registro de semana anterior | Sistema bloquea la edición y muestra mensaje de plazo vencido. | | | |
| CP-02-04 | Verificar cálculo del porcentaje acumulado de inasistencias | Estudiante con 2 faltas de 8 semanas registradas | Sistema muestra 25 % de inasistencia acumulada por bimestre. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-03 — Importación de resultados del Fast Test

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-03 |
| **Nombre** | Importación de resultados del Fast Test |
| **Actor(es)** | Coordinador Académico |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-03-01 | Importar archivo .xlsx del Fast Test válido | Archivo con columnas: `código_estudiante`, `dimensión_evaluada`, `puntaje` | Sistema vincula resultados al perfil del estudiante y los activa como predictores ML. | | | |
| CP-03-02 | Importar archivo con estudiante sin código en BD | Archivo con código inexistente | Sistema rechaza la fila y notifica al coordinador con el motivo. | | | |
| CP-03-03 | Verificar notificación de resultados de importación | Archivo con 10 filas válidas y 2 inválidas | Sistema muestra: 10 importados exitosamente, 2 rechazados con motivo. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-04 — Registro digital de reportes conductuales

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-04 |
| **Nombre** | Registro digital de reportes conductuales |
| **Actor(es)** | Psicólogo / Tutor |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-04-01 | Registrar reporte conductual desde perfil del estudiante | Psicólogo autenticado; formulario con fecha, tipo, descripción, gravedad y acción | Sistema guarda el reporte, lo vincula al perfil de riesgo y lo registra en `activity_log`. | | | |
| CP-04-02 | Intentar crear reporte conductual con rol Docente | Usuario con rol Docente intenta acceder al formulario de reporte conductual | Sistema deniega el acceso y muestra mensaje de permiso insuficiente. | | | |
| CP-04-03 | Verificar que el reporte actualiza el índice de riesgo conductual | Reporte de gravedad `grave` registrado para estudiante con riesgo bajo | Sistema actualiza el índice de riesgo conductual disponible para el motor ML. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-05 — Integración de variables socioeconómicas

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-05 |
| **Nombre** | Integración de variables socioeconómicas |
| **Actor(es)** | Administrador / Sistema |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-05-01 | Registrar variables socioeconómicas en matrícula | Formulario de matrícula con: composición familiar, nivel socioeconómico, acceso a internet, distancia | Sistema almacena las variables en el perfil del estudiante como predictores ML. | | | |
| CP-05-02 | Verificar que el Semáforo muestra variables socioeconómicas completas | Estudiante con todas las variables socioeconómicas registradas | Semáforo (RF-19) muestra estado Verde para variables socioeconómicas. | | | |
| CP-05-03 | Actualizar variables socioeconómicas en matrícula anual | Variables socioeconómicas del año anterior; se inicia nuevo proceso de matrícula | Sistema actualiza las variables y las mantiene como predictores activos en ML. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-06 — Procesamiento multivariable y cálculo del índice de riesgo

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-06 |
| **Nombre** | Procesamiento multivariable y cálculo del índice de riesgo |
| **Actor(es)** | Sistema |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-06-01 | Ejecutar procesamiento ML para estudiante con perfil completo | Perfil completo: notas, asistencia, Fast Test, variables socioeconómicas, reportes conductuales | Sistema envía variables al microservicio Flask, recibe índice entre 0 y 1, lo almacena con fecha. | | | |
| CP-06-02 | Verificar respuesta del sistema cuando Flask no responde | Microservicio Flask apagado | Sistema registra el fallo en el log y notifica al administrador. | | | |
| CP-06-03 | Verificar que el procesamiento completa en menos de 10 segundos | Perfil completo de un estudiante | El índice de riesgo es calculado y almacenado en menos de 10 segundos. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-07 — Evaluación automática del nivel de riesgo

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-07 |
| **Nombre** | Evaluación automática del nivel de riesgo |
| **Actor(es)** | Sistema |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-07-01 | Clasificar índice de riesgo Alto (≥ 0.70) | Índice calculado = 0.82 | Sistema asigna nivel Alto al perfil del estudiante y actualiza el semáforo del dashboard. | | | |
| CP-07-02 | Clasificar índice de riesgo Medio (0.40–0.69) | Índice calculado = 0.55 | Sistema asigna nivel Medio al perfil del estudiante. | | | |
| CP-07-03 | Clasificar índice de riesgo Bajo (< 0.40) | Índice calculado = 0.25 | Sistema asigna nivel Bajo al perfil del estudiante. | | | |
| CP-07-04 | Verificar que el administrador puede cambiar umbrales | Administrador cambia umbral Alto a ≥ 0.75 desde el panel de configuración | Sistema aplica el nuevo umbral en la siguiente clasificación. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-08 — Emisión de alertas tempranas accionables

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-08 |
| **Nombre** | Emisión de alertas tempranas accionables |
| **Actor(es)** | Sistema |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-08-01 | Verificar generación de alerta cuando índice supera umbral Alto | Estudiante con índice calculado ≥ 0.70 | Sistema genera alerta con nombre, índice, nivel, 3 factores y recomendación. Notifica al docente y directivo. | | | |
| CP-08-02 | Verificar que la alerta tiene estado inicial Pendiente | Alerta recién generada | La alerta aparece con estado `Pendiente` en la plataforma. | | | |
| CP-08-03 | Verificar registro en `activity_log` de la alerta generada | Alerta generada automáticamente | `activity_log` registra: alerta, estudiante, fecha/hora, usuario sistema. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-09 — Intervención preventiva del docente

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-09 |
| **Nombre** | Intervención preventiva del docente |
| **Actor(es)** | Docente |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-09-01 | Registrar intervención desde perfil del estudiante en riesgo | Docente accede al perfil tras recibir alerta; completa formulario: tipo, descripción, fecha | Sistema guarda la intervención, cambia estado de alerta a `En atención` y registra en `activity_log`. | | | |
| CP-09-02 | Verificar visualización del perfil completo del estudiante | Docente abre perfil de estudiante con alerta activa | Sistema muestra: notas, asistencia, reportes conductuales, variables socioeconómicas e historial de riesgo. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-10 — Decisión de derivación por el directivo

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-10 |
| **Nombre** | Decisión de derivación por el directivo |
| **Actor(es)** | Directivo |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-10-01 | Derivar estudiante al psicólogo desde panel de alertas | Directivo selecciona alerta activa y elige `Derivar a psicólogo` | Sistema notifica al psicólogo, actualiza perfil del estudiante a estado `Derivado` y registra en `activity_log`. | | | |
| CP-10-02 | Registrar decisión de intervención estándar | Directivo selecciona alerta activa y elige `Intervención estándar` | Sistema registra la decisión en `activity_log` y mantiene la alerta en estado `En atención`. | | | |
| CP-10-03 | Verificar filtros del listado de alertas activas | Directivo filtra por sede `Chilca` y nivel `Primaria` | Sistema muestra solo estudiantes de esa sede y nivel con alertas activas. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-11 — Atención psicológica preventiva con perfil integrado

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-11 |
| **Nombre** | Atención psicológica preventiva con perfil integrado |
| **Actor(es)** | Psicólogo / Tutor |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-11-01 | Visualizar perfil integrado del estudiante derivado | Psicólogo recibe notificación de derivación y accede al perfil | Sistema muestra datos académicos, conductuales, socioeconómicos e historial de intervenciones. | | | |
| CP-11-02 | Agregar nota de atención psicológica al perfil | Psicólogo redacta nota de atención y guarda | Sistema vincula la nota al perfil del estudiante y la registra en `activity_log`. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-12 — Comunicación formal y trazable con la familia

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-12 |
| **Nombre** | Comunicación formal y trazable con la familia |
| **Actor(es)** | Docente / Directivo |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-12-01 | Registrar comunicación presencial con la familia | Docente completa formulario: tipo = presencial, fecha, participantes, resumen de acuerdos | Sistema vincula la comunicación al perfil del estudiante y a la alerta activa. Se registra en `activity_log`. | | | |
| CP-12-02 | Registrar comunicación telefónica | Directivo registra comunicación de tipo telefónica | Sistema guarda el registro correctamente vinculado al perfil y al `activity_log`. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-13 — Registro de acción tomada y cierre de alerta

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-13 |
| **Nombre** | Registro de acción tomada y cierre de alerta |
| **Actor(es)** | Docente / Directivo / Psicólogo |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-13-01 | Cerrar alerta con intervención registrada | Alerta en estado `En atención` con al menos una intervención asociada; usuario ingresa descripción del resultado | Sistema marca la alerta como cerrada y registra en `activity_log` con fecha y resultado. | | | |
| CP-13-02 | Intentar cerrar alerta sin intervención previa | Alerta sin ninguna intervención, derivación o comunicación familiar asociada | Sistema bloquea el cierre y muestra mensaje indicando que se requiere al menos una acción previa. | | | |
| CP-13-03 | Verificar que una alerta cerrada no puede reabrirse | Alerta con estado `Cerrada` | Sistema no muestra opción de reapertura. Si el índice vuelve a superar el umbral, se genera una nueva alerta. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-14 — Panel de visualización (dashboard) de riesgo

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-14 |
| **Nombre** | Panel de visualización (dashboard) de riesgo |
| **Actor(es)** | Docente / Directivo |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-14-01 | Verificar que el docente ve solo su aula en el dashboard | Docente autenticado accede al dashboard | Sistema muestra distribución de riesgo únicamente del aula asignada al docente. | | | |
| CP-14-02 | Verificar que el directivo ve todas las sedes | Directivo autenticado accede al dashboard | Sistema muestra mapa de riesgo consolidado de todas las sedes con filtros. | | | |
| CP-14-03 | Exportar gráfico del dashboard en PDF | Directivo selecciona `Exportar PDF` en el dashboard | Sistema genera y descarga el PDF con los gráficos actualizados. | | | |
| CP-14-04 | Verificar actualización automática del dashboard tras nuevo índice | Se procesa un nuevo índice de riesgo para un estudiante | Dashboard refleja el nuevo estado de riesgo sin necesidad de recarga manual. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-15 — Gestión de usuarios y control de acceso por rol

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-15 |
| **Nombre** | Gestión de usuarios y control de acceso por rol |
| **Actor(es)** | Administrador |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-15-01 | Crear nuevo usuario con rol Docente | Administrador completa formulario de nuevo usuario y asigna rol Docente | Sistema crea el usuario con los permisos del rol Docente. Se registra en `activity_log`. | | | |
| CP-15-02 | Desactivar cuenta de usuario | Administrador selecciona un usuario activo y lo desactiva | Sistema desactiva la cuenta. El usuario no puede iniciar sesión. Se registra en `activity_log`. | | | |
| CP-15-03 | Verificar que el backend valida permisos independientemente del frontend | Usuario con rol Docente intenta acceder a endpoint de gestión de usuarios vía API directa | Backend responde con error 403 Forbidden aunque el frontend no muestre la opción. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-16 — Exportación de reportes en PDF

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-16 |
| **Nombre** | Exportación de reportes en PDF |
| **Actor(es)** | Docente / Directivo |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-16-01 | Exportar reporte individual de estudiante en PDF | Docente selecciona `Exportar PDF` en perfil de estudiante con riesgo Alto | PDF generado incluye: índice de riesgo, nivel, factores, historial e intervenciones. Incluye logo, fecha y usuario. | | | |
| CP-16-02 | Exportar reporte de aula en PDF | Docente selecciona `Exportar PDF` en vista de aula | PDF incluye distribución de riesgo y listado de estudiantes por nivel. | | | |
| CP-16-03 | Verificar registro en `activity_log` de PDF generado | PDF exportado por cualquier usuario | `activity_log` registra: usuario, fecha y tipo de reporte generado. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-17 — Registro de auditoría de acciones

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-17 |
| **Nombre** | Registro de auditoría de acciones |
| **Actor(es)** | Sistema |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-17-01 | Verificar registro automático de carga de datos | Docente importa archivo .xlsx de notas | `activity_log` registra: usuario, acción `carga de datos`, modelo afectado, fecha/hora. | | | |
| CP-17-02 | Verificar que ningún rol puede eliminar registros del log | Administrador intenta eliminar un registro del `activity_log` | Sistema deniega la acción. El registro permanece intacto. | | | |
| CP-17-03 | Filtrar `activity_log` por usuario y fecha | Administrador aplica filtro: `usuario=Diego`, `fecha=hoy` | Sistema muestra solo los registros que coinciden con los filtros. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-18 — Reentrenamiento del modelo ML

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-18 |
| **Nombre** | Reentrenamiento del modelo ML |
| **Actor(es)** | Administrador |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-18-01 | Iniciar reentrenamiento del modelo ML | Administrador accede al módulo ML y selecciona `Reentrenar modelo` | Sistema envía datos históricos al microservicio Flask y muestra métricas del nuevo modelo (accuracy, precision, recall, F1). | | | |
| CP-18-02 | Confirmar activación del nuevo modelo | Administrador revisa métricas y confirma activación | Sistema activa el nuevo modelo y conserva el anterior como respaldo por 30 días. | | | |
| CP-18-03 | Intentar reentrenar con rol Docente | Usuario con rol Docente intenta acceder al módulo de Gestión ML | Sistema deniega el acceso con mensaje de permiso insuficiente. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-19 — Semáforo de completitud de datos

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-19 |
| **Nombre** | Semáforo de completitud de datos |
| **Actor(es)** | Docente / Administrador |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-19-01 | Verificar semáforo Verde para perfil completo | Estudiante con todas las variables registradas: notas, asistencia, Fast Test, socioeconómicas, conductuales | Semáforo muestra Verde en todas las variables. Botón `Procesar ML` habilitado. | | | |
| CP-19-02 | Verificar semáforo Rojo para variables críticas faltantes | Estudiante sin notas bimestrales registradas | Semáforo muestra Rojo en `notas`. Botón `Procesar ML` deshabilitado. | | | |
| CP-19-03 | Verificar actualización automática del semáforo al cargar datos | Semáforo en Rojo por asistencia faltante; docente registra la asistencia | Semáforo actualiza automáticamente la variable de asistencia a Verde. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________

---

## RF-20 — Historial de riesgo por estudiante

| Campo | Valor |
|---|---|
| **ID Requerimiento** | RF-20 |
| **Nombre** | Historial de riesgo por estudiante |
| **Actor(es)** | Docente / Directivo |
| **Tester responsable** | |
| **Fecha de ejecución** | |

| ID Caso | Descripción del caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|---|---|---|
| CP-20-01 | Visualizar historial de riesgo por bimestre | Docente accede a perfil de estudiante con 3 bimestres de historial | Sistema muestra gráfico de líneas con eje X = bimestres, eje Y = índice de riesgo. Incluye intervenciones asociadas. | | | |
| CP-20-02 | Exportar historial de riesgo en PDF | Docente selecciona `Exportar PDF` en la pestaña de historial | Sistema genera PDF con el historial completo del estudiante apto para reportes ISO 9001. | | | |

**Firma del Tester:** ___________________________ &nbsp;&nbsp;&nbsp; **V°B° Líder de Pruebas:** ___________________________
