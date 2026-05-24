import { FIELD } from './utils';

export default function CompetenciaSelector({
  competenciaId,
  capacidadId,
  competencias,
  capacidades,
  onChangeCompetencia,
  onChangeCapacidad,
}) {
  return (
    <>
      <label className="block text-sm font-medium text-[var(--text)]">
        Competencia
        <select
          className={FIELD}
          value={competenciaId}
          onChange={(e) => onChangeCompetencia(e.target.value)}
          required
        >
          <option value="">Seleccione</option>
          {competencias.map((c) => (
            <option key={c.id} value={c.id}>
              {c.nombre}
            </option>
          ))}
        </select>
      </label>
      <label className="block text-sm font-medium text-[var(--text)]">
        Capacidad
        <select
          className={FIELD}
          value={capacidadId}
          onChange={(e) => onChangeCapacidad(e.target.value)}
          required
          disabled={!competenciaId}
        >
          <option value="">Seleccione</option>
          {capacidades.map((c) => (
            <option key={c.id} value={c.id}>
              {c.nombre}
            </option>
          ))}
        </select>
      </label>
    </>
  );
}
