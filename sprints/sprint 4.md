# Sprint 4: Integración Laravel -> ML `/predict` + persistencia de riesgo

## Objetivo
Integrar backend con el servicio Flask para calcular riesgo y guardar el resultado en base de datos.

## Duración estimada
1 semana

## Alcance
- Servicio cliente ML en Laravel.
- Endpoint de procesamiento por estudiante.
- Persistencia del riesgo en tabla `indices_riesgo`.
- Visualización del último riesgo en perfil.

## Actividades
1. Crear `MlRiskService` en Laravel para consumir:
   - `POST ${ML_SERVICE_URL}/predict`
2. Construir payload desde datos base del estudiante:
   - `promedio_notas`
   - `porcentaje_asistencia`
   - `reportes_conductuales` (si aplica)
   - `fast_test_puntaje` (valor controlado si no existe)
   - `nivel_socioeconomico`
   - `acceso_internet`
   - `distancia_colegio`
3. Crear endpoint:
   - `POST /api/estudiantes/{id}/procesar-riesgo`
   - validar prerequisitos de datos antes de invocar ML
4. Persistir en `indices_riesgo` con campos reales:
   - `estudiante_id`
   - `indice`
   - `nivel`
   - `anio_escolar`
   - `bimestre`
   - `variables_utilizadas`
   - `modelos_scores`
5. Frontend:
   - botón `Procesar riesgo`
   - visualización de último índice y nivel
6. Manejo de errores:
   - timeout
   - respuesta de fallback
   - logging de fallo ML

## Dependencias de entrada
Sprint 3B completado.

## Dependencias de salida
Habilita Sprint 5.

## Criterios de aceptación
- Endpoint de procesamiento funcional.
- Errores de ML no rompen la app.
- Resultado queda persistido correctamente.
- Perfil muestra último cálculo de riesgo.

## Entregables
- `MlRiskService`.
- Endpoint de procesamiento de riesgo.
- Persistencia en `indices_riesgo`.
- UI de resultado en perfil.

## Pruebas asociadas

### Pruebas manuales
- Verificar que el botón `Procesar riesgo` aparece en el perfil del estudiante.
- Verificar que el sistema no permite procesar riesgo si faltan datos mínimos.
- Verificar que el sistema calcula y muestra el índice de riesgo.
- Verificar que el nivel de riesgo se clasifica como `Alto`, `Medio` o `Bajo`.
- Verificar que el resultado queda guardado en el historial del estudiante.

### Pruebas automatizadas
- Ejecutar pruebas relacionadas a RF-06:
  - `test_envía_perfil_completo_al_microservicio_flask`
  - `test_registra_fallo_cuando_flask_no_responde`
  - `test_procesamiento_completa_en_menos_de_10_segundos`
- Ejecutar pruebas relacionadas a RF-07:
  - `test_clasifica_índice_0_82_como_alto`
  - `test_clasifica_índice_0_55_como_medio`
  - `test_clasifica_índice_0_25_como_bajo`

### Criterios de validación
- Laravel se comunica correctamente con Flask.
- El índice de riesgo se guarda en `indices_riesgo`.
- El nivel de riesgo se clasifica correctamente.
- El sistema maneja errores si el microservicio ML no responde.