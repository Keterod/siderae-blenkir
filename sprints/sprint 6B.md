# Sprint 6B: Filtros + export básico + ajuste fino por rol

## Objetivo
Completar el dashboard para demo ejecutiva con filtros, exportación y reglas de visibilidad por rol.

## Duración estimada
1 semana

## Alcance
- Filtros funcionales sobre dashboard.
- Export básico de reporte.
- Ajustes de acceso/visibilidad por rol.

## Actividades
1. Filtros dashboard:
   - sede
   - grado
   - sección
   - nivel de riesgo
2. Export básico:
   - generar reporte simple (CSV/PDF según factibilidad)
   - incluir fecha, resumen de riesgos, alertas activas
3. Ajuste fino por rol:
   - `docente`: visibilidad acotada
   - `directivo`: vista consolidada
   - `administrador`: vista total
4. Hardening:
   - validaciones de parámetros
   - control de permisos en endpoint y frontend

## Dependencias de entrada
Sprint 6A completado.

## Criterios de aceptación
- Filtros alteran correctamente KPIs y tabla.
- Reporte se genera con contenido válido.
- Visibilidad por rol respeta permisos definidos.

## Entregables
- Dashboard con filtros.
- Export básico funcional.
- Reglas finales de acceso por rol en dashboard.

## Definición de terminado
Flujo completo demostrable:
`Login -> Estudiantes -> Datos -> ML -> Alertas -> Intervención -> Dashboard`.
