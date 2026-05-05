export default function Card({ children, className = '', padding = true }) {
  return (
    <div
      className={`rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-card ${
        padding ? 'p-4' : ''
      } ${className}`.trim()}
    >
      {children}
    </div>
  );
}
