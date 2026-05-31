import { etiquetaNivelCurricular } from '../../../lib/academicoCurricular';
import {
  ADVERTENCIA_ELIMINAR_NOTA,
  filaTieneAlMenosUnaNota,
  valorNotaParaInput,
} from '../../../lib/notasCurricular';

/**
 * Opciones del filtro Área en Notas semanales.
 * Con nivel seleccionado: solo áreas de ese nivel.
 * Con nivel "Todos": etiqueta diferenciada si el nombre se repite entre niveles.
 *
 * @param {Array<{ nivel?: string, area_id?: number|string|null, area_nombre?: string }>} registros
 * @param {string} filtrosNivel
 * @returns {Array<[string, string]>}
 */
export function construirOpcionesAreasFiltro(registros, filtrosNivel = '') {
  const candidatos = filtrosNivel
    ? registros.filter((r) => r.nivel === filtrosNivel)
    : registros;

  const porId = new Map();
  for (const r of candidatos) {
    if (r.area_id == null || r.area_id === '') continue;
    porId.set(String(r.area_id), {
      nombre: r.area_nombre ?? '',
      nivel: r.nivel ?? '',
    });
  }

  const entries = [...porId.entries()];
  if (entries.length === 0) return [];

  if (filtrosNivel) {
    return entries
      .map(([id, { nombre }]) => [id, nombre])
      .sort((a, b) => a[1].localeCompare(b[1], 'es'));
  }

  const conteoNombre = new Map();
  for (const [, { nombre }] of entries) {
    conteoNombre.set(nombre, (conteoNombre.get(nombre) ?? 0) + 1);
  }

  return entries
    .map(([id, { nombre, nivel }]) => {
      const duplicadoNombre = (conteoNombre.get(nombre) ?? 0) > 1;
      const label = duplicadoNombre && nivel
        ? `${nombre} — ${etiquetaNivelCurricular(nivel)}`
        : nombre;
      return [id, label];
    })
    .sort((a, b) => a[1].localeCompare(b[1], 'es'));
}

export { ADVERTENCIA_ELIMINAR_NOTA };

export function claveCelda(estudianteId, criterioId) {
  return `${estudianteId}:${criterioId}`;
}

export function esModoCalificacionDinamica(formulario) {
  return formulario?.calificacion_dinamica_disponible === true
    && obtenerComponentesCalificacion(formulario).length > 0;
}

/**
 * @param {Record<string, unknown>|null|undefined} formulario
 * @returns {Array<{ id: number, codigo: string, nombre: string, peso: number, orden: number }>}
 */
export function obtenerComponentesCalificacion(formulario) {
  const lista = formulario?.componentes_calificacion ?? [];
  return [...lista].sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0));
}

export function etiquetaComponenteConPeso(componente) {
  const peso = Math.round(Number(componente.peso ?? 0));
  return `${componente.nombre} (${peso}%)`;
}

export function esCampoComponente(campo) {
  return String(campo).startsWith('comp_');
}

export function parseComponenteIdDesdeCampo(campo) {
  return Number(String(campo).slice(5));
}

export function filaVaciaLegacy() {
  return { nota_cuaderno: '', nota_libro: '', nota_tarea: '', _teniaNota: false, _modeloOriginal: 'legacy' };
}

/**
 * @param {Array<{ id: number }>} componentes
 */
export function filaVaciaDinamica(componentes) {
  return {
    componentes: Object.fromEntries(componentes.map((c) => [c.id, ''])),
    _teniaNota: false,
    _modeloOriginal: 'dinamico',
  };
}

const LEGACY_CODIGO_A_CAMPO = {
  cuaderno: 'nota_cuaderno',
  libro: 'nota_libro',
  tarea: 'nota_tarea',
};

/**
 * @param {Record<string, unknown>|null|undefined} nota
 * @param {boolean} esDinamico
 * @param {Array<{ id: number, codigo: string }>} componentes
 */
