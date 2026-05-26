import AlertasPanel from './components/alertas/AlertasPanel';
import AppLayout from './components/layout/AppLayout';
import Header from './components/layout/Header';
import Sidebar from './components/layout/Sidebar';
import DashboardPanel from './components/DashboardPanel';
import AsistenciaMasivaPanel from './components/academico/AsistenciaMasivaPanel';
import NotasMasivasPanel from './components/academico/NotasMasivasPanel';
import MateriasPanel from './components/materias/MateriasPanel';
import EstudiantesPanel from './components/estudiantes/EstudiantesPanel';
import MallaCurricularPanel from './components/curricular/MallaCurricularPanel';
import TemasSemanalesPanel from './components/curricular/TemasSemanalesPanel';
import PesosEvaluacionPanel from './components/curricular/PesosEvaluacionPanel';
import AsignacionDocentePanel from './components/curricular/AsignacionDocentePanel';
import RegistroNotasSemanalesPanel from './components/curricular/RegistroNotasSemanalesPanel';
import ConfiguracionBimestralPanel from './components/curricular/configuracion-bimestral/ConfiguracionBimestralPanel';
import LoginForm from './components/LoginForm';
import Card from './components/ui/Card';
import LoadingState from './components/ui/LoadingState';
import { useAuth } from './context/AuthContext';
import { useEffect, useMemo, useState } from 'react';

const SIDEBAR_COLLAPSED_KEY = 'siderae-sidebar-collapsed';

function leerSidebarColapsada() {
  try {
    return localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === 'true';
  } catch {
    return false;
  }
}

function esAdministrador(roles) {
  return (roles ?? []).includes('administrador');
}

function moduloPermitido(key, permissions, roles) {
  const admin = esAdministrador(roles);

  switch (key) {
    case 'dashboard':
      return permissions.includes('ver_dashboard');
    case 'estudiantes':
      return permissions.includes('gestionar_estudiantes');
    case 'alertas':
      return permissions.includes('ver_alertas');
    case 'materias':
      return permissions.includes('gestionar_materias') && (admin || !permissions.includes('gestionar_malla_curricular'));
    case 'asistencia':
      return permissions.includes('registrar_datos_academicos') && (admin || !permissions.includes('gestionar_malla_curricular'));
    case 'notas':
      return (
        permissions.includes('registrar_datos_academicos')
        && !permissions.includes('registrar_notas_semanales')
        && (admin || !permissions.includes('gestionar_malla_curricular'))
      );
    case 'curricular_malla':
      return permissions.includes('ver_malla_curricular') || permissions.includes('gestionar_malla_curricular');
    case 'curricular_temas':
      return permissions.includes('gestionar_temas_semanales');
    case 'curricular_pesos':
      return permissions.includes('configurar_pesos_evaluacion');
    case 'curricular_eval_bim':
      return permissions.includes('configurar_evaluacion_bimestral');
    case 'curricular_asignacion':
      return permissions.includes('gestionar_asignaciones_docente');
    case 'curricular_notas':
      return (
        permissions.includes('registrar_notas_semanales')
        || permissions.includes('gestionar_asignaciones_docente')
        || roles.includes('directivo')
      );
    default:
      return false;
  }
}

