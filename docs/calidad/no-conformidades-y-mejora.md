# No conformidades, brechas y mejora continua

Registro de **brechas documentales y técnicas** del prototipo SIDERAE-Blenkir V1, para defensa académica honesta. **No** sustituye un registro de no conformidades de un SGC ISO 9001 certificado.

Documento padre: [`alineacion-iso.md`](alineacion-iso.md).

---

## 1. Propósito

Centralizar brechas conocidas con fuente, impacto, prioridad y acción recomendada, alimentando la **mejora continua orientada** y el cierre académico V1 ([`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md)).

---

## 2. Criterios de clasificación

| Prioridad | Criterio |
|-----------|----------|
| **Crítica** | Impide uso seguro en producción o invalida afirmaciones del DRS sin aclaración |
| **Alta** | Requisito DRS importante sin equivalente V1; riesgo seguridad/documental alto |
| **Media** | Funcionalidad parcial; evidencia incompleta; deuda técnica documentada |
| **Baja** | Mejora deseable; entorno académico; impacto limitado en sustentación |

---

## 3. Registro de brechas

| ID | Brecha / no conformidad | Fuente | Impacto | Prioridad | Acción recomendada | Responsable sugerido | Estado |
|----|-------------------------|--------|---------|-----------|-------------------|---------------------|--------|
| NC-01 | DRS v1 PDF vs código V1; DRS v2 Markdown publicado | [`DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md) | PDF v1 puede inducir alcance incorrecto si no se lee v2 | Alta | Conversión PDF/Word formal desde v2; citar v2 en sustentación | Equipo documentación | Parcial — v2 publicado |
| NC-02 | RF-01 **SIAGIE fuera del alcance actual** | [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md), DRS v2.1 §RF-01 | Decisión de alcance documentada | Alta | Plantillas propias RF-32/RF-33 | Product owner académico | Documentada v2.1 |
| NC-03 | ML **determinístico** vs ensemble DRS v1 | [`limitaciones.md`](../limitaciones.md), DRS v2.1 §RF-06 | Expectativa ML avanzado no cumplida | Alta | RF-06 parcial; RF-18 planificado | Equipo ML / doc | Documentada v2.1 |
| NC-04 | RF-18 reentrenamiento ML **planificado** | DRS v2.1 | RF formal no implementado | Media | Implementar con dataset histórico | Equipo ML | Planificada |
| NC-05 | **Cypress** / E2E no existe | [`informe-pruebas.md`](../pruebas/informe-pruebas.md) §9 | Sin regresión UI automatizada | Media | Smoke manual por rol; Cypress opcional | QA académico | Abierta |
| NC-06 | Suite PHPUnit **OOM @ 128M** | [`hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md) | CI/local no verde completo | Media | `memory_limit=512M` documentado y en CI | DevOps académico | Abierta |
| NC-07 | **Seed oficial** de referencia pendiente | [`informe-pruebas.md`](../pruebas/informe-pruebas.md) §10 | Conteos demo inconsistentes | Media | Entorno referencia `migrate:fresh --seed` | Equipo backend | Abierta |
| NC-08 | **Activity log** parcial (RF-17) | [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) §13 | Trazabilidad incompleta | Media | Extender logging + UI si RF-17 exige | Backend | Abierta |
| NC-09 | `POST /register` **público** (guest) | [`README.md`](../../README.md), `RegistrationTest` | Riesgo en despliegue real | Alta (prod) / Baja (V1 local) | Deshabilitar antes producción | Backend | Abierta |
| NC-10 | Multi-sede **no activa** V1 vs posible DRS | [`AGENTS.md`](../../AGENTS.md), conteos Auquimarca Fase 1 | Interpretación alcance sedes | Media | DRS: Chilca operativa; Auquimarca histórico | Documentación | Documentada |
| NC-11 | UI riesgo **en pausa**; sin botón procesar riesgo | [`manual-usuario.md`](../manual-usuario.md), `EstudiantePerfilRiesgo.jsx` | RF-06/20 parcial en UX | Media | Alinear UI o documentar comando técnico | Frontend + doc | Abierta |
| NC-12 | **VSE retiradas** del flujo de riesgo (RF-05) | DRS v2.1 | No insumo obligatorio RF-06 | Media | Retirado en RF-06C (2026-06-23): VSE y Fast Test ya no son requisito; notas+asistencia mínimos; conducta opcional | Backend / Flask / docs / tests | **Cerrada V1** (RF-06C) |
| NC-13 | RF-10 **planificado**; RF-04 **V1 mínimo** (Fase 2E); RF-03/RF-12 **retirados**; RF-19 **implementado V1** | DRS v2.1 §2.3 | RF-10 sin API; RF-04 cerrado perfil; RF-19 V1 listo | Alta (RF-10) / Media (RF-04) | Backlog RF-10; brechas RF-04 documentadas | Documentación | RF-04 matizado v2.1; RF-19 cerrado V1 |
| NC-16 | ~~RF-04 reportes conductuales por implementar~~ | DRS v2.1 | Flujo registro conductual | Alta | API + UI + tests | Backend + Frontend | **Cerrada V1 mínimo** (Fase 2E) — smoke UI navegador pendiente |
| NC-17 | RF-10 escalamiento directivo crítico | DRS v2.1 | Solo casos extremos | Alta | API + UI + permisos | Backend | Abierta |
| NC-18 | RF-16 reportes de riesgo | DRS v2.1 | PDF dashboard parcial | Media | Zona reportes dedicada | Backend + doc | Abierta |
| NC-19 | RF-19 semáforo completitud | DRS v2.1 | Calidad datos riesgo | Media | Backend + frontend + docs + pruebas | Backend / Frontend | **Cerrada V1** (Fases 3B–3E); smoke manual navegador pendiente |
| NC-20 | RF-20 historial evolutivo | DRS v2.1 | Timeline por periodo | Media | UI perfil riesgo | Frontend | **Cerrada V1** (Fases 4B–4E); smoke manual navegador pendiente; Cypress global no ejecutado |
| NC-21 | RF-21–RF-35 documentados | DRS v2.1 §8.21 | Trazabilidad curricular | Media | Matriz actualizada | Documentación | Documentada v2.1 |
| NC-14 | Pruebas **401/403** no exhaustivas | [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) §12 | Riesgo seguridad no medido total | Media | Ampliar Feature tests | Backend | Abierta |
| NC-15 | **Sin auditoría externa** ni certificación ISO | [`limitaciones.md`](../limitaciones.md) §9 | No evaluación independiente | No aplica V1 | Declarar en sustentación | Equipo | Documentada |

---

## 4. Plan de mejora sugerido

| Acción | Prioridad | Fase sugerida | Evidencia esperada |
|--------|-----------|---------------|-------------------|
| Redactar DRS v2 PDF/Word formal | Alta | Post-Fase 8 | PDF/Markdown entregable institucional |
| Ejecutar suite PHPUnit @ 512M y archivar salida | Media | Pre-sustentación | Log tests en `docs/pruebas/` |
| Smoke manual por rol (manual usuario) | Media | Pre-sustentación | Acta o checklist |
| Corregir fichas `ImportarDatosTest` | Baja | Documentación | Fichas alineadas a tests reales |
| Decidir política `/register` | Alta | Pre-producción | Ruta deshabilitada o restringida |
| Definir seed entorno referencia | Media | DevOps académico | README conteos esperados |
| Ampliar activity log si RF-17 obliga | Media | Sprint futuro | `ActivityLogTest` ampliado |

---

## 5. Uso para DRS y sustentación

Este registro permite una **defensa honesta del alcance V1**:

- Reconocer brechas **antes** de que el tribunal las señale.
- Diferenciar **prototipo académico** de **producto certificado**.
- Vincular cada NC con documento fuente verificable.
- Demostrar **mejora continua orientada** sin afirmar ISO 9001 certificado.

En la sustentación oral, usar frases del tipo: *«brecha documentada NC-XX, fuente en matriz RF / limitaciones»*.

---

*Registro vivo — actualizado Fase 9 / DRS v2.1.*
