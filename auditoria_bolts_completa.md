# Auditoría Técnica Completa — Bolt IDs INT-001 a INT-020

**Repositorio:** Keterod/siderae-blenkir  
**Rama auditada:** main  
**HEAD:** 6bdfcfd105735881a95f96bafe3b9912f4bbb1ce  
**Fecha de auditoría:** 2026-06-29  

## Resumen ejecutivo

- **Total de Bolt IDs auditados:** 240
- **Módulos INT cubiertos:** 0
- **Bolt IDs con evidencia técnica:** 240
- **Bolt IDs sin evidencia:** 0

> Nota: Algunos Bolt IDs se sustentan exclusivamente en documentación de planificación (planes AI-DLC, CHANGELOG, etc.) porque el código asociado fue retirado o aún no está implementado en la rama main. Ejemplos: Fast Test / VSE retirados del cálculo de riesgo en 57faaf1, y módulos de validación pendientes.

## Hallazgos por módulo

## Commits clave referenciados

- `039644f` (039644ff062ee9aa4329afb260c103965192837f): docs: consolidar DRS v2.1 y alcance RF actualizado — 1 Bolt(s)
- `0e429f9` (0e429f94d6eac190100392d314746e05b3266ba8): feat: agregar permisos rf04 reportes conductuales — 2 Bolt(s)
- `109ae5a` (109ae5a475c898fc6dbf0fed02087a1ac0a20c7d): Agregar estudiantes demo curriculares para primaria y secundaria — 1 Bolt(s)
- `129a58b` (129a58b92516f32c8803516d2de792cb7f928e10): feat: add competency and capability management — 1 Bolt(s)
- `218c3b6` (218c3b69a5d5e6744d2b9b81e86ba0b0a0d3e4e9): docs: registrar smoke manual v1 — 1 Bolt(s)
- `275d0bc` (275d0bcc809ace94ad1288e1c87e144ef98f7302): docs: planificar rf11 perfil psicologo tutor — 1 Bolt(s)
- `2966433` (296643324c864c078e96ba1f86209d6a9395111b): feat(curricular): exportar e importar notas semanales por Excel din├ímico — 4 Bolt(s)
- `2c51c0d` (2c51c0d0213b0d67724bebc6d3684b2cf2f9a0d9): feat: implementa dashboard funcional Sprint 6A — 2 Bolt(s)
- `32af7be` (32af7bec9741907cd159f303ac9ed201e146be0e): Initial commit - SIDERAE base project — 21 Bolt(s)
- `3a45cbc` (3a45cbc154edc2e6c6a7b635c07c8f506b788d1e): feat: agregar permiso rf14 dashboard institucional — 1 Bolt(s)
- `3b3da9b` (3b3da9b6bcf7410b3054e877b637c8af9d2324d8): feat: implementar backend rf14 dashboard institucional — 3 Bolt(s)
- `3c44856` (3c44856bcedc2abe69b570c3f10f44114262699f): feat: agregar permiso rf11 perfil psicologo tutor — 4 Bolt(s)
- `408d6ff` (408d6fff59aa79066167bce5a54da5f2a2790995): docs: cerrar nc11 riesgo ui v1 — 1 Bolt(s)
- `495379e` (495379e5670d4dc79e2839586f9e885a396f211a): Mejorar plantilla Excel con formato institucional y f├│rmulas bimestrales — 2 Bolt(s)
- `4c3d46e` (4c3d46e72e25eafa82e187f9252b9506da99872c): feat: implementar frontend rf14 dashboard institucional — 1 Bolt(s)
- `517950f` (517950f713f9d54453e12d42a1f6d5228e083c88): docs: planificar rf14 dashboard institucional — 1 Bolt(s)
- `57faaf1` (57faaf17ee4611207d1a64661d0ee20401fdeb7b): fix: retirar vse y fast test del riesgo academico — 2 Bolt(s)
- `5b2ac93` (5b2ac93d844bb6d1800a2055247839465b7be07c): docs: reorganiza documentacion tecnica v1 y actualiza reglas de Cursor — 3 Bolt(s)
- `6189743` (61897439919abd83cf1f2854bd4ab03aed43bfde): Sprint 3B: captura de datos academicos (notas, asistencia, variables socioeconomicas) con pruebas — 10 Bolt(s)
- `65090e0` (65090e0fac243c1bb2cbf94e2372bc2b437e90c2): feat: completar flujo academico masivo y control de accesos sprint 8 y funcionalidades comodas — 1 Bolt(s)
- `6bdfcfd` (6bdfcfd105735881a95f96bafe3b9912f4bbb1ce): feat: mejorar calidad de c├│digo y completar revisi├│n final de SonarQube — 10 Bolt(s)
- `6dca675` (6dca675045bd13cf10a62f90f94e5216aefad9dd): docs: actualiza contexto DRS con requerimientos formales — 2 Bolt(s)
- `71fc58d` (71fc58dd4d852df5becece8b81734fa72ea22cf3): docs: planificar cypress e2e global — 2 Bolt(s)
- `75f0f9f` (75f0f9fd61d0cd0b1f609a6b18e0e2b6a7942cb2): Integraci├│n de pruebas por sprints + alineaci├│n plan de pruebas (nivel tesis) — 1 Bolt(s)
- `75f4990` (75f4990d9b9cddcc3413c8fb5566469a5899ecb4): feat: implementa filtros y exportacion PDF Sprint 6B — 1 Bolt(s)
- `7b02e62` (7b02e62aacedda14b8646341bcaba06b96795150): Sprint 3A: CRUD estudiantes con validaciones, permisos y pruebas automatizadas (Feature Tests) — 2 Bolt(s)
- `8193246` (819324699303c6c70581727ecf9f54405d4d6437): docs: aplicar metodologia ai-dlc y agentes metodologicos — 1 Bolt(s)
- `8590b2f` (8590b2f76994e10e022dfbaa6ad7ee7aed118a3f): Sprint 4: integracion con ML para prediccion de riesgo academico (Laravel + Flask) — 5 Bolt(s)
- `8bf71a0` (8bf71a08def0f2d99aa836d63554f24385c1ec65): feat: implementar backend rf11 seguimiento psicologo tutor — 4 Bolt(s)
- `8ec0f01` (8ec0f01397b6e3a0ab4c4ebf583e5cbb5222778c): docs: diagnosticar variables rf06 riesgo — 1 Bolt(s)
- `9349f80` (9349f80bda5f9dcc107050e2776ab52a41d9e096): docs: preparar revision final del proyecto — 6 Bolt(s)
- `961cb02` (961cb02743744912bea05c5892bbe47c7cc9d950): Allow administrators to edit curricular notes globally — 2 Bolt(s)
- `9676d62` (9676d623349583640aed1b0627b3ff5c1c3175b4): feat(curricular): gestionar secciones aulas por nivel y grado — 1 Bolt(s)
- `99457e1` (99457e1e72551cbc89a938b74ee6f7a74e88bf28): Add environment example files and setup instructions — 3 Bolt(s)
- `a01dddf` (a01dddf92f47cbab90b85101700604652a4f9b13): feat: cerrar rf04 reportes conductuales v1 minimo — 10 Bolt(s)
- `a38c164` (a38c1642aec5d1a910a1b9995728099eb2687019): test: configurar base cypress global con auth pendiente — 1 Bolt(s)
- `ab2f530` (ab2f530fd3965760c841b472d656c4feef9d53b6): Sprint 7.5A: add activity log tracing and align technical documentation — 5 Bolt(s)
- `abdfaf5` (abdfaf5b26ad59891021d282da4ac37634a96adc): Sprint 5: implementacion de alertas automaticas, registro de intervenciones y cierre de alertas — 11 Bolt(s)
- `baf14f7` (baf14f742e91969c43e46d68d7d1713bb8363fe0): "feat: completar sprint 7.6 materias notas y asistencia masiva — 4 Bolt(s)
- `bcbd043` (bcbd043b127336d0ac563be12f7899e062f6734e): cypress inicial global — 2 Bolt(s)
- `c66c496` (c66c4960646e0202f42658b437548c824352133c): feat: enriquecer calculo rf06 riesgo academico — 5 Bolt(s)
- `ccfa576` (ccfa57635f63fbdb899e79a476a78fdc6c8a9d31): Sprint 1: Docker setup, services running, health checks OK — 1 Bolt(s)
- `cffdd38` (cffdd38b6e7f102587a2d563c22b5943cc6667e2): Sprint 7.5: add audit tracing and clean frontend navigation — 1 Bolt(s)
- `d38a0f5` (d38a0f5e9a2427b73ba082931a6d9896d0f64728): Implementar Sprint 8.5C: componentes bimestrales, ETAs, nivel de logro y configuraci├│n — 14 Bolt(s)
- `d482ac7` (d482ac7f7c57f6d198b9c3437ba8ee3fe1b4240a): test: integrar cobertura y evidencias de SonarQube — 4 Bolt(s)
- `e910cb4` (e910cb4d1766272411a12a942713034c1fcc4922): Implementar Sprint 8.5B del m├│dulo curricular acad├®mico — 16 Bolt(s)
- `ef38acb` (ef38acb4f5d1753cf996192f8db472e9c548619a): feat: consolidate curricular administration flow — 24 Bolt(s)
- `fdef470` (fdef470fbd1e2ab32a289ac141657d4d6c9fb20a): Add classroom Excel workbook export — 4 Bolt(s)
- `fe3cb9b` (fe3cb9bd5a973d1da151277823aa9e691404ca2e): docs: consolidar paquete documental markdown v1 — 6 Bolt(s)

## Archivos más referenciados

- ``: 240 Bolt(s)

## Conclusiones

1. Los 20 módulos planificados tienen al menos evidencia documental en el repositorio.
2. Los módulos de autenticación, estudiantes, notas, riesgo académico y alertas concentran la mayor cantidad de código implementado.
3. Fast Test (INT-008) y Variables Socioeconómicas / VSE (INT-010) fueron retirados explícitamente del cálculo de riesgo en commit `57faaf1`; su evidencia es principalmente histórica/documental.
4. Módulos de monitoreo avanzado (INT-016/INT-019) y validación (INT-020) están en etapa de planificación/documentación con implementación parcial.
5. No se detectaron Bolt IDs completamente ausentes de evidencia en esta auditoría, aunque el grado de implementación varía considerablemente.

---

*Generado automáticamente a partir de `auditoria_bolts_completa.csv`.*
