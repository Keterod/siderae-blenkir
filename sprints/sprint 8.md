# Sprint 8: Seguridad, roles, auditoría y control de accesos

## Objetivo
Cerrar el control de acceso del sistema mediante defensa en profundidad: no basta ocultar o deshabilitar acciones en el frontend; el backend debe rechazar llamadas no autorizadas y las pruebas deben verificarlo. Incorporar revisión documentada de permisos, matriz rol–permiso–pantalla–acción–endpoint y, si existe en el proyecto, revisiones ligadas a `spatie/laravel-activitylog` (solo documentación/comprobación dentro del alcance ya implementado; **sin inventar nuevos módulos de auditoría**).

## Duración estimada
1 semana

## Alcance
- Formalizar y revisar contra código la **matriz**: rol → permiso → pantalla → acción → endpoint (o ruta API).
- Frontend: ocultar o deshabilitar botones y entradas de menú según permisos del usuario autenticado (`/api/me` o equivalente), con mensajes claros cuando la acción exista pero no esté permitida.
- Backend: proteger rutas con `auth:sanctum` y mecanismos ya usados en el proyecto (`permission:*`, policies o middleware) de forma que un usuario sin autorización **no pueda ejecutar la operación aunque llame directamente la API**.
- Validar contestaciones **`401`** (no autenticado) y **`403`** (autenticado sin permiso) según convenciones actuales del proyecto.
- Revisar registros de actividad/auditoría **si ya está configurado `spatie/laravel-activitylog`** ; documentar alcance observado (**pendiente de confirmar** cobertura real en código).
- Documento breve de decisiones de seguridad para defensa académica (nivel prototipo, no certificación).

## Actividades
1. Elaborar o actualizar la matriz rol–permiso–pantalla–acción–endpoint con los roles base del sistema (p. ej. administrador, docente, coordinador académico, psicólogo/tutor, directivo) según definición vigente del proyecto (**pendiente de confirmar** denominaciones exactas y permisos en seeders).
2. Auditoría de rutas Laravel: todas las rutas sensibles revisadas tienen middleware de auth y restricción de permiso donde corresponda.
3. Alineación frontend: cada acción sensible refleja el permiso efectivo (ocultar, deshabilitar con tooltip o mensaje, o flujo pendiente sólo si el producto así lo definiera previamente).
4. Implementar o completar Laravel Feature Tests / PHPUnit que aserten **`401`** o **`403`** en escenarios típicos (sin token, token inválido, usuario sin rol, usuario sin permiso concreto) para endpoints prioritarios (**pendiente de confirmar lista final** por matriz).
5. Si existe `activitylog`: verificar escenarios ya instrumentados según proyecto; documentar hallazgos; **no expandir alcance más allá de lo necesario para trazabilidad académica** sin backlog explícito.
6. Completar redacción del documento de decisiones de seguridad (cookies/sesión Sanctum, RBAC Spatie, no exposición innecesaria de datos sensibles en respuestas de error públicas).

## Dependencias de entrada
Sprint 7B completado.

## Dependencias de salida
Habilita Sprint 9.

## Criterios de aceptación
- Para las rutas marcadas como críticas en la matriz, un usuario sin permiso recibe **`403`** (o **`401`** si no autenticado) desde el backend, no sólo una restricción visual.
- El frontend es coherente con la política acordada (ocultar vs deshabilitar con mensaje) según tabla de equivalencias definida por el equipo.
- Existen casos automatizados (PHPUnit Feature) que demuestran acceso prohibido en al menos los endpoints clave (**pendiente de confirmar lista** tras priorización).
- Decisiones principales RBAC y sesión quedan documentadas para el cierre en Sprint 10.

## Entregables
- Matriz de control de accesos actualizada y coherente con despliegue actual.
- Endpoints y UI revisados según la matriz.
- Pruebas backend que verifican **401/403** en casos acordados.
- Documento breve de seguridad (prototipo académico).

## Pruebas asociadas

### Pruebas manuales
- Por cada rol de prueba, intentar ejecutar desde la UI una acción no permitida y confirmar rechazo desde backend (mensaje/registro esperado según proyecto).
- Con herramienta tipo cliente REST o navegador, invocar sin permiso los endpoints marcados como críticos y verificar código `401` o `403` según convenga.

### Pruebas automatizadas
- **Laravel Feature Tests / PHPUnit:** casos explícitos de **403** para usuario sin permiso y **401** donde aplique usuario no autenticado contra endpoints seleccionados.
- **Cypress (recomendado cuando interfaces estén alineadas a Sprint 7B):** verificar navegación y visibilidad de controles por rol (elementos ocultos, rutas prohibidas si el front redirige o muestra estado acorde). Consolidación amplia en Sprint 9.

## Criterios de validación
- La seguridad no depende sólo del frontend: existe evidencia de bloqueo en servidor y de pruebas automatizadas.
- La matriz es trazable y utilizable como insumo para documentación final (Sprint 10).
- Cualquier limitación (p. ej. activity log parcial queda sólo donde el código ya soporte (**pendiente de confirmar**) ) está registrada honestamente para el equipo y el informe final.
