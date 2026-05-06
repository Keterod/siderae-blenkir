import { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  getAlerta,
  getAlertas,
  postCerrarAlerta,
  postIntervencion,
} from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

function etiquetaEstado(estado) {
  if (estado === 'pendiente') {
    return 'Pendiente';
  }
  if (estado === 'en_atencion') {
    return 'En atención';
  }
  if (estado === 'cerrada') {
    return 'Cerrada';
  }
  return estado ?? '—';
}

function badgeVariantPorEstado(estado) {
  if (estado === 'pendiente') {
    return 'warning';
  }
  if (estado === 'en_atencion') {
    return 'info';
  }
  if (estado === 'cerrada') {
    return 'success';
  }
  return 'neutral';
}

function fechaLegible(valor) {
  if (!valor) {
    return '—';
  }
  const d = new Date(valor);
  return Number.isNaN(d.getTime()) ? String(valor) : d.toLocaleDateString('es-PE');
}

export default function AlertasPanel({ onClose = null }) {
  const { permissions } = useAuth();
  const puedeVer = permissions.includes('ver_alertas');
  const puedeRegistrar = permissions.includes('registrar_intervencion');

  const [vista, setVista] = useState('lista');
  const [lista, setLista] = useState([]);
  const [detalle, setDetalle] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [errorGeneral, setErrorGeneral] = useState(null);
  const [guardando, setGuardando] = useState(false);
  const [errorAccion, setErrorAccion] = useState(null);

  const [fmInter, setFmInter] = useState({
    tipo: 'academica',
    descripcion: '',
    fecha: new Date().toISOString().slice(0, 10),
  });

  const [resultadoCierre, setResultadoCierre] = useState('');

  const cargarLista = useCallback(async () => {
    setErrorGeneral(null);
    try {
      const data = await getAlertas();
      setLista(Array.isArray(data) ? data : []);
    } catch (error) {
      if (error.status === 403) {
        setErrorGeneral('Sin permiso para ver alertas.');
      } else {
        setErrorGeneral('No se pudo cargar el listado de alertas.');
      }
      setLista([]);
    }
  }, []);

  useEffect(() => {
    let omitir = false;
    (async () => {
      setCargando(true);
      await cargarLista();
      if (!omitir) {
        setCargando(false);
      }
    })();
    return () => {
      omitir = true;
    };
  }, [cargarLista]);

  async function abrirDetalle(alertaId) {
    setCargando(true);
    setErrorGeneral(null);
    setErrorAccion(null);
    try {
      const item = await getAlerta(alertaId);
      setDetalle(item);
      setVista('detalle');
    } catch {
      setErrorGeneral('No se pudo cargar el detalle de la alerta.');
    } finally {
      setCargando(false);
    }
  }

  async function enviarIntervencion() {
    if (!detalle?.id) {
      return;
    }

    setGuardando(true);
    setErrorAccion(null);
    try {
      await postIntervencion(detalle.id, {
        tipo: fmInter.tipo,
        descripcion: fmInter.descripcion.trim(),
        fecha: fmInter.fecha,
      });
      const actualizado = await getAlerta(detalle.id);
      setDetalle(actualizado);
      setFmInter((v) => ({ ...v, descripcion: '' }));
      await cargarLista();
    } catch (error) {
      if (error.status === 422 && error.payload?.message) {
        setErrorAccion(error.payload.message);
      } else if (error.status === 422 && error.payload?.errors) {
        setErrorAccion(JSON.stringify(error.payload.errors));
      } else {
        setErrorAccion('No se pudo registrar la intervención.');
      }
    } finally {
      setGuardando(false);
    }
  }

  async function enviarCierre() {
    if (!detalle?.id) {
      return;
    }

    setGuardando(true);
    setErrorAccion(null);
    try {
      const actualizado = await postCerrarAlerta(detalle.id, {
        resultado_cierre: resultadoCierre.trim(),
      });
      setDetalle(actualizado);
      setResultadoCierre('');
      await cargarLista();
    } catch (error) {
      if (error.status === 422 && error.payload?.message) {
        setErrorAccion(error.payload.message);
      } else {
        setErrorAccion('No se pudo cerrar la alerta.');
      }
    } finally {
      setGuardando(false);
    }
  }

  if (!puedeVer) {
    return (
      <Card className="space-y-3" data-testid="alertas-sin-permiso">
        <p className="text-sm text-muted">No tienes permiso para ver alertas.</p>
        {typeof onClose === 'function' ? (
          <Button type="button" variant="danger" size="sm" onClick={onClose}>
            Cerrar módulo
          </Button>
        ) : null}
      </Card>
    );
  }

  return (
    <Card className="space-y-6 border-[var(--border)] shadow-card" data-testid="alertas-panel">
      <div className="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border)] pb-4">
        <div className="min-w-0">
          <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">{vista === 'lista' ? 'Alertas' : 'Detalle de alerta'}</h2>
          <p className="mt-2 max-w-xl text-sm leading-relaxed text-muted">
            Gestión de alertas generadas por el sistema. Las intervenciones se registran al abrir el detalle del caso.
          </p>
        </div>

        <div className="flex flex-wrap gap-2">
          {vista === 'detalle' ? (
            <Button
              type="button"
              variant="outline"
              size="sm"
              data-testid="alertas-volver-listado"
              onClick={() => {
                setDetalle(null);
                setErrorAccion(null);
                setVista('lista');
                void cargarLista();
              }}
            >
              Volver al listado
            </Button>
          ) : null}

          {vista === 'lista' ? (
            <Button
              type="button"
              variant="outline"
              size="sm"
              data-testid="alertas-actualizar"
              onClick={() => {
                void cargarLista();
              }}
            >
              Actualizar
            </Button>
          ) : null}

          {typeof onClose === 'function' ? (
            <Button type="button" variant="danger" size="sm" onClick={onClose}>
              Cerrar módulo
            </Button>
          ) : null}
        </div>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}
      {errorAccion ? <AlertMessage>{errorAccion}</AlertMessage> : null}

      {vista === 'lista' && cargando ? <LoadingState label="Cargando alertas…" /> : null}

      {vista === 'lista' && !cargando ? (
        lista.length === 0 ? (
          <EmptyState
            title="Sin alertas registradas"
            description="Las alertas aparecen cuando el backend las genera según reglas de riesgo; no se inventan filas de demostración."
          />
        ) : (
          <div className="space-y-3">
            <p className="text-sm text-muted">
              {lista.length === 1
                ? '1 alerta mostrada.'
                : `${lista.length} alertas mostradas.`}{' '}
              Pulse «Ver alerta» para intervenir desde el caso.
            </p>
            <div className="overflow-x-auto rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm" data-testid="alertas-tabla">
              <table className="min-w-full text-left text-sm text-[var(--text)]">
                <thead className="border-b border-[var(--border)] bg-[var(--background)] text-[11px] font-semibold uppercase tracking-wide text-muted">
                  <tr>
                    <th className="px-4 py-3">Estudiante</th>
                    <th className="px-4 py-3">Estado</th>
                    <th className="px-4 py-3">Índice</th>
                    <th className="px-4 py-3">Nivel</th>
                    <th className="px-4 py-3 text-right">Acción</th>
                  </tr>
                </thead>
                <tbody>
                  {lista.map((item, index) => (
                    <tr
                      key={item.id}
                      className={`border-b border-[var(--border)]/70 last:border-0 ${
                        index % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/35'
                      }`}
                    >
                      <td className="px-4 py-3">
                        <span className="font-medium">
                          {item.estudiante?.apellidos}, {item.estudiante?.nombres}
                        </span>
                      </td>
                      <td className="px-4 py-3">
                        <Badge variant={badgeVariantPorEstado(item.estado)} className="normal-case">
                          {etiquetaEstado(item.estado)}
                        </Badge>
                      </td>
                      <td className="px-4 py-3 font-mono text-xs tabular-nums">{item.indice_riesgo?.indice ?? '—'}</td>
                      <td className="px-4 py-3 capitalize text-muted">{item.indice_riesgo?.nivel ?? '—'}</td>
                      <td className="px-4 py-3 text-right">
                        <Button
                          type="button"
                          variant="outline"
                          size="sm"
                          className="text-[var(--secondary)]"
                          data-testid={`alerta-abrir-${item.id}`}
                          onClick={() => {
                            void abrirDetalle(item.id);
                          }}
                        >
                          Ver alerta
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )
      ) : null}

      {vista === 'detalle' && detalle ? (
        <div className="space-y-6 text-sm text-[var(--text)]">
          <Card className="space-y-4 bg-[var(--background)]/40">
            <div className="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border)]/70 pb-3">
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-wide text-muted">Estado de la alerta</p>
                <Badge variant={badgeVariantPorEstado(detalle.estado)} className="mt-2 normal-case">
                  {etiquetaEstado(detalle.estado)}
                </Badge>
              </div>
            </div>

            <dl className="grid gap-x-6 gap-y-4 sm:grid-cols-2">
              <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-muted">Estudiante</dt>
                <dd className="mt-1 font-medium">
                  {detalle.estudiante?.apellidos}, {detalle.estudiante?.nombres} ({detalle.estudiante?.codigo})
                </dd>
              </div>
              <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-muted">Grado / sección</dt>
                <dd className="mt-1 text-muted">
                  {detalle.estudiante?.grado} {detalle.estudiante?.seccion} · Año {detalle.estudiante?.anio_escolar}
                </dd>
              </div>
              <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-muted">Índice de riesgo</dt>
                <dd className="mt-1 font-mono tabular-nums">{detalle.indice_riesgo?.indice ?? '—'}</dd>
              </div>
              <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-muted">Nivel (cálculo)</dt>
                <dd className="mt-1 capitalize">{detalle.indice_riesgo?.nivel ?? '—'}</dd>
              </div>
            </dl>
          </Card>

          <Card className="space-y-2">
            <p className="text-[11px] font-semibold uppercase tracking-wide text-muted">Recomendación SIDERAE</p>
            <p className="leading-relaxed whitespace-pre-wrap">{detalle.recomendacion ?? '—'}</p>
          </Card>

          {detalle.estado === 'cerrada' ? (
            <Card className="border-[var(--border)] bg-[var(--background)]/60">
              <p className="text-[11px] font-semibold uppercase tracking-wide text-muted">Cierre</p>
              <p className="mt-3 leading-relaxed whitespace-pre-wrap">{detalle.resultado_cierre ?? '—'}</p>
              <p className="mt-3 border-t border-[var(--border)]/70 pt-3 text-xs text-muted">
                Fecha de cierre: {fechaLegible(detalle.fecha_cierre)} · Por: {detalle.cerrada_por?.email ?? '—'}
              </p>
            </Card>
          ) : null}

          <Card className="space-y-4">
            <h3 className="text-sm font-semibold text-[var(--text)]">Historial de intervenciones</h3>
            {detalle.intervenciones?.length ? (
              <ul className="divide-y divide-[var(--border)] rounded-lg border border-[var(--border)]">
                {detalle.intervenciones.map((row) => (
                  <li key={row.id} className="px-4 py-3">
                    <p className="font-medium capitalize">{row.tipo}</p>
                    <p className="mt-1 text-muted">{row.descripcion}</p>
                    <p className="mt-2 text-xs text-muted">
                      {fechaLegible(row.fecha)} · {row.registrado_por?.email ?? '—'}
                    </p>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="rounded-lg border border-dashed border-[var(--border)] bg-[var(--background)]/30 px-4 py-6 text-center text-sm text-muted">
                Sin intervenciones registradas.
              </p>
            )}
          </Card>

          {puedeRegistrar && detalle.estado !== 'cerrada' ? (
            <Card className="border-[var(--border)] bg-[var(--background)]/80">
              <form
                className="space-y-4"
                data-testid="form-intervencion"
                onSubmit={(e) => {
                  e.preventDefault();
                  void enviarIntervencion();
                }}
              >
                <h3 className="border-b border-[var(--border)]/70 pb-2 text-sm font-semibold text-[var(--text)]">
                  Registrar intervención
                </h3>

                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Tipo de intervención</label>
                    <select
                      className="sb-field min-w-0"
                      value={fmInter.tipo}
                      onChange={(e) => setFmInter((v) => ({ ...v, tipo: e.target.value }))}
                    >
                      <option value="academica">Académica</option>
                      <option value="emocional">Emocional</option>
                      <option value="familiar">Familiar</option>
                    </select>
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Fecha</label>
                    <input
                      type="date"
                      required
                      className="sb-field min-w-0"
                      value={fmInter.fecha}
                      onChange={(e) => setFmInter((v) => ({ ...v, fecha: e.target.value }))}
                    />
                  </div>

                  <div className="flex flex-col gap-1 sm:col-span-2">
                    <label className="text-sm font-medium text-[var(--text)]">Descripción</label>
                    <textarea
                      required
                      className="sb-field min-w-0"
                      rows={3}
                      value={fmInter.descripcion}
                      onChange={(e) => setFmInter((v) => ({ ...v, descripcion: e.target.value }))}
                    />
                  </div>
                </div>

                <div className="flex justify-end">
                  <Button type="submit" variant="primary" size="sm" disabled={guardando} data-testid="intervencion-guardar">
                    {guardando ? 'Guardando…' : 'Guardar intervención'}
                  </Button>
                </div>
              </form>
            </Card>
          ) : null}

          {puedeRegistrar && detalle.estado !== 'cerrada' ? (
            <Card className="border-amber-200/90 bg-amber-50/50">
              <form
                className="space-y-4"
                data-testid="form-cerrar-alerta"
                onSubmit={(e) => {
                  e.preventDefault();
                  void enviarCierre();
                }}
              >
                <div className="border-b border-amber-200/80 pb-3">
                  <h3 className="text-sm font-semibold text-[var(--text)]">Cerrar alerta</h3>
                  <p className="mt-1 text-xs text-muted">
                    Requiere al menos una intervención previa sobre esta alerta, según reglas institucionales.
                  </p>
                </div>

                <div className="flex flex-col gap-1">
                  <label className="text-sm font-medium text-[var(--text)]">Resultado del cierre</label>
                  <textarea
                    required
                    className="sb-field min-w-0"
                    rows={3}
                    placeholder="Describe el resultado institucional"
                    value={resultadoCierre}
                    onChange={(e) => setResultadoCierre(e.target.value)}
                  />
                </div>

                <div className="flex justify-end">
                  <Button type="submit" variant="outline" size="sm" disabled={guardando} data-testid="alerta-cerrar">
                    {guardando ? 'Cerrando…' : 'Cerrar alerta'}
                  </Button>
                </div>
              </form>
            </Card>
          ) : null}
        </div>
      ) : null}
    </Card>
  );
}
