import Button from '../ui/Button';
import { NavIcon } from './navIcons';

export default function Sidebar({
  navItems,
  mobileOpen,
  onCloseMobile,
  collapsed = false,
  onToggleCollapsed,
}) {
  const visible = navItems.filter((item) => item.visible);

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
        className={`fixed inset-y-0 left-0 z-50 flex h-full shrink-0 flex-col border-r border-[var(--border)] bg-[var(--surface)] shadow-card transition-[width,transform] duration-200 ease-out md:relative md:z-0 md:translate-x-0 md:shadow-none ${
          mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        } ${collapsed ? 'w-[min(288px,90vw)] md:w-[4.25rem]' : 'w-[min(288px,90vw)] md:w-64'}`}
        aria-label="Navegación principal"
        data-collapsed={collapsed ? 'true' : 'false'}
      >
        <div className="flex items-center justify-between gap-2 border-b border-[var(--border)] px-3 py-3">
          <div className={`min-w-0 overflow-hidden transition-opacity ${collapsed ? 'md:w-0 md:opacity-0' : ''}`}>
            <p className="truncate text-lg font-semibold tracking-tight text-[var(--text)]">SIDERAE</p>
            <p className="truncate text-xs font-medium uppercase tracking-wide text-muted">Blenkir Analytics</p>
          </div>
          <div className={`flex shrink-0 items-center gap-1 ${collapsed ? 'md:mx-auto md:w-full md:justify-center' : ''}`}>
            {onToggleCollapsed ? (
              <Button
                type="button"
                variant="ghost"
                size="sm"
                className="hidden px-2 md:inline-flex"
                onClick={onToggleCollapsed}
                aria-label={collapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral'}
                title={collapsed ? 'Expandir menú' : 'Colapsar menú'}
              >
                <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
                  {collapsed ? (
                    <path strokeLinecap="round" strokeLinejoin="round" d="m9 18 6-6-6-6M15 6v12" />
                  ) : (
                    <path strokeLinecap="round" strokeLinejoin="round" d="m15 18-6-6 6-6M9 6v12" />
                  )}
                </svg>
              </Button>
            ) : null}
            <Button type="button" variant="ghost" size="sm" className="-mr-1 md:hidden" onClick={onCloseMobile}>
              Cerrar
            </Button>
          </div>
        </div>

        <nav className="flex flex-1 flex-col gap-1 overflow-y-auto p-2 pb-6" role="navigation">
          {visible.map((item) => (
            <span key={item.key} className="contents">
              {item.dividerBefore ? (
                <div
                  className={`my-2 shrink-0 border-t border-[var(--border)]/90 ${collapsed ? 'md:mx-1.5' : ''}`}
                  role="separator"
                  aria-hidden
                />
              ) : null}
              {item.groupTitle ? (
                <p
                  className={`truncate px-2 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-muted first:pt-1 ${
                    collapsed ? 'md:hidden' : ''
                  }`}
                >
                  {item.groupTitle}
                </p>
              ) : null}
              <SidebarNavButton item={item} collapsed={collapsed} onCloseMobile={onCloseMobile} />
            </span>
          ))}
        </nav>
      </aside>
    </>
  );
}

function SidebarNavButton({ item, collapsed, onCloseMobile }) {
  const disabled = Boolean(item.disabled);
  const isLegacyLabel = item.key === 'legacy_divider';
  const base =
    'flex w-full items-center gap-3 rounded-md text-sm font-medium transition border-l-4 ';
  const padding = collapsed ? 'justify-center px-2 py-2.5 md:px-2' : 'px-3 py-2.5';
  let visual = '';
  if (disabled) {
    visual = 'cursor-not-allowed border-transparent opacity-55 text-muted';
  } else if (item.active) {
    visual = 'border-[var(--primary)] bg-orange-50/90 text-[var(--primary-dark)] shadow-sm';
  } else {
    visual = 'border-transparent text-foreground hover:bg-background';
  }

  const label = item.label;
  const showIcon = !isLegacyLabel;

  return (
    <button
      type="button"
      role="menuitem"
      data-testid={item.testId ?? `nav-${item.key}`}
      disabled={disabled}
      aria-current={item.active ? 'page' : undefined}
      aria-disabled={disabled}
      aria-label={label}
      title={collapsed && !disabled ? label : disabled ? item.disabledReason || 'No disponible' : undefined}
      onClick={() => {
        if (disabled) {
          return;
        }
        item.onSelect();
        onCloseMobile();
      }}
      className={`${base} ${padding} ${visual}`}
    >
      {showIcon ? <NavIcon name={item.key} /> : null}
      <span className={collapsed ? 'md:sr-only' : 'truncate'}>{label}</span>
    </button>
  );
}
