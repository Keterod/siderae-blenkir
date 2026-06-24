# Contexto frontend React (v2 — Fase 2)

> **Documento de contexto operativo.** Fuente formal vigente del estado V1: [`docs/drs/DRS_SIDERAE_Blenkir_v2.md`](../drs/DRS_SIDERAE_Blenkir_v2.md). Flujos de usuario: [`docs/manual-usuario.md`](../manual-usuario.md).

## Rol del frontend

SPA React que consume la API Laravel, adapta menú y acciones por permisos (`GET /api/me`) y concentra la operación académica curricular, riesgo y alertas.

Referencia: [`docs/limitaciones.md`](../limitaciones.md) · [`frontend/src/lib/api.js`](../../frontend/src/lib/api.js)

---

## Stack (`frontend/package.json`)

- React 18, Vite, Tailwind CSS, ESLint
- **Sin** React Router — navegación por estado `moduloActivo` en [`App.jsx`](../../frontend/src/App.jsx)
- **Sin** Cypress en el repositorio

---

## Estructura (`frontend/src`)

| Ruta | Rol |
|------|-----|
| `main.jsx` | Entrada |
| `App.jsx` | Layout, sidebar, routing por módulo |
| `context/AuthContext.jsx` | Sesión, roles, permisos |
| `components/LoginForm.jsx` | Login |
| `lib/api.js` | Cliente HTTP (Sanctum cookies + CSRF) |
| `lib/sedeOperativa.js` | Sede fija Chilca en payloads |
| `components/estudiantes/*` | Estudiantes + perfil + riesgo |
| `components/alertas/AlertasPanel.jsx` | Alertas |
| `components/usuarios/UsuariosPanel.jsx` | Gestión usuarios |
| `components/curricular/*` | Módulo curricular completo |
| `components/DashboardPanel.jsx` | Dashboard |
| `components/materias/MateriasPanel.jsx` | **Legacy — sin menú** |
| `components/academico/*` | **Legacy — sin menú** |

---

## Autenticación

1. `getCsrfCookie()` → `/sanctum/csrf-cookie`
2. `login()` → `POST /login`
3. `getMe()` → `GET /api/me` (usuario, roles, permisos)
4. `logout()` → `POST /logout`

Implementación: [`AuthContext.jsx`](../../frontend/src/context/AuthContext.jsx).

Base URL: `VITE_API_URL` ([`frontend/.env.example`](../../frontend/.env.example) → `http://localhost:8000`).

---

## Módulos UI y permisos (`App.jsx`)

Función `moduloPermitido(key, permissions, roles)` controla visibilidad del sidebar.

| Módulo sidebar | Clave | Permiso / regla principal |
|----------------|-------|---------------------------|
| Dashboard | `dashboard` | `ver_dashboard` |
| Estudiantes | `estudiantes` | `gestionar_estudiantes` |
| Usuarios | `usuarios` | `gestionar_usuarios` |
| Notas semanales | `curricular_notas` | `registrar_notas_semanales` OR `gestionar_asignaciones_docente` OR rol `directivo` (consulta institucional / excepción UI) |
| Excel por aula | `curricular_excel_aula` | `descargar_excel_aula` |
| Asistencia | `curricular_asistencia` | `registrar_asistencia_curricular` OR `ver_asistencia_curricular` |
| Alertas | `alertas` | `ver_alertas` |
| Malla curricular | `curricular_malla` | `ver_malla_curricular` OR `gestionar_malla_curricular` |
| Criterios evaluación | `curricular_temas` | `gestionar_temas_semanales` |
| Componentes calificación | `curricular_componentes_calificacion` | `gestionar_componentes_calificacion` |
| Configuración bimestral | `curricular_eval_bim` | `configurar_evaluacion_bimestral` |
| Secciones/Aulas | `curricular_secciones_aulas` | `gestionar_secciones_aulas` |
| Asignación docente | `curricular_asignacion` | `gestionar_asignaciones_docente` |
| Competencias | `curricular_competencias` | `gestionar_competencias_capacidades` |
| Periodos académicos | `curricular_calendario` | `gestionar_calendario_academico` |
| Pesos evaluación | `curricular_pesos` | **`visible: false`** (oculto) |

