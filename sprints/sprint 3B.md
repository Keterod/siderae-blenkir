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

## Pruebas asociadas

### Pruebas manuales
- Verificar que se pueden registrar notas de un estudiante.
- Verificar que el sistema rechaza notas fuera del rango 0 a 20.
- Verificar que se puede registrar asistencia con estados válidos: `presente`, `tardanza`, `falta`.
- Verificar que se pueden registrar variables socioeconómicas del estudiante.
- Verificar que el perfil del estudiante muestra notas, asistencia y variables registradas.

### Pruebas automatizadas
- Ejecutar pruebas relacionadas a RF-01:
  - `test_importar_xlsx_valido_vincula_notas_al_perfil`
  - `test_importar_rechaza_nota_fuera_de_rango`
- Ejecutar pruebas relacionadas a RF-02:
  - `test_docente_registra_asistencia_de_su_aula`
  - `test_calcula_porcentaje_inasistencias_por_bimestre`
- Ejecutar pruebas relacionadas a RF-05:
  - `test_variables_socioeconómicas_se_almacenan_en_perfil`

### Criterios de validación
- Las notas se guardan correctamente y respetan el rango permitido.
- La asistencia se registra con estados válidos.
- Las variables socioeconómicas quedan asociadas al perfil del estudiante.
- Los datos base quedan listos para el procesamiento ML del Sprint 4.