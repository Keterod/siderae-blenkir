const VARIANTS = {
  neutral: 'bg-background text-foreground border-[var(--border)]',
  primary: 'bg-[#fde8dc] text-[var(--primary-dark)] border-[#f5c4a8]',
  success: 'bg-[#e6f7ef] text-green-900 border-[#8fd9b6]',
  warning: 'bg-[#fef6e8] text-amber-900 border-[#f6d088]',
  danger: 'bg-red-50 text-red-900 border-red-200',
  info: 'bg-[#eaf2fb] text-[var(--secondary)] border-[#c5d9f0]',
};

export default function Badge({ children, variant = 'neutral', className = '' }) {
  const v = VARIANTS[variant] ?? VARIANTS.neutral;

  return (
    <span
      className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize ${v} ${className}`.trim()}
    >
      {children}
    </span>
  );
}
