# Sprint 7.5B: Corrección visual final de interfaces

## Objetivo

Corregir y consolidar visualmente las interfaces principales del sistema SIDERAE-Blenkir para que el prototipo sea defendible frente a los mockups 01–12 y la guía UI, sin agregar nuevas funcionalidades de backend.

Este sprint busca cerrar las brechas visuales pendientes de Sprint 7B y dejar una interfaz más coherente, navegable y presentable antes de continuar con Sprint 8.

---

## Duración estimada

1 semana

---

## Contexto

Sprint 7A estableció una base visual global:

- Layout general.
- Sidebar.
- Header.
- Tokens visuales.
- Componentes UI base.
- Estilos comunes.

Sprint 7B mejoró navegación e interfaces, pero algunas pantallas quedaron parcialmente alineadas o visualmente insuficientes.

Sprint 7.5A corrigió brechas funcionales P0 antes de seguridad, principalmente relacionadas con trazabilidad, auditoría, permisos mínimos y coherencia técnica.

Sprint 7.5B se enfoca exclusivamente en corregir la presentación visual y navegación frontend, sin tocar backend ni contratos API.

---

## Pantallas de referencia

Las pantallas esperadas del prototipo son:

1. Login.
2. Dashboard.
3. Listado de estudiantes.
4. Registro/edición de estudiante.
5. Perfil del estudiante.
6. Registro de notas.
7. Registro de asistencia.
8. Variables socioeconómicas.
9. Riesgo académico.
10. Listado de alertas.
11. Detalle de alerta.
12. Registro de intervención.

Los mockups son referencia visual, no fuente de datos reales.

No se deben copiar nombres, fechas, cantidades, porcentajes ni valores de ejemplo de los mockups como datos reales.

---

## Alcance

### 1. Login

Corregir visualmente la pantalla de inicio de sesión.

Debe mantenerse:

- Autenticación existente.
- `AuthContext`.
- Endpoint de login.
- Endpoint de logout.
- Manejo CSRF/Sanctum existente.
- Mensajes de error reales.

Se permite:

- Mejorar jerarquía visual.
- Mejorar tarjeta de login.
- Mejorar espaciado.
- Mejorar textos orientativos.
- Usar paleta institucional.
- Usar componentes UI existentes.

No se permite:

- Cambiar endpoints.
- Cambiar lógica de autenticación.
- Cambiar credenciales reales.
- Agregar dependencias.

---

### 2. Dashboard

Corregir visualmente el dashboard sin romper lo implementado en Sprint 6A/6B.

Debe mantenerse:

- `getDashboard`.
- `exportDashboardPdf`.
- `GET /api/dashboard`.
- `GET /api/dashboard/export`.
- Filtros por campos reales.
- Porcentajes de riesgo.
- Porcentajes de alertas.
- Últimos riesgos.
- Botón Exportar PDF.
- Estados de carga, error y vacío.

Se permite:

- Mejorar tarjetas KPI.
- Mejorar distribución visual de filtros.
- Mejorar tabla de últimos riesgos.
- Mejorar badges.
- Mejorar mensajes.
- Mejorar visual de distribución de riesgo con HTML/CSS ya existente.

No se permite:

- Cambiar contratos API.
- Cambiar `api.js` salvo necesidad mínima justificada.
- Cambiar lógica de exportación.
- Agregar CSV.
- Agregar nuevas librerías de gráficos.
- Crear nuevos endpoints.

---

### 3. Estudiantes

Corregir visualmente:

- Listado de estudiantes.
- Formulario de registro.
- Formulario de edición.
- Perfil del estudiante.

Prioridad alta:

- Formulario de estudiante ordenado.
- Secciones claras:
  - Información básica.
  - Información académica.
- Inputs y selects alineados.
- Botones consistentes.
- Estados vacíos.
- Acciones visibles:
  - Nuevo estudiante.
  - Editar.
  - Ver perfil.
  - Guardar.
  - Cancelar.

No se permite:

- Agregar campos no existentes.
- Cambiar payloads.
- Cambiar endpoints.
- Inventar filtros.
- Inventar paginación.
- Cambiar validaciones backend.

---

### 4. Datos académicos del estudiante

Corregir visualmente secciones o pantallas reales de:

- Notas.
- Asistencia.
- Variables socioeconómicas.
- Riesgo académico.

Debe mantenerse:

- Endpoints existentes.
- Payloads actuales.
- Acciones reales:
  - Registrar nota.
  - Registrar asistencia.
  - Guardar variables socioeconómicas.
  - Procesar riesgo.

