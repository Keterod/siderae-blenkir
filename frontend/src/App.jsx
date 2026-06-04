import { lazy, Suspense, useEffect, useMemo, useState } from 'react';
import AppLayout from './components/layout/AppLayout';
import Header from './components/layout/Header';
import Sidebar from './components/layout/Sidebar';
import LoginForm from './components/LoginForm';
import Card from './components/ui/Card';
import LoadingState from './components/ui/LoadingState';
import { useAuth } from './context/AuthContext';
import { EVENTO_ABRIR_COMPETENCIAS } from './lib/api';

const DashboardPanel = lazy(() => import('./components/DashboardPanel'));
const EstudiantesPanel = lazy(() => import('./components/estudiantes/EstudiantesPanel'));
const MallaCurricularPanel = lazy(() => import('./components/curricular/MallaCurricularPanel'));
const TemasSemanalesPanel = lazy(() => import('./components/curricular/TemasSemanalesPanel'));
const CompetenciasCapacidadesPanel = lazy(() => import('./components/curricular/CompetenciasCapacidadesPanel'));
const PesosEvaluacionPanel = lazy(() => import('./components/curricular/PesosEvaluacionPanel'));
const ComponentesCalificacionNivelPanel = lazy(() => import('./components/curricular/ComponentesCalificacionNivelPanel'));
const SeccionesAulasPanel = lazy(() => import('./components/curricular/SeccionesAulasPanel'));
const AsignacionDocentePanel = lazy(() => import('./components/curricular/AsignacionDocentePanel'));
const RegistroNotasSemanalesPanel = lazy(() => import('./components/curricular/RegistroNotasSemanalesPanel'));
const ExcelPorAulaPanel = lazy(() => import('./components/curricular/ExcelPorAulaPanel'));
const ConfiguracionBimestralPanel = lazy(() => import('./components/curricular/configuracion-bimestral/ConfiguracionBimestralPanel'));
const PeriodosAcademicosPanel = lazy(() => import('./components/curricular/calendario/PeriodosAcademicosPanel'));
const AsistenciaCurricularPanel = lazy(() => import('./components/curricular/asistencia/AsistenciaCurricularPanel'));
const AlertasPanel = lazy(() => import('./components/alertas/AlertasPanel'));
const UsuariosPanel = lazy(() => import('./components/usuarios/UsuariosPanel'));

const SIDEBAR_COLLAPSED_KEY = 'siderae-sidebar-collapsed';

/** Orden y agrupación visual del menú lateral (sin cambiar permisos ni módulos). */
const SIDEBAR_NAV_GROUPS = [
  { title: 'Inicio', keys: ['dashboard'] },
  {
    title: 'Gestión académica',
    keys: ['estudiantes', 'curricular_notas', 'curricular_excel_aula', 'curricular_asistencia', 'alertas'],
  },
  {
    title: 'Gestión docente y aulas',
    keys: ['curricular_secciones_aulas', 'curricular_asignacion'],
  },
  {
    title: 'Configuración curricular',
    keys: [
      'curricular_malla',
      'curricular_temas',
      'curricular_componentes_calificacion',
      'curricular_eval_bim',
    ],
  },
  {
    title: 'Configuración avanzada',
    keys: ['curricular_competencias', 'curricular_calendario'],
  },
  { title: 'Administración', keys: ['usuarios'] },
];

/** Ítems fuera de grupos visibles (p. ej. ocultos por transición). */
const SIDEBAR_NAV_KEYS_SIN_GRUPO = ['curricular_pesos'];

