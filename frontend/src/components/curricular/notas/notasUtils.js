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

export function filaVacia() {
  return { nota_cuaderno: '', nota_libro: '', nota_tarea: '', _teniaNota: false };
}

export function initFilasEstudiante(criterios, notasPorCriterio) {
  const filas = {};
  for (const criterio of criterios) {
    const nota = notasPorCriterio?.[criterio.id] ?? notasPorCriterio?.[String(criterio.id)];
    filas[criterio.id] = {
      nota_cuaderno: valorNotaParaInput(nota?.nota_cuaderno),
      nota_libro: valorNotaParaInput(nota?.nota_libro),
      nota_tarea: valorNotaParaInput(nota?.nota_tarea),
      _teniaNota: Boolean(nota),
    };
  }
  return filas;
}

export function initMatrizAula(estudiantes, criterios, notasPorEstudianteCriterio = {}) {
  const matriz = {};
  for (const estudiante of estudiantes) {
    matriz[estudiante.id] = {};
    const notasEst = notasPorEstudianteCriterio?.[estudiante.id]
      ?? notasPorEstudianteCriterio?.[String(estudiante.id)]
      ?? {};
    for (const criterio of criterios) {
      const nota = notasEst[criterio.id] ?? notasEst[String(criterio.id)];
      matriz[estudiante.id][criterio.id] = {
        nota_cuaderno: valorNotaParaInput(nota?.nota_cuaderno),
        nota_libro: valorNotaParaInput(nota?.nota_libro),
        nota_tarea: valorNotaParaInput(nota?.nota_tarea),
        _teniaNota: Boolean(nota),
      };
    }
  }
  return matriz;
}

export function notaFueraDeRango(valor) {
  if (valor === '' || valor == null) return false;
  const n = Number(valor);
  return Number.isNaN(n) || n < 0 || n > 20;
}

export function validarFilasEnRango(filas, criterios, etiquetaFn) {
  for (const criterio of criterios) {
    const fila = filas[criterio.id] ?? {};
    for (const campo of ['nota_cuaderno', 'nota_libro', 'nota_tarea']) {
      if (notaFueraDeRango(fila[campo])) {
        return `La nota de «${etiquetaFn(criterio)}» debe estar entre 0 y 20.`;
      }
    }
  }
  return null;
}

export function validarMatrizEnRango(matriz, estudiantes, criterios, nombreEstudianteFn) {
  for (const estudiante of estudiantes) {
    const filasEst = matriz[estudiante.id] ?? {};
    for (const criterio of criterios) {
      const fila = filasEst[criterio.id] ?? {};
      for (const campo of ['nota_cuaderno', 'nota_libro', 'nota_tarea']) {
        if (notaFueraDeRango(fila[campo])) {
          return `Nota inválida (${nombreEstudianteFn(estudiante)} · ${criterio.titulo}). Debe estar entre 0 y 20.`;
        }
      }
    }
  }
  return null;
}

export function construirPayloadEstudiante(filas, criterios) {
  const registros = [];
  let intentoBorrar = false;

  for (const criterio of criterios) {
    const fila = filas[criterio.id] ?? {};
    const payload = { tema_semanal_id: criterio.id };
    if (fila.nota_cuaderno !== '') payload.nota_cuaderno = Number(fila.nota_cuaderno);
    if (fila.nota_libro !== '') payload.nota_libro = Number(fila.nota_libro);
    if (fila.nota_tarea !== '') payload.nota_tarea = Number(fila.nota_tarea);

    if (filaTieneAlMenosUnaNota(fila)) {
      registros.push(payload);
    } else if (fila._teniaNota) {
      intentoBorrar = true;
    }
  }

  return { registros, intentoBorrar };
}

export function construirPayloadAula(matriz, estudiantes, criterios) {
  const registrosPorEstudiante = [];
  let intentoBorrar = false;

  for (const estudiante of estudiantes) {
    const filasEst = matriz[estudiante.id] ?? {};
    const registros = [];

    for (const criterio of criterios) {
      const fila = filasEst[criterio.id] ?? {};
      const payload = { tema_semanal_id: criterio.id };
      if (fila.nota_cuaderno !== '') payload.nota_cuaderno = Number(fila.nota_cuaderno);
      if (fila.nota_libro !== '') payload.nota_libro = Number(fila.nota_libro);
      if (fila.nota_tarea !== '') payload.nota_tarea = Number(fila.nota_tarea);

      if (filaTieneAlMenosUnaNota(fila)) {
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
