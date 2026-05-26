import RegistroNotasCompetenciaBlock from './RegistroNotasCompetenciaBlock';

export default function RegistroNotasAulaTable({
  soloLectura = false,
  estructura,
  estudiantes,
  matriz,
  pesos,
  onChangeNota,
}) {
  if (!estudiantes.length) {
    return (
      <p className="text-xs text-muted">No hay estudiantes activos en esta sección.</p>
    );
  }

  return (
    <div className="space-y-4">
      {estructura.map((grupo) => (
        <RegistroNotasCompetenciaBlock
          key={grupo.competencia.id}
          grupo={grupo}
          estudiantes={estudiantes}
          matriz={matriz}
          pesos={pesos}
          soloLectura={soloLectura}
          onChangeNota={onChangeNota}
        />
      ))}
    </div>
  );
}
