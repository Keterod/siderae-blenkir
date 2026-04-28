# Sprint 6A: Dashboard mínimo (KPIs + tabla último riesgo + alertas por estado)

## Objetivo
Publicar un dashboard mínimo demostrable con datos reales del flujo operativo.

## Duración estimada
1 semana

## Alcance
- Endpoint agregado de dashboard.
- KPIs principales.
- Tabla de estudiantes con último riesgo.
- Resumen de alertas por estado.

## Actividades
1. Crear endpoint:
   - `GET /api/dashboard`
2. Incluir en respuesta:
   - total estudiantes
   - conteo por nivel de riesgo
   - conteo de alertas por estado
   - últimos estudiantes procesados
3. Frontend dashboard:
   - tarjetas KPI
   - tabla con último índice/nivel por estudiante
   - visual de distribución (simple)
4. Seguridad:
   - proteger con permiso `ver_dashboard`

## Dependencias de entrada
Sprint 5 completado.

## Dependencias de salida
Habilita Sprint 6B.

## Criterios de aceptación
- Dashboard carga sin errores.
- KPIs y tabla reflejan datos reales de BD.
- Endpoint responde en tiempos aceptables para demo.

## Entregables
- Endpoint `/api/dashboard`.
- `DashboardPage` mínimo.
- KPIs y tabla operativa.
