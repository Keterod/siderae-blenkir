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
