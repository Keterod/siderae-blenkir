export const FIELD =
  'mt-1.5 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--text)] shadow-sm outline-none transition focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-1';

export const TOLERANCIA_PESOS = 0.01;

export const ETIQUETA_TIPO_COMPONENTE = {
  promedio_criterios: 'Promedio criterios',
  oral: 'Oral',
  promedio_eta: 'Prom. ETA',
  examen_bimestral: 'Examen bimestral',
  personalizado: 'Personalizado',
};

export function etiquetaTipoComponente(componente) {
  if (componente?.tipo === 'personalizado') return 'Personalizado';
  return ETIQUETA_TIPO_COMPONENTE[componente?.codigo] ?? componente?.codigo ?? '—';
}

export function separarActivosInactivos(items = []) {
  const activos = items.filter((i) => i.activo).sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0));
  const inactivos = items.filter((i) => !i.activo).sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0));
  return { activos, inactivos };
}

export function sumaPesos(items, campo = 'peso') {
  return Math.round(items.reduce((acc, item) => acc + Number(item[campo] ?? 0), 0) * 100) / 100;
}

export function pesosValidos(suma) {
  return Math.abs(suma - 100) <= TOLERANCIA_PESOS;
}

export function validarPesoIndividual(valor) {
  if (valor === '' || valor == null) return 'Ingrese un peso.';
  const n = Number(valor);
  if (Number.isNaN(n)) return 'Peso inválido.';
  if (n < 0) return 'El peso debe ser mayor o igual a 0.';
  if (n > 100) return 'El peso no puede superar 100.';
  return null;
}

export function calcularSumaConEdicion(activos, idEditado, nuevoPeso, campo = 'peso') {
  return Math.round(
    activos.reduce((acc, item) => {
      const peso = String(item.id) === String(idEditado) ? Number(nuevoPeso) : Number(item[campo] ?? 0);
      return acc + (Number.isNaN(peso) ? 0 : peso);
    }, 0) * 100,
  ) / 100;
}

export function validarSumaManual(activos, idEditado, nuevoPeso, campo = 'peso') {
  const errorIndividual = validarPesoIndividual(nuevoPeso);
  if (errorIndividual) return errorIndividual;
  const suma = calcularSumaConEdicion(activos, idEditado, nuevoPeso, campo);
  if (!pesosValidos(suma)) {
    return `La suma de pesos activos debe ser 100 % (actual: ${suma} %).`;
  }
  return null;
}

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

export function formatoPeso(valor) {
  const n = Number(valor);
  if (Number.isNaN(n)) return '—';
  return Number.isInteger(n) ? `${n} %` : `${n.toFixed(2)} %`;
}
