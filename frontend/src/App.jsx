import AlertasPanel from './components/alertas/AlertasPanel';
import AppLayout from './components/layout/AppLayout';
import Header from './components/layout/Header';
import Sidebar from './components/layout/Sidebar';
import DashboardPanel from './components/DashboardPanel';
import EstudiantesPanel from './components/estudiantes/EstudiantesPanel';
import LoginForm from './components/LoginForm';
import Card from './components/ui/Card';
import Badge from './components/ui/Badge';
import AlertMessage from './components/ui/AlertMessage';
import Button from './components/ui/Button';
import LoadingState from './components/ui/LoadingState';
import { useAuth } from './context/AuthContext';
import { useEffect, useMemo, useState } from 'react';

function moduloPermitido(key, permissions) {
  switch (key) {
    case 'dashboard':
    case 'reportes':
      return permissions.includes('ver_dashboard');
    case 'estudiantes':
      return permissions.includes('gestionar_estudiantes');
    case 'alertas':
    case 'intervenciones':
      return permissions.includes('ver_alertas');
    case 'configuracion':
      return true;
    default:
      return false;
  }
}

function moduloPorDefecto(permissions) {
  if (permissions.includes('ver_dashboard')) {
    return 'dashboard';
  }
  if (permissions.includes('gestionar_estudiantes')) {
    return 'estudiantes';
  }
  if (permissions.includes('ver_alertas')) {
    return 'alertas';
  }
  return 'configuracion';
}

function tituloModulo(key) {
  switch (key) {
    case 'dashboard':
      return 'Dashboard';
    case 'estudiantes':
      return 'Estudiantes';
    case 'alertas':
      return 'Alertas';
    case 'reportes':
      return 'Reportes';
    case 'intervenciones':
      return 'Intervenciones';
    case 'configuracion':
      return 'Configuración';
    default:
      return 'SIDERAE-Blenkir';
  }
}

function descripcionModulo(key) {
  switch (key) {
    case 'dashboard':
      return 'Indicadores de riesgo y alertas; exportación PDF disponible desde esta vista.';
    case 'estudiantes':
      return 'Gestión del estudiantado: listado, registro y perfil con datos académicos y riesgo.';
    case 'alertas':
      return 'Alertas tempranas, detalle y acciones registradas.';
    case 'reportes':
      return 'Los reportes en PDF disponibles corresponden a la exportación del Dashboard (Sprint 6B).';
    case 'intervenciones':
      return 'Las intervenciones se registran en el detalle de cada alerta abierta.';
    case 'configuracion':
      return 'Preferencias institucionales del sistema.';
    default:
      return '';
  }
}

function ModuloReportes({ onIrDashboard }) {
  return (
    <Card className="max-w-2xl space-y-5 border-[var(--secondary)]/35 bg-[#f5f9fd]" data-testid="module-reportes">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <h2 className="text-xl font-semibold text-[var(--text)]">Reportes</h2>
        <Badge variant="info" className="normal-case">
          Estado controlado — sin módulo adicional
        </Badge>
      </div>
      <AlertMessage variant="info">
        Esta pantalla no genera archivos nuevos: el único PDF de reportes del prototipo se obtiene en el Dashboard con
        los mismos filtros activos (<strong className="font-medium">Sprint 6B</strong>).
      </AlertMessage>
      <ol className="list-inside list-decimal space-y-2 text-sm text-[var(--text)]">
        <li>Abra la vista Dashboard desde el menú lateral.</li>
        <li>Ajuste filtros si lo necesita y pulse Aplicar.</li>
        <li>Use el botón Exportar PDF — la descarga la produce el servidor (DomPDF).</li>
      </ol>
      <div className="flex flex-wrap gap-2 pt-1">
        <Button type="button" variant="primary" size="sm" onClick={onIrDashboard} data-testid="reportes-ir-dashboard">
          Ir al Dashboard
        </Button>
      </div>
    </Card>
  );
}

function ModuloIntervenciones({ onIrAlertas }) {
  return (
    <Card className="max-w-2xl space-y-5 border-primary/35 bg-orange-50/40" data-testid="module-intervenciones">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <h2 className="text-xl font-semibold text-[var(--text)]">Intervenciones</h2>
        <Badge variant="primary" className="normal-case">
          Flujo real vía Alertas
        </Badge>
      </div>
      <AlertMessage variant="warning">
        No existe un listado ficticio de intervenciones en este prototipo: el registro válido sólo aparece en el detalle de
        una alerta y depende del permiso <span className="font-mono text-xs">registrar_intervencion</span>.
      </AlertMessage>
      <ol className="list-inside list-decimal space-y-2 text-sm text-[var(--text)]">
        <li>Abra Alertas desde el menú lateral.</li>
        <li>Pulse Ver alerta sobre un caso pendiente o en atención.</li>
        <li>Complete el bloque Registrar intervención o Cerrar alerta cuando corresponda.</li>
      </ol>
      <Button type="button" variant="primary" size="sm" onClick={onIrAlertas} data-testid="intervenciones-ir-alertas">
        Ir a Alertas
      </Button>
    </Card>
  );
}

function ModuloConfiguracion() {
  return (
    <Card
      className="max-w-2xl space-y-4 border-2 border-dashed border-amber-300 bg-amber-50/70"
      data-testid="module-configuracion"
    >
      <div className="flex flex-wrap items-start justify-between gap-2">
        <h2 className="text-xl font-semibold text-[var(--text)]">Configuración</h2>
        <Badge variant="warning" className="normal-case">
          Pendiente de desarrollo
        </Badge>
      </div>
      <AlertMessage variant="warning">
        No hay pantallas de configuración institucional, parámetros de riesgo editables ni gestión extendida en este sprint.
        Tampoco se crean permisos ni endpoints nuevos: lo visible aquí sólo comunica una limitación conocida del prototipo.
      </AlertMessage>
      <ul className="list-inside list-disc space-y-1 text-sm text-muted">
        <li>Los roles y permisos efectivos provienen de la cuenta asignada (ver desplegable en el área de trabajo).</li>
        <li>Para seguridad endurecida y matriz rol–endpoint, consulte planificación Sprint 8.</li>
      </ul>
    </Card>
  );
}

