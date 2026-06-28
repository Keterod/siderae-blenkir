import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const SONAR_URL = (process.env.SONAR_URL || 'http://localhost:9000').replace(/\/+$/, '');
const SONAR_USER = process.env.SONAR_USER;
const SONAR_PASS = process.env.SONAR_PASS;
const SONAR_PROJECT_KEY = process.env.SONAR_PROJECT_KEY || 'SIDERAE-Blenkir';
const HEADLESS = process.env.HEADLESS !== 'false';

const OUTPUT_DIR = path.resolve(
  __dirname, '..', 'docs', 'evidencias', 'sonarqube', '02_revision_con_coverage', 'capturas',
);
const DEBUG_DIR = path.resolve(__dirname, '..', 'docs', 'evidencias', 'sonarqube');

const NAV_TIMEOUT = 30000;

async function clickOverallCode(page) {
  const selectors = [
    'button:has-text("Overall Code")',
    'a:has-text("Overall Code")',
    'span:has-text("Overall Code")',
    '[aria-label*="Overall"]',
    '.overall-code',
  ];
  for (const sel of selectors) {
    try {
      const el = await page.$(sel);
      if (el && (await el.isVisible().catch(() => false))) {
        await el.click();
        await sleep(2000);
        return true;
      }
    } catch { /* ignore */ }
  }
  return false;
}

const PAGES = [
  { name: '01_overview_quality_gate_coverage.png', url: `/dashboard?id=${SONAR_PROJECT_KEY}`,            label: 'Overview / Quality Gate / Coverage',
    beforeCapture: async (page) => {
      const clicked = await clickOverallCode(page);
      if (clicked) {
        ok('Cambiado a pestaña "Overall Code"');
      } else {
        warn('No se encontró la pestaña "Overall Code" — se captura la vista actual');
      }
    },
  },
  { name: '02_issues_sonarqube.png',              url: `/project/issues?id=${SONAR_PROJECT_KEY}`,         label: 'Issues' },
  { name: '03_security_hotspots.png',             url: `/security_hotspots?id=${SONAR_PROJECT_KEY}`,      label: 'Security Hotspots' },
  { name: '04_measures_metricas.png',             url: `/component_measures?id=${SONAR_PROJECT_KEY}`,     label: 'Measures / Métricas' },
  { name: '05_coverage_detail.png',               url: `/component_measures?id=${SONAR_PROJECT_KEY}&metric=coverage`, label: 'Coverage detail' },
  { name: '06_duplications.png',                  url: `/component_measures?id=${SONAR_PROJECT_KEY}&metric=duplicated_lines_density`, label: 'Duplications' },
];

function fatal(msg)  { console.error(`[ERROR] ${msg}`);  process.exit(1); }
function info(msg)   { console.log(`[INFO] ${msg}`);  }
function ok(msg)     { console.log(`[OK]   ${msg}`);  }
function warn(msg)   { console.warn(`[WARN] ${msg}`); }

/** Return true if current page looks like a SonarQube dashboard (not login). */
async function isOnDashboard(page) {
  try {
    const url = page.url();
    if (url.includes('/sessions/new') || url.includes('/login')) return false;

    const body = await page.textContent('body').catch(() => '');
    const hasProjectName = body.includes(SONAR_PROJECT_KEY);

    const layoutSel = await page.$('.layout-page, .page, main, #content, .page-container');
    const hasLayout = !!layoutSel;

    return hasProjectName || hasLayout;
  } catch {
    return false;
  }
}

async function findVisible(page, selectors) {
  for (const sel of selectors) {
    try {
      const el = await page.$(sel);
      if (el && (await el.isVisible().catch(() => false))) return el;
    } catch { /* ignore */ }
  }
  return null;
}

async function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

/**
 * Attempt login. Returns true if login succeeded, false if no form was found
 * (possibly already authenticated), and fatals if login form was found but
 * authentication failed.
 */
