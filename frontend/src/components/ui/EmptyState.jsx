export default function EmptyState({ title, description, children, className = '' }) {
  return (
    <div className={`rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] px-4 py-6 text-center ${className}`}>
      <p className="text-sm font-semibold text-foreground">{title}</p>
      {description ? (
        <p className="mt-1 text-sm text-muted">{description}</p>
      ) : null}
      {children ? <div className="mt-4 flex justify-center gap-2">{children}</div> : null}
    </div>
  );
}
