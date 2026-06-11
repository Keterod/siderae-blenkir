# Smoke manual RF-04 — Reportes conductuales

**Fase:** 2E — Cierre RF-04  
**Fecha documento:** 2026-06-10  
**Ejecutor sesión 2E:** Equipo AI-DLC (validación automatizada + revisión de código; **sin sesión interactiva en navegador**)

Referencias: [`plan-rf-04-reportes-conductuales.md`](../metodologia/planes-ai-dlc/plan-rf-04-reportes-conductuales.md) · [`manual-usuario.md`](../manual-usuario.md) §7.18 · [`ReporteConductualTest.php`](../../backend/tests/Feature/ReporteConductualTest.php)

---

## 1. Objetivo

Validar el flujo mínimo RF-04 desde la UI del perfil de estudiante (consulta, registro, anulación lógica) y confirmar que no se introducen regresiones en módulos adyacentes.

---

## 2. Precondiciones

| Requisito | Estado sesión 2E |
|-----------|------------------|
| Docker activo (`docker compose ps`) | Asumido (tests y build ejecutados en contenedores) |
| Backend migrado (columna `estado` en `reportes_conductuales`) | Requerido — ver migración `2026_06_10_000001_*` |
| `PermissionsSeeder` ejecutado | Requerido — permisos `ver_reportes_conductuales`, `registrar_reportes_conductuales` |
| Usuario con `ver_reportes_conductuales` (p. ej. docente demo) | Disponible en seed |
| Usuario con `registrar_reportes_conductuales` (p. ej. docente demo) | Disponible en seed |
| Usuario **directivo** (solo lectura backend) | Disponible en seed |
| Estudiante sede **Chilca** existente | Requerido en BD local |

**Comandos de verificación automatizada ejecutados en 2E:**

```bash
docker compose exec app-backend php artisan test --filter=ReporteConductualTest
docker compose exec app-frontend npm run build
```

---

## 3. Casos smoke

| Caso | Rol | Acción | Resultado esperado | Resultado obtenido | Estado |
| ---- | --- | ------ | ------------------ | ------------------ | ------ |
| 1 | Docente / coord. (ver) | Abrir perfil estudiante Chilca | Bloque **Reportes conductuales** visible | No ejecutado en navegador | **Pendiente** — revisión código confirma render condicional en `EstudiantesPanel.jsx` |
| 2 | Rol sin permisos conductuales | Abrir perfil | Bloque no visible | No ejecutado en navegador | **Pendiente** — componente retorna `null` sin permisos |
| 3 | Docente (registrar) | Registrar reporte vía formulario | 201 + fila en listado | API validada: `ReporteConductualTest` «usuario con permiso registro puede crear reporte» | **Parcial** (API ✓; UI no probada en browser) |
| 4 | Docente (ver) | Tras registro, listar | Reporte en tabla | API validada: listado activos en test index | **Parcial** (API ✓) |
| 5 | Docente (registrar) | Enviar formulario vacío / sin descripción | Mensajes validación; no guarda | API: 422 en test validación; frontend: `validarFormulario()` en componente | **Parcial** (API ✓ + validación JS en código) |
| 6 | Docente (registrar) | Anular reporte con confirmación | PATCH anular; desaparece de lista activa | API validada: «anular cambia estado sin borrar físicamente» | **Parcial** (API ✓; diálogo UI no probado en browser) |
| 7 | Docente (ver) | Tras anular | Reporte no en GET listado activos | API validada: «lista solo reportes activos» | **Parcial** (API ✓) |
| 8 | Directivo | Intentar POST/PATCH reportes | 403 backend | API validada: «directivo con solo lectura no puede crear ni anular» | **Parcial** (API ✓; directivo sin menú Estudiantes en UI V1) |
| 9 | Cualquier rol con acceso perfil | Revisar pantalla | Sin selector de sede en bloque RF-04 | No ejecutado en navegador | **Pendiente** — código no incluye selector sede |
| 10 | Docente | Revisar notas, asistencia, riesgo, Excel | Sin cambios/regresión visible | No ejecutado en navegador | **Pendiente** — fase 2D no modificó esos módulos (diff acotado) |

---

## 4. Evidencia automatizada (sustituto parcial del smoke UI)

| Evidencia | Comando | Resultado 2026-06-10 |
|-----------|---------|----------------------|
| Backend RF-04 | `php artisan test --filter=ReporteConductualTest` | **8 passed**, **26 assertions**, ~15.5 s |
| Build frontend | `npm run build` (contenedor `app-frontend`) | **Exit 0**, Vite build ~7.7 s, 108 módulos |

Casos API cubiertos por PHPUnit: 401 sin sesión, 403 sin permiso registro, creación OK, 422 validación, listado solo activos, anulación lógica, directivo solo lectura, rechazo estudiante Auquimarca (403).

---

## 5. Conclusión smoke

| Aspecto | Estado |
|---------|--------|
| Smoke UI en navegador | **No ejecutado** en sesión 2E (sin operador humano en UI) |
| Evidencia API + build | **Verde** — suficiente para cierre **V1 mínimo** con brecha documentada |
| Acción recomendada | Ejecutar checklist §3 en entorno demo antes de sustentación oral (docente + coordinador) |

---

*Ficha AI-DLC Fase 2E — 2026-06-10.*
