import RegistroNotasAlumnoRow from './RegistroNotasAlumnoRow';

const CRITERIO_GROUP_W = 'w-[8.5rem] max-w-[8.5rem]';

export default function RegistroNotasCapacidadTable({ capacidad, criterios, estudiantes, matriz, pesos, onChangeNota }) {
  const criteriosActivos = criterios.filter((c) => c.activo !== false);
  if (criteriosActivos.length === 0) return null;

  return (
    <div className="overflow-hidden rounded border border-[var(--border)] bg-[var(--surface)]">
      <div className="border-b border-[var(--border)] px-2 py-1">
        <p className="truncate text-[11px] font-medium text-[var(--text)]" title={capacidad.nombre}>
          <span className="mr-1 text-[10px] font-semibold uppercase text-muted">Cap.</span>
          {capacidad.nombre}
        </p>
      </div>
      <div className="max-h-[calc(100vh-14rem)] overflow-auto">
        <table className="w-max min-w-full border-collapse text-[11px]">
          <thead className="sticky top-0 z-20 bg-[var(--surface-muted)]">
            <tr className="border-b text-[10px] uppercase tracking-wide text-muted">
              <th
                rowSpan={2}
                className="sticky left-0 z-30 w-[8rem] min-w-[8rem] max-w-[8rem] border-r border-[var(--border)] bg-[var(--surface-muted)] px-2 py-1 text-left font-semibold shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]"
              >
                Estudiante
              </th>
              {criteriosActivos.map((criterio) => (
                <th
                  key={criterio.id}
                  colSpan={4}
                  className={`${CRITERIO_GROUP_W} border-l border-[var(--border)] px-1 py-0.5 text-center font-semibold normal-case text-[var(--text)]`}
                  title={criterio.titulo}
                >
                  <span className="line-clamp-2 text-[10px] leading-3">{criterio.titulo}</span>
                </th>
              ))}
            </tr>
            <tr className="border-b text-[9px] font-semibold uppercase tracking-wide text-muted">
              {criteriosActivos.map((criterio) => (
                <SubHeaders key={criterio.id} />
              ))}
            </tr>
          </thead>
          <tbody>
            {estudiantes.map((estudiante) => (
              <RegistroNotasAlumnoRow
                key={estudiante.id}
                estudiante={estudiante}
                criterios={criteriosActivos}
                filas={matriz[estudiante.id] ?? {}}
                pesos={pesos}
                onChangeNota={onChangeNota}
              />
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function SubHeaders() {
  return (
    <>
      <th className="w-9 min-w-[2.125rem] border-l border-[var(--border)]/50 px-0 py-0.5 text-center">C</th>
      <th className="w-9 min-w-[2.125rem] px-0 py-0.5 text-center">L</th>
      <th className="w-9 min-w-[2.125rem] px-0 py-0.5 text-center">T</th>
      <th className="w-9 min-w-[2.125rem] px-0 py-0.5 text-center text-[var(--primary-dark)]">CE</th>
    </>
  );
}
