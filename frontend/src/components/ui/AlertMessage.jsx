const STYLES = {
  error: 'border-red-200 bg-red-50 text-red-900',
  warning: 'border-amber-200 bg-amber-50 text-amber-950',
  info: 'border-[#c5d9f0] bg-[#f5f9fd] text-[var(--secondary)]',
  success: 'border-success/40 bg-success/10 text-green-950',
};

export default function AlertMessage({ children, variant = 'error', className = '' }) {
  const s = STYLES[variant] ?? STYLES.error;

  return (
    <p
      role={variant === 'error' ? 'alert' : 'status'}
      className={`rounded-md border px-3 py-2 text-sm ${s} ${className}`.trim()}
    >
      {children}
    </p>
  );
}
