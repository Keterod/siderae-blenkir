import { valorNotaParaInput } from '../../../../lib/notasCurricular';
import { notaFueraDeRango } from '../notasUtils';

export function componentesActivos(componentes = []) {
  return componentes.filter((c) => c.activo !== false);
}

export function etasActivas(etas = []) {
  return etas.filter((e) => e.activo !== false).sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0));
}

export function componentePorCodigo(componentes, codigo) {
  return componentesActivos(componentes).find((c) => c.codigo === codigo) ?? null;
}

export function componentesPersonalizadosActivos(componentes = []) {
  return componentesActivos(componentes).filter((c) => c.tipo === 'personalizado');
}

export function initMatrizEvalBim(estudiantes, formulario) {
  const componentes = formulario?.componentes ?? [];
  const oralComp = componentePorCodigo(componentes, 'oral');
  const examenComp = componentePorCodigo(componentes, 'examen_bimestral');
  const personalizados = componentesPersonalizadosActivos(componentes);
  const etas = etasActivas(formulario?.etas ?? []);

  const matriz = {};

  for (const estudiante of estudiantes) {
    const id = estudiante.id;
    const scalars = formulario?.notas_scalar_por_estudiante?.[id]
      ?? formulario?.notas_scalar_por_estudiante?.[String(id)]
      ?? {};
    const etasNotas = formulario?.notas_eta_por_estudiante?.[id]
      ?? formulario?.notas_eta_por_estudiante?.[String(id)]
      ?? {};
    const resultado = formulario?.resultados_por_estudiante?.[id]
      ?? formulario?.resultados_por_estudiante?.[String(id)]
      ?? null;

    const fila = {
      oral: '',
      examen_bimestral: '',
      personalizados: {},
      etas: {},
      conclusion: resultado?.conclusion_descriptiva ?? '',
      _teniaOral: false,
      _teniaExamen: false,
      _teniaPersonalizados: {},
      _teniaEtas: {},
      _conclusionInicial: resultado?.conclusion_descriptiva ?? '',
    };

    if (oralComp) {
      const nota = scalars[oralComp.id]?.nota ?? scalars[String(oralComp.id)]?.nota;
      fila.oral = valorNotaParaInput(nota);
      fila._teniaOral = nota != null;
    }

    if (examenComp) {
      const nota = scalars[examenComp.id]?.nota ?? scalars[String(examenComp.id)]?.nota;
      fila.examen_bimestral = valorNotaParaInput(nota);
      fila._teniaExamen = nota != null;
    }

    for (const comp of personalizados) {
      const nota = scalars[comp.id]?.nota ?? scalars[String(comp.id)]?.nota;
      fila.personalizados[comp.id] = valorNotaParaInput(nota);
      fila._teniaPersonalizados[comp.id] = nota != null;
    }

    for (const eta of etas) {
      const nota = etasNotas[eta.id]?.nota ?? etasNotas[String(eta.id)]?.nota;
      fila.etas[eta.id] = valorNotaParaInput(nota);
      fila._teniaEtas[eta.id] = nota != null;
    }

    matriz[id] = fila;
  }

  return matriz;
}

export function initFilaEvalBimEstudiante(estudianteId, formulario) {
  const matriz = initMatrizEvalBim([{ id: estudianteId }], formulario);
  return matriz[estudianteId] ?? {
    oral: '',
    examen_bimestral: '',
    personalizados: {},
    etas: {},
    conclusion: '',
    _teniaOral: false,
    _teniaExamen: false,
    _teniaPersonalizados: {},
    _teniaEtas: {},
    _conclusionInicial: '',
  };
}

export function validarMatrizEvalBim(matriz, estudiantes, nombreEstudianteFn) {
  for (const estudiante of estudiantes) {
    const fila = matriz[estudiante.id] ?? {};
    const campos = [
      ['oral', 'Oral'],
      ['examen_bimestral', 'Examen bimestral'],
    ];
    for (const [campo, etiqueta] of campos) {
      if (notaFueraDeRango(fila[campo])) {
        return `Nota inválida (${nombreEstudianteFn(estudiante)} · ${etiqueta}). Debe estar entre 0 y 20.`;
      }
    }
    for (const valor of Object.values(fila.personalizados ?? {})) {
      if (notaFueraDeRango(valor)) {
        return `Nota inválida (${nombreEstudianteFn(estudiante)} · componente personalizado). Debe estar entre 0 y 20.`;
      }
    }
    for (const [etaId, valor] of Object.entries(fila.etas ?? {})) {
      if (notaFueraDeRango(valor)) {
        return `Nota inválida (${nombreEstudianteFn(estudiante)} · ETA). Debe estar entre 0 y 20.`;
      }
    }
  }
  return null;
}

