import AlertasPanel from './components/alertas/AlertasPanel';
import AppLayout from './components/layout/AppLayout';
import Header from './components/layout/Header';
import Sidebar from './components/layout/Sidebar';
import DashboardPanel from './components/DashboardPanel';
import EstudiantesPanel from './components/estudiantes/EstudiantesPanel';
import LoginForm from './components/LoginForm';
import LoadingState from './components/ui/LoadingState';
import { useAuth } from './context/AuthContext';
import { useState } from 'react';

function App() {
  const { authUser, roles, permissions, logout, isLoading, error } = useAuth();
  const [mostrarEstudiantes, setMostrarEstudiantes] = useState(false);
  const [mostrarAlertas, setMostrarAlertas] = useState(false);
  const [mostrarDashboard, setMostrarDashboard] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(false);

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

  const roleSummary =
    roles && roles.length > 0
      ? `Roles: ${roles.slice(0, 3).join(', ')}${roles.length > 3 ? '…' : ''}`
      : 'Sesión institucional';

  const navItems = [
    {
      key: 'dashboard',
      label: 'Dashboard',
      visible: permissions.includes('ver_dashboard'),
      active: mostrarDashboard,
      onSelect: () => setMostrarDashboard((valor) => !valor),
    },
    {
      key: 'estudiantes',
      label: 'Estudiantes',
      visible: permissions.includes('gestionar_estudiantes'),
      active: mostrarEstudiantes,
      onSelect: () => setMostrarEstudiantes((valor) => !valor),
    },
    {
      key: 'alertas',
      label: 'Alertas',
      visible: permissions.includes('ver_alertas'),
      active: mostrarAlertas,
      onSelect: () => setMostrarAlertas((valor) => !valor),
    },
  ];

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
      <div className="mx-auto flex max-w-6xl flex-col gap-6">
        <div className="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-card">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div className="min-w-0">
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">
                Centro de trabajo
              </p>
              <h1 className="text-2xl font-semibold tracking-tight text-[var(--text)]">
                SIDERAE-Blenkir
              </h1>
              <p className="mt-1 text-sm text-muted">
                Paneles funcionales siguen igual; solo cambia el contenedor visual y la navegación lateral.
              </p>
            </div>

            <div className="flex flex-wrap gap-2 text-xs text-muted">
              {permissions.includes('registrar_datos_academicos') ? (
                <span className="rounded-full bg-background px-2 py-1">Datos Académicos</span>
              ) : null}
              {permissions.includes('procesar_riesgo') ? (
                <span className="rounded-full bg-background px-2 py-1">Procesar riesgo</span>
              ) : null}
              {permissions.includes('registrar_intervencion') ? (
                <span className="rounded-full bg-background px-2 py-1">Intervenciones</span>
              ) : null}
            </div>
          </div>
        </div>

        <details className="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-4 text-sm shadow-card">
          <summary className="cursor-pointer select-none text-sm font-medium text-[var(--text)]">
            Ver lista detallada de roles y permisos
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

        {permissions.includes('ver_dashboard') && mostrarDashboard ? <DashboardPanel /> : null}

        {permissions.includes('gestionar_estudiantes') && mostrarEstudiantes ? (
          <EstudiantesPanel onClose={() => setMostrarEstudiantes(false)} />
        ) : null}

        {permissions.includes('ver_alertas') && mostrarAlertas ? (
          <AlertasPanel onClose={() => setMostrarAlertas(false)} />
        ) : null}

        {error ? <p className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">{error}</p> : null}
      </div>
    </AppLayout>
  );
}

export default App;
