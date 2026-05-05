import Button from '../ui/Button';

export default function Header({ email, roleSummary, onLogout, onOpenNav }) {
  return (
    <header className="sticky top-0 z-30 flex flex-wrap items-center gap-3 border-b border-[var(--border)] bg-[var(--surface)]/95 px-4 py-3 shadow-sm backdrop-blur-sm md:gap-4 md:px-6 md:py-3.5">
      <Button type="button" variant="outline" size="sm" className="shrink-0 md:hidden" onClick={onOpenNav}>
        Menú
      </Button>

      <div className="flex min-w-0 flex-[1_1_12rem] flex-col gap-0.5 md:max-w-md">
        <span className="text-[10px] font-semibold uppercase tracking-wider text-muted">Búsqueda</span>
        <input
          disabled
          readOnly
          title="Pendiente de desarrollo: la búsqueda global requerirá soporte en backend."
          className="sb-field cursor-not-allowed opacity-75"
          aria-label="Búsqueda global no disponible en el prototipo"
          data-testid="header-busqueda-global"
          placeholder="Buscar estudiante o código (no disponible aún)…"
        />
      </div>

      <div className="ml-auto flex min-w-0 flex-[1_1_10rem] items-center justify-end gap-3">
        <div className="min-w-0 text-right">
          <p className="truncate text-xs text-muted">{roleSummary}</p>
          <p className="truncate text-sm font-medium text-foreground">{email ?? 'Usuario'}</p>
        </div>

        <Button
          type="button"
          variant="outline"
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
