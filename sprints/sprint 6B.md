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

## Pruebas asociadas

### Pruebas manuales
- Verificar que se pueden aplicar filtros en el dashboard (por aula, nivel, sede).
- Verificar que los resultados cambian correctamente según los filtros.
- Verificar que se puede exportar un reporte en PDF.
- Verificar que el PDF contiene datos correctos del estudiante o aula.
- Verificar que los permisos afectan la visualización (docente vs directivo).
- Verificar que los usuarios solo ven lo que su rol permite.

### Pruebas automatizadas
- Ejecutar pruebas relacionadas a RF-14 (avanzado):
  - validación de filtros por rol y contexto
- Ejecutar pruebas relacionadas a RF-16:
  - `test_genera_pdf_reporte_individual_con_datos_completos`
  - `test_genera_pdf_reporte_de_aula`
- Ejecutar pruebas relacionadas a RF-17:
  - `test_carga_de_datos_registrada_en_activity_log`
  - `test_ningún_rol_puede_eliminar_registros_del_log`

### Criterios de validación
- Los filtros funcionan correctamente y afectan los datos mostrados.
- La exportación PDF genera documentos válidos y completos.
- El sistema respeta los permisos de cada rol en visualización y acciones.
- El sistema está listo para uso como prototipo funcional completo.