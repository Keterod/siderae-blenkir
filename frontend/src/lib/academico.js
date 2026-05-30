/** Niveles del módulo de estudiantes (alineado con backend / CatalogoNivelGrado). */
export const NIVELES_ESTUDIANTE = [
  { value: 'inicial', label: 'Inicial' },
  { value: 'primaria', label: 'Primaria' },
  { value: 'secundaria', label: 'Secundaria' },
];

export const GRADOS_POR_NIVEL = {
  inicial: ['3 años', '4 años', '5 años'],
  primaria: ['1°', '2°', '3°', '4°', '5°', '6°'],
  secundaria: ['1°', '2°', '3°', '4°', '5°'],
};

export function anioEscolarActual() {
  return String(new Date().getFullYear());
}

export function gradosPorNivel(nivel) {
  return GRADOS_POR_NIVEL[nivel] ?? [];
}

export function gradoEsValidoParaNivel(nivel, grado) {
  if (!nivel || !grado) {
    return false;
  }

  return gradosPorNivel(nivel).includes(grado);
}

export function etiquetaNivelEstudiante(nivel) {
  return NIVELES_ESTUDIANTE.find((item) => item.value === nivel)?.label ?? nivel ?? '';
}

/** Equivalencia curricular → formato estudiante (alineado con EquivalenciasGradoSeeder). */
const MAPA_GRADO_CURRICULAR_A_ESTUDIANTE = {
  primaria: { '1ro': '1°', '2do': '2°', '3ro': '3°', '4to': '4°', '5to': '5°', '6to': '6°' },
  secundaria: { '1ro': '1°', '2do': '2°', '3ro': '3°', '4to': '4°', '5to': '5°' },
};

export function gradoCurricularAEstudiante(nivel, gradoCurricular) {
  if (!nivel || !gradoCurricular) {
    return gradoCurricular ?? '';
  }
  if (nivel === 'inicial') {
    return gradoCurricular;
  }
  return MAPA_GRADO_CURRICULAR_A_ESTUDIANTE[nivel]?.[gradoCurricular] ?? gradoCurricular;
}

/**
 * Deduplica asignaciones docente por aula (año, nivel, sede, grado, sección).
 * Convierte grado curricular al formato de estudiante para filtros de asistencia.
 */
export function deduplicarAulasDocente(asignaciones) {
  const vistos = new Set();
  const aulas = [];

  for (const asignacion of asignaciones ?? []) {
    const clave = [
      asignacion.anio_escolar,
      asignacion.nivel,
      asignacion.sede,
      asignacion.grado,
      asignacion.seccion,
    ].join('|');

    if (vistos.has(clave)) {
      continue;
    }
    vistos.add(clave);

    aulas.push({
      anio_escolar: asignacion.anio_escolar,
      nivel: asignacion.nivel,
      sede: asignacion.sede,
      grado: gradoCurricularAEstudiante(asignacion.nivel, asignacion.grado),
      seccion: asignacion.seccion,
    });
  }

  return aulas;
}

/** Preset demo operativo: primaria 2° A Chilca 2026. */
export const AULA_ASISTENCIA_DEMO = {
  anio_escolar: '2026',
  nivel: 'primaria',
  sede: 'chilca',
  grado: '2°',
  seccion: 'A',
};
