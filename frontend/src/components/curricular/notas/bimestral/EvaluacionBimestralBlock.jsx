import Button from '../../../ui/Button';
import LoadingState from '../../../ui/LoadingState';
import ConclusionDescriptivaModal from './ConclusionDescriptivaModal';
import EvaluacionBimestralTable from './EvaluacionBimestralTable';

export default function EvaluacionBimestralBlock({
  formulario,
  estudiantes,
  matriz,
  soloLectura,
  cargando,
  guardando,
  puedeGuardar,
  onChangeCampo,
  onGuardar,
  modalConclusion,
  onCerrarConclusion,
  onGuardarConclusion,
}) {
  if (cargando) {
    return (
      <section className="mt-4" data-testid="eval-bim-block">
        <LoadingState label="Cargando evaluación bimestral…" />
      </section>
    );
  }

  if (!formulario) {
    return null;
  }

  const escala = formulario.escala_logro ?? [];

  return (
    <section className="mt-4 space-y-2" data-testid="eval-bim-block">
      <div className="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--border)] pb-1.5">
        <div>
          <h3 className="text-xs font-semibold text-[var(--text)]">Evaluación bimestral</h3>
          {soloLectura ? (
            <p className="text-[10px] text-muted">Modo consulta — solo lectura</p>
          ) : (
            <p className="text-[10px] text-muted">
              Oral, ETAs, examen y personalizados. Prom. ETA y nivel se previsualizan al escribir; al guardar se confirma en el sistema.
            </p>
          )}
        </div>
        {!soloLectura ? (
          <Button
            type="button"
            variant="primary"
            size="sm"
            className="shrink-0 px-3 py-1 text-xs"
            disabled={guardando || !puedeGuardar}
            onClick={onGuardar}
            data-testid="eval-bim-guardar"
          >
            {guardando ? 'Guardando…' : 'Guardar evaluación bimestral'}
          </Button>
        ) : null}
      </div>

      {escala.length > 0 ? (
        <p className="text-[10px] text-muted">
          Escala:
          {' '}
          {escala.map((e) => `${e.literal} (${e.minimo}–${e.maximo})`).join(' · ')}
        </p>
      ) : null}

      <EvaluacionBimestralTable
        estudiantes={estudiantes}
        formulario={formulario}
        matriz={matriz}
        soloLectura={soloLectura}
        onChangeCampo={onChangeCampo}
        onAbrirConclusion={modalConclusion.onAbrir}
      />

      <ConclusionDescriptivaModal
        abierto={modalConclusion.abierto}
        estudiante={modalConclusion.estudiante}
        valorInicial={modalConclusion.valorInicial}
        soloLectura={soloLectura}
        onCerrar={onCerrarConclusion}
        onGuardar={onGuardarConclusion}
      />
    </section>
  );
}