Se permite:

- Organizar en cards, secciones o pestañas visuales si ya existe estructura compatible.
- Mejorar estados vacíos.
- Mejorar mensajes de éxito/error.
- Mejorar botones.
- Mejorar jerarquía visual del perfil.

No se permite:

- Implementar RF-19.
- Inventar semáforo de completitud.
- Inventar historial avanzado.
- Agregar campos no confirmados.
- Crear rutas falsas.
- Crear endpoints nuevos.

---

### 5. Alertas e intervenciones

Corregir visualmente:

- Listado de alertas.
- Detalle de alerta.
- Registro de intervención.
- Cierre de alerta.

Debe mantenerse el flujo real existente:

- Ver alerta.
- Registrar intervención.
- Cerrar alerta.
- Estados de alerta.
- Validaciones existentes.
- Mensajes reales.

Se permite:

- Mejorar tablas/listas.
- Mejorar tarjetas de detalle.
- Mejorar badges de estado.
- Mejorar formulario de intervención.
- Mejorar bloque de cierre.
- Mejorar estados vacíos.

No se permite:

- Cambiar endpoints.
- Cambiar payloads.
- Cambiar estados de alerta.
- Crear módulo independiente falso de intervenciones.
- Implementar derivación directivo.
- Implementar atención psicológica nueva.
- Implementar comunicación familiar.

---

### 6. Vistas controladas

Mantener vistas controladas para:

- Reportes.
- Intervenciones.
- Configuración.

Estas vistas deben explicar claramente el estado real:

- Reportes: el PDF disponible se genera desde Dashboard.
- Intervenciones: se registran desde el detalle de una alerta.
- Configuración: pendiente de desarrollo.

No deben tener botones muertos.

Se permite:

- Mostrar mensaje informativo.
- Mostrar botón real para ir a Dashboard.
- Mostrar botón real para ir a Alertas.
- Mostrar estado “Pendiente de desarrollo”.

No se permite:

- Crear nuevo módulo de reportes.
- Crear nueva exportación.
- Crear CSV.
- Crear backend de configuración.
- Crear endpoints nuevos.

---

## Fuera de alcance

No implementar en Sprint 7.5B:

- Backend.
- Nuevos endpoints.
- Nuevos permisos.
- Nuevos roles.
- Nuevas migraciones.
- Nuevas dependencias.
- Cambios en ML Service.
- Cambios en Docker.
- Sprint 8.
- Sprint 9.
- Cypress.
- RF-18.
- RF-19.
- Fast Test.
- Derivación directivo.
- Comunicación familiar.
- Relación docente-aula.
- Relación directivo-sede.
- Nueva exportación.
- CSV.
- Nueva lógica PDF.
- Activity log.
- Seguridad avanzada.
- Matriz rol–permiso–endpoint.
- Auditoría adicional.
- Refactor masivo.
- Cambios en documentación técnica salvo autorización explícita.

---

## Actividades

1. Revisar las 12 pantallas esperadas contra el frontend real.
2. Identificar cuáles son pantallas independientes y cuáles son secciones internas.
3. Corregir visualmente componentes existentes.
4. Usar componentes UI base creados en Sprint 7A.
5. Evitar duplicación de estilos.
6. Mantener navegación por vista activa si es la arquitectura actual.
7. Asegurar que no haya múltiples módulos abiertos al mismo tiempo.
8. Verificar botones y enlaces visibles.
9. Deshabilitar u ocultar controles sin funcionalidad real.
10. Dejar estados pendientes claros cuando algo no esté implementado.
11. Confirmar que no se rompe Dashboard ni Exportar PDF.
12. Confirmar que no se rompe login/logout.
13. Confirmar que no se rompe estudiantes.
14. Confirmar que no se rompe alertas/intervenciones.

---

## Dependencias de entrada

- Sprint 7A implementado.
- Sprint 7B implementado parcialmente.
- Sprint 7.5A completado o sin cambios pendientes que afecten UI.
- Dashboard funcional.
- Exportación PDF funcional.
- Login funcional.
- CRUD estudiantes funcional.
- Datos académicos funcionales.
- Riesgo funcional.
- Alertas/intervenciones funcionales.

---

## Dependencias de salida

Habilita:

- Sprint 8 con UI más estable para revisar permisos y seguridad.
- Sprint 9 con pantallas listas para pruebas manuales y futuras pruebas E2E.
- Sprint 10 con capturas más defendibles.

---

## Criterios de aceptación

