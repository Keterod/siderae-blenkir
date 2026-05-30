import { getAnioEscolarActivo } from './api';
import { anioEscolarActual } from './academico';

/**
 * @returns {Promise<{ anio: string, periodoVigenteId: string|null }|null>}
 */
export async function resolverCalendarioActivoParaFiltros() {
  try {
    const data = await getAnioEscolarActivo();
    const anio = data?.anio_escolar?.anio;
    if (!anio) {
      return null;
    }
    return {
      anio,
      periodoVigenteId: data?.periodo_vigente?.id != null ? String(data.periodo_vigente.id) : null,
    };
  } catch {
    return null;
  }
}

export function anioEscolarPorDefecto() {
  return anioEscolarActual();
}