function initFilaDesdeNota(nota, esDinamico, componentes) {
  const teniaNota = Boolean(nota);
  const modeloOriginal = nota?.modelo_calificacion ?? 'legacy';

  if (esDinamico) {
    const componentesMap = Object.fromEntries(componentes.map((c) => [c.id, '']));

    if (modeloOriginal === 'dinamico') {
      for (const item of nota?.notas_componentes ?? []) {
        if (item?.componente_id != null) {
          componentesMap[item.componente_id] = valorNotaParaInput(item.nota);
        }
      }
    } else if (teniaNota) {
      for (const comp of componentes) {
        const campo = LEGACY_CODIGO_A_CAMPO[comp.codigo];
        if (campo && nota?.[campo] != null && nota[campo] !== '') {
          componentesMap[comp.id] = valorNotaParaInput(nota[campo]);
        }
      }
    }

    return {
      componentes: componentesMap,
      _teniaNota: teniaNota,
      _modeloOriginal: modeloOriginal,
    };
  }

  return {
    nota_cuaderno: valorNotaParaInput(nota?.nota_cuaderno),
    nota_libro: valorNotaParaInput(nota?.nota_libro),
    nota_tarea: valorNotaParaInput(nota?.nota_tarea),
    _teniaNota: teniaNota,
    _modeloOriginal: modeloOriginal,
  };
}

export function initFilasEstudiante(criterios, notasPorCriterio, formulario = null) {
  const esDinamico = esModoCalificacionDinamica(formulario);
  const componentes = obtenerComponentesCalificacion(formulario);
  const filas = {};

  for (const criterio of criterios) {
    const nota = notasPorCriterio?.[criterio.id] ?? notasPorCriterio?.[String(criterio.id)];
    filas[criterio.id] = initFilaDesdeNota(nota, esDinamico, componentes);
  }

  return filas;
}

export function initMatrizAula(estudiantes, criterios, notasPorEstudianteCriterio = {}, formulario = null) {
  const esDinamico = esModoCalificacionDinamica(formulario);
  const componentes = obtenerComponentesCalificacion(formulario);
  const matriz = {};

  for (const estudiante of estudiantes) {
    matriz[estudiante.id] = {};
    const notasEst = notasPorEstudianteCriterio?.[estudiante.id]
      ?? notasPorEstudianteCriterio?.[String(estudiante.id)]
      ?? {};

    for (const criterio of criterios) {
      const nota = notasEst[criterio.id] ?? notasEst[String(criterio.id)];
      matriz[estudiante.id][criterio.id] = initFilaDesdeNota(nota, esDinamico, componentes);
    }
  }

  return matriz;
}

export function notaFueraDeRango(valor) {
  if (valor === '' || valor == null) return false;
  const n = Number(valor);
  return Number.isNaN(n) || n < 0 || n > 20;
}

export function validarFilasEnRango(filas, criterios, etiquetaFn, formulario = null) {
  const esDinamico = esModoCalificacionDinamica(formulario);
  const componentes = obtenerComponentesCalificacion(formulario);

  for (const criterio of criterios) {
    const fila = filas[criterio.id] ?? {};

    if (esDinamico) {
      for (const comp of componentes) {
        const valor = fila.componentes?.[comp.id] ?? fila.componentes?.[String(comp.id)];
        if (notaFueraDeRango(valor)) {
          return `La nota de «${etiquetaFn(criterio)} · ${comp.nombre}» debe estar entre 0 y 20.`;
        }
      }
      continue;
    }

    for (const campo of ['nota_cuaderno', 'nota_libro', 'nota_tarea']) {
      if (notaFueraDeRango(fila[campo])) {
        return `La nota de «${etiquetaFn(criterio)}» debe estar entre 0 y 20.`;
      }
    }
  }

  return null;
}

export function validarMatrizEnRango(matriz, estudiantes, criterios, nombreEstudianteFn, formulario = null) {
  const esDinamico = esModoCalificacionDinamica(formulario);
  const componentes = obtenerComponentesCalificacion(formulario);

  for (const estudiante of estudiantes) {
    const filasEst = matriz[estudiante.id] ?? {};

    for (const criterio of criterios) {
      const fila = filasEst[criterio.id] ?? {};

      if (esDinamico) {
        for (const comp of componentes) {
          const valor = fila.componentes?.[comp.id] ?? fila.componentes?.[String(comp.id)];
          if (notaFueraDeRango(valor)) {
            return `Nota inválida (${nombreEstudianteFn(estudiante)} · ${criterio.titulo} · ${comp.nombre}). Debe estar entre 0 y 20.`;
          }
        }
        continue;
      }

      for (const campo of ['nota_cuaderno', 'nota_libro', 'nota_tarea']) {
        if (notaFueraDeRango(fila[campo])) {
          return `Nota inválida (${nombreEstudianteFn(estudiante)} · ${criterio.titulo}). Debe estar entre 0 y 20.`;
        }
      }
    }
  }

  return null;
}

