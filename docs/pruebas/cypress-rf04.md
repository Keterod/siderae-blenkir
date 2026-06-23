# Cypress RF-04 — Reportes conductuales

## Requisitos

- Docker activo.
- Backend migrado.
- `PermissionsSeeder` ejecutado.
- Usuario E2E con permisos:
  - `ver_reportes_conductuales`
  - `registrar_reportes_conductuales`
- Estudiante Chilca existente.
- Frontend disponible en `http://localhost:5173` o URL definida por `CYPRESS_BASE_URL`.

## Variables

```bash
CYPRESS_E2E_EMAIL=
CYPRESS_E2E_PASSWORD=
CYPRESS_E2E_STUDENT_TEXT=
```

`CYPRESS_E2E_STUDENT_TEXT` es opcional. Si no se define, la prueba usa el primer estudiante visible del listado Chilca. No guardar credenciales reales en el repositorio.

Variables opcionales futuras para specs RBAC:

```bash
CYPRESS_E2E_NO_PERMISSION_EMAIL=
CYPRESS_E2E_NO_PERMISSION_PASSWORD=
CYPRESS_E2E_DIRECTIVO_EMAIL=
CYPRESS_E2E_DIRECTIVO_PASSWORD=
```

Fase 2H agrega comandos Cypress globales de autenticación, logout y navegación. RF-04 usa esos helpers comunes, pero el alcance de este documento sigue siendo el smoke RF-04.

## Comandos

Desde `frontend/`:

```bash
npm run cy:open
npm run cy:run
```

O con Docker si el entorno dispone del navegador y dependencias gráficas necesarias:

```bash
docker compose exec app-frontend npm run cy:run
```

## Alcance

- Solo smoke E2E RF-04.
- No es suite E2E completa del sistema.
- No reemplaza PHPUnit.
- No valida RF-10, RF-11, RF-16, RF-18, RF-19 ni RF-20.
- No crea módulo global RF-04 ni selector de sede.

## Casos cubiertos

1. Ver bloque **Reportes conductuales** en perfil de estudiante Chilca.
2. Registrar reporte conductual con texto único `Reporte E2E RF04 <timestamp>`.
3. Validar que no se guarda un reporte sin descripción obligatoria.
4. Anular el reporte creado con confirmación del navegador.
5. Confirmar que RF-04 no expone selector ni opción operativa multi-sede.

## Resultado

Fase 2F (2026-06-17):

| Comando | Resultado | Observación |
|---------|-----------|-------------|
| `npm run cy:run` | Ejecutado; Cypress 15.17.0 verificado; spec no completó | Falla esperada por entorno sin `CYPRESS_E2E_EMAIL`: `Debe definir CYPRESS_E2E_EMAIL para ejecutar el smoke RF-04.` |
| `npm run build` | Exit 0 | Vite build correcto, 108 módulos transformados |

Estado: **Cypress configurado; ejecución funcional pendiente por variables de entorno E2E**.

## Infraestructura global relacionada

Fase 2H agrega:

- `cy.visitApp()`
- `cy.getByTestId(testId)`
- `cy.requireE2ECredentials()`
- `cy.loginAsE2EUser()`
- `cy.logout()`
- `cy.openModule(moduleKey, expectedTestId)`

Specs mínimos de infraestructura:

- `frontend/cypress/e2e/auth-login.cy.js`
- `frontend/cypress/e2e/logout.cy.js`

Esto no convierte Cypress en suite global completa; solo habilita la base reutilizable para fases posteriores.

Resultado Fase 2H:

| Comando | Resultado | Observación |
|---------|-----------|-------------|
| `npm run build` | Exit 0 | Vite build correcto, 108 módulos transformados |
| `npm run cy:run` | Ejecutado; 3 specs detectados; 2 tests passed, 5 failed, 4 skipped | Los fallos corresponden a specs que requieren `CYPRESS_E2E_EMAIL` / `CYPRESS_E2E_PASSWORD`; mensaje claro: `Debe definir CYPRESS_E2E_EMAIL (correo del usuario E2E) para ejecutar los specs E2E.` |

Estado Fase 2H: **infraestructura Cypress global configurada; ejecución funcional completa pendiente por credenciales E2E**.

## Resultado Fase 2H.1 — Corrección auth/logout

Fase 2H.1 ajustó helpers base de Cypress para estabilizar sesión:

- `ensureLoggedOut()` limpia cookies, localStorage y sessionStorage antes de visitar la app.
- `loginAsE2EUser()` valida de forma explícita si llega a layout autenticado o vuelve a login.
- `assertAuthenticated()` acepta el selector estable del menú lateral o el `aria-label` existente como fallback.
- RF-04 conserva el mismo alcance y sigue usando los helpers comunes.

Ejecución local con variables temporales E2E:

| Comando | Resultado | Observación |
|---------|-----------|-------------|
| `npm run build` | Exit 0 | Vite build correcto, 108 módulos transformados |
| `npm run cy:run` | Ejecutado con fallas | 3 specs detectados; `auth-login` llegó a 2 passed / 2 failed en una corrida previa; después de ajustes siguieron fallas de sesión/layout |
| `npx cypress run --spec "cypress/e2e/auth-login.cy.js"` | Bloqueado por entorno/sesión | La corrida aislada quedó colgada antes de iniciar tests y fue detenida manualmente |

Estado Fase 2H.1: **Cypress base ejecutado con fallas pendientes de sesión/entorno**. No se guardaron credenciales en archivos.
