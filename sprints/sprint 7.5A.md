# Sprint 7.5A: Correcciones funcionales P0 antes de seguridad

## Objetivo

Corregir brechas funcionales críticas detectadas en la auditoría DRS vs código real antes de continuar con Sprint 8.

Este sprint busca fortalecer la trazabilidad, la revisión mínima de permisos y la coherencia documental del prototipo, sin ampliar de forma descontrolada el alcance funcional del sistema.

Sprint 7.5A no reemplaza Sprint 8. Lo prepara.

---

## Duración estimada

1 semana

---

## Contexto

Después de Sprint 7B se detectó que SIDERAE-Blenkir cuenta con un prototipo funcional avanzado, pero todavía no cumple completamente el DRS.

La auditoría identificó brechas críticas relacionadas con:

- Auditoría real mediante `activity_log`.
- Diferencias entre DRS, documentación y código real.
- Permisos y roles que requieren revisión antes de Sprint 8.
- Funcionalidades del DRS todavía no implementadas o implementadas parcialmente.

Estado actual relevante:

- Login, Sanctum y permisos base: implementados.
- CRUD estudiantes: implementado.
- Notas, asistencia y variables socioeconómicas: implementadas.
- Procesamiento de riesgo Laravel + Flask: implementado parcialmente.
- Alertas, intervenciones y cierre: implementados parcialmente.
- Dashboard y exportación PDF básica: implementados parcialmente.
- UI 7A/7B: implementada parcialmente.
- `activity_log`: instalado o disponible, pero uso funcional completo pendiente de verificar.
- RF-14: implementado parcialmente.
- RF-16: implementado parcialmente.
- RF-17: pendiente o implementado parcialmente, según evidencia real.
- RF-18 y RF-19: pendientes de desarrollo.

---

## Alcance

### 1. Auditoría real con `activity_log`

Revisar e implementar, si el código lo permite sin migraciones nuevas, el registro de actividad en acciones críticas del sistema:

- Creación de estudiante.
- Edición de estudiante.
- Registro de notas.
- Registro de asistencia.
- Registro de variables socioeconómicas.
- Procesamiento de riesgo.
- Generación automática de alerta.
- Registro de intervención.
- Cierre de alerta.
- Exportación PDF del dashboard.

Debe usarse `spatie/activitylog` si ya está instalado y configurado.

No se debe inventar una auditoría paralela.

Si alguna acción crítica no puede registrarse de forma segura en este sprint, debe quedar marcada como:

- Pendiente de verificar.
- Implementado parcialmente.
- Pendiente de desarrollo.

---

### 2. Revisión mínima rol–permiso–endpoint

Revisar permisos existentes y rutas críticas:

- Estudiantes.
- Datos académicos.
- Procesamiento de riesgo.
- Alertas.
- Intervenciones.
- Dashboard.
- Exportación PDF.

Verificar que las rutas sensibles estén protegidas con:

- `auth:sanctum`
- `permission:*` cuando corresponda

No crear roles nuevos sin autorización.

No crear permisos nuevos salvo que sea estrictamente necesario, esté justificado por el código real y se mantenga dentro del alcance del prototipo.

Esta revisión no reemplaza Sprint 8. Solo prepara la base.

---

### 3. Corrección de claims documentales técnicos

Revisar documentación que pueda sobrestimar el estado real del sistema.

Corregir únicamente si es necesario:

- `README.md`
- `ARCHITECTURE.md`
- documentos en `docs/arquitectura/`

La documentación debe diferenciar claramente:

- Definido en DRS.
- Confirmado en código.
- Implementado parcialmente.
- Pendiente de desarrollo.
- Pendiente de verificar.
- No confirmado en el estado actual.

No modificar el DRS formal.

---

### 4. Matriz breve de brechas P0

Generar o actualizar una matriz breve de brechas críticas detectadas, si el equipo lo autoriza.

La matriz debe incluir:

- Brecha.
- RF/RN/RNF relacionado.
- Evidencia.
- Riesgo.
- Estado.
- Sprint sugerido.

Esta matriz puede quedar en documentación técnica si el equipo lo autoriza.

---

## Fuera de alcance

No implementar en Sprint 7.5A:

- Sprint 8 completo.
- Cypress.
- Rediseño visual.
- Sprint 7.5B.
- RF-18 reentrenamiento ML.
- RF-19 semáforo de completitud.
- Fast Test completo.
- Importación Excel/CSV completa.
- Derivación directivo → psicólogo completa.
- Comunicación con familia.
- Relación docente-aula.
- Relación directivo-sede.
- Nuevas migraciones.
- Nuevas tablas.
- Nuevos modelos.
- Nuevas dependencias.
- Cambios en Docker.
- Cambios en ML Service.
- Rediseño UI.
- Nuevos reportes avanzados.
- CSV.
- Nuevos módulos no definidos.
- Refactor masivo de backend.
- Cambios amplios de arquitectura.
- Cambios en contratos API existentes salvo corrección mínima justificada.

---

## Actividades

1. Ejecutar inspección de código real contra la auditoría previa.
2. Confirmar uso actual de `spatie/activitylog`.
3. Identificar modelos, controladores o servicios donde deben registrarse acciones críticas.
4. Implementar registros de actividad solo donde sea seguro y trazable.
5. Revisar rutas críticas y permisos existentes.
6. Ajustar permisos solo si existe evidencia clara y necesidad real.
7. Agregar o ajustar pruebas backend relacionadas con auditoría/permisos.
8. Revisar documentación técnica para evitar afirmaciones no confirmadas.
9. Reportar brechas que no deben resolverse en este sprint.
10. Mantener el alcance limitado para no convertir Sprint 7.5A en Sprint 8 completo.

