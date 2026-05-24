import Card from '../../ui/Card';

export default function CriteriosResumen({
  nivelLabel,
  grado,
  anioEscolar,
  nombreCurso,
  totalActivos,
  totalRegistrados,
}) {
  return (
    <Card className="border-[var(--border)]/90 bg-gradient-to-br from-orange-50/30 via-[var(--surface)] to-[#eaf2fb]/25 p-5 sm:p-6">
      <p className="text-sm text-muted">
        {nivelLabel} · {grado} · {anioEscolar}
      </p>
      <h3 className="mt-1 text-lg font-semibold text-[var(--text)]">
        Curso: {nombreCurso || '—'}
      </h3>
      <p className="mt-2 text-sm text-muted">
        {totalActivos} criterio{totalActivos === 1 ? '' : 's'} activo{totalActivos === 1 ? '' : 's'}{' '}
        · {totalRegistrados} registrado{totalRegistrados === 1 ? '' : 's'}
      </p>
    </Card>
  );
}
