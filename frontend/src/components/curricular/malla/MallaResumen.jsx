import Card from '../../ui/Card';

function StatPill({ label, value }) {
  return (
    <div className="rounded-lg border border-[var(--border)]/80 bg-[var(--surface)] px-4 py-3 shadow-sm">
      <p className="text-xs font-medium uppercase tracking-wide text-muted">{label}</p>
      <p className="mt-1 text-lg font-semibold text-[var(--text)]">{value}</p>
    </div>
  );
}

export default function MallaResumen({ filtros, nivelLabel, totalAreas, totalCursosActivos, mostrarContenido }) {
  return (
    <Card className="border-[var(--border)]/90 bg-gradient-to-br from-orange-50/40 via-[var(--surface)] to-[#eaf2fb]/30 p-5 sm:p-6">
      <h3 className="text-sm font-semibold text-[var(--text)]">Resumen del grado</h3>
      <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <StatPill label="Año escolar" value={filtros.anio_escolar} />
        <StatPill label="Nivel" value={nivelLabel} />
        <StatPill label="Grado" value={filtros.grado} />
        <StatPill label="Total de áreas" value={totalAreas} />
        <StatPill label="Cursos activos" value={mostrarContenido ? totalCursosActivos : '—'} />
      </div>
    </Card>
  );
}
