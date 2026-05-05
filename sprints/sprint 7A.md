# Sprint 7A: Rediseño UI/UX global

## Objetivo
Establecer una capa visual institucional coherente en todo el frontend según la guía oficial de mockups, sin alterar la lógica de negocio ni los contratos de las APIs existentes, de modo que Sprint 7B pueda completar pantallas y navegación sobre un sistema de diseño estable.

## Duración estimada
1 a 2 semanas

## Alcance
- Layout general (fondo, rejilla, espaciado) alineado a `docs/ui/mockups/guia-ui-siderae.md`.
- Sidebar y header reutilizables.
- Paleta de colores oficial, variables CSS sugeridas en la guía y componentes base (tarjetas, tablas, formularios, botones, badges).
- Estados visuales (riesgo, alerta, formulario) y mensajes de error / éxito / advertencia.
- Estados vacíos consistentes.
- Responsive básico para lectura y navegación principal.
- Cambios **exclusivamente** en presentación y organización visual del frontend; **no** cambios de lógica backend.

## Actividades
1. Aplicar paleta, tipografía jerárquica y variables CSS documentadas en la guía (`--primary`, `--secondary`, `--background`, `--surface`, etc.).
2. Implementar layout con sidebar persistente (nombre SIDERAE, subtítulo institucional tipo `Blenkir Analytics`, navegación principal según guía).
3. Implementar header superior (espacio para búsqueda global cuando aplique, notificaciones, ayuda/información, usuario y cierre de sesión según patrones de la guía).
4. Unificar estilos de tarjetas KPI, tablas, formularios, botones (principal, secundario, cancelar) y badges de riesgo / alerta según guía.
5. Normalizar mensajes de validación, errores de red o negocio ya expuestos por el backend, y estados vacíos (sin datos / sin resultados).
6. Ajustar responsive básico (anchos intermedios y móvil de consulta) sin rediseñar dominio.
7. Documentar en el repositorio los componentes base o tokens reutilizados (por ejemplo convención de clases o tema) para uso en Sprint 7B.
8. Coordinar con Sprint 7B: dejar contratos de rutas y puntos de montaje de pantallas listos para integración visual por mockup.

## Dependencias de entrada
Sprint 6B completado.

## Dependencias de salida
Habilita Sprint 7B.

## Criterios de aceptación
- La aplicación muestra un aspecto institucional unificado acorde a la guía UI; no se introducen colores ni patrones contradictorios fuera de la paleta definida.
- Sidebar, header y contenedor principal son coherentes en todas las rutas ya existentes tocadas en este sprint.
- Formularios y tablas comparten el mismo lenguaje visual (bordes, botones, badges, estados vacíos).
- No se modifica el comportamiento funcional de endpoints ni reglas de negocio en servidor; cualquier cambio de consumo de API es puramente de presentación.
- El sistema visual permite a Sprint 7B alinear cada pantalla a su mockup `.md` / `.png` sin rehacer el layout global.

## Entregables
- Tema/layout global y componentes visuales base aplicados al frontend existente.
- Referencia viva a `docs/ui/mockups/guia-ui-siderae.md` como fuente de diseño (actualizaciones menores de documentación en `docs/` solo si el equipo lo acuerda).
- Guía breve interna de uso de componentes o tokens para desarrollo de Sprint 7B (archivo o sección en README del front, según convenga al proyecto).

## Pruebas asociadas

### Pruebas manuales
- Recorrer rutas principales ya existentes tras el rediseño y verificar legibilidad, contraste básico y coherencia sidebar/header.
- Verificar que botones y enlaces visibles siguen llevando a las mismas rutas y acciones que antes del rediseño.
- Comprobar estados vacíos y mensajes de error/éxito en al menos un formulario y una tabla representativos.

### Pruebas automatizadas
- Mantener y ejecutar Laravel Feature Tests / PHPUnit existentes relacionados con vistas no aplica directamente al front; el backend no debe romperse (regresión rápida donde el equipo lo defina).
- **Cypress (futuro):** a partir de Sprint 7B se recomienda automatizar flujos sobre la UI ya estabilizada; en 7A no se exige ejecutar ni configurar Cypress si el foco es únicamente sistema visual, salvo humo manual acordado por el equipo.

## Criterios de validación
- La interfaz refleja los criterios visuales de la guía (`docs/ui/mockups/guia-ui-siderae.md`) y prepara el terreno para mockups 01–12 en Sprint 7B.
- No hay regresiones funcionales atribuibles a cambios de solo presentación verificadas en pruebas backend y recorrido manual principal.
- Las decisiones de diseño quedan suficientemente documentadas para continuidad en Sprint 7B.
