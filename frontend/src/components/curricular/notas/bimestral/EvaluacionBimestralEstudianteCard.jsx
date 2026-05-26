import { useMemo } from 'react';
import { nombreEstudiante } from '../../../../lib/notasCurricular';
import { notaFueraDeRango } from '../notasUtils';
import EvalBimInputCell from './EvalBimInputCell';
import EvalBimPreviewValue from './EvalBimPreviewValue';
import EvalBimReadonlyCell from './EvalBimReadonlyCell';
import {
  claseEstadoCalculo,
  componentePorCodigo,
  componentesPersonalizadosActivos,
  computarPreviewsEvalBim,
  etiquetaEstadoCalculo,
  etasActivas,
  formatoNotaDisplay,
} from './evaluacionBimestralUtils';

function CampoLecturaPreview({ etiqueta, valor, isPreview }) {
  return (
    <div>
      <p className="text-[10px] font-medium uppercase text-muted">{etiqueta}</p>
      <EvalBimPreviewValue
        value={valor}
        isPreview={isPreview}
        className="text-sm font-semibold tabular-nums text-[var(--text)]"
      />
    </div>
  );
}

export default function EvaluacionBimestralEstudianteCard({
  estudiante,
  formulario,
  fila,
  matriz,
  estudiantes,
  soloLectura,
  onChangeCampo,
  onAbrirConclusion,
}) {
  const previewData = useMemo(() => {
    if (soloLectura || !estudiante || !formulario) return null;
    const lista = estudiantes?.length ? estudiantes : [estudiante];
    const mat = matriz ?? { [estudiante.id]: fila };
    return computarPreviewsEvalBim(lista, mat, formulario);
  }, [soloLectura, estudiante, formulario, fila, matriz, estudiantes]);

  if (!formulario || !estudiante) return null;

  const resultado = formulario?.resultados_por_estudiante?.[estudiante.id]
    ?? formulario?.resultados_por_estudiante?.[String(estudiante.id)]
    ?? null;

  const preview = previewData?.porEstudiante?.[estudiante.id] ?? null;
  const esPreview = !soloLectura && Boolean(preview?.esPreview);

  const oralComp = componentePorCodigo(formulario.componentes, 'oral');
  const examenComp = componentePorCodigo(formulario.componentes, 'examen_bimestral');
  const personalizados = componentesPersonalizadosActivos(formulario.componentes);
  const etas = etasActivas(formulario.etas);
  const tieneConclusion = Boolean((fila?.conclusion ?? '').trim() || resultado?.conclusion_descriptiva);
  const conclusionSinGuardar = (fila?.conclusion ?? '') !== (fila?._conclusionInicial ?? '');

  const promEta = soloLectura ? resultado?.promedio_eta : preview?.promedio_eta;
  const nivelNum = soloLectura ? resultado?.nivel_logro_numerico : preview?.nivel_logro_numerico;
  const nivelLit = soloLectura ? resultado?.nivel_logro_literal : preview?.nivel_logro_literal;
  const estado = soloLectura ? resultado?.estado_calculo : preview?.estado_calculo;

  return (
    <section
      className="mt-3 rounded border border-[var(--border)] bg-[var(--surface)] p-3"
      data-testid="eval-bim-estudiante-card"
    >
      <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
        <h3 className="text-xs font-semibold text-[var(--text)]">Evaluación bimestral</h3>
        <span
          className={`inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-semibold uppercase ${claseEstadoCalculo(estado)}`}
        >
          {etiquetaEstadoCalculo(estado)}
          {esPreview ? (
            <span className="normal-case text-[8px] font-medium text-amber-800">preview</span>
          ) : null}
        </span>
      </div>
      <p className="mb-2 text-[11px] text-muted">{nombreEstudiante(estudiante)}</p>

      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div>
          <p className="text-[10px] font-medium uppercase text-muted">Prom. criterios</p>
          <p className="text-sm font-semibold tabular-nums text-[var(--text)]">
            {formatoNotaDisplay(resultado?.promedio_criterios)}
          </p>
        </div>
        {oralComp?.activo !== false ? (
          <div>
            <p className="text-[10px] font-medium uppercase text-muted">Oral</p>
            {!soloLectura ? (
              <EvalBimInputCell
                value={fila?.oral ?? ''}
                invalid={notaFueraDeRango(fila?.oral)}
                onChange={(v) => onChangeCampo(estudiante.id, 'oral', v)}
              />
            ) : (
              <EvalBimReadonlyCell value={resultado?.oral} className="text-left" />
            )}
          </div>
        ) : null}
        {etas.map((eta, idx) => (
          <div key={eta.id}>
            <p className="text-[10px] font-medium uppercase text-muted">{eta.nombre || `ETA ${idx + 1}`}</p>
            {!soloLectura ? (
              <EvalBimInputCell
                value={fila?.etas?.[eta.id] ?? ''}
                invalid={notaFueraDeRango(fila?.etas?.[eta.id])}
                onChange={(v) => onChangeCampo(estudiante.id, 'eta', v, eta.id)}
              />
            ) : (
              <EvalBimReadonlyCell
                value={
                  formulario?.notas_eta_por_estudiante?.[estudiante.id]?.[eta.id]?.nota
                  ?? formulario?.notas_eta_por_estudiante?.[String(estudiante.id)]?.[eta.id]?.nota
                }
                className="text-left"
              />
            )}
          </div>
        ))}
        <CampoLecturaPreview etiqueta="Prom. ETA" valor={promEta} isPreview={esPreview} />
        {examenComp?.activo !== false ? (
          <div>
            <p className="text-[10px] font-medium uppercase text-muted">Examen</p>
            {!soloLectura ? (
              <EvalBimInputCell
                value={fila?.examen_bimestral ?? ''}
                invalid={notaFueraDeRango(fila?.examen_bimestral)}
                onChange={(v) => onChangeCampo(estudiante.id, 'examen_bimestral', v)}
              />
            ) : (
              <EvalBimReadonlyCell value={resultado?.examen_bimestral} className="text-left" />
            )}
          </div>
        ) : null}
        {personalizados.map((comp) => (
          <div key={comp.id}>
            <p className="truncate text-[10px] font-medium uppercase text-muted" title={comp.nombre}>
              {comp.nombre}
            </p>
            {!soloLectura ? (
              <EvalBimInputCell
                value={fila?.personalizados?.[comp.id] ?? ''}
                invalid={notaFueraDeRango(fila?.personalizados?.[comp.id])}
                onChange={(v) => onChangeCampo(estudiante.id, 'personalizado', v, comp.id)}
              />
            ) : (
              <EvalBimReadonlyCell
                value={
                  formulario?.notas_scalar_por_estudiante?.[estudiante.id]?.[comp.id]?.nota
                  ?? formulario?.notas_scalar_por_estudiante?.[String(estudiante.id)]?.[comp.id]?.nota
                }
                className="text-left"
              />
            )}
          </div>
        ))}
        <CampoLecturaPreview etiqueta="Nivel numérico" valor={nivelNum} isPreview={esPreview} />
        <div>
          <p className="text-[10px] font-medium uppercase text-muted">Logro</p>
          <EvalBimPreviewValue
            isPreview={esPreview}
            className="text-sm font-bold text-[var(--text)]"
          >
            {nivelLit ?? '—'}
          </EvalBimPreviewValue>
        </div>
      </div>

      <div className="mt-3">
        <button
          type="button"
          className={`rounded border px-2 py-1 text-[11px] font-medium ${
            tieneConclusion
              ? 'border-[var(--primary)]/50 bg-[var(--primary)]/10 text-[var(--primary-dark)]'
              : 'border-[var(--border)] text-muted'
          } ${conclusionSinGuardar ? 'ring-1 ring-amber-400/80' : ''}`}
          onClick={() => onAbrirConclusion(estudiante)}
        >
          {conclusionSinGuardar
            ? 'Conclusión sin guardar'
            : tieneConclusion
              ? 'Ver conclusión descriptiva'
              : 'Agregar conclusión descriptiva'}
        </button>
      </div>
    </section>
  );
}