async function doLogin(page) {
  info(`Navegando a formulario de login: ${SONAR_URL}/sessions/new`);
  await page.goto(`${SONAR_URL}/sessions/new`, { waitUntil: 'networkidle', timeout: NAV_TIMEOUT });
  await sleep(1000);

  const loginInput = await findVisible(page, [
    '#login', 'input[name="login"]', 'input[name="username"]', 'input[type="text"]',
  ]);
  const passInput = await findVisible(page, [
    '#password', 'input[name="password"]', 'input[type="password"]',
  ]);

  /* ── No hay formulario ─────────────────────────────────────────────── */
  if (!loginInput || !passInput) {
    const debugPath = path.join(DEBUG_DIR, 'debug_login.png');
    await page.screenshot({ path: debugPath, fullPage: true }).catch(() => {});
    const title = await page.title().catch(() => '(sin título)');
    warn(`No se encontró formulario de login. URL: ${page.url()}, título: "${title}"`);
    warn(`Captura de diagnóstico: ${debugPath}`);

    /* Reintentar dashboard por si ya está autenticado */
    await page.goto(`${SONAR_URL}/dashboard?id=${SONAR_PROJECT_KEY}`, {
      waitUntil: 'networkidle', timeout: NAV_TIMEOUT,
    });
    await sleep(1500);

    if (await isOnDashboard(page)) {
      ok('Ya estaba autenticado — se omite login');
      return false;
    }
    fatal('No se pudo determinar el estado de autenticación. Revisa debug_login.png');
  }

  /* ── Hay formulario: validar credenciales ──────────────────────────── */
  if (!SONAR_USER) fatal('Variable SONAR_USER requerida para iniciar sesión');
  if (!SONAR_PASS) fatal('Variable SONAR_PASS requerida para iniciar sesión');

  info('Llenando credenciales ...');
  await loginInput.fill(SONAR_USER);
  await passInput.fill(SONAR_PASS);
  await sleep(300);

  /* Buscar botón submit */
  const btn = (await findVisible(page, ['button[type="submit"]', 'input[type="submit"]']))
    || (await findVisible(page, [
      'button:has-text("Log in")', 'button:has-text("Login")',
      'button:has-text("Iniciar sesión")', 'button:has-text("Sign in")',
      'input[value="Log in"]', 'input[value="Login"]',
    ]));

  if (!btn) fatal('No se encontró botón de login');

  info('Enviando formulario de login ...');
  await btn.click();

  /* Esperar a que la navegación termine completamente */
  await page.waitForLoadState('networkidle', { timeout: NAV_TIMEOUT }).catch(() => {});
  await sleep(1500);

  /* ── Validar que el login fue exitoso ──────────────────────────────── */
  const currentUrl = page.url();
  const stillOnLogin = currentUrl.includes('/sessions/new') || currentUrl.includes('/login');
  const formStillVisible = await findVisible(page, ['#login', 'input[name="login"]', '#password']);

  if (stillOnLogin || formStillVisible) {
    /* Login falló — tomar diagnóstico detallado */
    const debugPath = path.join(DEBUG_DIR, 'debug_login_failed.png');
    await page.screenshot({ path: debugPath, fullPage: true }).catch(() => {});
    const title = await page.title().catch(() => '(sin título)');
    const hasUser = !!(await findVisible(page, ['#login', 'input[name="login"]', 'input[name="username"]']));
    const hasPass = !!(await findVisible(page, ['#password', 'input[name="password"]']));
    const errorText = await page.textContent('body').catch(() => '');
    const hasErrorMsg = /(incorrect|invalid|error|fail|wrong|bad credentials)/i.test(errorText);

    warn('══════════════════ DIAGNÓSTICO DE LOGIN ══════════════════');
    warn(`URL actual       : ${currentUrl}`);
    warn(`Título           : ${title}`);
    warn(`Input usuario    : ${hasUser ? 'visible' : 'NO visible'}`);
    warn(`Input password   : ${hasPass ? 'visible' : 'NO visible'}`);
    warn(`Mensaje error    : ${hasErrorMsg ? 'detectado en la página' : 'no se detectó'}`);
    warn(`Captura diagnóst.: ${debugPath}`);
    warn('═══════════════════════════════════════════════════════════');

    fatal('No se pudo iniciar sesión en SonarQube');
  }

  /* Navegar explícitamente al dashboard para confirmar */
  await page.goto(`${SONAR_URL}/dashboard?id=${SONAR_PROJECT_KEY}`, {
    waitUntil: 'networkidle', timeout: NAV_TIMEOUT,
  });
  await sleep(1500);

  if (!(await isOnDashboard(page))) {
    fatal('Login completado pero no se pudo acceder al dashboard del proyecto');
  }

  ok('Sesión iniciada correctamente');
  return true;
}

