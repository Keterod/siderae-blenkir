import Card from '../ui/Card';

const MENSAJE_RIESGO_PENDIENTE = 'Riesgo académico pendiente de actualización al nuevo flujo curricular.';

const SUBTEXTO_RIESGO_PENDIENTE =
  'Las notas, asistencia y evaluación ya se gestionan desde el módulo curricular. El cálculo de riesgo será actualizado en una fase posterior.';

export default function EstudiantePerfilRiesgo() {
  return (
    <Card
      className="space-y-4 border-[var(--border)] ring-1 ring-[var(--border)]/70"
      data-testid="perfil-riesgo"
    >
      <div className="border-b border-[var(--border)]/80 pb-4">
        <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Riesgo académico</h3>
        <p className="mt-1.5 text-sm leading-relaxed text-muted">
          Módulo en pausa mientras se rediseña el modelo institucional.
        </p>
      </div>

      <div
        className="rounded-lg border border-[var(--border)] bg-[var(--background)]/30 px-4 py-5"
        data-testid="perfil-riesgo-pendiente"
      >
        <p className="text-sm font-medium leading-relaxed text-[var(--text)]">{MENSAJE_RIESGO_PENDIENTE}</p>
        <p className="mt-2 text-sm leading-relaxed text-muted">{SUBTEXTO_RIESGO_PENDIENTE}</p>
      </div>
    </Card>
  );
}
