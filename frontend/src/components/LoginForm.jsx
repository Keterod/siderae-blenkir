import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import AlertMessage from './ui/AlertMessage';
import Button from './ui/Button';
import Card from './ui/Card';

export default function LoginForm() {
  const { login, isLoading, error } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  async function handleSubmit(event) {
    event.preventDefault();
    await login({ email, password });
  }

  return (
    <div
      className="relative flex min-h-screen flex-col bg-background px-4 py-10"
      data-testid="login-screen"
    >
      <div className="pointer-events-none absolute inset-0 opacity-70 [background-image:radial-gradient(circle_at_20%_20%,rgba(240,90,14,0.08),transparent_45%),radial-gradient(circle_at_80%_0%,rgba(30,99,181,0.06),transparent_42%)]" />

      <div className="relative mx-auto mt-4 flex w-full max-w-md flex-1 flex-col justify-center pb-16">
        <div className="mb-8 text-center">
          <div
            className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl border border-[var(--border)] bg-[var(--surface)] shadow-card"
            aria-hidden
          >
            <span className="text-xl font-bold text-[var(--primary)]">S</span>
          </div>
          <p className="text-sm font-semibold uppercase tracking-[0.2em] text-muted">Colegio Blenkir</p>
          <h1 className="mt-3 text-balance text-3xl font-bold text-[var(--text)]">
            Ingresar a <span className="text-[var(--primary-dark)]">SIDERAE</span>-Blenkir
          </h1>
          <p className="mx-auto mt-2 max-w-sm text-sm text-muted">
            Sistema inteligente de detección temprana del riesgo académico y deserción estudiantil.
          </p>
        </div>

        <Card data-testid="login-card" className="ring-1 ring-[var(--border)]/90 shadow-card">
          <form className="space-y-5" onSubmit={(e) => void handleSubmit(e)}>
            <div className="space-y-1">
              <label htmlFor="email" className="text-sm font-medium text-[var(--text)]">
                Correo institucional
              </label>
              <input
                id="email"
                type="email"
                name="email"
                autoComplete="username"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                placeholder="nombre@institucion.edu.pe"
                className="sb-field"
                required
                data-testid="login-email"
              />
            </div>

            <div className="space-y-1">
              <label htmlFor="password" className="text-sm font-medium text-[var(--text)]">
                Contraseña
              </label>
              <input
                id="password"
                type="password"
                name="password"
                autoComplete="current-password"
                value={password}
                onChange={(event) => setPassword(event.target.value)}
                placeholder="••••••••"
                className="sb-field"
                required
                data-testid="login-password"
              />
            </div>

            <p className="text-xs text-muted">
              <span
                title="Funcionalidad pendiente de desarrollo; use el flujo institucional de recuperación hasta entonces."
                className="border-b border-dotted border-muted text-muted"
              >
                ¿Olvidó su contraseña?
              </span>
            </p>

            {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}

            <Button type="submit" variant="primary" size="lg" className="w-full" disabled={isLoading} data-testid="login-submit">
              {isLoading ? 'Ingresando…' : 'Iniciar sesión'}
            </Button>

            <p className="text-center text-xs text-muted">
              Solo personal institucional con credenciales asignadas.
            </p>
          </form>
        </Card>
      </div>
    </div>
  );
}