Matriz vigente completa: [`docs/seguridad-roles-permisos.md`](../seguridad-roles-permisos.md).

---

## Cliente API (`lib/api.js`)

Funciones exportadas agrupadas:

- **Auth/sesión:** `login`, `logout`, `getMe`
- **Dashboard:** `getDashboard`, `exportDashboardPdf`
- **Estudiantes / legacy académico:** CRUD, notas, asistencias, VSE, lotes, materias
- **Riesgo/alertas:** `postProcesarRiesgo`, alertas, intervención, cierre
- **Usuarios:** CRUD + activar/desactivar/restablecer
- **Curricular:** catálogo, malla, temas, competencias, pesos, componentes, secciones, asignaciones, notas semanales, Excel, bimestre, asistencia diaria

El frontend **no** llama a `:5000` (Flask); riesgo vía backend.

---

## Perfil estudiante

- `EstudiantesPanel` + `EstudiantePerfilRiesgo` — sección **riesgo activada V1** (NC-11 cerrada): muestra último índice/nivel y botón **Procesar/Actualizar riesgo** para usuarios con permiso `procesar_riesgo`; llama `POST /api/estudiantes/{id}/procesar-riesgo` y refresca historial RF-20 y semáforo RF-19. No recalcula automáticamente al abrir el perfil.
- `EstudiantePerfilDatos`:
  - Notas curriculares (`ver_notas_academicas`)
  - Asistencia curricular
  - **Variables socioeconómicas:** componente existe pero `mostrarVariablesSocio` **no se pasa** desde `EstudiantesPanel` → UI **pausada** (RF-05 parcial)

---

## Mockups vs UI real

- Mockups [`docs/ui/mockups/01–12`](../ui/mockups/) y [`guia-ui-siderae.md`](../ui/mockups/guia-ui-siderae.md): referencia **histórica/diseño** flujo legacy.
- Módulos curricurales **no** tienen mockups equivalentes.
- Sede única Chilca: mockups pueden asumir multi-sede — UI real no expone selector.

---

## Estado UI por RF

| RF | Estado UI |
|----|-----------|
| RF-01 | **Parcial** — notas/asistencia curricular; plantilla Excel curricular; Excel aula descarga; **SIAGIE pendiente** |
| RF-02 | **Confirmado** — asistencia curricular |
| RF-05 | **Parcial** — API backend; **pestaña VSE pausada** en perfil |
| RF-06/07 | **Implementado V1** — UI riesgo activada; botón procesar en perfil con permiso `procesar_riesgo`; backend/API operativo; smoke manual navegador pendiente |
| RF-08–09 | **Confirmado** — alertas e intervenciones |
| RF-13 | **Parcial** — cierre vía intervención; sin derivación/comunicación familiar en UI |
| RF-14 | **Parcial** — dashboard básico (subset REQ-14) |
| RF-16 | **Parcial** — export PDF dashboard; Excel aula `.xlsx` |
| RF-15 | **Confirmado** — panel usuarios |
| RF-17 | **N/A UI** — activity log solo backend |
| RF-19 | **Implementado V1** — `EstudiantePerfilSemaforoCompletitud.jsx` en perfil estudiante; render condicional por permiso; build OK |
| RF-20 | **Implementado V1** — componente `EstudiantePerfilHistorialRiesgo.jsx` en perfil estudiante; smoke manual navegador pendiente |

---

## Pruebas

- Build smoke: `npm run build` (documentado en manual técnico)
- Cypress: **no confirmado** (sin carpeta en repo)
- PHPUnit cubre API; pruebas UI manuales pendientes de informe formal

---

*Actualizado: saneamiento post-Fase 8 (2026-06-09). Alineado a DRS v2. RF-19 cerrado V1 Fase 3E (2026-06-23).*
