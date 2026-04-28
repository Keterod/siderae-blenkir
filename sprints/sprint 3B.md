# Sprint 3B: Captura de datos base + validaciones

## Objetivo
Registrar la información base para análisis de riesgo: notas, asistencias y variables socioeconómicas.

## Duración estimada
1 a 2 semanas

## Alcance
- Endpoints de captura por estudiante.
- Validaciones de integridad de datos.
- Visualización de datos en perfil del estudiante.

## Actividades
1. Notas:
   - `POST /api/estudiantes/{id}/notas`
   - validación de rango (0 a 20)
2. Asistencias:
   - `POST /api/estudiantes/{id}/asistencias`
   - estados permitidos: `presente`, `tardanza`, `falta`
3. Variables socioeconómicas:
   - `POST /api/estudiantes/{id}/variables-socioeconomicas`
   - nivel socioeconómico, acceso internet, distancia
4. Frontend:
   - formularios por bloque de datos en perfil
   - historial básico por estudiante
5. Preparar resumen de features para ML:
   - promedio de notas
   - porcentaje de asistencia
   - variables requeridas para `/predict`

## Dependencias de entrada
Sprint 3A completado.

## Dependencias de salida
Habilita Sprint 4.

## Criterios de aceptación
- Registro y consulta de notas/asistencia/socioeconómico sin errores.
- Validaciones bloquean datos inválidos.
- Perfil muestra datos base listos para procesamiento ML.

## Entregables
- APIs de datos base.
- UI de captura en perfil.
- Resumen de features mínimo para ML.
