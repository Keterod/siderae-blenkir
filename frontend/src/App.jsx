import AlertasPanel from './components/alertas/AlertasPanel';
import AppLayout from './components/layout/AppLayout';
import Header from './components/layout/Header';
import Sidebar from './components/layout/Sidebar';
import DashboardPanel from './components/DashboardPanel';
import AsistenciaMasivaPanel from './components/academico/AsistenciaMasivaPanel';
import NotasMasivasPanel from './components/academico/NotasMasivasPanel';
import MateriasPanel from './components/materias/MateriasPanel';
import EstudiantesPanel from './components/estudiantes/EstudiantesPanel';
import LoginForm from './components/LoginForm';
import Card from './components/ui/Card';
import LoadingState from './components/ui/LoadingState';
import { useAuth } from './context/AuthContext';
import { useEffect, useMemo, useState } from 'react';

function moduloPermitido(key, permissions) {
  switch (key) {
    case 'dashboard':
      return permissions.includes('ver_dashboard');
    case 'estudiantes':
      return permissions.includes('gestionar_estudiantes');
    case 'alertas':
      return permissions.includes('ver_alertas');
    case 'materias':
      return permissions.includes('gestionar_materias');
    case 'asistencia':
      return permissions.includes('registrar_datos_academicos');
    case 'notas':
      return permissions.includes('registrar_datos_academicos');
    default:
      return false;
  }
}

function moduloPorDefecto(permissions) {
  if (permissions.includes('ver_dashboard')) {
    return 'dashboard';
  }
  if (permissions.includes('gestionar_materias')) {
    return 'materias';
  }
  if (permissions.includes('gestionar_estudiantes')) {
    return 'estudiantes';
  }
  if (permissions.includes('ver_alertas')) {
    return 'alertas';
  }
  return null;
}

function tituloModulo(key) {
  switch (key) {
    case 'dashboard':
      return 'Dashboard';
    case 'estudiantes':
      return 'Estudiantes';
    case 'alertas':
      return 'Alertas';
    case 'materias':
      return 'Materias';
    case 'asistencia':
      return 'Asistencia';
    case 'notas':
      return 'Notas';
    default:
      return 'SIDERAE-Blenkir';
  }
}

function etiquetaSesionAmigable(roles) {
  if (!roles || roles.length === 0) {
    return null;
  }
  const etiquetas = {
    administrador: 'Administración del sistema',
    docente: 'Docente',
    coordinador_academico: 'Coordinación académica',
    psicologo_tutor: 'Psicología / tutoría',
    directivo: 'Dirección',
  };
  const primero = roles[0];
  return etiquetas[primero] ?? `Perfil: ${primero.replace(/_/g, ' ')}`;
}

function App() {
  const { authUser, roles, permissions, logout, isLoading, error } = useAuth();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [moduloActivo, setModuloActivo] = useState('dashboard');

  const moduloVista =
    moduloActivo != null && moduloPermitido(moduloActivo, permissions) ? moduloActivo : moduloPorDefecto(permissions);

  const etiquetaSesion = etiquetaSesionAmigable(roles ?? []);

  useEffect(() => {
    if (!authUser) {
      return;
    }
    setModuloActivo((prev) =>
      moduloPermitido(prev, permissions) ? prev : (moduloPorDefecto(permissions) ?? prev),
    );
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
        key: 'asistencia',
        label: 'Asistencia',
        visible: moduloPermitido('asistencia', permissions),
        active: moduloVista === 'asistencia',
        onSelect: () => setModuloActivo('asistencia'),
      },
      {
        key: 'notas',
        label: 'Notas',
        visible: moduloPermitido('notas', permissions),
        active: moduloVista === 'notas',
        onSelect: () => setModuloActivo('notas'),
      },
      {
        key: 'materias',
        label: 'Materias',
        visible: moduloPermitido('materias', permissions),
        active: moduloVista === 'materias',
        onSelect: () => setModuloActivo('materias'),
      },
      {
        key: 'alertas',
        label: 'Alertas',
        visible: moduloPermitido('alertas', permissions),
        active: moduloVista === 'alertas',
        onSelect: () => setModuloActivo('alertas'),
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
          etiquetaSesion={etiquetaSesion}
          onLogout={logout}
          onOpenNav={() => setSidebarOpen(true)}
        />
      }
    >
      <div className="mx-auto flex w-full max-w-6xl flex-col gap-8" data-testid="workspace-main">
        {moduloVista === null ? (
          <Card className="border-[var(--border)] bg-[var(--surface)] p-8 text-center shadow-card">
            <h1 className="text-lg font-semibold text-[var(--text)]">Sin módulos asignados</h1>
            <p className="mt-2 text-sm text-muted">
              Esta cuenta no tiene permisos para Dashboard, estudiantes o alertas. Solicite la asignación de rol al
              administrador del sistema.
            </p>
          </Card>
        ) : (
          <>
            <h1 className="sr-only">{tituloModulo(moduloVista)}</h1>

            {moduloVista === 'dashboard' && moduloPermitido('dashboard', permissions) ? (
              <DashboardPanel />
            ) : null}

            {moduloVista === 'estudiantes' && moduloPermitido('estudiantes', permissions) ? <EstudiantesPanel /> : null}

            {moduloVista === 'asistencia' && moduloPermitido('asistencia', permissions) ? (
              <AsistenciaMasivaPanel />
            ) : null}

            {moduloVista === 'notas' && moduloPermitido('notas', permissions) ? <NotasMasivasPanel /> : null}

            {moduloVista === 'materias' && moduloPermitido('materias', permissions) ? <MateriasPanel /> : null}

            {moduloVista === 'alertas' && moduloPermitido('alertas', permissions) ? <AlertasPanel /> : null}
          </>
        )}

        {error ? (
          <p className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">{error}</p>
        ) : null}
      </div>
    </AppLayout>
  );
}

export default App;
