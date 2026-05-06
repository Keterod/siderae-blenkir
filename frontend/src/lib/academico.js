export const GRADOS_POR_NIVEL = {
  primaria: ['1°', '2°', '3°', '4°', '5°', '6°'],
  secundaria: ['1°', '2°', '3°', '4°', '5°'],
};

export function anioEscolarActual() {
  return String(new Date().getFullYear());
}

export function gradosPorNivel(nivel) {
  return GRADOS_POR_NIVEL[nivel] || [];
}

export function gradoEsValidoParaNivel(nivel, grado) {
  return gradosPorNivel(nivel).includes(grado);
}