function App() {
  const { authUser, roles, permissions, logout, isLoading, error } = useAuth();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [moduloActivo, setModuloActivo] = useState('dashboard');

  const moduloVista =
    moduloActivo != null && moduloPermitido(moduloActivo, permissions)
      ? moduloActivo
      : moduloPorDefecto(permissions);

  const roleSummary =
    roles && roles.length > 0
      ? `Roles: ${roles.slice(0, 3).join(', ')}${roles.length > 3 ? '…' : ''}`
      : 'Sesión institucional';

  useEffect(() => {
    if (!authUser) {
      return;
    }
    setModuloActivo((prev) => (moduloPermitido(prev, permissions) ? prev : moduloPorDefecto(permissions)));
  }, [authUser, permissions]);

  const navItems = useMemo(
    () => [
      {
        key: 'dashboard',
        label: 'Dashboard',
        visible: moduloPermitido('dashboard', permissions),
        active: moduloVista === 'dashboard',
        onSelect: () => setModuloActivo('dashboard'),
      },
      {
        key: 'estudiantes',
        label: 'Estudiantes',
        visible: moduloPermitido('estudiantes', permissions),
        active: moduloVista === 'estudiantes',
        onSelect: () => setModuloActivo('estudiantes'),
      },
      {
        key: 'alertas',
        label: 'Alertas',
        visible: moduloPermitido('alertas', permissions),
        active: moduloVista === 'alertas',
        onSelect: () => setModuloActivo('alertas'),
      },
      {
        key: 'intervenciones',
        label: 'Intervenciones',
        visible: moduloPermitido('intervenciones', permissions),
        active: moduloVista === 'intervenciones',
        onSelect: () => setModuloActivo('intervenciones'),
      },
      {
        key: 'reportes',
        label: 'Reportes',
        visible: moduloPermitido('reportes', permissions),
        active: moduloVista === 'reportes',
        onSelect: () => setModuloActivo('reportes'),
      },
      {
        key: 'configuracion',
        label: 'Configuración',
        visible: true,
        active: moduloVista === 'configuracion',
        dividerBefore: true,
        onSelect: () => setModuloActivo('configuracion'),
      },
    ],
    [permissions, moduloVista],
  );

  if (isLoading && !authUser) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-background px-6">
        <LoadingState label="Validando sesión…" />
      </div>
    );
  }

  if (!authUser) {
    return <LoginForm />;
  }

  return (
    <AppLayout
      sidebar={
        <Sidebar
          mobileOpen={sidebarOpen}
          onCloseMobile={() => setSidebarOpen(false)}
          navItems={navItems}
        />
      }
      header={
        <Header
          email={authUser.email}
          roleSummary={roleSummary}
          onLogout={logout}
          onOpenNav={() => setSidebarOpen(true)}
        />
      }
    >
      <div className="mx-auto flex w-full max-w-6xl flex-col gap-8" data-testid="workspace-main">
        <div className="rounded-lg border border-[var(--border)] bg-[var(--surface)] px-5 py-5 shadow-card sm:px-6">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div className="min-w-0">
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">Vista activa</p>
              <h1 className="text-2xl font-semibold tracking-tight text-[var(--text)]">{tituloModulo(moduloVista)}</h1>
              <p className="mt-1 text-sm text-muted">{descripcionModulo(moduloVista)}</p>
            </div>
          </div>
        </div>

        <details className="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 text-sm shadow-card sm:p-6">
          <summary className="cursor-pointer select-none text-sm font-medium text-[var(--text)]">
            Roles y permisos del usuario actual
          </summary>
          <div className="mt-4 space-y-3 text-muted">
            <div>
              <p className="font-semibold text-[var(--text)]">Roles</p>
              <ul className="mt-1 list-inside list-disc">
                {roles.map((role) => (
                  <li key={role}>{role}</li>
                ))}
              </ul>
            </div>
            <div>
              <p className="font-semibold text-[var(--text)]">Permisos activos</p>
              <ul className="mt-1 list-inside list-disc text-xs md:text-sm">
                {permissions.map((permission) => (
                  <li key={permission}>{permission}</li>
                ))}
              </ul>
            </div>
          </div>
        </details>

        {moduloVista === 'dashboard' && moduloPermitido('dashboard', permissions) ? (
          <DashboardPanel />
        ) : null}

        {moduloVista === 'estudiantes' && moduloPermitido('estudiantes', permissions) ? <EstudiantesPanel /> : null}

        {moduloVista === 'alertas' && moduloPermitido('alertas', permissions) ? <AlertasPanel /> : null}

        {moduloVista === 'reportes' && moduloPermitido('reportes', permissions) ? (
          <ModuloReportes onIrDashboard={() => setModuloActivo('dashboard')} />
        ) : null}

        {moduloVista === 'intervenciones' && moduloPermitido('intervenciones', permissions) ? (
          <ModuloIntervenciones onIrAlertas={() => setModuloActivo('alertas')} />
        ) : null}

        {moduloVista === 'configuracion' ? <ModuloConfiguracion /> : null}

        {error ? <p className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">{error}</p> : null}
      </div>
    </AppLayout>
  );
}

export default App;
