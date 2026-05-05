# Sprint 10: Documentación final y cierre de calidad

## Objetivo
Consolidar la documentación del proyecto SIDERAE-Blenkir para defensa académica y cierre de calidad del prototipo: entregables legibles para director, asesor y tribunal, incorporando evidencias de prueba del Sprint 9 y usando normas ISO **solo como referencia orientativa** de calidad (**no** compromiso de certificación ISO formal ni auditoría externa).

## Duración estimada
1 semana

## Alcance
- README final actualizado orientado a levantamiento con Docker stack ya definido por el proyecto.
- Manual de usuario por rol (o secciones por rol en un único manual), alineado a las pantallas y permisos reales del prototipo.
- Manual técnico breve (stack, servicios, cómo correr pruebas básicas, variables de entorno **sin** secretos reales).
- Descripción de arquitectura (diagrama lógico y texto: React, Laravel, MySQL, Flask ML, Docker).
- **Matriz trazabilidad:** requerimiento funcional (RF) → sprint → caso o prueba (según documentos existentes en `docs/pruebas/` y Formato 06 **pendiente de confirmar** ubicación en el repositorio).
- Compilación de **evidencias de pruebas** generadas en Sprint 9 (capturas, reportes PHPUnit, salidas Cypress si existen).
- Capturas finales de la interfaz representativa del estado del producto.
- Sección de **limitaciones del sistema** (alcance académico, datos de prueba, exclusiones ya reconocidas en plan de pruebas u otros documentos).
- Apartado de **criterios de calidad orientativos** inspirados en:
  - **ISO/IEC 25000 / 25010** — calidad del producto software (usabilidad, seguridad funcional, mantenibilidad a nivel descriptivo del prototipo).
  - **ISO/IEC 27000** (familia) — orientación a buenas prácticas de seguridad de la información en el contexto del prototipo (sesión, roles, no divulgación de credenciales en documentos).
  - **ISO 9001** — idea de documentación, trazabilidad y mejora continua del **proceso de desarrollo y prueba** del proyecto académico.

## Actividades
1. Redactar o actualizar README con pasos mínimos reproducibles y limitaciones.
2. Elaborar manual de usuario con lenguaje claro (flujos principales: login, estudiantes, datos, riesgo, alertas, dashboard, reportes según permisos).
3. Redactar manual técnico breve y descripción de arquitectura alineada al despliegue actual (**sin** inventar componentes no existentes).
4. Construir matriz RF–Sprint–Test con las fuentes disponibles; marcar celdas **pendiente de confirmar** donde falte el Formato 06 digital o trazabilidad incompleta.
5. Incorporar evidencias del Sprint 9 en anexo o carpeta `docs/` según convención del equipo.
6. Seleccionar y anexar capturas finales (interfaz post–Sprint 7B/8).
7. Redactar limitaciones honestas (datos sintéticos, exclusiones de carga, etc., según plan de pruebas y realidad del prototipo).
8. Redactar apartado “Calidad y estándares (referencia no certificada)” explicando que ISO se usa como **marco conceptual**, no como certificación obtenida.

## Dependencias de entrada
Sprint 9 completado.

## Dependencias de salida
Cierre de documentación del hito académico acordado con asesoría (**pendiente de confirmar** fecha de sustentación o entrega formal).

## Criterios de aceptación
- Un lector externo puede entender qué hace el sistema, cómo ejecutarlo en entorno de prototipo y cuáles son sus límites.
- La matriz RF–Sprint–Test refleja el estado documentado; las lagunas están identificadas explícitamente.
- Las evidencias de prueba del Sprint 9 están referenciadas o adjuntas.
- **No** se afirma certificación ISO ni cumplimiento normativo certificado; sólo alineación orientativa y argumentación académica.

## Entregables
- README final.
- Manual de usuario.
- Manual técnico breve + arquitectura.
- Matriz RF–Sprint–Test (formato acordado por el equipo: hoja, Markdown o documento).
- Paquete de evidencias y capturas.
- Documento o sección de cierre de calidad con ISO como referencia orientativa y limitaciones explícitas.

## Pruebas asociadas

### Pruebas manuales
- Revisión por par académico o asesor: completitud, claridad, ausencia de datos sensibles reales en ejemplos.
- Verificación de que los pasos del README permiten reproducir el arranque en el entorno declarado (**pendiente de confirmar** máquina de referencia).

### Pruebas automatizadas
- No se definen nuevas suites en este sprint documental; se **referencian** los resultados ya obtenidos en Sprint 9:
  - **Laravel PHPUnit / Feature Tests** según informe de ejecución.
  - **Cypress** según informe de ejecución, si aplica.

## Criterios de validación
- El paquete documental es coherente con el software entregado y con las pruebas registradas.
- No hay promesas de certificación ISO ni de funcionalidades no implementadas.
- El cierre permite una defensa académica fundamentada en evidencia y trazabilidad explícita.
