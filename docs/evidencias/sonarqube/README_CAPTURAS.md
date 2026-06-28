# Capturas automáticas de SonarQube

Script basado en **Playwright** que toma capturas de pantalla del dashboard de SonarQube para el proyecto **SIDERAE-Blenkir**.

## Requisitos previos

1.  Node.js 18+
2.  Playwright instalado globalmente o en el proyecto

```powershell
# Instalar dependencias
npm install -D playwright
npx playwright install chromium
```

## Configuración

El script lee estas variables de entorno:

| Variable | Valor por defecto | Obligatoria |
|---|---|---|
| `SONAR_URL` | `http://localhost:9000` | No |
| `SONAR_USER` | — | Sí (si requiere login) |
| `SONAR_PASS` | — | Sí (si requiere login) |
| `SONAR_PROJECT_KEY` | `SIDERAE-Blenkir` | No |
| `HEADLESS` | `true` | No |

## Ejecución

Desde la raíz del proyecto, en **PowerShell**:

```powershell
$env:SONAR_URL="http://localhost:9000"
$env:SONAR_USER="admin"
$env:SONAR_PASS="TU_PASSWORD"
$env:SONAR_PROJECT_KEY="SIDERAE-Blenkir"
node tools/capturar-sonarqube.mjs
```

### Ejecución en modo visible (para depurar)

```powershell
$env:HEADLESS="false"
$env:SONAR_URL="http://localhost:9000"
$env:SONAR_USER="admin"
$env:SONAR_PASS="TU_PASSWORD"
$env:SONAR_PROJECT_KEY="SIDERAE-Blenkir"
node tools/capturar-sonarqube.mjs
```

### Ejemplo con tokens (alternativa)

Si usas **token de SonarQube** en lugar de usuario/contraseña, puedes asignar cualquier valor a `SONAR_USER` y el token a `SONAR_PASS` — la pantalla de login acepta ambas formas.

```powershell
$env:SONAR_USER="admin"
$env:SONAR_PASS="squ_XXXXXXXXXXXXXXXXXXXXXX"
node tools/capturar-sonarqube.mjs
```

## Capturas generadas

El script crea 6 capturas en **`docs/evidencias/sonarqube/02_revision_con_coverage/capturas/`**:

| Archivo | Sección |
|---|---|
| `01_overview_quality_gate_coverage.png` | Dashboard general con Quality Gate y coverage |
| `02_issues_sonarqube.png` | Listado de issues |
| `03_security_hotspots.png` | Security Hotspots |
| `04_measures_metricas.png` | Measures / métricas generales |
| `05_coverage_detail.png` | Detalle de cobertura |
| `06_duplications.png` | Duplicación de código |

## Solución de problemas

**"No se pudo capturar ... (Timeout)"**
Si SonarQube tarda en cargar, el script espera 30 segundos por página. Si alguna sección no existe en tu versión, el script la omite sin romper el resto.

**Playwright no encuentra Chromium**
Ejecuta `npx playwright install chromium` para descargar el navegador.

**Error de autenticación / capturas en pantalla de login**
Si las capturas muestran la pantalla de login en lugar del dashboard:

1. Ejecuta con `$env:HEADLESS="false"` para ver el navegador en acción.
2. Verifica que `SONAR_USER` y `SONAR_PASS` sean correctos.
3. Si el login falla, el script genera `docs/evidencias/sonarqube/debug_login_failed.png` con diagnóstico detallado (URL, título, visibilidad de campos, mensajes de error).

**Diagnóstico automático**
En caso de fallo de login, el script imprime:
- URL actual después del intento
- Título de la página
- Si los campos de usuario/contraseña siguen visibles
- Si detecta mensaje de error de credenciales en la página
