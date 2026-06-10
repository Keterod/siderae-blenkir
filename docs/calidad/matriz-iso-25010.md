# Matriz ISO/IEC 25010 — Calidad del producto

Referencia académica inspirada en **ISO/IEC 25010** (modelo de calidad del producto software). **No** implica conformidad certificada ni evaluación formal según la norma.

Documento padre: [`alineacion-iso.md`](alineacion-iso.md).

---

## 1. Propósito

Mapear características de calidad del producto del prototipo **SIDERAE-Blenkir V1** frente a criterios **inspirados en** ISO/IEC 25010, indicando evidencia, brecha y recomendación.

---

## 2. Alcance

- Prototipo académico V1 — sede operativa **Chilca**.
- Evidencias del repositorio y documentación Fases 1–6.
- Excluye certificación de producto o evaluación independiente.

---

## 3. Criterios de estado

| Estado | Significado |
|--------|-------------|
| **Evidencia confirmada** | Evidencia verificable y documentada sin reservas mayores |
| **Evidencia parcial** | Solo parte del criterio está cubierta |
| **Pendiente** | Requisito identificado; sin evidencia suficiente |
| **No aplica V1** | Fuera del alcance del prototipo V1 |
| **No confirmado** | Mencionado; sin verificación en esta revisión |

---

## 4. Matriz de calidad

| Característica ISO/IEC 25010 | Subcaracterística / criterio aplicado | Evidencia en SIDERAE-Blenkir | Documento / fuente | Estado | Brecha | Recomendación |
|------------------------------|---------------------------------------|------------------------------|-------------------|--------|--------|---------------|
| **Adecuación funcional** | Completitud funcional | Módulos curriculares, estudiantes, alertas, dashboard; RF parciales mapeados | [`matriz-rf-sprint-test.md`](../matriz-rf-sprint-test.md), [`App.jsx`](../../frontend/src/App.jsx) | Evidencia parcial | RF-03, RF-04, RF-10–12, RF-18–19 pendientes; SIAGIE pendiente | Actualizar DRS con estado V1 |
| Adecuación funcional | Corrección funcional | Tests PHPUnit Feature en auth, estudiantes, curricular, alertas | [`informe-pruebas.md`](../pruebas/informe-pruebas.md) | Evidencia parcial | Suite no completó @ 128M | Ejecutar suite @ 512M y registrar |
| **Eficiencia de desempeño** | Tiempo de respuesta | Sin métricas formales RNF-01; Docker local | [`limitaciones.md`](../limitaciones.md), DRS resumen | No confirmado | Sin pruebas de carga | Medición opcional pre-sustentación |
| **Compatibilidad** | Interoperabilidad | API REST Laravel ↔ React; Laravel ↔ Flask ML | [`ARCHITECTURE.md`](../../ARCHITECTURE.md), [`api.md`](../api.md) | Evidencia confirmada | ML determinístico; sin contrato formal versionado | Documentar contrato ML vigente |
| Compatibilidad | Coexistencia | Legacy API + curricular en paralelo | [`limitaciones.md`](../limitaciones.md) §4 | Evidencia parcial | Legacy sin menú; posible confusión | Restringir legacy en prod |
| **Usabilidad** | Aprendizabilidad / operabilidad | Manual usuario por rol; sidebar por permisos | [`manual-usuario.md`](../manual-usuario.md) | Evidencia parcial | Sin guía formal accesibilidad | Smoke manual por rol |
| Usabilidad | Protección frente a errores de usuario | Validaciones 422; mensajes error UI | Tests Feature, componentes UI | Evidencia parcial | Recuperación contraseña UI pendiente | Implementar o documentar flujo |
| **Fiabilidad** | Madurez | ~277 tests pasaron antes OOM (Fase 1) | [`hallazgos-fase1-documentacion.md`](../pruebas/hallazgos-fase1-documentacion.md) | Evidencia parcial | Fallo OOM ExcelAulaTest @ 128M | Ajustar memory_limit tests |
| Fiabilidad | Recuperabilidad | Docker restart; migrate en arranque | [`docker-compose.yml`](../../docker-compose.yml) | Evidencia parcial | Backups BD no confirmados | Política backup fuera V1 |
| **Seguridad** | Confidencialidad / control de acceso | Sanctum, Spatie RBAC, 401/403 en tests principales | [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md) | Evidencia parcial | 401/403 no exhaustivos; register público | Ver matriz 27000 |
| Seguridad | Integridad | Validación Form Request; permisos backend | [`api.php`](../../backend/routes/api.php) | Evidencia parcial | UI oculta ≠ API legacy | Endurecer prod |
| **Mantenibilidad** | Modularidad | Backend/frontend/ML desacoplados; módulo curricular | [`resumen-arquitectura.md`](../arquitectura/resumen-arquitectura.md) | Evidencia confirmada | Deuda legacy coexistiendo | Plan retirada legacy |
| Mantenibilidad | Documentación | Manuales, API, matrices, limitaciones | `docs/*` | Evidencia confirmada | DRS PDF externo desactualizado | Fase 7 DRS |
| **Portabilidad** | Adaptabilidad / instalabilidad | Docker Compose 4 servicios; `.env.example` | [`instalacion-docker.md`](../instalacion-docker.md), README | Evidencia confirmada | Solo entorno local documentado | Guía despliegue futuro |

---

*Matriz de referencia académica — no evaluación ISO/IEC 25010 certificada.*
