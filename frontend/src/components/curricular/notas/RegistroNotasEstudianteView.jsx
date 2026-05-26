import { calcularCePreview } from '../../../lib/notasCurricular';
import Button from '../../ui/Button';
import NotaInputCell from './NotaInputCell';
import { notaFueraDeRango } from './notasUtils';

export default function RegistroNotasEstudianteView({
  soloLectura = false,
  estructura,
  formulario,
  filas,
  filtros,
  onCambiarNota,
  guardando,
  cargandoFormulario,
}) {
  return (
    <div className="rounded border border-[var(--border)] bg-[var(--surface)] p-2 sm:p-3">
      {!filtros.estudiante_id ? (
        <p className="mb-2 text-xs text-muted">Seleccione un estudiante para registrar notas.</p>
      ) : null}

      <div className="space-y-3">
        {estructura.map((grupo) => (
          <section key={grupo.competencia.id} className="space-y-1.5">
            <p className="truncate text-[11px] font-semibold text-[var(--text)]" title={grupo.competencia.nombre}>
              <span className="mr-1 text-[10px] font-semibold uppercase text-muted">Comp.</span>
              {grupo.competencia.nombre}
            </p>

            {grupo.capacidades.map(({ capacidad, criterios }) => (
              <div key={capacidad.id} className="overflow-hidden rounded border border-[var(--border)] bg-[var(--surface)]">
                <div className="border-b border-[var(--border)] px-2 py-1">
                  <p className="truncate text-[11px] font-medium text-[var(--text)]" title={capacidad.nombre}>
                    <span className="mr-1 text-[10px] font-semibold uppercase text-muted">Cap.</span>
                    {capacidad.nombre}
                  </p>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[520px] border-collapse text-[11px]">
                    <thead>
                      <tr className="border-b bg-[var(--surface-muted)] text-left text-[10px] uppercase tracking-wide text-muted">
                        <th className="px-2 py-1 font-semibold">Criterio</th>
                        <th className="w-12 px-1 py-1 text-center font-semibold">C</th>
                        <th className="w-12 px-1 py-1 text-center font-semibold">L</th>
                        <th className="w-12 px-1 py-1 text-center font-semibold">T</th>
                        <th className="w-12 px-1 py-1 text-center font-semibold">CE</th>
                      </tr>
                    </thead>
                    <tbody>
                      {criterios.filter((c) => c.activo !== false).map((criterio) => {
                        const fila = filas[criterio.id] ?? {};
                        const ce = calcularCePreview(
                          fila.nota_cuaderno,
                          fila.nota_libro,
                          fila.nota_tarea,
                          formulario?.pesos,
                        );
                        const ceInvalido = ce === 'invalid';

                        return (
                          <tr key={criterio.id} className="border-b last:border-b-0">
                            <td className="px-2 py-0.5 align-top">
                              <p className="line-clamp-2 font-medium leading-tight text-[var(--text)]" title={criterio.titulo}>
                                {criterio.titulo}
                              </p>
                            </td>
                            {['nota_cuaderno', 'nota_libro', 'nota_tarea'].map((campo) => (
                              <td key={campo} className="px-1 py-0.5 text-center align-top">
                                <NotaInputCell
                                  value={fila[campo]}
                                  invalid={notaFueraDeRango(fila[campo])}
                                  onChange={(valor) => onCambiarNota(criterio.id, campo, valor)}
                                  disabled={soloLectura || !filtros.estudiante_id}
                                />
                              </td>
                            ))}
                            <td className="px-1 py-0.5 text-center align-top text-[10px] font-semibold tabular-nums text-[var(--primary-dark)]">
                              {ceInvalido ? (
                                <span className="text-red-600">!</span>
                              ) : ce != null ? (
                                ce
                              ) : (
                                <span className="text-muted">—</span>
                              )}
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              </div>
            ))}
          </section>
        ))}

        {!soloLectura ? (
          <Button
            type="submit"
            variant="primary"
            size="sm"
            className="px-3 py-1 text-xs"
            disabled={guardando || !filtros.estudiante_id || cargandoFormulario}
          >
            {guardando ? 'Guardando…' : 'Guardar notas'}
          </Button>
        ) : null}
      </div>
    </div>
  );
}
