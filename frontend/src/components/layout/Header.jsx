import Button from '../ui/Button';

export default function Header({ email, roleSummary, onLogout, onOpenNav }) {
  return (
    <header className="sticky top-0 z-30 flex flex-wrap items-center gap-3 border-b border-[var(--border)] bg-[var(--surface)]/95 px-3 py-2.5 backdrop-blur-sm md:px-5">
      <Button type="button" variant="outline" size="sm" className="shrink-0 md:hidden" onClick={onOpenNav}>
        Menú
      </Button>

      <div className="flex min-w-0 flex-[1_1_12rem] md:max-w-md">
        <input
          disabled
          readOnly
          title="Disponible en una versión futura del sistema"
          className="sb-field opacity-70"
          aria-label="Búsqueda global (pendiente)"
          placeholder="Buscar estudiante o código…"
        />
      </div>

      <div className="ml-auto flex min-w-0 flex-[1_1_10rem] items-center justify-end gap-3">
        <div className="min-w-0 text-right">
          <p className="truncate text-xs text-muted">{roleSummary}</p>
          <p className="truncate text-sm font-medium text-foreground">{email ?? 'Usuario'}</p>
        </div>

        <Button type="button" variant="outline" size="sm" className="shrink-0" onClick={onLogout}>
          Cerrar sesión
        </Button>
      </div>
    </header>
  );
}
