import Button from '../ui/Button';

export default function Header({ email, etiquetaSesion, onLogout, onOpenNav }) {
  return (
    <header className="sticky top-0 z-30 flex flex-wrap items-center gap-3 border-b border-[var(--border)] bg-[var(--surface)]/95 px-4 py-3 shadow-sm backdrop-blur-sm md:gap-5 md:px-6 md:py-3.5">
      <Button type="button" variant="outline" size="sm" className="shrink-0 md:hidden" onClick={onOpenNav}>
        Menú
      </Button>

      <div className="ml-auto flex min-w-0 items-center justify-end gap-4">
        <div className="min-w-0 text-right leading-tight">
          {etiquetaSesion ? <p className="truncate text-xs font-medium text-[var(--text)]">{etiquetaSesion}</p> : null}
          <p className="truncate text-xs text-muted">{email ?? 'Usuario'}</p>
        </div>

        <Button
          type="button"
          variant="primary"
          size="sm"
          className="shrink-0"
          onClick={onLogout}
          data-testid="header-logout"
        >
          Cerrar sesión
        </Button>
      </div>
    </header>
  );
}
