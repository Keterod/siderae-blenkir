/** Sede de operación vigente en la UI (esquema multi-sede se mantiene en backend). */
export const SEDE_OPERATIVA = 'chilca';

export const ETIQUETA_SEDE_OPERATIVA = 'Chilca';

/** Añade o fija la sede operativa en parámetros de consulta o payloads. */
export function conSedeOperativa(params = {}) {
  return { ...params, sede: params.sede ?? SEDE_OPERATIVA };
}

/** Filtros de dashboard u otros paneles con sede fija. */
export function filtrosConSedeOperativa(filtros = {}) {
  return { ...filtros, sede: SEDE_OPERATIVA };
}

/** Indica si hay filtros activos además de la sede operativa fija. */
export function tieneFiltrosAdemasDeSede(filtros = {}) {
  return Object.entries(filtros).some(
    ([clave, valor]) => clave !== 'sede' && valor !== undefined && valor !== null && valor !== '',
  );
}
