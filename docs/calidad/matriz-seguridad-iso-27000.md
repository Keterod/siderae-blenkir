# Matriz de seguridad — Referencia ISO/IEC 27000

Análisis de **referencia académica** inspirado en la familia **ISO/IEC 27000** (gestión de la seguridad de la información). **No** implica SGSI certificado según ISO/IEC 27001 ni auditoría externa.

Documento padre: [`alineacion-iso.md`](alineacion-iso.md). Detalle RBAC: [`seguridad-roles-permisos.md`](../seguridad-roles-permisos.md).

---

## 1. Propósito

Identificar activos de información del prototipo, controles existentes y brechas frente a buenas prácticas de la familia ISO/IEC 27000, para sustentación académica honesta.

---

## 2. Alcance

- Prototipo V1 local Docker — sede operativa **Chilca**.
- Controles implementados en código y documentados.
- Excluye evaluación de riesgos formal ISO 27005 ni certificación 27001.

---

## 3. Criterios de estado

| Estado | Significado |
|--------|-------------|
| **Evidencia confirmada** | Control verificable en código/docs |
| **Evidencia parcial** | Control incompleto o solo en subconjunto |
| **Pendiente** | Control planificado; no implementado |
| **No aplica V1** | Fuera alcance prototipo académico |
| **No confirmado** | Sin verificación en esta revisión |

---

## 4. Activos de información

| Activo | Descripción | Riesgo principal | Control existente | Estado | Brecha |
|--------|-------------|------------------|-------------------|--------|--------|
| Credenciales de usuario | Email/contraseña Sanctum | Suplantación, filtración | Breeze login, bcrypt (framework); usuarios demo README | Evidencia parcial | `POST /register` público; recuperación UI pendiente | Restringir register en prod |
| Datos de estudiantes | PII académica (nombres, grado, sede) | Acceso no autorizado | RBAC; rutas `gestionar_estudiantes` | Evidencia confirmada | Sin cifrado reposo documentado | Política prod |
| Notas / asistencia | Calificaciones y asistencia curricular | Integridad / confidencialidad | Permisos `registrar_*` / `ver_*`; tests | Evidencia parcial | Coordinador lectura; legacy API | Auditar rutas legacy |
| Alertas / intervenciones | Seguimiento psicopedagógico | Exposición sensible | `ver_alertas`, `registrar_intervencion` | Evidencia confirmada | Sin cifrado campo a campo | — |
| Configuración curricular | Malla, criterios, bimestral | Alteración no autorizada | Permisos `gestionar_*` curriculares | Evidencia confirmada | — | — |
| Base de datos MySQL | `siderae_db` en Docker | Pérdida / acceso host | Volumen `docker/mysql_data`; puerto 3307 | Evidencia parcial | Backups no confirmados | Backup documentado |
| Archivos Excel | Descarga aula / import plantilla | Fuga fuera del sistema | HTTPS no en local; auth en API | Evidencia parcial | Archivos en cliente usuario | Política institucional |
| Logs / auditoría | `activity_log` Spatie | Trazabilidad incompleta | Registro parcial en acciones API | Evidencia parcial | Sin UI consulta; no todos los REQ-17 | Extender logging |
| Variables de entorno | `.env` backend/frontend/ML | Secreto en repo | `.env.example`; README advierte | Evidencia confirmada | Riesgo usuario sube `.env` real | Mantener .gitignore |

---

## 5. Controles y evidencias

| Dominio / control de referencia | Riesgo tratado | Evidencia SIDERAE | Estado | Brecha | Recomendación |
|---------------------------------|----------------|-------------------|--------|--------|---------------|
| Autenticación de usuarios | Acceso no identificado | Sanctum + Breeze; `AuthenticationTest` | Evidencia confirmada | Register público | Deshabilitar guest register prod |
| Autorización RBAC | Escalada privilegios | Spatie Permission; middleware `permission:*` | Evidencia confirmada | 401/403 no exhaustivos | Ampliar Feature tests |
| Gestión de sesiones | Secuestro sesión | Cookies + CSRF (`api.js`) | Evidencia confirmada | Rate limit login: pendiente verificar | Throttle login |
| Segregación de funciones | Conflicto roles | 5 roles seed; matriz permisos | Evidencia confirmada | Directivo excepción UI notas | Alinear UI/permisos |
| Control de acceso a la red | Exposición servicios | Docker localhost; no prod | Evidencia parcial | Puertos expuestos en dev | Firewall prod |
| Validación de entradas | Inyección / datos inválidos | Form Requests; 422 en tests | Evidencia parcial | Cobertura total: no confirmada | Revisión por módulo |
| Registro y monitoreo | Falta trazabilidad | `activity_log` parcial; `ActivityLogTest` | Evidencia parcial | RF-17 incompleto | Extender activity + UI |
| Gestión de vulnerabilidades | Dependencias obsoletas | Composer/npm lock en repo | No confirmado | Sin pipeline SCA | Opcional CI |
| Copias de seguridad | Pérdida datos | Volumen MySQL persistente | Evidencia parcial | Sin procedimiento backup | Documentar backup manual |
| Desarrollo seguro | Credenciales en docs | README usuarios demo ficticios | Evidencia confirmada | Contraseña demo `password` | Rotar en prod |
| Protección datos (Ley 29733 ref. DRS) | Privacidad | RBAC; no certificación | Evidencia parcial | Sin DPIA formal | Referencia académica DRS |
| Pruebas de seguridad | Regresión controles | PHPUnit 401/403 módulos clave | Evidencia parcial | Sin Cypress E2E | Smoke manual + PHPUnit |
| Entorno local Docker | Configuración inconsistente | `docker-compose.yml`, instalacion-docker | Evidencia confirmada | Healthcheck parcial | Mejorar healthchecks |

---

*Referencia académica — no SGSI ISO/IEC 27001 certificado.*
