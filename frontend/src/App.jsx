import LoginForm from './components/LoginForm';
import AlertasPanel from './components/alertas/AlertasPanel';
import DashboardPanel from './components/DashboardPanel';
import EstudiantesPanel from './components/estudiantes/EstudiantesPanel';
import { useAuth } from './context/AuthContext';
import { useState } from 'react';

function App() {
  const { authUser, roles, permissions, logout, isLoading, error } = useAuth();
  const [mostrarEstudiantes, setMostrarEstudiantes] = useState(false);
  const [mostrarAlertas, setMostrarAlertas] = useState(false);
  const [mostrarDashboard, setMostrarDashboard] = useState(false);


  if (isLoading && !authUser) {
    return <div className="p-8 text-sm text-slate-600">Cargando sesión...</div>;
  }

  if (!authUser) {
    return <LoginForm />;
  }

  return (
    <main className="mx-auto mt-10 w-full max-w-3xl space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <header className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold text-slate-900">SIDERAE-Blenkir</h1>
          <p className="text-sm text-slate-600">Sesión iniciada como {authUser.email}</p>
        </div>
        <button
          type="button"
          onClick={logout}
          className="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700"
        >
          Cerrar sesión
        </button>
      </header>

      <section className="space-y-2">
        <h2 className="text-lg font-medium text-slate-900">Roles</h2>
        <ul className="list-inside list-disc text-sm text-slate-700">
          {roles.map((role) => <li key={role}>{role}</li>)}
        </ul>
      </section>

      <section className="space-y-2">
        <h2 className="text-lg font-medium text-slate-900">Permisos activos</h2>
        <ul className="list-inside list-disc text-sm text-slate-700">
          {permissions.map((permission) => <li key={permission}>{permission}</li>)}
        </ul>
      </section>

      <section className="space-y-2">
        <h2 className="text-lg font-medium text-slate-900">Menú por permisos</h2>
        <div className="flex flex-wrap gap-2">
          {permissions.includes('ver_dashboard') ? (
            <button
              type="button"
              onClick={() => setMostrarDashboard((valor) => !valor)}
              className={`rounded px-2 py-1 text-sm ${mostrarDashboard ? 'bg-[#C94A0C] text-white' : 'bg-slate-100 text-slate-800'}`}
            >
              Dashboard
            </button>
          ) : null}
          {permissions.includes('gestionar_estudiantes') ? (
            <button
              type="button"
              onClick={() => setMostrarEstudiantes((valor) => !valor)}
              className={`rounded px-2 py-1 text-sm ${mostrarEstudiantes ? 'bg-slate-300' : 'bg-slate-100'}`}
            >
              Estudiantes
            </button>
          ) : null}
          {permissions.includes('registrar_datos_academicos') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Datos Académicos</span> : null}
          {permissions.includes('procesar_riesgo') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Procesar Riesgo</span> : null}
          {permissions.includes('ver_alertas') ? (
            <button
              type="button"
              onClick={() => setMostrarAlertas((valor) => !valor)}
              className={`rounded px-2 py-1 text-sm ${mostrarAlertas ? 'bg-slate-300' : 'bg-slate-100'}`}
            >
              Alertas
            </button>
          ) : null}
          {permissions.includes('registrar_intervencion') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Intervenciones</span> : null}
        </div>
      </section>

      {permissions.includes('ver_dashboard') && mostrarDashboard ? <DashboardPanel /> : null}

      {permissions.includes('gestionar_estudiantes') && mostrarEstudiantes ? (
        <EstudiantesPanel onClose={() => setMostrarEstudiantes(false)} />
      ) : null}

      {permissions.includes('ver_alertas') && mostrarAlertas ? (
        <AlertasPanel onClose={() => setMostrarAlertas(false)} />
      ) : null}

      {error ? <p className="text-sm text-red-600">{error}</p> : null}
    </main>
  );
}

export default App;
