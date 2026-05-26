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

export default function EvaluacionBimestralAlumnoRow({
  estudiante,
  fila,
  formulario,
  soloLectura,
  oralActivo,
  examenActivo,
  etaParticipantesIds,
  preview,
  onChangeCampo,
  onAbrirConclusion,
}) {
  const resultado = formulario?.resultados_por_estudiante?.[estudiante.id]
    ?? formulario?.resultados_por_estudiante?.[String(estudiante.id)]
    ?? null;

  const personalizados = componentesPersonalizadosActivos(formulario?.componentes);
  const etas = etasActivas(formulario?.etas);
  const tieneConclusion = Boolean((fila?.conclusion ?? '').trim() || resultado?.conclusion_descriptiva);
  const conclusionSinGuardar = (fila?.conclusion ?? '') !== (fila?._conclusionInicial ?? '');

  const promEta = soloLectura ? resultado?.promedio_eta : preview?.promedio_eta;
  const nivelNum = soloLectura ? resultado?.nivel_logro_numerico : preview?.nivel_logro_numerico;
  const nivelLit = soloLectura ? resultado?.nivel_logro_literal : preview?.nivel_logro_literal;
  const estado = soloLectura ? resultado?.estado_calculo : preview?.estado_calculo;
  const esPreview = !soloLectura && Boolean(preview?.esPreview);

  return (
    <tr className="border-b last:border-b-0 hover:bg-[var(--surface-muted)]/40">
      <td className="sticky left-0 z-10 w-[8rem] min-w-[8rem] max-w-[8rem] border-r border-[var(--border)] bg-[var(--surface)] px-2 py-0.5 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]">
        <span className="line-clamp-2 text-[11px] leading-tight" title={nombreEstudiante(estudiante)}>
          {nombreEstudiante(estudiante)}
        </span>
      </td>

      <td className="px-1 py-0.5 text-center">
        <EvalBimReadonlyCell value={resultado?.promedio_criterios} />
      </td>

      {oralActivo ? (
        <td className="px-1 py-0.5 text-center">
          {!soloLectura ? (
            <EvalBimInputCell
              value={fila?.oral ?? ''}
              invalid={notaFueraDeRango(fila?.oral)}
              onChange={(v) => onChangeCampo(estudiante.id, 'oral', v)}
            />
          ) : (
            <EvalBimReadonlyCell value={resultado?.oral ?? fila?.oral} />
          )}
        </td>
      ) : null}

      {etas.map((eta) => {
        const participa = etaParticipantesIds?.includes(eta.id)
          || etaParticipantesIds?.includes(String(eta.id));
        return (
          <td key={eta.id} className="px-1 py-0.5 text-center">
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
              />
            )}
            {participa ? (
              <span className="sr-only">ETA participante en el aula</span>
            ) : null}
          </td>
        );
      })}

      <td className="px-1 py-0.5 text-center">
        <EvalBimPreviewValue
          value={promEta}
          isPreview={esPreview}
          className="text-[11px] tabular-nums text-muted"
        />
      </td>

      {examenActivo ? (
        <td className="px-1 py-0.5 text-center">
          {!soloLectura ? (
            <EvalBimInputCell
              value={fila?.examen_bimestral ?? ''}
              invalid={notaFueraDeRango(fila?.examen_bimestral)}
              onChange={(v) => onChangeCampo(estudiante.id, 'examen_bimestral', v)}
            />
          ) : (
            <EvalBimReadonlyCell value={resultado?.examen_bimestral ?? fila?.examen_bimestral} />
          )}
        </td>
      ) : null}

      {personalizados.map((comp) => (
        <td key={comp.id} className="px-1 py-0.5 text-center">
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
            />
          )}
        </td>
      ))}

      <td className="px-1 py-0.5 text-center text-[11px] font-semibold tabular-nums text-[var(--primary-dark)]">
        <EvalBimPreviewValue value={nivelNum} isPreview={esPreview} />
      </td>

      <td className="px-1 py-0.5 text-center">
        <EvalBimPreviewValue
          isPreview={esPreview}
          className="text-[11px] font-bold tracking-wide text-[var(--text)]"
        >
          {nivelLit ?? '—'}
        </EvalBimPreviewValue>
      </td>

      <td className="px-1 py-0.5 text-center">
        <span
          className={`inline-flex items-center gap-0.5 rounded px-1 py-0.5 text-[9px] font-semibold uppercase ${claseEstadoCalculo(estado)}`}
        >
          {etiquetaEstadoCalculo(estado)}
          {esPreview ? (
            <span className="normal-case text-[8px] font-medium text-amber-800">preview</span>
          ) : null}
        </span>
      </td>

      <td className="px-1 py-0.5 text-center">
        <button
          type="button"
          className={`rounded border px-1.5 py-0.5 text-[10px] font-medium ${
            tieneConclusion
              ? 'border-[var(--primary)]/50 bg-[var(--primary)]/10 text-[var(--primary-dark)]'
              : 'border-[var(--border)] text-muted hover:bg-[var(--surface-muted)]'
          } ${conclusionSinGuardar ? 'ring-1 ring-amber-400/80' : ''}`}
          onClick={() => onAbrirConclusion(estudiante)}
          title={
            conclusionSinGuardar
              ? 'Conclusión editada sin guardar'
              : tieneConclusion
                ? 'Ver o editar conclusión'
                : 'Agregar conclusión'
          }
        >
          {conclusionSinGuardar ? '●' : tieneConclusion ? 'Ver' : '…'}
        </button>
      </td>
    </tr>
  );
}
