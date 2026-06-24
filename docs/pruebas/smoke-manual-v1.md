# Smoke manual V1 — SIDERAE-Blenkir

## Ambiente

- Docker local.
- Frontend: `http://localhost:5173`.
- Backend: `http://localhost:8000`.
- Usuario probado: administrador.
- Sede operativa V1: **Chilca**.

## Resultado general

Smoke manual aprobado con observaciones menores.

## Módulos probados

| Módulo | Resultado | Observación |
| ------ | --------- | ----------- |
| Login y navegación base | Aprobado | Sesión Sanctum funciona; menú lateral renderiza ítems según permisos. |
| Perfil estudiante | Aprobado | Ficha visible, datos generales, pestañas de notas/asistencia/variables. |
| NC-11 — Procesamiento de riesgo desde UI | Aprobado | Botón **Procesar/Actualizar riesgo** visible para usuario autorizado; llama `POST /api/estudiantes/{id}/procesar-riesgo`; refresca historial RF-20 y semáforo RF-19. |
| RF-14 — Dashboard institucional | Aprobado | Módulo separado del dashboard legacy; filtros, tarjetas y tabla por grado/sección funcionan; solo Chilca. |
| RF-16 — Reportes de riesgo | Aprobado | Listado filtrable paginado; solo Chilca; sin PDF/exportación nueva. |
| RF-11 — Seguimiento psicólogo/tutor | Aprobado | Menú visible, filtros funcionan, tabla muestra estudiantes con señales de seguimiento, semáforo de completitud visible; sin datos clínicos/médicos. |
| RF-19 — Semáforo de completitud | Aprobado | Bloque visible en perfil estudiante; colores verde/amarillo/rojo con mensaje y razones. |
| RF-20 — Historial de riesgo | Aprobado | Tabla evolutiva visible en perfil estudiante; ordenado del más reciente al más antiguo. |

## Observaciones

1. **Inicial no tiene cálculo de riesgo académico automático en V1.** Es comportamiento esperado: RF-06 aplica a niveles con estructura curricular evaluable mediante notas/asistencia/cursos.
2. **Primaria muestra botón “Procesar riesgo”** para usuario autorizado (`procesar_riesgo`).
3. **Dashboard legacy conserva exportación PDF** como antecedente existente; no corresponde al Dashboard institucional RF-14.
4. **Dashboard institucional RF-14** se trata como módulo separado.
5. **No se validó Cypress** en esta fase.
6. **Lint global** mantiene 88 problemas preexistentes; componentes nuevos V1 no agregan errores.

## Hallazgos

| Código | Módulo | Severidad | Descripción | Acción |
| ------ | ------ | --------- | ----------- | ------ |
| OBS-01 | Inicial | Baja | Riesgo no disponible para el nivel Inicial | Comportamiento esperado V1 |
| OBS-02 | Dashboard legacy | Baja | Botón **Exportar PDF** permanece en dashboard legacy | No corresponde a RF-14 institucional; es funcionalidad existente |

## Limitaciones vigentes confirmadas

- Sede operativa V1: **Chilca**; sin selector de sede.
- RF-06 no procesa riesgo para nivel **Inicial** en V1.
- No se afirma ML real entrenado; el servicio Flask es determinístico.
- No se afirma certificación ISO.
- Cypress global sigue pendiente.

## Resultado final

**Smoke manual V1 aprobado con observaciones menores.**

---

*Fase QA-V1A.1 — 2026-06-24.*
