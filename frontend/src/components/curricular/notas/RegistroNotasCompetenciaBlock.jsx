import RegistroNotasCapacidadTable from './RegistroNotasCapacidadTable';

export default function RegistroNotasCompetenciaBlock({
  grupo,
  estudiantes,
  matriz,
  pesos,
  onChangeNota,
}) {
  return (
    <section className="space-y-1.5">
      <p className="truncate text-[11px] font-semibold text-[var(--text)]" title={grupo.competencia.nombre}>
        <span className="mr-1 text-[10px] font-semibold uppercase text-muted">Comp.</span>
        {grupo.competencia.nombre}
      </p>
      {grupo.capacidades.map(({ capacidad, criterios }) => (
        <RegistroNotasCapacidadTable
          key={capacidad.id}
          capacidad={capacidad}
          criterios={criterios}
          estudiantes={estudiantes}
          matriz={matriz}
          pesos={pesos}
          onChangeNota={onChangeNota}
        />
      ))}
    </section>
  );
}
