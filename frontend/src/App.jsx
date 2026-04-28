import LoginForm from './components/LoginForm';
import { useAuth } from './context/AuthContext';

function App() {
  const { authUser, roles, permissions, logout, isLoading, error } = useAuth();

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
          {permissions.includes('ver_dashboard') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Dashboard</span> : null}
          {permissions.includes('gestionar_estudiantes') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Estudiantes</span> : null}
          {permissions.includes('registrar_datos_academicos') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Datos Académicos</span> : null}
          {permissions.includes('procesar_riesgo') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Procesar Riesgo</span> : null}
          {permissions.includes('ver_alertas') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Alertas</span> : null}
          {permissions.includes('registrar_intervencion') ? <span className="rounded bg-slate-100 px-2 py-1 text-sm">Intervenciones</span> : null}
        </div>
      </section>

      {error ? <p className="text-sm text-red-600">{error}</p> : null}
    </main>
  );
}

export default App;