function moduloPorDefecto(permissions, roles) {
  if (permissions.includes('registrar_notas_semanales') && !permissions.includes('ver_dashboard')) {
    return 'curricular_notas';
  }
  if (permissions.includes('ver_dashboard')) {
    return 'dashboard';
  }
  if (permissions.includes('gestionar_malla_curricular') || permissions.includes('ver_malla_curricular')) {
    return 'curricular_malla';
  }
  if (permissions.includes('gestionar_materias') && moduloPermitido('materias', permissions, roles)) {
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
  const titulos = {
    dashboard: 'Dashboard',
    estudiantes: 'Estudiantes',
    alertas: 'Alertas',
    materias: 'Materias (legacy)',
    asistencia: 'Asistencia masiva',
    notas: 'Notas masivas',
    curricular_malla: 'Malla curricular',
    curricular_temas: 'Criterios de evaluación',
    curricular_pesos: 'Pesos C/L/T',
    curricular_eval_bim: 'Configuración bimestral',
    curricular_asignacion: 'Asignación docente',
    curricular_notas: 'Notas semanales',
  };
  return titulos[key] ?? 'SIDERAE-Blenkir';
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
  const [sidebarCollapsed, setSidebarCollapsed] = useState(leerSidebarColapsada);
  const [moduloActivo, setModuloActivo] = useState('dashboard');

  const moduloVista =
    moduloActivo != null && moduloPermitido(moduloActivo, permissions, roles)
      ? moduloActivo
      : moduloPorDefecto(permissions, roles);

  const etiquetaSesion = etiquetaSesionAmigable(roles ?? []);
  const admin = esAdministrador(roles);
  const muestraLegacy = admin && permissions.some((p) => ['gestionar_materias', 'registrar_datos_academicos'].includes(p));

  useEffect(() => {
    if (!authUser) {
      return;
    }
    setModuloActivo((prev) =>
      moduloPermitido(prev, permissions, roles) ? prev : (moduloPorDefecto(permissions, roles) ?? prev),
    );
  }, [authUser, permissions, roles]);

  useEffect(() => {
    try {
      localStorage.setItem(SIDEBAR_COLLAPSED_KEY, String(sidebarCollapsed));
    } catch {
      /* almacenamiento no disponible */
    }
  }, [sidebarCollapsed]);

  const navItems = useMemo(() => {
    const items = [
      {
        key: 'dashboard',
        label: 'Dashboard',
        visible: moduloPermitido('dashboard', permissions, roles),
        active: moduloVista === 'dashboard',
        onSelect: () => setModuloActivo('dashboard'),
      },
      {
        key: 'estudiantes',
        label: 'Estudiantes',
        visible: moduloPermitido('estudiantes', permissions, roles),
        active: moduloVista === 'estudiantes',
        onSelect: () => setModuloActivo('estudiantes'),
      },
      {
        key: 'curricular_malla',
        label: 'Malla curricular',
        visible: moduloPermitido('curricular_malla', permissions, roles),
        active: moduloVista === 'curricular_malla',
        onSelect: () => setModuloActivo('curricular_malla'),
      },
      {
        key: 'curricular_temas',
        label: 'Criterios de evaluación',
        visible: moduloPermitido('curricular_temas', permissions, roles),
        active: moduloVista === 'curricular_temas',
        onSelect: () => setModuloActivo('curricular_temas'),
      },
      {
        key: 'curricular_pesos',
        label: 'Pesos evaluación',
        visible: moduloPermitido('curricular_pesos', permissions, roles),
        active: moduloVista === 'curricular_pesos',
        onSelect: () => setModuloActivo('curricular_pesos'),
      },
      {
        key: 'curricular_eval_bim',
        label: 'Configuración bimestral',
        visible: moduloPermitido('curricular_eval_bim', permissions, roles),
        active: moduloVista === 'curricular_eval_bim',
        onSelect: () => setModuloActivo('curricular_eval_bim'),
      },
      {
        key: 'curricular_asignacion',
        label: 'Asignación docente',
        visible: moduloPermitido('curricular_asignacion', permissions, roles),
        active: moduloVista === 'curricular_asignacion',
        onSelect: () => setModuloActivo('curricular_asignacion'),
      },
      {
        key: 'curricular_notas',
        label: 'Notas semanales',
        visible: moduloPermitido('curricular_notas', permissions, roles),
        active: moduloVista === 'curricular_notas',
        onSelect: () => setModuloActivo('curricular_notas'),
      },
      {
        key: 'alertas',
        label: 'Alertas',
        visible: moduloPermitido('alertas', permissions, roles),
        active: moduloVista === 'alertas',
        onSelect: () => setModuloActivo('alertas'),
      },
    ];

    if (muestraLegacy) {
      items.push(
        {
          key: 'legacy_divider',
          label: 'Datos académicos (legacy)',
          visible: true,
          disabled: true,
          dividerBefore: true,
          onSelect: () => {},
        },
        {
          key: 'asistencia',
          label: 'Asistencia',
          visible: moduloPermitido('asistencia', permissions, roles),
          active: moduloVista === 'asistencia',
          onSelect: () => setModuloActivo('asistencia'),
        },
        {
          key: 'notas',
          label: 'Notas masivas',
          visible: moduloPermitido('notas', permissions, roles),
          active: moduloVista === 'notas',
          onSelect: () => setModuloActivo('notas'),
        },
        {
          key: 'materias',
          label: 'Materias',
          visible: moduloPermitido('materias', permissions, roles),
          active: moduloVista === 'materias',
          onSelect: () => setModuloActivo('materias'),
        },
      );
    }

    return items;
  }, [permissions, roles, moduloVista, muestraLegacy]);

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
      mainClassName={
        moduloVista === 'curricular_notas'
          ? 'min-h-0 flex-1 overflow-y-auto bg-[var(--surface)] px-4 pb-4 pt-0 sm:px-6 lg:px-10 lg:pb-6 lg:pt-0'
          : undefined
      }
      sidebar={
        <Sidebar
          mobileOpen={sidebarOpen}
          onCloseMobile={() => setSidebarOpen(false)}
          collapsed={sidebarCollapsed}
          onToggleCollapsed={() => setSidebarCollapsed((prev) => !prev)}
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
      <div
        className={`mx-auto flex w-full flex-col ${
          moduloVista === 'curricular_notas' ? 'w-full max-w-none gap-0' : 'max-w-6xl gap-8'
        }`}
        data-testid="workspace-main"
      >
        {moduloVista === null ? (
          <Card className="border-[var(--border)] bg-[var(--surface)] p-8 text-center shadow-card">
            <h1 className="text-lg font-semibold text-[var(--text)]">Sin módulos asignados</h1>
            <p className="mt-2 text-sm text-muted">
              Esta cuenta no tiene permisos asignados. Solicite la asignación de rol al administrador del sistema.
            </p>
          </Card>
        ) : (
          <>
            <h1 className="sr-only">{tituloModulo(moduloVista)}</h1>

            {moduloVista === 'dashboard' ? <DashboardPanel /> : null}
            {moduloVista === 'estudiantes' ? <EstudiantesPanel /> : null}
            {moduloVista === 'curricular_malla' ? <MallaCurricularPanel /> : null}
            {moduloVista === 'curricular_temas' ? <TemasSemanalesPanel /> : null}
            {moduloVista === 'curricular_pesos' ? <PesosEvaluacionPanel /> : null}
            {moduloVista === 'curricular_eval_bim' ? <ConfiguracionBimestralPanel /> : null}
            {moduloVista === 'curricular_asignacion' ? <AsignacionDocentePanel /> : null}
            {moduloVista === 'curricular_notas' ? <RegistroNotasSemanalesPanel /> : null}
            {moduloVista === 'asistencia' ? <AsistenciaMasivaPanel /> : null}
            {moduloVista === 'notas' ? <NotasMasivasPanel /> : null}
            {moduloVista === 'materias' ? <MateriasPanel /> : null}
            {moduloVista === 'alertas' ? <AlertasPanel /> : null}
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
