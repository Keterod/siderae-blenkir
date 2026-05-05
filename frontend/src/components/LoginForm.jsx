import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import AlertMessage from './ui/AlertMessage';
import Button from './ui/Button';
import Card from './ui/Card';

export default function LoginForm() {
  const { login, isLoading, error } = useAuth();
  const [email, setEmail] = useState('test@example.com');
  const [password, setPassword] = useState('password');

  async function handleSubmit(event) {
    event.preventDefault();
    await login({ email, password });
  }

  return (
    <div className="relative flex min-h-screen flex-col bg-background px-4 py-10">
      <div className="pointer-events-none absolute inset-0 opacity-70 [background-image:radial-gradient(circle_at_20%_20%,rgba(240,90,14,0.08),transparent_45%),radial-gradient(circle_at_80%_0%,rgba(30,99,181,0.06),transparent_42%)]" />

      <div className="relative mx-auto mt-6 flex w-full max-w-md flex-1 flex-col justify-center pb-16">
        <div className="mb-8 text-center">
          <p className="text-sm font-semibold uppercase tracking-[0.2em] text-muted">Colegio Blenkir</p>
          <h1 className="mt-3 text-balance text-3xl font-bold text-[var(--text)]">
            Ingresar a <span className="text-[var(--primary-dark)]">SIDERAE</span>-Blenkir
          </h1>
          <p className="mx-auto mt-2 max-w-sm text-sm text-muted">
            Sistema inteligente de detección temprana del riesgo académico y deserción estudiantil.
          </p>
        </div>

        <Card>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-1">
              <label htmlFor="email" className="text-sm font-medium text-[var(--text)]">
                Correo
              </label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                className="sb-field"
                required
              />
            </div>

            <div className="space-y-1">
              <label htmlFor="password" className="text-sm font-medium text-[var(--text)]">
                Contraseña
              </label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(event) => setPassword(event.target.value)}
                className="sb-field"
                required
              />
            </div>

            {error ? <AlertMessage>{error}</AlertMessage> : null}

            <Button type="submit" variant="primary" size="lg" className="w-full" disabled={isLoading}>
              {isLoading ? 'Ingresando…' : 'Iniciar sesión'}
            </Button>

            <p className="text-center text-xs text-muted">Acceso restringido al personal institucional autorizado.</p>
          </form>
        </Card>
      </div>
    </div>
  );
}
