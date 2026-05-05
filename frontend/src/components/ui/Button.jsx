const VARIANTS = {
  primary:
    'bg-[var(--primary)] text-white hover:bg-[var(--primary-light)] focus-visible:ring-[var(--primary)] disabled:opacity-60',
  secondary:
    'border border-[var(--secondary)] bg-surface text-[var(--secondary)] hover:bg-[#eaf2fb] focus-visible:ring-[var(--secondary)] disabled:opacity-60',
  outline:
    'border border-[var(--border)] bg-surface text-foreground hover:bg-background focus-visible:ring-[var(--primary)] disabled:opacity-60',
  ghost:
    'text-foreground hover:bg-black/5 focus-visible:ring-[var(--primary)] disabled:opacity-60',
  danger:
    'border border-red-200 bg-red-50 text-red-800 hover:bg-red-100 focus-visible:ring-danger disabled:opacity-60',
};

const SIZES = {
  sm: 'rounded-md px-2.5 py-1.5 text-xs font-medium',
  md: 'rounded-md px-3 py-2 text-sm font-medium',
  lg: 'rounded-md px-4 py-2.5 text-base font-semibold',
};

export default function Button({
  children,
  type = 'button',
  variant = 'primary',
  size = 'md',
  className = '',
  ...props
}) {
  const v = VARIANTS[variant] ?? VARIANTS.primary;
  const s = SIZES[size] ?? SIZES.md;

  return (
    <button
      type={type}
      className={`inline-flex items-center justify-center gap-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 ${v} ${s} ${className}`}
      {...props}
    >
      {children}
    </button>
  );
}
