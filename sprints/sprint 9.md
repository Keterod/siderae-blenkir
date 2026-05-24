# Sprint 9: Pruebas integrales, regresión y corrección de bugs

## Objetivo
Validar la estabilidad del sistema como prototipo académico mediante prueba integral y regresión: backend, flujos críticos instrumentados cuando corresponda, permisos, dashboard y reportes en el alcance ya implementado, registrando evidencias y corrigiendo defectos sin incorporar grandes funcionalidades nuevas.

## Duración estimada
1 a 2 semanas

## Alcance
- Ejecución ordenada de pruebas backend (Laravel PHPUnit / Feature Tests ya existentes o ampliaciones mínimas acordadas en sprints previos).
- Pruebas manuales finales con registro de resultados y capturas donde el equipo lo requiera.
- Smoke tests y flujos end-to-end con **Cypress** sobre la interfaz estabilizada (Sprint 7B y refuerzos de Sprint 8), cuando el entorno y la suite estén listos; **no** es obligatorio que Cypress se configure por primera vez en este sprint si ya existiera parcialmente, pero debe quedar definido qué suite se ejecuta y cómo.
- Recorrido integral acordado: **login → estudiante → datos (notas, asistencia, variables) → procesamiento de riesgo → alerta → intervención → cierre** → **dashboard** → **reportes/export** dentro del alcance de Sprint 6B y permisos de Sprint 8.
- Recorrido curricular (Sprint 8.5B): **malla curricular → temas semanales → asignación docente → registro notas C/L/T (docente) → CE → consulta coordinador → riesgo con promedio desde CE o legacy**.
- Verificación focalizada de **401/403** en escenarios combinados después de Sprint 8.
- Corrección de bugs detectados (priorización por severidad) sin ampliación de alcance funcional mayor.
- Producción de **evidencias de prueba** (reportes PHPUnit, vídeos o capturas Cypress, registros manuales) para uso en Sprint 10.

## Actividades
1. Planificar la campaña desde el Plan de Pruebas del proyecto y la matriz RF–caso donde existan (`docs/pruebas/`), priorizando riesgos y flujos P0 (**pendiente de confirmar** mapeo RF exacto desde Formato 06 si está sólo fuera del repo digital).
2. Ejecutar `php artisan test` (o comando equivalente) en estado limpio de entorno reproducible según README/Docker ya documentados.
3. Ejecutar o completar suite **Cypress** para flujos críticos: login correcto e incorrecto, navegación sidebar/header, estudiantes (lista, alta/edición, perfil), notas, asistencia, variables socioeconómicas, procesamiento de riesgo, alertas (lista, detalle), intervención, cierre de alerta, dashboard, reportes/export según permiso; y, si la suite está lista, smoke curricular (malla, tema, asignación docente, notas semanales).
4. Sesiones de prueba manual para huecos no cubiertos por automatización o casos de negocio frágiles.
5. Registro de defectos: descripción, severidad, pasos, estado; triage y corrección iterativa.
6. Regresión breve tras correcciones críticas.
7. Compilar paquete de evidencias (salidas de test, enlaces o archivos consolidados) para entregar a Sprint 10.

## Dependencias de entrada
Sprint **8.5B** completado (módulo curricular académico: API, UI, asignación docente, integración riesgo). Sprint 8 (seguridad RBAC) es prerrequisito indirecto vía 8.5A/8.5B.

## Dependencias de salida
Habilita Sprint 10.

## Criterios de aceptación
- Los flujos críticos acordados pasan manualmente en el escenario definido por el equipo, o quedan documentados como limitaciones conocidas (**pendiente de confirmar** si algún caso queda aplazado con justificación académica).
- Backend: suite ejecutada con resultado explícito registrado para el hito (**pendiente objetivo cuantitativo**, p. ej. “sin fallos en tests P0” según equipo).
- Cypress: ejecutado al menos smoke ampliado o plan documentado más ejecución parcial completa según capacidad del sprint; lo no ejecutado debe listarse explícitamente como deuda.
- Bugs críticos resueltos o mitigados con decisión documentada.

## Entregables
- Informe corto de ejecución de pruebas (alcance, ambiente, fecha, resultados, defectos abiertos/cerrados).
- Evidencias adjuntas o referenciadas para documentación final.
- Código fuente actualizado con las correcciones de bugs priorizadas acordadas al ejecutar el sprint (los entregables concretos los define el equipo durante la ejecución).

## Pruebas asociadas

### Pruebas manuales
- Casos de aceptación del flujo completo de negocio del prototipo y casos exploratorios en dashboard y reportes.
- Verificación de permisos tras Sprint 8 con usuarios de prueba por rol.
- Revisión de consistencia de datos visibles frente a base de datos en escenarios de muestra.

### Pruebas automatizadas
- **Backend:** Laravel Feature Tests / PHPUnit — suite completa o subconjunto P0 acordado; evidencia de ejecución.
- **Frontend / E2E:** **Cypress** — flujos críticos listados en el alcance; idealmente integrado al pipeline local del equipo al finalizar el sprint (**pendiente de confirmar** CI).

## Criterios de validación
- El sistema se considera **estable para demostración académica** según criterios del equipo y asesor, con riesgos documentados.
- Las evidencias son suficientes para alimentar la documentación y la matriz RF–Sprint–Test en Sprint 10.
- No se abren funcionalidades mayores fuera de backlog acordado; el foco es calidad y trazabilidad de lo ya construido.