function filaTieneDatosEvalBim(fila) {
  if (fila.oral !== '') return true;
  if (fila.examen_bimestral !== '') return true;
  if ((fila.conclusion ?? '') !== (fila._conclusionInicial ?? '')) return true;
  if (Object.values(fila.personalizados ?? {}).some((v) => v !== '')) return true;
  if (Object.values(fila.etas ?? {}).some((v) => v !== '')) return true;
  return false;
}

export function construirPayloadEvalBim(matriz, estudiantes, componentes, etas) {
  const oralComp = componentePorCodigo(componentes, 'oral');
  const examenComp = componentePorCodigo(componentes, 'examen_bimestral');
  const personalizados = componentesPersonalizadosActivos(componentes);
  const etasActivasList = etasActivas(etas);

  const registrosPorEstudiante = [];
  let intentoBorrar = false;

  for (const estudiante of estudiantes) {
    const fila = matriz[estudiante.id] ?? {};
    if (!filaTieneDatosEvalBim(fila)) {
      if (fila._teniaOral || fila._teniaExamen) intentoBorrar = true;
      if (Object.values(fila._teniaPersonalizados ?? {}).some(Boolean)) intentoBorrar = true;
      if (Object.values(fila._teniaEtas ?? {}).some(Boolean)) intentoBorrar = true;
      continue;
    }

    const registro = { estudiante_id: estudiante.id };

    if (oralComp && fila.oral !== '') {
      registro.oral = Number(fila.oral);
    } else if (fila._teniaOral && fila.oral === '') {
      intentoBorrar = true;
    }

    if (examenComp && fila.examen_bimestral !== '') {
      registro.examen_bimestral = Number(fila.examen_bimestral);
    } else if (fila._teniaExamen && fila.examen_bimestral === '') {
      intentoBorrar = true;
    }

    const componentesPayload = [];
    for (const comp of personalizados) {
      const valor = fila.personalizados?.[comp.id] ?? '';
      if (valor !== '') {
        componentesPayload.push({ componente_id: comp.id, nota: Number(valor) });
      } else if (fila._teniaPersonalizados?.[comp.id]) {
        intentoBorrar = true;
      }
    }
    if (componentesPayload.length > 0) {
      registro.componentes_personalizados = componentesPayload;
    }

    const etasPayload = [];
    for (const eta of etasActivasList) {
      const valor = fila.etas?.[eta.id] ?? '';
      if (valor !== '') {
        etasPayload.push({ eta_item_id: eta.id, nota: Number(valor) });
      } else if (fila._teniaEtas?.[eta.id]) {
        intentoBorrar = true;
      }
    }
    if (etasPayload.length > 0) {
      registro.etas = etasPayload;
    }

    if ((fila.conclusion ?? '') !== (fila._conclusionInicial ?? '')) {
      registro.conclusion_descriptiva = fila.conclusion ?? '';
    } else if (fila.conclusion) {
      registro.conclusion_descriptiva = fila.conclusion;
    }

    registrosPorEstudiante.push(registro);
  }

  return { registrosPorEstudiante, intentoBorrar };
}

export function formatoNotaDisplay(valor) {
  if (valor == null || valor === '') return '—';
  const n = Number(valor);
  if (Number.isNaN(n)) return '—';
  return Number.isInteger(n) ? String(n) : n.toFixed(2);
}

export function etiquetaEstadoCalculo(estado) {
  if (estado === 'completo') return 'Completo';
  if (estado === 'pendiente') return 'Pendiente';
  return estado ?? '—';
}

export function claseEstadoCalculo(estado) {
  if (estado === 'completo') {
    return 'bg-emerald-100 text-emerald-900';
  }
  return 'bg-amber-100 text-amber-950';
}

