import Button from '../ui/Button';

export default function Sidebar({ navItems, mobileOpen, onCloseMobile }) {
  return (
    <>
      <button
        type="button"
        className={`fixed inset-0 z-40 bg-black/40 backdrop-blur-[1px] transition-opacity md:hidden ${
          mobileOpen ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'
        }`}
        aria-hidden={!mobileOpen}
        tabIndex={-1}
        onClick={onCloseMobile}
      />

      <aside
        className={`fixed inset-y-0 left-0 z-50 flex w-[min(288px,90vw)] flex-col border-r border-[var(--border)] bg-[var(--surface)] shadow-card transition-transform duration-200 ease-out md:relative md:z-0 md:w-64 md:min-h-screen md:translate-x-0 md:shadow-none ${
          mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        }`}
        aria-label="Navegación principal"
      >
        <div className="flex items-start justify-between gap-2 border-b border-[var(--border)] px-4 py-4">
          <div>
            <p className="text-lg font-semibold tracking-tight text-[var(--text)]">SIDERAE</p>
            <p className="text-xs font-medium uppercase tracking-wide text-muted">Blenkir Analytics</p>
          </div>
          <Button type="button" variant="ghost" size="sm" className="-mr-2 md:hidden" onClick={onCloseMobile}>
            Cerrar
          </Button>
        </div>

        <nav className="flex flex-1 flex-col gap-1 p-3" role="navigation">
          {navItems
            .filter((item) => item.visible)
            .map((item) => (
              <button
                key={item.key}
                type="button"
                role="menuitem"
                onClick={() => {
                  item.onSelect();
                  onCloseMobile();
                }}
                className={`rounded-md px-3 py-2.5 text-left text-sm font-medium transition ${
                  item.active
                    ? 'border-l-4 border-[var(--primary)] bg-orange-50/80 text-[var(--primary-dark)]'
                    : 'border-l-4 border-transparent text-foreground hover:bg-background'
                }`}
              >
                {item.label}
              </button>
            ))}
        </nav>
      </aside>
    </>
  );
}
