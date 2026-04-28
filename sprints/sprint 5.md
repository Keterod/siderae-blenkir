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