export function mergeResultadosEnFormulario(formulario, resultadosApi) {
  if (!formulario || !Array.isArray(resultadosApi)) return formulario;
  const mapa = { ...(formulario.resultados_por_estudiante ?? {}) };
  for (const r of resultadosApi) {
    mapa[r.estudiante_id] = {
      estudiante_id: r.estudiante_id,
      promedio_criterios: r.promedio_criterios,
      oral: r.oral,
      promedio_eta: r.promedio_eta,
      examen_bimestral: r.examen_bimestral,
      nivel_logro_numerico: r.nivel_logro_numerico,
      nivel_logro_literal: r.nivel_logro_literal,
      conclusion_descriptiva: r.conclusion_descriptiva,
      estado_calculo: r.estado_calculo,
      detalle_json: r.detalle_json,
      calculado_en: r.calculado_en,
    };
  }
  return { ...formulario, resultados_por_estudiante: mapa };
}

// --- Previsualización en vivo (no reemplaza cálculo backend) ---

const ESCALA_DEFECTO = [
  { codigo_literal: 'AD', nota_min: 18, nota_max: 20 },
  { codigo_literal: 'A', nota_min: 14, nota_max: 17.99 },
  { codigo_literal: 'B', nota_min: 11, nota_max: 13.99 },
  { codigo_literal: 'C', nota_min: 0, nota_max: 10.99 },
];

export function etaEstaCargada(valor) {
  if (valor === null || valor === undefined || valor === '') return false;
  const n = Number(valor);
  return !Number.isNaN(n) && n >= 0 && n <= 20;
}

function renormalizarPesos(pesos) {
  const ids = Object.keys(pesos).map(Number);
  if (ids.length === 0) return {};
  const suma = ids.reduce((acc, id) => acc + pesos[id], 0);
  if (suma <= 0) return { ...pesos };

  const resultado = {};
  let acumulado = 0;
  ids.forEach((id, idx) => {
    if (idx === ids.length - 1) {
      resultado[id] = Math.round((100 - acumulado) * 100) / 100;
    } else {
      const normalizado = Math.round((pesos[id] / suma) * 10000) / 100;
      resultado[id] = normalizado;
      acumulado += normalizado;
    }
  });
  return resultado;
}

function resultadoEstudiante(formulario, estudianteId) {
  return formulario?.resultados_por_estudiante?.[estudianteId]
    ?? formulario?.resultados_por_estudiante?.[String(estudianteId)]
    ?? null;
}

function notaScalarBackend(formulario, estudianteId, componenteId) {
  const scalars = formulario?.notas_scalar_por_estudiante?.[estudianteId]
    ?? formulario?.notas_scalar_por_estudiante?.[String(estudianteId)]
    ?? {};
  const nota = scalars[componenteId]?.nota ?? scalars[String(componenteId)]?.nota;
  return nota != null ? Number(nota) : null;
}

export function resolverNotaEtaPreview(estudianteId, etaId, fila, formulario) {
  const local = fila?.etas?.[etaId];
  if (local !== undefined && local !== '') {
    const n = Number(local);
    return Number.isNaN(n) ? null : n;
  }
  if (local === '') return null;

  const backend = formulario?.notas_eta_por_estudiante?.[estudianteId]?.[etaId]?.nota
    ?? formulario?.notas_eta_por_estudiante?.[String(estudianteId)]?.[etaId]?.nota
    ?? formulario?.notas_eta_por_estudiante?.[estudianteId]?.[String(etaId)]?.nota;
  return backend != null ? Number(backend) : null;
}

function resolverScalarPreview(estudianteId, fila, formulario, componenteId, campoLocal) {
  const local = fila?.[campoLocal];
  if (local !== undefined && local !== '') {
    const n = Number(local);
    if (!Number.isNaN(n) && n >= 0 && n <= 20) return n;
    return null;
  }
  if (local === '') return null;

  const backend = notaScalarBackend(formulario, estudianteId, componenteId);
  if (backend != null) return backend;

  const resultado = resultadoEstudiante(formulario, estudianteId);
  if (campoLocal === 'oral' && resultado?.oral != null) return Number(resultado.oral);
  if (campoLocal === 'examen_bimestral' && resultado?.examen_bimestral != null) {
    return Number(resultado.examen_bimestral);
  }
  return null;
}

function resolverPersonalizadoPreview(estudianteId, fila, formulario, componenteId) {
  const local = fila?.personalizados?.[componenteId];
  if (local !== undefined && local !== '') {
    const n = Number(local);
    if (!Number.isNaN(n) && n >= 0 && n <= 20) return n;
    return null;
  }
  if (local === '') return null;
  return notaScalarBackend(formulario, estudianteId, componenteId);
}

