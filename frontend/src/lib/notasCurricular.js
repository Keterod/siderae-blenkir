/**
 * Cálculo de CE en cliente (misma lógica que pesos iguales por defecto).
 * @param {{ cuaderno?: number, libro?: number, tarea?: number }} pesos
 */
export function calcularCePreview(cuaderno, libro, tarea, pesos = null) {
  const presentes = [];
  const claves = [];

  if (cuaderno !== '' && cuaderno != null && !Number.isNaN(Number(cuaderno))) {
    presentes.push(Number(cuaderno));
    claves.push('cuaderno');
  }
  if (libro !== '' && libro != null && !Number.isNaN(Number(libro))) {
    presentes.push(Number(libro));
    claves.push('libro');
  }
  if (tarea !== '' && tarea != null && !Number.isNaN(Number(tarea))) {
    presentes.push(Number(tarea));
    claves.push('tarea');
  }

  if (presentes.length === 0) return null;

  for (const v of presentes) {
    if (v < 0 || v > 20) return 'invalid';
  }

  const pesosDef = pesos ?? { cuaderno: 33.33, libro: 33.33, tarea: 33.34 };
  const esDefecto =
  Math.abs(pesosDef.cuaderno - 33.33) < 0.01 &&
  Math.abs(pesosDef.libro - 33.33) < 0.01 &&
  Math.abs(pesosDef.tarea - 33.34) < 0.01;

  if (esDefecto || presentes.length === claves.length) {
    if (esDefecto) {
      return (presentes.reduce((a, b) => a + b, 0) / presentes.length).toFixed(2);
    }
  }

  let sumaPesos = 0;
  let sumaPonderada = 0;
  for (const clave of claves) {
    const peso = Number(pesosDef[clave] ?? 0);
    const valor = clave === 'cuaderno' ? Number(cuaderno) : clave === 'libro' ? Number(libro) : Number(tarea);
    sumaPesos += peso;
    sumaPonderada += valor * peso;
  }

  if (sumaPesos <= 0) return null;
  return (sumaPonderada / sumaPesos).toFixed(2);
}

/**
 * CE preview para componentes dinámicos por nivel.
 * @param {Record<string|number, string|number>} componentesMap
 * @param {Array<{ id: number, peso?: number|string }>} componentesConfig
 */
export function calcularCePreviewDinamico(componentesMap, componentesConfig = []) {
  const presentes = [];

  for (const comp of componentesConfig) {
    const valor = componentesMap?.[comp.id] ?? componentesMap?.[String(comp.id)];
    if (valor === '' || valor == null || Number.isNaN(Number(valor))) continue;
    presentes.push({ nota: Number(valor), peso: Number(comp.peso ?? 0) });
  }

  if (presentes.length === 0) return null;

  for (const item of presentes) {
    if (item.nota < 0 || item.nota > 20) return 'invalid';
  }

  const pesos = presentes.map((item) => item.peso);
  const referencia = pesos[0];
  const pesosIguales = pesos.every((peso) => Math.abs(peso - referencia) < 0.02);

  if (pesosIguales) {
    const promedio = presentes.reduce((suma, item) => suma + item.nota, 0) / presentes.length;
    return promedio.toFixed(2);
  }

  let sumaPesos = 0;
  let sumaPonderada = 0;
  for (const item of presentes) {
    sumaPesos += item.peso;
    sumaPonderada += item.nota * item.peso;
  }

  if (sumaPesos <= 0) return null;
  return (sumaPonderada / sumaPesos).toFixed(2);
}

/**
 * @param {Record<string, unknown>} fila
 * @param {boolean} [esDinamico=false]
 * @param {Array<{ id: number }>} [componentes=[]]
 */
export function filaTieneAlMenosUnaNota(fila, esDinamico = false, componentes = []) {
  if (esDinamico) {
    for (const comp of componentes) {
      const valor = fila?.componentes?.[comp.id] ?? fila?.componentes?.[String(comp.id)];
      if (valor !== '' && valor != null) return true;
    }
    return false;
  }

  return ['nota_cuaderno', 'nota_libro', 'nota_tarea'].some((campo) => {
    const valor = fila?.[campo];
    return valor !== '' && valor != null;
  });
}

export function valorNotaParaInput(valor) {
  if (valor == null || valor === '') return '';
  return String(valor);
}

export function etiquetaBimestre(periodo) {
  const bim = periodo?.bimestre;
  return bim != null ? `Bimestre ${bim}` : 'Bimestre';
}

export function nombreEstudiante(est) {
  return `${est.apellidos ?? ''}, ${est.nombres ?? ''}`.trim();
}

export function obtenerMensajeErrorNotas(err, fallback = 'No se pudo completar la operación.') {
  if (err?.payload?.message) return err.payload.message;
  if (err?.payload?.errors) {
    const first = Object.values(err.payload.errors).flat()[0];
    if (first) return first;
  }
  return fallback;
}

export const ADVERTENCIA_ELIMINAR_NOTA =
  'Para eliminar una nota registrada se requiere una acción específica.';

export const MENSAJE_CALIFICACION_DINAMICA =
  'Los criterios de este nivel se califican con componentes configurados por nivel.';

export const MENSAJE_EXCEL_DINAMICO =
  'Excel usará los componentes de calificación configurados para este nivel.';

export const MENSAJE_PLANTILLA_EXCEL =
  'La plantilla se genera con los componentes configurados para el nivel.';
