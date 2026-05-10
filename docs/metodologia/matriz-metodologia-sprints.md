# Matriz metodología y sprints

Relación orientativa entre **sprints** (carpeta `sprints/`), **propósito** resumido y **componente metodológico** predominante en cada fase. **AI-DLC** actúa de forma transversal en el desarrollo asistido por IA; la columna “principal” indica el **énfasis** relativo de cada sprint para lectura rápida, no exclusividad.

| Sprint | Propósito general | Componente metodológico principal | Evidencia esperada | Estado de revisión |
| ------ | ----------------- | --------------------------------- | ------------------ | ------------------ |
| **1** | Infraestructura y entorno base (Docker Compose, servicios). | DevOps ligero | `docker-compose.yml`, README de arranque, logs básicos de servicios. | Documentado en sprint; pendiente de contrastar con evidencia |
| **2** | Autenticación, sesión, roles y permisos (API y modelo de autorización). | Scrum | Archivo `sprints/sprint 2.md`, rutas API protegidas, seeders de permisos. | Documentado en sprint; pendiente de contrastar con evidencia |
| **3A** | Gestión base de estudiantes (CRUD, validaciones, perfil). | AI-DLC | `sprints/sprint 3A.md`, pruebas de estudiante si existen, capturas opcionales. | Documentado en sprint; pendiente de contrastar con evidencia |
| **3B** | Captura de datos académicos y socioeconómicos vinculados al perfil. | AI-DLC | `sprints/sprint 3B.md`, endpoints y UI de notas/asistencia/variables. | Documentado en sprint; pendiente de contrastar con evidencia |
| **4** | Integración con servicio ML y cálculo/persistencia de riesgo. | MLOps básico | `sprints/sprint 4.md`, `docs/arquitectura/contexto-ml-service-flask.md`, llamada Laravel→Flask. | Documentado en sprint; pendiente de contrastar con evidencia |
| **5** | Alertas, intervenciones y cierre de alerta. | Scrum | `sprints/sprint 5.md`, flujos de alerta en API/UI. | Documentado en sprint; pendiente de contrastar con evidencia |
| **6A** | Dashboard mínimo (KPIs, tabla de riesgo, alertas por estado). | Scrum | `sprints/sprint 6A.md`, endpoint dashboard, capturas o pruebas manuales. | Documentado en sprint; parcialmente revisado |
| **6B** | Filtros, reportes/export básico y ajuste por rol. | Scrum | `sprints/sprint 6B.md`, export PDF en alcance documentado (parcial vs DRS según README). | Documentado en sprint; parcialmente revisado |
| **7A** | Rediseño visual global (layout, guía, componentes base). | Context Engineering | `sprints/sprint 7A.md`, `docs/ui/mockups/guia-ui-siderae.md`, coherencia visual. | Documentado en sprint; pendiente de contrastar con evidencia |
| **7B** | Pantallas y navegación según mockups; controles coherentes con permisos. | Context Engineering | `sprints/sprint 7B.md`, mockups 01–12, mensajes de estado pendiente explícitos. | Documentado en sprint; pendiente de contrastar con evidencia |
| **7.5A** | Ajuste intermedio documentado del sistema. | Scrum | `sprints/sprint 7.5A.md`, registros en `activity_log` donde aplique. | Documentado en sprint; pendiente de contrastar con evidencia |
| **7.5B** | Refinamiento de interfaz, ordenamiento o flujo según planificación del proyecto. | Context Engineering | `sprints/sprint 7.5B.md`, revisión frente a mockups/guía. | Documentado en sprint; pendiente de contrastar con evidencia |
| **7.6A** | Evolución visual/funcional vinculada a módulos académicos según planificación del proyecto. | Scrum | `sprints/sprint 7.6A.md`, modelo/API de materias según diseño del sprint. | Documentado en sprint; pendiente de contrastar con evidencia |
| **7.6B** | Continuación de ajustes posteriores al 7.6A. | Scrum | `sprints/sprint 7.6B.md`, endpoints batch, notas de dependencia 7.6A. | Documentado en sprint; pendiente de contrastar con evidencia |
| **8** | Seguridad, auditoría y control de accesos (matriz rol–permiso–pantalla). | Scrum | `sprints/sprint 8.md`, `docs/arquitectura/matriz-control-accesos-sprint8.md`. | Documentado en sprint; pendiente de contrastar con evidencia |
| **9** | Pruebas integrales, regresión y corrección de bugs. | Scrum | `sprints/sprint 9.md`, salidas de tests, informes o capturas si se producen. | Pendiente de contrastar con evidencia |
| **10** | Documentación final y cierre de calidad académico. | Scrum | `sprints/sprint 10.md`, manuales, matrices de trazabilidad cuando existan. | Pendiente de contrastar con evidencia |

---

Esta matriz será refinada posteriormente con revisión detallada de cada archivo de sprint, evidencias de pruebas y fuentes académicas.
