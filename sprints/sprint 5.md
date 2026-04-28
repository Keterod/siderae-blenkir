# Sprint 5: Alertas automáticas + detalle + intervención + cierre

## Objetivo
Implementar gestión operativa de alertas a partir de riesgo alto, con trazabilidad de intervención.

## Duración estimada
1 semana

## Alcance
- Generación automática de alerta por riesgo alto.
- Listado y detalle de alertas.
- Registro de intervención.
- Cierre controlado de alertas.

## Actividades
1. Generación automática:
   - al persistir `nivel = Alto` en Sprint 4
   - crear alerta con `estudiante_id`, `indice_riesgo_id`, `estado`, `recomendacion`
2. Evitar duplicados:
   - no crear nueva alerta si existe activa (`pendiente` o `en_atencion`)
3. Crear endpoints:
   - `GET /api/alertas`
   - `GET /api/alertas/{id}`
   - `POST /api/alertas/{id}/intervenciones`
   - `POST /api/alertas/{id}/cerrar`
4. Intervención:
   - tipos alineados al esquema: `academica`, `emocional`, `familiar`
   - al registrar intervención: cambiar estado a `en_atencion`
5. Cierre:
   - exigir al menos una intervención previa
   - guardar `resultado_cierre`, `fecha_cierre`, `cerrada_por`
6. Frontend:
   - lista de alertas
   - detalle con historial de intervenciones
   - acción de cierre con validación

## Dependencias de entrada
Sprint 4 completado.

## Dependencias de salida
Habilita Sprint 6A.

## Criterios de aceptación
- Se crea alerta automática para riesgo alto.
- No hay duplicidad de alertas activas.
- Intervenciones se registran y cambian estado.
- Cierre solo con evidencia de atención.

## Entregables
- API de alertas e intervenciones.
- Lógica de generación automática.
- UI de lista/detalle/cierre de alertas.

## Pruebas asociadas

### Pruebas manuales
- Verificar que se genera una alerta cuando el riesgo es alto.
- Verificar que la alerta muestra: estudiante, nivel, índice y recomendación.
- Verificar que el estado inicial de la alerta es `Pendiente`.
- Verificar que el docente puede registrar una intervención.
- Verificar que el estado de la alerta cambia a `En atención`.
- Verificar que se puede cerrar una alerta con intervención registrada.
- Verificar que no se puede cerrar una alerta sin intervención previa.

### Pruebas automatizadas
- Ejecutar pruebas relacionadas a RF-08:
  - `test_genera_alerta_cuando_índice_supera_umbral_alto`
  - `test_alerta_tiene_estado_inicial_pendiente`
- Ejecutar pruebas relacionadas a RF-09:
  - `test_docente_registra_intervención_y_actualiza_alerta`
- Ejecutar pruebas relacionadas a RF-13:
  - `test_cierra_alerta_con_intervención_previa_registrada`
  - `test_bloquea_cierre_sin_intervención_previa`

### Criterios de validación
- Las alertas se generan automáticamente al superar el umbral.
- Las alertas tienen estados correctamente gestionados.
- Las intervenciones quedan registradas y vinculadas a la alerta.
- El flujo completo (alerta → intervención → cierre) funciona correctamente.