function construirNavItemsSidebar(permissions, roles, moduloVista, setModuloActivo) {
  const defs = {
    dashboard: {
      key: 'dashboard',
      label: 'Dashboard',
      visible: moduloPermitido('dashboard', permissions, roles),
      active: moduloVista === 'dashboard',
      onSelect: () => setModuloActivo('dashboard'),
    },
    estudiantes: {
      key: 'estudiantes',
      label: 'Estudiantes',
      visible: moduloPermitido('estudiantes', permissions, roles),
      active: moduloVista === 'estudiantes',
      onSelect: () => setModuloActivo('estudiantes'),
    },
    usuarios: {
      key: 'usuarios',
      label: 'Usuarios',
      visible: moduloPermitido('usuarios', permissions, roles),
      active: moduloVista === 'usuarios',
      onSelect: () => setModuloActivo('usuarios'),
    },
    curricular_malla: {
      key: 'curricular_malla',
      label: 'Malla curricular',
      visible: moduloPermitido('curricular_malla', permissions, roles),
      active: moduloVista === 'curricular_malla',
      onSelect: () => setModuloActivo('curricular_malla'),
    },
    curricular_temas: {
      key: 'curricular_temas',
      label: 'Criterios de evaluación',
      visible: moduloPermitido('curricular_temas', permissions, roles),
      active: moduloVista === 'curricular_temas',
      onSelect: () => setModuloActivo('curricular_temas'),
    },
    curricular_competencias: {
      key: 'curricular_competencias',
      label: 'Competencias y capacidades',
      visible: moduloPermitido('curricular_competencias', permissions, roles),
      active: moduloVista === 'curricular_competencias',
      onSelect: () => setModuloActivo('curricular_competencias'),
    },
    curricular_componentes_calificacion: {
      key: 'curricular_componentes_calificacion',
      label: 'Componentes de calificación',
      visible: moduloPermitido('curricular_componentes_calificacion', permissions, roles),
      active: moduloVista === 'curricular_componentes_calificacion',
      onSelect: () => setModuloActivo('curricular_componentes_calificacion'),
    },
    curricular_secciones_aulas: {
      key: 'curricular_secciones_aulas',
      label: 'Secciones / Aulas',
      visible: moduloPermitido('curricular_secciones_aulas', permissions, roles),
      active: moduloVista === 'curricular_secciones_aulas',
      onSelect: () => setModuloActivo('curricular_secciones_aulas'),
    },
    curricular_pesos: {
      key: 'curricular_pesos',
      label: 'Pesos evaluación',
      // Oculto en transición: el módulo sigue en código/API por compatibilidad con notas semanales.
      visible: false,
      active: moduloVista === 'curricular_pesos',
      onSelect: () => setModuloActivo('curricular_pesos'),
    },
    curricular_eval_bim: {
      key: 'curricular_eval_bim',
      label: 'Configuración bimestral',
      visible: moduloPermitido('curricular_eval_bim', permissions, roles),
      active: moduloVista === 'curricular_eval_bim',
      onSelect: () => setModuloActivo('curricular_eval_bim'),
    },
    curricular_asignacion: {
      key: 'curricular_asignacion',
      label: 'Asignación docente',
      visible: moduloPermitido('curricular_asignacion', permissions, roles),
      active: moduloVista === 'curricular_asignacion',
      onSelect: () => setModuloActivo('curricular_asignacion'),
    },
    curricular_calendario: {
      key: 'curricular_calendario',
      label: 'Periodos académicos',
      visible: moduloPermitido('curricular_calendario', permissions, roles),
      active: moduloVista === 'curricular_calendario',
      onSelect: () => setModuloActivo('curricular_calendario'),
    },
    curricular_notas: {
      key: 'curricular_notas',
      label: 'Notas semanales',
      visible: moduloPermitido('curricular_notas', permissions, roles),
      active: moduloVista === 'curricular_notas',
      onSelect: () => setModuloActivo('curricular_notas'),
    },
    curricular_excel_aula: {
      key: 'curricular_excel_aula',
      label: 'Excel por aula',
      visible: moduloPermitido('curricular_excel_aula', permissions, roles),
      active: moduloVista === 'curricular_excel_aula',
      onSelect: () => setModuloActivo('curricular_excel_aula'),
    },
    curricular_asistencia: {
      key: 'curricular_asistencia',
      label: 'Asistencia',
      visible: moduloPermitido('curricular_asistencia', permissions, roles),
      active: moduloVista === 'curricular_asistencia',
      onSelect: () => setModuloActivo('curricular_asistencia'),
    },
    alertas: {
      key: 'alertas',
      label: 'Alertas',
      visible: moduloPermitido('alertas', permissions, roles),
      active: moduloVista === 'alertas',
      onSelect: () => setModuloActivo('alertas'),
    },
  };

  const items = [];
  let primerGrupoVisible = true;

  for (const group of SIDEBAR_NAV_GROUPS) {
    const visibles = group.keys.map((key) => defs[key]).filter((item) => item?.visible);

    visibles.forEach((item, index) => {
      items.push({
        ...item,
        ...(index === 0 && !primerGrupoVisible ? { dividerBefore: true } : {}),
        ...(index === 0 ? { groupTitle: group.title } : {}),
      });
    });

    if (visibles.length > 0) {
      primerGrupoVisible = false;
    }
  }

  for (const key of SIDEBAR_NAV_KEYS_SIN_GRUPO) {
    const item = defs[key];
    if (item?.visible) {
      items.push({
        ...item,
        ...(items.length > 0 ? { dividerBefore: true } : {}),
      });
    }
  }

  return items;
}

