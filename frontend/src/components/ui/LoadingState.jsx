export default function LoadingState({ label = 'Cargando…', className = '' }) {
  return (
    <div className={`flex items-center gap-3 text-sm text-muted ${className}`}>
      <span
        className="inline-block h-5 w-5 animate-spin rounded-full border-2 border-[var(--border)] border-t-[var(--primary)]"
        aria-hidden
      />
      <span>{label}</span>
    </div>
  );
}
