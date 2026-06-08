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

/** Modo plantilla por grado: permite editar pesos sin exigir suma 100 en cada paso. */
export function validarPesoEdicionPlantillaGrado(nuevoPeso) {
  return validarPesoIndividual(nuevoPeso);
}

/** Plantilla institucional por defecto (alineada al backend). */
export function crearPlantillaBimestralPorDefecto() {
  const componentes = [
    { id: 'promedio_criterios', codigo: 'promedio_criterios', tipo: 'promedio_criterios', nombre: 'Promedio de criterios', peso: 25, orden: 1, activo: true },
    { id: 'oral', codigo: 'oral', tipo: 'oral', nombre: 'Oral', peso: 25, orden: 2, activo: true },
    { id: 'promedio_eta', codigo: 'promedio_eta', tipo: 'promedio_eta', nombre: 'Promedio ETA', peso: 25, orden: 3, activo: true },
    { id: 'examen_bimestral', codigo: 'examen_bimestral', tipo: 'examen_bimestral', nombre: 'Examen bimestral', peso: 25, orden: 4, activo: true },
  ];
  const etas = [
    { id: 'eta-1', nombre: 'ETA 1', peso_interno: 33.33, orden: 1, activo: true },
    { id: 'eta-2', nombre: 'ETA 2', peso_interno: 33.33, orden: 2, activo: true },
    { id: 'eta-3', nombre: 'ETA 3', peso_interno: 33.34, orden: 3, activo: true },
  ];
  return { componentes, etas };
}

export function serializarPlantillaParaApi(plantilla) {
  return {
    componentes: (plantilla?.componentes ?? []).map((c) => ({
      codigo: c.codigo ?? c.tipo,
      nombre: c.nombre,
      peso: Number(c.peso),
      activo: Boolean(c.activo),
      orden: Number(c.orden ?? 0),
    })),
    etas: (plantilla?.etas ?? []).map((e) => ({
      nombre: e.nombre,
      peso_interno: Number(e.peso_interno),
      activo: Boolean(e.activo),
      orden: Number(e.orden ?? 0),
    })),
  };
}

export function redistribuirEquitativo(items, campo = 'peso') {
  const activos = items.filter((i) => i.activo);
  if (activos.length === 0) {
    return items.map((item) => ({ ...item, [campo]: 0 }));
  }

  const base = Math.round((100 / activos.length) * 100) / 100;
  const pesos = Array(activos.length).fill(base);
  const suma = Math.round(pesos.reduce((acc, p) => acc + p, 0) * 100) / 100;
  const ajuste = Math.round((100 - suma) * 100) / 100;
  pesos[pesos.length - 1] = Math.round((pesos[pesos.length - 1] + ajuste) * 100) / 100;

  let indice = 0;

  return items.map((item) => {
    if (!item.activo) {
      return { ...item, [campo]: 0 };
    }
    const actualizado = { ...item, [campo]: pesos[indice] };
    indice += 1;
    return actualizado;
  });
}

export function slugPersonalizado(nombre) {
  const base = String(nombre ?? '')
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '_')
    .replace(/[^a-z0-9_]/g, '');
  return base ? `personalizado_${base}` : `personalizado_${Date.now()}`;
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
