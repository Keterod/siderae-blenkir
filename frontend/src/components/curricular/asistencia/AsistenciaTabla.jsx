import { etiquetaEstadoAsistencia } from './asistenciaUtils';

export default function AsistenciaTabla({
  estudiantes,
  filasPorId,
  estadosPermitidos,
  onCambiarEstado,
  onCambiarObservacion,
  soloLectura = false,
}) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[720px] border-collapse text-left text-sm">
        <thead>
          <tr className="border-b border-[var(--border)] text-muted">
            <th className="py-2 pr-4 font-medium">Estudiante</th>
            {(estadosPermitidos ?? []).map((estado) => (
              <th key={estado} className="px-2 py-2 text-center font-medium">
                {etiquetaEstadoAsistencia(estado)}
              </th>
            ))}
            <th className="py-2 pl-2 font-medium">Observación</th>
          </tr>
        </thead>
        <tbody>
          {estudiantes.map((est, index) => {
            const fila = filasPorId[est.id] ?? { estado: null, observacion: '' };

            return (
              <tr
                key={est.id}
                className={`border-b border-[var(--border)]/60 ${
                  index % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/30'
                }`}
              >
                <td className="py-2 pr-4 align-middle">
                  <p className="font-medium text-[var(--text)]">
                    {est.apellidos}, {est.nombres}
                  </p>
                  <p className="font-mono text-xs text-muted">{est.codigo}</p>
                </td>
                {(estadosPermitidos ?? []).map((estado) => (
                  <td key={estado} className="px-2 py-2 text-center align-middle">
                    <input
                      type="radio"
                      name={`asistencia-estado-${est.id}`}
                      value={estado}
                      checked={fila.estado === estado}
                      disabled={soloLectura}
                      className="h-4 w-4 cursor-pointer disabled:cursor-not-allowed"
                      aria-label={`${etiquetaEstadoAsistencia(estado)} — ${est.codigo}`}
                      onChange={() => onCambiarEstado(est.id, estado)}
                    />
                  </td>
                ))}
                <td className="py-2 pl-2 align-middle">
                  <input
                    type="text"
                    className="sb-field min-w-[12rem] w-full max-w-xs text-sm"
                    placeholder="Opcional"
                    value={fila.observacion}
                    disabled={soloLectura}
                    onChange={(ev) => onCambiarObservacion(est.id, ev.target.value)}
                  />
                </td>
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}
