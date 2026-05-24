export const FIELD =
  'mt-1.5 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--text)] shadow-sm outline-none transition focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-1';

export const FORM_CRITERIO_INICIAL = {
  competencia_id: '',
  capacidad_id: '',
  semana_academica_id: '',
  titulo: '',
  descripcion: '',
};

export function resolverCapacidadesTema(tema) {
  return tema?.capacidades ?? [];
}

export function resolverCompetenciasTema(tema) {
  return tema?.competencias ?? [];
}

export function ordenarCriterios(a, b) {
  const semA = a.semana_academica?.numero_semana ?? a.semanaAcademica?.numero_semana;
  const semB = b.semana_academica?.numero_semana ?? b.semanaAcademica?.numero_semana;
  if (semA != null && semB != null && semA !== semB) return semA - semB;
  if (semA != null && semB == null) return -1;
  if (semA == null && semB != null) return 1;
  return (a.id ?? 0) - (b.id ?? 0);
}

export function semanaReferencialLabel(criterio) {
  const semRef =
    criterio.semana_academica?.numero_semana ?? criterio.semanaAcademica?.numero_semana;
  return semRef ? `Sem. ref. ${semRef}` : 'Sin semana referencial';
}

export function bimestreLabel(criterio) {
  const bim = criterio.periodo_academico?.bimestre ?? criterio.periodoAcademico?.bimestre;
  return bim != null ? `B.${bim}` : 'B.—';
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

export function construirResumenRegistrado(temas) {
  const porCompetencia = new Map();

  for (const tema of temas) {
    for (const cap of resolverCapacidadesTema(tema)) {
      const compId = cap.pivot?.competencia_id ?? cap.competencia_id;
      const comp =
        resolverCompetenciasTema(tema).find((c) => String(c.id) === String(compId)) ??
        { id: compId, nombre: 'Competencia' };

      if (!porCompetencia.has(String(compId))) {
        porCompetencia.set(String(compId), { competencia: comp, capacidades: new Map() });
      }

      const grupo = porCompetencia.get(String(compId));
      if (!grupo.capacidades.has(String(cap.id))) {
        grupo.capacidades.set(String(cap.id), { capacidad: cap, criterios: [] });
      }

      const lista = grupo.capacidades.get(String(cap.id)).criterios;
      if (!lista.some((c) => c.id === tema.id)) {
        lista.push(tema);
      }
    }
  }

  return [...porCompetencia.values()]
    .sort((a, b) => a.competencia.nombre.localeCompare(b.competencia.nombre, 'es'))
    .map((grupo) => {
      const capacidades = [...grupo.capacidades.values()]
        .sort((a, b) => a.capacidad.nombre.localeCompare(b.capacidad.nombre, 'es'))
        .map(({ capacidad, criterios }) => ({
          capacidad,
          criterios: [...criterios].sort(ordenarCriterios),
        }));

      const totalCriterios = capacidades.reduce((acc, c) => acc + c.criterios.length, 0);
      const totalActivos = capacidades.reduce(
        (acc, c) => acc + c.criterios.filter((t) => t.activo).length,
        0,
      );

      return {
        competencia: grupo.competencia,
        capacidades,
        totalCapacidades: capacidades.length,
        totalCriterios,
        totalActivos,
      };
    });
}

export function nombreCursoMalla(curso) {
  return curso?.curso_catalogo?.nombre ?? curso?.cursoCatalogo?.nombre ?? '';
}

/** Paleta suave institucional por competencia (estable por id). */
const COMPETENCIA_TONES = [
  {
    card: 'border-l-4 border-l-[#5b9bd5] bg-[#f3f9fd]',
    header: 'bg-[#e8f3fb]/90',
    badge: 'bg-[#d6ebf8] text-[#1e5a8a] border-[#b3d4ef]',
    label: 'text-[#2563a8]',
    capacidadAccent: 'border-t-2 border-t-[#5b9bd5]/35',
    capacidadLabel: 'text-[#2563a8]',
    criterioBorderActivo: 'border-l-[3px] border-l-[#5b9bd5]/75',
    criterioBorderInactivo: 'border-l-[3px] border-l-slate-300',
  },
  {
    card: 'border-l-4 border-l-[#e8915a] bg-[#fff8f3]',
    header: 'bg-[#fdeee3]/90',
    badge: 'bg-[#fde4d0] text-[#9a4a1a] border-[#f5c9a8]',
    label: 'text-[var(--primary-dark)]',
    capacidadAccent: 'border-t-2 border-t-[#e8915a]/35',
    capacidadLabel: 'text-[var(--primary-dark)]',
    criterioBorderActivo: 'border-l-[3px] border-l-[#e8915a]/75',
    criterioBorderInactivo: 'border-l-[3px] border-l-slate-300',
  },
  {
    card: 'border-l-4 border-l-[#5cb88a] bg-[#f2fbf6]',
    header: 'bg-[#e6f7ef]/90',
    badge: 'bg-[#d4f0e0] text-[#1a6b42] border-[#a8dfc4]',
    label: 'text-[#1a6b42]',
    capacidadAccent: 'border-t-2 border-t-[#5cb88a]/35',
    capacidadLabel: 'text-[#1a6b42]',
    criterioBorderActivo: 'border-l-[3px] border-l-[#5cb88a]/75',
    criterioBorderInactivo: 'border-l-[3px] border-l-slate-300',
  },
  {
    card: 'border-l-4 border-l-[#9b7fd4] bg-[#f7f4fc]',
    header: 'bg-[#efe9f9]/90',
    badge: 'bg-[#e2d8f4] text-[#5c3d99] border-[#c9b8e8]',
    label: 'text-[#5c3d99]',
    capacidadAccent: 'border-t-2 border-t-[#9b7fd4]/35',
    capacidadLabel: 'text-[#5c3d99]',
    criterioBorderActivo: 'border-l-[3px] border-l-[#9b7fd4]/75',
    criterioBorderInactivo: 'border-l-[3px] border-l-slate-300',
  },
  {
    card: 'border-l-4 border-l-[#d4b84a] bg-[#fdfbf2]',
    header: 'bg-[#faf5e3]/90',
    badge: 'bg-[#f5ecc4] text-[#7a6218] border-[#e8d89a]',
    label: 'text-[#7a6218]',
    capacidadAccent: 'border-t-2 border-t-[#d4b84a]/35',
    capacidadLabel: 'text-[#7a6218]',
    criterioBorderActivo: 'border-l-[3px] border-l-[#d4b84a]/75',
    criterioBorderInactivo: 'border-l-[3px] border-l-slate-300',
  },
];

export function getCompetenciaTone(competenciaId) {
  const id = Number(competenciaId);
  const index = Number.isFinite(id) && id > 0 ? id % COMPETENCIA_TONES.length : 0;
  return COMPETENCIA_TONES[index];
}

export function getCompetenciaToneByIndex(index) {
  return COMPETENCIA_TONES[Math.abs(index) % COMPETENCIA_TONES.length];
}