async function ensureAuthenticated(page) {
  info(`Verificando autenticación en ${SONAR_URL} ...`);
  await page.goto(`${SONAR_URL}/dashboard?id=${SONAR_PROJECT_KEY}`, {
    waitUntil: 'networkidle', timeout: NAV_TIMEOUT,
  });
  await sleep(1500);

  if (await isOnDashboard(page)) {
    ok('Ya está autenticado en SonarQube');
    return;
  }

  await doLogin(page);
}

async function takeScreenshot(page, { name, url, label, beforeCapture }) {
  const fullUrl = `${SONAR_URL}${url}`;
  const filePath = path.join(OUTPUT_DIR, name);

  try {
    info(`Navegando a: ${label} ...`);
    await page.goto(fullUrl, { waitUntil: 'networkidle', timeout: NAV_TIMEOUT });
    await sleep(1500);

    /* Si fue redirigido a login, no guardar esta captura */
    if (page.url().includes('/sessions/new') || page.url().includes('/login')) {
      warn(`Redirigido a login en "${label}" — no se guarda captura`);
      return;
    }

    /* Ejecutar acción previa a la captura si está definida */
    if (beforeCapture) {
      await beforeCapture(page);
    }

    /* Esperar elemento estable */
    const stableSel = await findVisible(page, [
      '.layout-page', '.page', 'main', '#content', '.page-container',
      '[data-test="project"]', '.overview', '.issues', '.hotspot', '.measure-details',
    ]);
    if (stableSel) {
      await stableSel.waitForElementState('visible', { timeout: 5000 }).catch(() => {});
    }

    await page.screenshot({ path: filePath, fullPage: true });
    ok(`Captura guardada: ${name}`);
  } catch (err) {
    warn(`No se pudo capturar "${label}" (${name}): ${err.message}`);
    try {
      await page.screenshot({ path: filePath, fullPage: true });
      ok(`Captura (fallback) guardada: ${name}`);
    } catch {
      warn(`No se pudo guardar ni el fallback para: ${name}`);
    }
  }
}

async function main() {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
  info(`Directorio de salida: ${OUTPUT_DIR}`);
  info(`Modo headless: ${HEADLESS}`);

  const browser = await chromium.launch({ headless: HEADLESS });
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    locale: 'es-PE',
    timezoneId: 'America/Lima',
  });
  const page = await context.newPage();

  let exitCode = 0;

  try {
    await ensureAuthenticated(page);

    for (const p of PAGES) {
      await takeScreenshot(page, p);
    }
  } catch (err) {
    warn(`Error inesperado durante la ejecución: ${err.message}`);
    exitCode = 1;
  } finally {
    await browser.close();
  }

  const files = fs.readdirSync(OUTPUT_DIR).filter((f) => f.endsWith('.png'));
  console.log('');
  info('=== RESUMEN ===');
  info(`Directorio: ${OUTPUT_DIR}`);
  info(`Total de capturas: ${files.length}`);
  for (const f of files.sort()) {
    const stat = fs.statSync(path.join(OUTPUT_DIR, f));
    info(`  ${f}  (${(stat.size / 1024).toFixed(1)} KB)`);
  }

  if (exitCode !== 0) {
    warn('El script finalizó con algunos errores. Revisa los mensajes anteriores.');
  }

  process.exit(exitCode);
}

main();
