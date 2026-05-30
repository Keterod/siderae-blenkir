export const ESTADOS_ASISTENCIA_FALLBACK = ['presente', 'tarde', 'falta', 'justificado'];

export const ETIQUETAS_ESTADO_ASISTENCIA = {
  presente: 'Presente',
  tarde: 'Tarde',
  falta: 'Falta',
  justificado: 'Justificado',
};

export function etiquetaEstadoAsistencia(estado) {
  return ETIQUETAS_ESTADO_ASISTENCIA[estado] ?? estado ?? '—';
}

export function fechaHoyIso() {
  return new Date().toISOString().slice(0, 10);
}

export function mensajeErrorAsistenciaApi(error) {
  const p = error?.payload;
  if (p?.message && typeof p.message === 'string') {
    return p.message;
  }
  if (p?.errors && typeof p.errors === 'object') {
    return Object.entries(p.errors)
      .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(', ') : String(v)}`)
      .join(' ');
  }
  if (error?.status === 403) {
    return 'No tiene permiso para esta operación en el aula seleccionada.';
  }
  return 'No se pudo completar la operación.';
}

/**
 * @param {Array<{ id: number, asistencia?: { estado?: string, observacion?: string|null }|null }>} estudiantes
 * @returns {Record<number, { estado: string|null, observacion: string }>}
 */
export function filasDesdeFormulario(estudiantes) {
  const out = {};
  (estudiantes ?? []).forEach((est) => {
    out[est.id] = {
      estado: est.asistencia?.estado ?? null,
      observacion: est.asistencia?.observacion ?? '',
    };
  });
  return out;
}

/**
 * @param {Record<number, { estado: string|null, observacion: string }>} filasPorId
 * @param {string[]} estadosPermitidos
 */
export function contadoresAsistencia(filasPorId, estadosPermitidos = ESTADOS_ASISTENCIA_FALLBACK) {
  const conteos = Object.fromEntries(estadosPermitidos.map((e) => [e, 0]));
  let sinMarcar = 0;

  Object.values(filasPorId).forEach((fila) => {
    const estado = fila?.estado;
    if (!estado) {
      sinMarcar += 1;
      return;
    }
    if (Object.prototype.hasOwnProperty.call(conteos, estado)) {
      conteos[estado] += 1;
    }
  });

  return { ...conteos, sin_marcar: sinMarcar, total: Object.keys(filasPorId).length };
}

/**
 * @param {Record<number, { estado: string|null, observacion: string }>} filasPorId
 */
export function filasParaBulk(filasPorId) {
  return Object.entries(filasPorId)
    .filter(([, fila]) => Boolean(fila?.estado))
    .map(([estudianteId, fila]) => ({
      estudiante_id: Number(estudianteId),
      estado: fila.estado,
      observacion: fila.observacion?.trim() ? fila.observacion.trim() : null,
    }));
}