export function calcularParticipacionEtasPreview(estudiantes, matriz, formulario, etasList) {
  if (!etasList?.length || !estudiantes?.length) {
    return {
      participantesIds: [],
      pesosEfectivos: {},
      notasPorEstudianteEta: {},
    };
  }

  const notasPorEstudianteEta = {};
  for (const est of estudiantes) {
    const fila = matriz[est.id] ?? {};
    notasPorEstudianteEta[est.id] = {};
    for (const eta of etasList) {
      notasPorEstudianteEta[est.id][eta.id] = resolverNotaEtaPreview(est.id, eta.id, fila, formulario);
    }
  }

  const participantes = etasList.filter((eta) =>
    estudiantes.some((est) => etaEstaCargada(notasPorEstudianteEta[est.id][eta.id])),
  );

  const pesosConfig = {};
  for (const eta of participantes) {
    pesosConfig[eta.id] = Number(eta.peso_interno);
  }

  const pesosEfectivos = Object.keys(pesosConfig).length > 0
    ? renormalizarPesos(pesosConfig)
    : {};

  return {
    participantesIds: participantes.map((e) => e.id),
    pesosEfectivos,
    notasPorEstudianteEta,
  };
}

export function calcularPromedioEtaPreview(estudianteId, participacion) {
  const { pesosEfectivos, notasPorEstudianteEta } = participacion;
  if (!pesosEfectivos || Object.keys(pesosEfectivos).length === 0) {
    return { valor: null, pendienteBloque: true };
  }

  const notas = notasPorEstudianteEta[estudianteId] ?? {};
  let sumaPonderada = 0;
  for (const [etaId, peso] of Object.entries(pesosEfectivos)) {
    const nota = notas[Number(etaId)];
    const valor = etaEstaCargada(nota) ? Number(nota) : 0;
    sumaPonderada += valor * (Number(peso) / 100);
  }

  return {
    valor: Math.round(sumaPonderada * 100) / 100,
    pendienteBloque: false,
  };
}

export function resolverLiteralPreview(nivelNumerico, escalaLogro) {
  if (nivelNumerico == null) return null;
  const nota = Math.round(Number(nivelNumerico) * 100) / 100;
  const rangos = (escalaLogro?.length ? escalaLogro : ESCALA_DEFECTO).map((r) => ({
    codigo: r.codigo_literal ?? r.literal ?? r.codigo,
    min: Number(r.nota_min ?? r.minimo ?? r.min),
    max: Number(r.nota_max ?? r.maximo ?? r.max),
  }));

  for (const rango of rangos) {
    if (nota >= rango.min && nota <= rango.max) return rango.codigo;
  }
  return null;
}

export function calcularNivelLogroPreview(estudianteId, matriz, formulario, participacion) {
  const fila = matriz[estudianteId] ?? {};
  const resultado = resultadoEstudiante(formulario, estudianteId);
  const componentes = componentesActivos(formulario?.componentes ?? []);
  const pendientes = [];
  const valoresPorCodigo = {};

  let promedioEtaPreview = null;

  for (const comp of componentes) {
    let valor = null;
    let pendiente = false;

    switch (comp.tipo) {
      case 'promedio_criterios':
        valor = resultado?.promedio_criterios ?? null;
        pendiente = valor == null;
        break;
      case 'oral':
        valor = resolverScalarPreview(estudianteId, fila, formulario, comp.id, 'oral');
        pendiente = valor === null;
        break;
      case 'promedio_eta': {
        const etaRes = calcularPromedioEtaPreview(estudianteId, participacion);
        promedioEtaPreview = etaRes.pendienteBloque ? null : etaRes.valor;
        if (etaRes.pendienteBloque) {
          pendiente = true;
          valor = null;
        } else {
          valor = etaRes.valor;
          pendiente = false;
        }
        break;
      }
      case 'examen_bimestral':
        valor = resolverScalarPreview(estudianteId, fila, formulario, comp.id, 'examen_bimestral');
        pendiente = valor === null;
        break;
      case 'personalizado':
        valor = resolverPersonalizadoPreview(estudianteId, fila, formulario, comp.id);
        pendiente = valor === null;
        break;
      default:
        pendiente = true;
        break;
    }

    if (pendiente) pendientes.push(comp.codigo);
    else valoresPorCodigo[comp.codigo] = valor;
  }

  if (pendientes.length > 0) {
    return {
      promedio_eta: promedioEtaPreview,
      nivel_logro_numerico: null,
      nivel_logro_literal: null,
      estado_calculo: 'pendiente',
      pendientes,
    };
  }

  let suma = 0;
  for (const comp of componentes) {
    const v = valoresPorCodigo[comp.codigo];
    if (v != null) suma += v * (Number(comp.peso) / 100);
  }
  const nivel = Math.round(suma * 100) / 100;

  return {
    promedio_eta: promedioEtaPreview,
    nivel_logro_numerico: nivel,
    nivel_logro_literal: resolverLiteralPreview(nivel, formulario?.escala_logro),
    estado_calculo: 'completo',
    pendientes: [],
  };
}