- Las 12 pantallas o secciones equivalentes son identificables en la UI.
- No hay botones muertos evidentes.
- Las acciones reales siguen funcionando.
- Login/logout funcionan.
- Dashboard conserva filtros y Exportar PDF.
- Estudiantes conserva CRUD.
- Perfil conserva notas, asistencia, variables y riesgo si existen como secciones.
- Alertas conserva listado, detalle, intervención y cierre.
- Reportes, Intervenciones y Configuración quedan controlados.
- No se toca backend.
- No se cambian contratos API.
- No se agregan dependencias.
- El build frontend pasa.
- La suite backend sigue pasando como regresión.
- La UI no promete funcionalidades inexistentes.

---

## Entregables

- Interfaces visualmente corregidas.
- Navegación más clara.
- Formularios ordenados.
- Tablas consistentes.
- Tarjetas consistentes.
- Badges consistentes.
- Estados vacíos y pendientes controlados.
- Reporte de pantallas completas, parciales y pendientes.
- Confirmación de contratos preservados:
  - `getDashboard`.
  - `exportDashboardPdf`.
  - `GET /api/dashboard`.
  - `GET /api/dashboard/export`.
  - Login/logout.
  - Estudiantes.
  - Alertas/intervenciones.

---

## Pruebas asociadas

### Pruebas automatizadas

Frontend:

```powershell
docker compose exec app-frontend npm run build
```
Backend, para asegurar no regresión:

```powershell
docker compose exec app-backend php artisan test --filter=Dashboard
docker compose exec app-backend php artisan test
```


### Pruebas manuales

Validar:

- Login.
- Logout.
- Navegación sidebar/header.
- Dashboard.
- Filtros dashboard.
- Exportar PDF.
- Listado estudiantes.
- Crear estudiante.
- Editar estudiante.
- Ver perfil.
- Registrar nota.
- Registrar asistencia.
- Guardar variables socioeconómicas.
- Procesar riesgo.
- Listado alertas.
- Ver detalle alerta.
- Registrar intervención.
- Cerrar alerta.
- Reportes controlado.
- Intervenciones controlado.
- Configuración pendiente.
- Ausencia de botones muertos.
- Ausencia de datos falsos provenientes de mockups.
- Ausencia de funcionalidades prometidas que no existen.

---

## Criterios de validación

- El sistema se percibe como un prototipo institucional completo.
- Las pantallas están alineadas visualmente a la guía UI sin inventar datos.
- La UI no promete funcionalidades inexistentes.
- Las brechas restantes quedan documentadas como pendientes.
- El sistema queda listo para Sprint 8 y Sprint 9.
- Las capturas finales son más defendibles para Sprint 10.

---

## Riesgos

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Romper Exportar PDF | Alto | No tocar `exportDashboardPdf` ni contrato de descarga |
| Romper login/logout | Alto | No tocar `AuthContext` ni endpoints |
| Usar datos falsos de mockups | Alto | Consumir solo datos reales o estados vacíos |
| Crear botones muertos | Alto | Deshabilitar, ocultar o explicar pendiente |
| Convertir 7.5B en backend | Alto | No tocar backend |
| Cambiar contratos API | Alto | No tocar `api.js` salvo necesidad justificada |
| Prometer RF no implementados | Alto | Marcar como pendiente |
| Duplicar componentes/estilos | Medio | Reutilizar componentes UI existentes |
| Dejar formularios desordenados | Medio | Aplicar grid/cards/espaciado |
| Mezclar Sprint 8 o Sprint 9 | Alto | Mantener fuera de alcance seguridad y Cypress |

---

## Reglas para Cursor

- Primero hacer **FASE 0 — Hallazgos** si hay dudas.
- No ejecutar comandos.
- No tocar backend.
- No tocar `api.js` salvo necesidad mínima justificada.
- No cambiar:
  - `getDashboard`.
  - `exportDashboardPdf`.
  - `GET /api/dashboard`.
  - `GET /api/dashboard/export`.
- No romper Exportar PDF.
- No usar datos falsos de mockups.
- No instalar dependencias.
- No crear rutas falsas.
- No crear módulos falsos.
- No implementar Sprint 8.
- No implementar Sprint 9.
- No implementar Cypress.
- No implementar RF-18.
- No implementar RF-19.
- No crear permisos.
- No crear roles.
- No crear migraciones.
- No crear endpoints.
- No modificar DRS formal.
- No prometer funcionalidades inexistentes.

Diferenciar siempre:

- Definido en DRS.
- Confirmado en código.
- Implementado parcialmente.
- Pendiente de desarrollo.
- Pendiente de verificar.
- No confirmado en el estado actual.