# Sprint 7B: Pantallas completas y navegación funcional según mockups

## Objetivo
Completar la presentación y la navegación de las pantallas principales alineadas a los 12 mockups y a la guía UI, asegurando que cada control visible tenga destino o estado controlado (acción real, permiso, deshabilitado con mensaje o pendiente explícito), sin reimplementar la lógica de reportes ni exportación ya entregada en Sprint 6B.

## Duración estimada
1 a 2 semanas

## Alcance
- Alinear visual y de navegación las pantallas correspondientes a los mockups numerados en `docs/ui/mockups/`:
  - Login
  - Dashboard
  - Listado de estudiantes
  - Registro/edición de estudiante
  - Perfil del estudiante
  - Registro de notas
  - Registro de asistencia
  - Variables socioeconómicas
  - Riesgo académico
  - Listado de alertas
  - Detalle de alerta
  - Registro de intervención
- **Reportes y exportación:** solo **acceso visual y navegación coherente** hacia las funcionalidades ya implementadas en **Sprint 6B** (rutas menú, página o sección de acceso, labels, estados vacíos, coherencia con guía). **No** reimplementar endpoints, generación de archivos ni reglas de export en este sprint.
- Política ante funcionalidad no disponible: ocultar por permiso, deshabilitar con mensaje claro, o mostrar estado pendiente controlado; **no** dejar botones sin acción efectiva para el usuario.

## Actividades
1. Mapear cada ruta del frontend a su mockup `.md` / `.png` y a la guía `docs/ui/mockups/guia-ui-siderae.md` (jerarquía, KPIs, tablas, formularios).
2. Ajustar vistas para que layout, títulos, descripciones breves y bloques de contenido coincidan con la intención documentada en cada mockup, reutilizando el sistema visual de Sprint 7A.
3. Garantizar navegación Sidebar/Header ↔ pantallas sin enlaces rotos; ítem de menú activo coherente con la guía (p. ej. resaltado institucional).
4. Implementar flujos de clic que invoquen las acciones ya existentes (`Guardar`, `Procesar riesgo`, `Registrar intervención`, `Cerrar alerta`, etc.) o redirijan donde corresponda; manejar estados de carga y errores ya expuestos por la API.
5. **Reportes:** integrar únicamente la **entrada UX** al módulo de reportes/export existente post-6B (textos, disposición, botones que llaman a lo ya desarrollado en 6B); no duplicar lógica ni nuevos tipos de exportación aquí.
6. Donde aplique, favorizar elementos identificables de forma estable (atributos `data-*`, roles ARIA u otras convenciones acordadas por el equipo) para facilitar Cypress en Sprint 9, sin ejecutar Cypress en este sprint como obligación.
7. Revisión cruzada: lista de todos los botones y enlaces visibles por pantalla contra acción o política acordada (ocultar / deshabilitar / pendiente).

## Dependencias de entrada
Sprint 7A completado.

## Dependencias de salida
Habilita Sprint 8.

## Criterios de aceptación
- Las pantallas listadas reflejan el mockup y la guía a nivel institucional; la navegación entre ellas funciona sin enlaces muertos dentro del alcance del prototipo.
- La funcionalidad de reportes/exportación sigue siendo la definida en **Sprint 6B**; 7B no añade nueva lógica de negocio de reporteo.
- Cada control prominente ejecuta una acción conocida o muestra política explícita (permiso/mensaje/pendiente).
- Las vistas quedan listas para pruebas E2E con Cypress (selectores o convenciones acordadas) en sprints siguientes.

## Entregables
- Pantallas ajustadas según mockups 01–12 y guía UI, con navegación operativa dentro del alcance actual del sistema.
- Acceso UX al módulo de reportes alineado a lo implementado en Sprint 6B.
- Lista o checklist interna de enlaces/controles revisados contra “sin botones muertos”.

## Pruebas asociadas

### Pruebas manuales
- Recorrer en orden hábil: Login → Dashboard → estudiantes (lista, alta/edición, perfil con notas, asistencia, variables, riesgo) → alertas (lista, detalle, intervención, cierre cuando aplique datos) → acceso visual a Reportes/export según rol.
- Verificar comportamiento de cada botón principal mencionado en los mockups (navegar, guardar, procesar, alertas).
- Confirmar que el acceso a reportes invoca únicamente comportamiento ya disponible tras Sprint 6B.
- Confirmar mensajes claros cuando una acción no está permitida para el usuario o no hay datos.

### Pruebas automatizadas
- **Backend:** mantener ejecutables Laravel Feature Tests / PHPUnit relacionados con los flujos anteriores; corregir roturas sólo si el contrato API no debe cambiar.
- **Cypress (recomendado cuando la UI esté estable en este sprint):** preparación o primera versión de pruebas sobre flujos críticos: login correcto e incorrecto, navegación sidebar/header, listado de estudiantes, creación/edición, perfil, notas, asistencia, variables, procesamiento de riesgo, alertas, detalle, intervención, cierre de alerta, dashboard, acceso a reportes/export según permiso. La ejecución y el alcance exacto pueden completarse sobre todo en Sprint 9.

## Criterios de validación
- Cobertura documental mockup ↔ pantalla revisada sin contradicción grave con la guía UI.
- Reportes/export sin duplicación lógica respecto a Sprint 6B.
- El conjunto está preparado para reforzar permisos y pruebas 401/403 en Sprint 8 y para regresión E2E en Sprint 9.
