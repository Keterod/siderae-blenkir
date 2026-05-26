import { useMemo } from 'react';
import EvaluacionBimestralAlumnoRow from './EvaluacionBimestralAlumnoRow';
import {
  componentePorCodigo,
  componentesPersonalizadosActivos,
  computarPreviewsEvalBim,
  etasActivas,
} from './evaluacionBimestralUtils';

export default function EvaluacionBimestralTable({
  estudiantes,
  formulario,
  matriz,
  soloLectura,
  onChangeCampo,
  onAbrirConclusion,
}) {
  const componentes = formulario?.componentes ?? [];
  const oralComp = componentePorCodigo(componentes, 'oral');
  const examenComp = componentePorCodigo(componentes, 'examen_bimestral');
  const personalizados = componentesPersonalizadosActivos(componentes);
  const etas = etasActivas(formulario?.etas);

  const { participacion, porEstudiante } = useMemo(
    () => (soloLectura
      ? { participacion: { participantesIds: formulario?.eta_participantes_ids ?? [] }, porEstudiante: {} }
      : computarPreviewsEvalBim(estudiantes, matriz, formulario)),
    [soloLectura, estudiantes, matriz, formulario],
  );

  const etaParticipantesIds = soloLectura
    ? (formulario?.eta_participantes_ids ?? [])
    : participacion.participantesIds;

  if (!estudiantes.length) {
    return (
      <p className="text-xs text-muted">No hay estudiantes activos en esta sección.</p>
    );
  }

  return (
    <div className="overflow-hidden rounded border border-[var(--border)] bg-[var(--surface)]">
      <div className="max-h-[calc(100vh-14rem)] overflow-auto">
        <table className="w-max min-w-full border-collapse text-[11px]">
          <thead className="sticky top-0 z-20 bg-[var(--surface-muted)]">
            <tr className="border-b text-[10px] uppercase tracking-wide text-muted">
              <th className="sticky left-0 z-30 w-[8rem] min-w-[8rem] max-w-[8rem] border-r border-[var(--border)] bg-[var(--surface-muted)] px-2 py-1 text-left font-semibold shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]">
                Estudiante
              </th>
              <th className="w-14 min-w-[3.25rem] px-1 py-1 text-center font-semibold normal-case" title="Promedio de criterios (CE)">
                Prom. crit.
              </th>
              {oralComp?.activo !== false ? (
                <th className="w-9 min-w-[2.125rem] px-1 py-1 text-center font-semibold">Oral</th>
              ) : null}
              {etas.map((eta, idx) => (
                <th
                  key={eta.id}
                  className="w-9 min-w-[2.125rem] px-1 py-1 text-center font-semibold normal-case"
                  title={eta.nombre}
                >
                  {eta.nombre || `ETA ${idx + 1}`}
                </th>
              ))}
              <th className="w-14 min-w-[3.25rem] px-1 py-1 text-center font-semibold normal-case" title="Promedio ETA">
                Prom. ETA
              </th>
              {examenComp?.activo !== false ? (
                <th className="w-9 min-w-[2.125rem] px-1 py-1 text-center font-semibold">Examen</th>
              ) : null}
              {personalizados.map((comp) => (
                <th
                  key={comp.id}
                  className="min-w-[2.5rem] max-w-[5rem] px-1 py-1 text-center font-semibold normal-case"
                  title={comp.nombre}
                >
                  <span className="line-clamp-2 text-[9px] leading-3">{comp.nombre}</span>
                </th>
              ))}
              <th className="w-10 min-w-[2.5rem] px-1 py-1 text-center font-semibold">Nivel</th>
              <th className="w-9 min-w-[2.125rem] px-1 py-1 text-center font-semibold">Logro</th>
              <th className="w-14 min-w-[3.25rem] px-1 py-1 text-center font-semibold">Estado</th>
              <th className="w-12 min-w-[2.75rem] px-1 py-1 text-center font-semibold">Concl.</th>
            </tr>
          </thead>
          <tbody>
            {estudiantes.map((estudiante) => (
              <EvaluacionBimestralAlumnoRow
                key={estudiante.id}
                estudiante={estudiante}
                fila={matriz[estudiante.id] ?? {}}
                formulario={formulario}
                soloLectura={soloLectura}
                oralActivo={oralComp?.activo !== false}
                examenActivo={examenComp?.activo !== false}
                etaParticipantesIds={etaParticipantesIds}
                preview={porEstudiante[estudiante.id]}
                onChangeCampo={onChangeCampo}
                onAbrirConclusion={onAbrirConclusion}
              />
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
