export const NIVELES_CURRICULARES = [
  { value: 'inicial', label: 'Inicial' },
  { value: 'primaria', label: 'Primaria' },
  { value: 'secundaria', label: 'Secundaria' },
];

export const GRADOS_POR_NIVEL = {
  inicial: ['3 años', '4 años', '5 años'],
  primaria: ['1ro', '2do', '3ro', '4to', '5to', '6to'],
  secundaria: ['1ro', '2do', '3ro', '4to', '5to'],
};

export function gradosCurricularesPorNivel(nivel) {
  return GRADOS_POR_NIVEL[nivel] ?? [];
}

export function etiquetaNivelCurricular(nivel) {
  return NIVELES_CURRICULARES.find((n) => n.value === nivel)?.label ?? nivel;
}