function numDiff(a, b) {
  if (a == null && b == null) return false;
  if (a == null || b == null) return true;
  return Math.abs(Number(a) - Number(b)) > 0.009;
}

export function previewDifiereDePersistido(preview, resultado) {
  if (!preview) return false;
  if (!resultado) {
    return preview.estado_calculo === 'completo'
      || preview.promedio_eta != null
      || preview.nivel_logro_numerico != null;
  }
  if (preview.estado_calculo !== resultado.estado_calculo) return true;
  if (numDiff(preview.promedio_eta, resultado.promedio_eta)) return true;
  if (numDiff(preview.nivel_logro_numerico, resultado.nivel_logro_numerico)) return true;
  if ((preview.nivel_logro_literal ?? null) !== (resultado.nivel_logro_literal ?? null)) return true;
  return false;
}

function scalarCambio(local, backendValor, teniaBackend) {
  if (local === '') return Boolean(teniaBackend);
  const n = Number(local);
  if (Number.isNaN(n)) return false;
  if (backendValor == null) return true;
  return Math.abs(n - Number(backendValor)) > 0.009;
}

export function detectarCambiosSinGuardar(fila, resultado, formulario, estudianteId) {
  if (!fila) return false;
  if ((fila.conclusion ?? '') !== (fila._conclusionInicial ?? '')) return true;
  return detectarCambiosEscalaresSinGuardar(fila, resultado, formulario, estudianteId);
}

export function detectarCambiosEscalaresSinGuardar(fila, resultado, formulario, estudianteId) {
  if (!fila) return false;

  const componentes = formulario?.componentes ?? [];
  const oralComp = componentePorCodigo(componentes, 'oral');
  if (oralComp && scalarCambio(fila.oral, resultado?.oral ?? notaScalarBackend(formulario, estudianteId, oralComp.id), fila._teniaOral)) {
    return true;
  }

  const examenComp = componentePorCodigo(componentes, 'examen_bimestral');
  if (examenComp && scalarCambio(
    fila.examen_bimestral,
    resultado?.examen_bimestral ?? notaScalarBackend(formulario, estudianteId, examenComp.id),
    fila._teniaExamen,
  )) {
    return true;
  }

  for (const comp of componentesPersonalizadosActivos(componentes)) {
    const local = fila.personalizados?.[comp.id] ?? '';
    const backend = notaScalarBackend(formulario, estudianteId, comp.id);
    if (scalarCambio(local, backend, fila._teniaPersonalizados?.[comp.id])) return true;
  }

  for (const eta of etasActivas(formulario?.etas)) {
    const local = fila.etas?.[eta.id] ?? '';
    const backendNotas = formulario?.notas_eta_por_estudiante?.[estudianteId]
      ?? formulario?.notas_eta_por_estudiante?.[String(estudianteId)]
      ?? {};
    const raw = backendNotas[eta.id]?.nota ?? backendNotas[String(eta.id)]?.nota;
    const backend = raw != null ? Number(raw) : null;
    if (scalarCambio(local, backend, fila._teniaEtas?.[eta.id])) return true;
  }

  return false;
}

export function computarPreviewsEvalBim(estudiantes, matriz, formulario) {
  const etas = etasActivas(formulario?.etas ?? []);
  const participacion = calcularParticipacionEtasPreview(estudiantes, matriz, formulario, etas);
  const porEstudiante = {};

  for (const est of estudiantes) {
    const preview = calcularNivelLogroPreview(est.id, matriz, formulario, participacion);
    const resultado = resultadoEstudiante(formulario, est.id);
    const fila = matriz[est.id] ?? {};
    porEstudiante[est.id] = {
      ...preview,
      esPreview: previewDifiereDePersistido(preview, resultado)
        || detectarCambiosEscalaresSinGuardar(fila, resultado, formulario, est.id),
    };
  }

  return { participacion, porEstudiante };
}