function leerSidebarColapsada() {
  try {
    return localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === 'true';
  } catch {
    return false;
  }
}

function moduloPermitido(key, permissions, roles) {
  switch (key) {
    case 'dashboard':
      return permissions.includes('ver_dashboard');
    case 'estudiantes':
      return permissions.includes('gestionar_estudiantes');
    case 'usuarios':
      return permissions.includes('gestionar_usuarios');
    case 'alertas':
      return permissions.includes('ver_alertas');
    case 'curricular_asistencia':
      return (
        permissions.includes('registrar_asistencia_curricular')
        || permissions.includes('ver_asistencia_curricular')
      );
    case 'curricular_malla':
      return permissions.includes('ver_malla_curricular') || permissions.includes('gestionar_malla_curricular');
    case 'curricular_temas':
      return permissions.includes('gestionar_temas_semanales');
    case 'curricular_competencias':
      return permissions.includes('gestionar_competencias_capacidades');
    case 'curricular_pesos':
      return permissions.includes('configurar_pesos_evaluacion');
    case 'curricular_componentes_calificacion':
      return permissions.includes('gestionar_componentes_calificacion');
    case 'curricular_secciones_aulas':
      return permissions.includes('gestionar_secciones_aulas');
    case 'curricular_eval_bim':
      return permissions.includes('configurar_evaluacion_bimestral');
    case 'curricular_asignacion':
      return permissions.includes('gestionar_asignaciones_docente');
    case 'curricular_calendario':
      return permissions.includes('gestionar_calendario_academico');
    case 'curricular_notas':
      return (
        permissions.includes('registrar_notas_semanales')
        || permissions.includes('gestionar_asignaciones_docente')
        || roles.includes('directivo')
      );
    case 'curricular_excel_aula':
      return permissions.includes('descargar_excel_aula');
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
    usuarios: 'Usuarios',
    alertas: 'Alertas',
    curricular_malla: 'Malla curricular',
    curricular_temas: 'Criterios de evaluación',
    curricular_competencias: 'Competencias y capacidades',
    curricular_pesos: 'Pesos C/L/T',
    curricular_componentes_calificacion: 'Componentes de calificación',
    curricular_secciones_aulas: 'Secciones / Aulas',
    curricular_eval_bim: 'Configuración bimestral',
    curricular_asignacion: 'Asignación docente',
    curricular_calendario: 'Periodos académicos',
    curricular_notas: 'Notas semanales',
    curricular_excel_aula: 'Excel por aula',
    curricular_asistencia: 'Asistencia',
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

function PanelModulo({ modulo }) {
  switch (modulo) {
    case 'dashboard':
      return <DashboardPanel />;
    case 'estudiantes':
      return <EstudiantesPanel />;
    case 'usuarios':
      return <UsuariosPanel />;
    case 'curricular_malla':
      return <MallaCurricularPanel />;
    case 'curricular_temas':
      return <TemasSemanalesPanel />;
    case 'curricular_competencias':
      return <CompetenciasCapacidadesPanel />;
    case 'curricular_pesos':
      return <PesosEvaluacionPanel />;
    case 'curricular_componentes_calificacion':
      return <ComponentesCalificacionNivelPanel />;
    case 'curricular_secciones_aulas':
      return <SeccionesAulasPanel />;
    case 'curricular_eval_bim':
      return <ConfiguracionBimestralPanel />;
    case 'curricular_asignacion':
      return <AsignacionDocentePanel />;
    case 'curricular_calendario':
      return <PeriodosAcademicosPanel />;
    case 'curricular_notas':
      return <RegistroNotasSemanalesPanel />;
    case 'curricular_excel_aula':
      return <ExcelPorAulaPanel />;
    case 'curricular_asistencia':
      return <AsistenciaCurricularPanel />;
    case 'alertas':
      return <AlertasPanel />;
    default:
      return null;
  }
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

  useEffect(() => {
    const abrirCompetencias = () => {
      if (moduloPermitido('curricular_competencias', permissions, roles)) {
        setModuloActivo('curricular_competencias');
      }
    };
    window.addEventListener(EVENTO_ABRIR_COMPETENCIAS, abrirCompetencias);
    return () => window.removeEventListener(EVENTO_ABRIR_COMPETENCIAS, abrirCompetencias);
  }, [permissions, roles]);

  const navItems = useMemo(
    () => construirNavItemsSidebar(permissions, roles, moduloVista, setModuloActivo),
    [permissions, roles, moduloVista],
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

            <Suspense fallback={<LoadingState label="Cargando módulo…" />}>
              <PanelModulo modulo={moduloVista} />
            </Suspense>
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