/**
 * @param {Record<string, unknown>} fila
 * @param {number|string} criterioId
 * @param {boolean} esDinamico
 * @param {Array<{ id: number }>} componentes
 */
function construirRegistroDesdeFila(fila, criterioId, esDinamico, componentes) {
  const payload = { tema_semanal_id: criterioId };

  if (esDinamico) {
    const notasComponentes = [];
    for (const comp of componentes) {
      const valor = fila.componentes?.[comp.id] ?? fila.componentes?.[String(comp.id)];
      if (valor !== '' && valor != null) {
        notasComponentes.push({
          componente_id: comp.id,
          nota: Number(valor),
        });
      }
    }

    if (notasComponentes.length === 0) {
      return null;
    }

    payload.notas_componentes = notasComponentes;
    return payload;
  }

  if (fila.nota_cuaderno !== '') payload.nota_cuaderno = Number(fila.nota_cuaderno);
  if (fila.nota_libro !== '') payload.nota_libro = Number(fila.nota_libro);
  if (fila.nota_tarea !== '') payload.nota_tarea = Number(fila.nota_tarea);

  if (!filaTieneAlMenosUnaNota(fila, false)) {
    return null;
  }

  return payload;
}

export function construirPayloadEstudiante(filas, criterios, formulario = null) {
  const esDinamico = esModoCalificacionDinamica(formulario);
  const componentes = obtenerComponentesCalificacion(formulario);
  const registros = [];
  let intentoBorrar = false;

  for (const criterio of criterios) {
    const fila = filas[criterio.id] ?? {};
    const payload = construirRegistroDesdeFila(fila, criterio.id, esDinamico, componentes);

    if (payload) {
      registros.push(payload);
    } else if (fila._teniaNota) {
      intentoBorrar = true;
    }
  }

  return { registros, intentoBorrar };
}

export function construirPayloadAula(matriz, estudiantes, criterios, formulario = null) {
  const esDinamico = esModoCalificacionDinamica(formulario);
  const componentes = obtenerComponentesCalificacion(formulario);
  const registrosPorEstudiante = [];
  let intentoBorrar = false;

  for (const estudiante of estudiantes) {
    const filasEst = matriz[estudiante.id] ?? {};
    const registros = [];

    for (const criterio of criterios) {
      const fila = filasEst[criterio.id] ?? {};
      const payload = construirRegistroDesdeFila(fila, criterio.id, esDinamico, componentes);

      if (payload) {
        registros.push(payload);
      } else if (fila._teniaNota) {
        intentoBorrar = true;
      }
    }

    if (registros.length > 0) {
      registrosPorEstudiante.push({
        estudiante_id: estudiante.id,
        registros,
      });
    }
  }

  return { registrosPorEstudiante, intentoBorrar };
}

export function filtrarEntradaNota(valor) {
  if (valor === '') return '';
  const normalizado = String(valor).replace(',', '.');
  if (!/^\d*\.?\d*$/.test(normalizado)) return null;
  return normalizado;
}

export const INPUT_NOTA =
  'w-9 min-w-[2.125rem] rounded border border-[var(--border)] px-0.5 py-0 text-center text-[11px] leading-4 tabular-nums';

export const FIELD_COMPACT =
  'mt-0.5 w-full rounded border border-[var(--border)] bg-[var(--surface)] px-2 py-1 text-xs text-[var(--text)] shadow-sm outline-none transition focus-visible:ring-1 focus-visible:ring-[var(--primary)]';

export const LABEL_COMPACT = 'block text-[11px] font-medium leading-tight text-[var(--text)]';
