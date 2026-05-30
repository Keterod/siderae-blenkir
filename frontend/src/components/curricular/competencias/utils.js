export const FIELD =
  'mt-1.5 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--text)] shadow-sm outline-none transition focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-1';

export function obtenerMensajeError(err, fallback) {
  const payload = err?.payload;
  if (!payload) return fallback;
  if (payload.message) return payload.message;
  const errors = payload.errors;
  if (errors) {
    const first = Object.values(errors).flat()[0];
    if (first) return first;
  }
  return fallback;
}

export function filtroActivoApi(estadoFiltro) {
  if (estadoFiltro === 'activas') return { activo: true };
  if (estadoFiltro === 'inactivas') return { activo: false };
  return { activo: 'all' };
}