---

## Dependencias de entrada

- Sprint 7B implementado parcialmente.
- Auditoría DRS vs código real completada.
- Dashboard funcional.
- Exportación PDF funcional.
- Backend con tests pasando antes de modificar.
- Frontend funcional antes de modificar.
- Evidencia de brechas P0 detectadas.

---

## Dependencias de salida

Habilita Sprint 8 con mejor base para:

- Seguridad.
- Roles.
- Auditoría.
- Matriz rol–permiso–endpoint.
- Validación 401/403.
- Revisión de acceso por permisos.

---

## Criterios de aceptación

- Las acciones críticas seleccionadas registran actividad en `activity_log` o quedan justificadas como pendientes.
- Las rutas críticas revisadas mantienen protección backend.
- No se crean migraciones nuevas.
- No se agregan dependencias.
- No se inventan roles, permisos ni relaciones.
- La documentación técnica no afirma funcionalidades no confirmadas.
- Las pruebas backend siguen pasando.
- Las nuevas pruebas de auditoría/permisos pasan si se implementan.
- Las brechas no resueltas quedan explícitamente clasificadas.
- No se rompe login, dashboard, estudiantes, riesgo, alertas, intervenciones ni exportación PDF.

---

## Entregables

- Registro real de actividad en acciones críticas posibles.
- Ajustes mínimos de permisos si corresponden.
- Pruebas backend asociadas.
- Documentación técnica corregida, si aplica.
- Matriz breve de brechas P0, si el equipo la autoriza.
- Reporte de lo confirmado, parcial y pendiente.

---

## Pruebas asociadas

### Pruebas automatizadas

Ejecutar después de implementar:

```powershell
docker compose exec app-backend php artisan test
```

Si se crean pruebas específicas:

```powershell
docker compose exec app-backend php artisan test --filter=ActivityLog
docker compose exec app-backend php artisan test --filter=Permission
```
También mantener:


```powershell
docker compose exec app-backend php artisan test --filter=Dashboard
```

### Pruebas manuales

Validar manualmente:

- Crear estudiante y verificar registro en `activity_log`.
- Editar estudiante y verificar registro en `activity_log`.
- Registrar nota y verificar trazabilidad si aplica.
- Registrar asistencia y verificar trazabilidad si aplica.
- Guardar variables socioeconómicas y verificar trazabilidad si aplica.
- Procesar riesgo y verificar trazabilidad si aplica.
- Generar alerta y verificar trazabilidad si aplica.
- Registrar intervención y verificar trazabilidad si aplica.
- Cerrar alerta y verificar trazabilidad si aplica.
- Exportar PDF y verificar trazabilidad si aplica.
- Probar usuario sin permiso en rutas críticas.
- Confirmar que no se rompió login.
- Confirmar que no se rompió dashboard.
- Confirmar que no se rompió Exportar PDF.
- Confirmar que no se rompió estudiantes.
- Confirmar que no se rompió alertas/intervenciones.

---

## Criterios de validación

- El sistema queda mejor preparado para Sprint 8.
- La trazabilidad deja de ser solo dependencia instalada y pasa a tener uso real en acciones críticas.
- Las brechas no resueltas quedan explícitamente clasificadas.
- No se amplía el alcance del prototipo sin autorización.
- El backend conserva estabilidad.
- La documentación técnica queda más honesta frente al código real.
- El equipo puede defender qué está implementado y qué queda pendiente.

---

## Riesgos

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Convertir 7.5A en Sprint 8 completo | Alto | Limitarse a trazabilidad y revisión mínima |
| Romper flujos ya validados | Alto | Ejecutar suite completa backend |
| Registrar `activity_log` de forma inconsistente | Medio | Usar patrón real de Spatie |
| Crear permisos innecesarios | Medio | No crear sin evidencia clara |
| Documentación contradice código | Alto | Corregir claims técnicos |
| Faltan relaciones docente-aula/directivo-sede | Alto | Marcar pendiente, no inventar |
| Falta cobertura de tests | Medio | Crear pruebas solo para cambios reales |

---

## Reglas para Cursor

- Primero hacer **FASE 0 — Hallazgos**.
- No implementar sin confirmar si hay decisiones importantes.
- No ejecutar comandos.
- No crear migraciones.
- No instalar dependencias.
- No tocar frontend salvo necesidad mínima.
- No modificar DRS formal.
- No inventar roles.
- No inventar permisos.
- No inventar relaciones.
- No inventar endpoints.
- No crear auditoría paralela.
- No marcar como confirmado algo que no esté en código.
- No convertir Sprint 7.5A en Sprint 8.
- No implementar RF-18.
- No implementar RF-19.
- No implementar Fast Test.
- No implementar comunicación familiar.
- No implementar derivación directivo.
- No implementar relación docente-aula.
- No implementar relación directivo-sede.

Diferenciar siempre:

- Definido en DRS.
- Confirmado en código.
- Implementado parcialmente.
- Pendiente de desarrollo.
- Pendiente de verificar.
- No confirmado en el estado actual.