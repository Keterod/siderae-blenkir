import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getAnioEscolar,
  getAniosEscolares,
  patchPeriodoAcademico,
  postActivarAnioEscolar,
  postAnioEscolar,
  postCerrarAnioEscolar,
  postCerrarPeriodoAcademico,
  postGenerarBimestresAnioEscolar,
  postGenerarSemanasPeriodo,
  postMarcarPeriodoVigente,
} from '../../../lib/api';
import AlertMessage from '../../ui/AlertMessage';
import Button from '../../ui/Button';
import Card from '../../ui/Card';
import EmptyState from '../../ui/EmptyState';
import LoadingState from '../../ui/LoadingState';

const FIELD = 'sb-field min-w-0 w-full';

function etiquetaEstado(estado) {
  if (estado === 'activo') return 'Activo';
  if (estado === 'cerrado') return 'Cerrado';
  return 'Inactivo';
}

function obtenerMensajeError(err) {
  if (err?.payload?.message) return err.payload.message;
  if (err?.payload?.errors) {
    const first = Object.values(err.payload.errors)[0];
    if (Array.isArray(first) && first[0]) return first[0];
  }
  return 'No se pudo completar la operación.';
}

export default function PeriodosAcademicosPanel() {
  const [anios, setAnios] = useState([]);
  const [seleccionadoId, setSeleccionadoId] = useState(null);
  const [detalle, setDetalle] = useState(null);
  const [periodoSeleccionadoId, setPeriodoSeleccionadoId] = useState(null);
  const [cargandoLista, setCargandoLista] = useState(true);
  const [cargandoDetalle, setCargandoDetalle] = useState(false);
  const [procesando, setProcesando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [formAnio, setFormAnio] = useState({
    anio: '',
    nombre: '',
    fecha_inicio: '',
    fecha_fin: '',
    generar_bimestres: true,
  });
  const [mostrarFormAnio, setMostrarFormAnio] = useState(false);

  const cargarLista = useCallback(async () => {
    setCargandoLista(true);
    setError(null);
    try {
      const data = await getAniosEscolares();
      setAnios(Array.isArray(data) ? data : []);
    } catch (e) {
      setError(obtenerMensajeError(e));
      setAnios([]);
    } finally {
      setCargandoLista(false);
    }
  }, []);

  const cargarDetalle = useCallback(async (id) => {
    if (!id) {
      setDetalle(null);
      return;
    }
    setCargandoDetalle(true);
    setError(null);
    try {
      const data = await getAnioEscolar(id);
      setDetalle(data);
      setPeriodoSeleccionadoId((prev) => {
        if (prev && data?.periodos?.some((p) => p.id === prev)) {
          return prev;
        }
        return data?.periodos?.[0]?.id ?? null;
      });
    } catch (e) {
      setError(obtenerMensajeError(e));
      setDetalle(null);
    } finally {
      setCargandoDetalle(false);
    }
  }, []);

  useEffect(() => {
    void cargarLista();
  }, [cargarLista]);

  useEffect(() => {
    if (seleccionadoId) {
      void cargarDetalle(seleccionadoId);
    }
  }, [seleccionadoId, cargarDetalle]);

  const periodoSeleccionado = useMemo(
    () => detalle?.periodos?.find((p) => p.id === periodoSeleccionadoId) ?? null,
    [detalle, periodoSeleccionadoId],
  );

  async function ejecutar(accion) {
    setProcesando(true);
    setError(null);
    setExito(null);
    try {
      await accion();
      await cargarLista();
      if (seleccionadoId) {
        await cargarDetalle(seleccionadoId);
      }
    } catch (e) {
      setError(obtenerMensajeError(e));
    } finally {
      setProcesando(false);
    }
  }

  async function crearAnio(e) {
    e.preventDefault();
    await ejecutar(async () => {
      const creado = await postAnioEscolar({
        anio: formAnio.anio.trim(),
        nombre: formAnio.nombre.trim(),
        fecha_inicio: formAnio.fecha_inicio || null,
        fecha_fin: formAnio.fecha_fin || null,
        generar_bimestres: formAnio.generar_bimestres,
      });
      setSeleccionadoId(creado.id);
      setMostrarFormAnio(false);
      setFormAnio({ anio: '', nombre: '', fecha_inicio: '', fecha_fin: '', generar_bimestres: true });
      setExito(`Año escolar ${creado.anio} creado.`);
    });
  }

  return (
    <div className="flex flex-col gap-6" data-testid="periodos-academicos-panel">
      <Card className="p-6">
        <h2 className="text-xl font-semibold text-[var(--text)]">Periodos académicos</h2>
        <p className="mt-1 text-sm text-muted">
          Administre años escolares, bimestres y semanas. Solo puede haber un año activo y un bimestre vigente por año.
        </p>
      </Card>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <div className="grid gap-6 lg:grid-cols-[minmax(280px,360px)_1fr]">
        <Card className="p-5">
          <div className="flex items-center justify-between gap-2">
            <h3 className="text-sm font-semibold">Años escolares</h3>
            <Button type="button" size="sm" variant="primary" onClick={() => setMostrarFormAnio((v) => !v)}>
              {mostrarFormAnio ? 'Cancelar' : 'Crear año'}
            </Button>
          </div>

          {mostrarFormAnio ? (
            <form className="mt-4 space-y-3 border-t border-[var(--border)] pt-4" onSubmit={(e) => void crearAnio(e)}>
              <label className="block text-sm">
                Año
                <input className={FIELD} value={formAnio.anio} onChange={(e) => setFormAnio((p) => ({ ...p, anio: e.target.value }))} required />
              </label>
              <label className="block text-sm">
                Nombre
                <input className={FIELD} value={formAnio.nombre} onChange={(e) => setFormAnio((p) => ({ ...p, nombre: e.target.value }))} required />
              </label>
              <label className="block text-sm">
                Inicio
                <input type="date" className={FIELD} value={formAnio.fecha_inicio} onChange={(e) => setFormAnio((p) => ({ ...p, fecha_inicio: e.target.value }))} />
              </label>
              <label className="block text-sm">
                Fin
                <input type="date" className={FIELD} value={formAnio.fecha_fin} onChange={(e) => setFormAnio((p) => ({ ...p, fecha_fin: e.target.value }))} />
              </label>
              <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" checked={formAnio.generar_bimestres} onChange={(e) => setFormAnio((p) => ({ ...p, generar_bimestres: e.target.checked }))} />
                Generar 4 bimestres al crear
              </label>
              <Button type="submit" size="sm" disabled={procesando}>Guardar año</Button>
            </form>
          ) : null}

          <div className="mt-4">
            {cargandoLista ? <LoadingState label="Cargando años…" /> : null}
            {!cargandoLista && anios.length === 0 ? (
              <EmptyState title="Sin años escolares" description="Cree el primer año escolar para comenzar." />
            ) : null}
            <ul className="space-y-2">
              {anios.map((anio) => (
                <li key={anio.id}>
                  <button
                    type="button"
                    onClick={() => setSeleccionadoId(anio.id)}
                    className={`w-full rounded-lg border px-3 py-3 text-left text-sm transition ${
                      seleccionadoId === anio.id
                        ? 'border-[var(--primary)] bg-[var(--primary-soft)]'
                        : 'border-[var(--border)] hover:border-[var(--primary)]/40'
                    }`}
                  >
                    <p className="font-medium">{anio.anio} — {anio.nombre}</p>
                    <p className="mt-0.5 text-xs text-muted">
                      {etiquetaEstado(anio.estado)}{anio.es_activo ? ' · Institucional activo' : ''}
                    </p>
                  </button>
                </li>
              ))}
            </ul>
          </div>
        </Card>

        <Card className="p-5">
          {!seleccionadoId ? (
            <EmptyState title="Seleccione un año escolar" description="Elija un año de la lista para ver bimestres y semanas." />
          ) : cargandoDetalle ? (
            <LoadingState label="Cargando detalle…" />
          ) : detalle ? (
            <div className="space-y-6">
              <div>
                <h3 className="text-lg font-semibold">{detalle.anio} — {detalle.nombre}</h3>
                <p className="mt-1 text-sm text-muted">
                  {etiquetaEstado(detalle.estado)}
                  {detalle.fecha_inicio ? ` · ${detalle.fecha_inicio} → ${detalle.fecha_fin ?? '—'}` : ''}
                </p>
                <div className="mt-3 flex flex-wrap gap-2">
                  {!detalle.es_activo && detalle.estado !== 'cerrado' ? (
                    <Button size="sm" disabled={procesando} onClick={() => void ejecutar(async () => {
                      await postActivarAnioEscolar(detalle.id);
                      setExito('Año escolar activado.');
                    })}>Activar año</Button>
                  ) : null}
                  {detalle.estado !== 'cerrado' ? (
                    <Button size="sm" variant="outline" disabled={procesando} onClick={() => void ejecutar(async () => {
                      await postGenerarBimestresAnioEscolar(detalle.id);
                      setExito('Bimestres generados o actualizados.');
                    })}>Generar bimestres</Button>
                  ) : null}
                  {detalle.estado !== 'cerrado' ? (
                    <Button size="sm" variant="secondary" disabled={procesando} onClick={() => void ejecutar(async () => {
                      await postCerrarAnioEscolar(detalle.id);
                      setExito('Año escolar cerrado.');
                    })}>Cerrar año</Button>
                  ) : null}
                </div>
              </div>

              <div>
                <h4 className="text-sm font-semibold">Bimestres</h4>
                {(detalle.periodos ?? []).length === 0 ? (
                  <p className="mt-2 text-sm text-muted">No hay bimestres. Use «Generar bimestres».</p>
                ) : (
                  <ul className="mt-3 space-y-2">
                    {(detalle.periodos ?? []).map((periodo) => (
                      <li key={periodo.id}>
                        <button
                          type="button"
                          onClick={() => setPeriodoSeleccionadoId(periodo.id)}
                          className={`w-full rounded-md border px-3 py-2 text-left text-sm ${
                            periodoSeleccionadoId === periodo.id ? 'border-[var(--primary)]' : 'border-[var(--border)]'
                          }`}
                        >
                          Bimestre {periodo.bimestre} · {etiquetaEstado(periodo.estado)}
                          {periodo.es_vigente ? ' · Vigente' : ''}
                          {periodo.fecha_inicio ? ` · ${periodo.fecha_inicio}–${periodo.fecha_fin ?? '?'}` : ''}
                        </button>
                      </li>
                    ))}
                  </ul>
                )}
              </div>

              {periodoSeleccionado ? (
                <div className="rounded-lg border border-[var(--border)] p-4">
                  <h4 className="font-medium">Bimestre {periodoSeleccionado.bimestre}</h4>
                  <div className="mt-3 grid gap-3 sm:grid-cols-2">
                    <label className="text-sm">
                      Inicio
                      <input
                        type="date"
                        className={FIELD}
                        defaultValue={periodoSeleccionado.fecha_inicio ?? ''}
                        onBlur={(e) => void ejecutar(async () => {
                          await patchPeriodoAcademico(periodoSeleccionado.id, {
                            fecha_inicio: e.target.value || null,
                            fecha_fin: periodoSeleccionado.fecha_fin,
                          });
                          setExito('Fechas del bimestre actualizadas.');
                        })}
                      />
                    </label>
                    <label className="text-sm">
                      Fin
                      <input
                        type="date"
                        className={FIELD}
                        defaultValue={periodoSeleccionado.fecha_fin ?? ''}
                        onBlur={(e) => void ejecutar(async () => {
                          await patchPeriodoAcademico(periodoSeleccionado.id, {
                            fecha_inicio: periodoSeleccionado.fecha_inicio,
                            fecha_fin: e.target.value || null,
                          });
                          setExito('Fechas del bimestre actualizadas.');
                        })}
                      />
                    </label>
                  </div>
                  <div className="mt-3 flex flex-wrap gap-2">
                    {periodoSeleccionado.estado !== 'cerrado' ? (
                      <Button size="sm" disabled={procesando} onClick={() => void ejecutar(async () => {
                        await postMarcarPeriodoVigente(periodoSeleccionado.id);
                        setExito(`Bimestre ${periodoSeleccionado.bimestre} marcado como vigente.`);
                      })}>Marcar vigente</Button>
                    ) : null}
                    {periodoSeleccionado.estado !== 'cerrado' ? (
                      <Button size="sm" variant="outline" disabled={procesando} onClick={() => void ejecutar(async () => {
                        await postGenerarSemanasPeriodo(periodoSeleccionado.id);
                        setExito('Semanas generadas.');
                      })}>Generar semanas</Button>
                    ) : null}
                    {periodoSeleccionado.estado !== 'cerrado' ? (
                      <Button size="sm" variant="secondary" disabled={procesando} onClick={() => void ejecutar(async () => {
                        await postCerrarPeriodoAcademico(periodoSeleccionado.id);
                        setExito(`Bimestre ${periodoSeleccionado.bimestre} cerrado.`);
                      })}>Cerrar bimestre</Button>
                    ) : null}
                  </div>

                  <div className="mt-4">
                    <h5 className="text-sm font-semibold">Semanas ({periodoSeleccionado.semanas?.length ?? 0})</h5>
                    {(periodoSeleccionado.semanas ?? []).length === 0 ? (
                      <p className="mt-1 text-sm text-muted">Sin semanas. Pulse «Generar semanas».</p>
                    ) : (
                      <ul className="mt-2 space-y-1 text-sm text-muted">
                        {(periodoSeleccionado.semanas ?? []).map((s) => (
                          <li key={s.id}>
                            Semana {s.numero_semana}
                            {s.fecha_inicio ? ` · ${s.fecha_inicio}–${s.fecha_fin ?? '?'}` : ''}
                            {!s.activo ? ' · Inactiva' : ''}
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                </div>
              ) : null}
            </div>
          ) : null}
        </Card>
      </div>
    </div>
  );
}
