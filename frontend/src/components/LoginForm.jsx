import { useState } from 'react';
import { useAuth } from '../context/AuthContext';

export default function LoginForm() {
  const { login, isLoading, error } = useAuth();
  const [email, setEmail] = useState('test@example.com');
  const [password, setPassword] = useState('password');

  async function handleSubmit(event) {
    event.preventDefault();
    await login({ email, password });
  }

  return (
    <form onSubmit={handleSubmit} className="mx-auto mt-20 w-full max-w-sm space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <h1 className="text-xl font-semibold text-slate-900">Ingresar a SIDERAE-Blenkir</h1>

      <div className="space-y-1">
        <label htmlFor="email" className="text-sm text-slate-700">Correo</label>
        <input
          id="email"
          type="email"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
          required
        />
      </div>

      <div className="space-y-1">
        <label htmlFor="password" className="text-sm text-slate-700">Contraseña</label>
        <input
          id="password"
          type="password"
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
          required
        />
      </div>

      {error ? <p className="text-sm text-red-600">{error}</p> : null}

      <button
        type="submit"
        disabled={isLoading}
        className="w-full rounded bg-slate-900 px-3 py-2 text-sm font-medium text-white disabled:opacity-60"
      >
        {isLoading ? 'Ingresando...' : 'Iniciar sesión'}
      </button>
    </form>
  );
}
