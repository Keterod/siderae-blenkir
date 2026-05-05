import { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  getAlerta,
  getAlertas,
  postCerrarAlerta,
  postIntervencion,
} from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
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

function fechaLegible(valor) {
  if (!valor) {
    return '—';
  }

  const d = new Date(valor);

  return Number.isNaN(d.getTime()) ? String(valor) : d.toLocaleDateString('es-PE');
}

export default function AlertasPanel({ onClose }) {
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
      <Card className="space-y-3">
        <p className="text-sm text-muted">No tienes permiso para ver alertas.</p>
        <Button type="button" variant="danger" size="sm" onClick={onClose}>
          Cerrar módulo
        </Button>
      </Card>
    );
  }

  return (
    <Card className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--border)] pb-3">
        <h2 className="text-xl font-semibold text-[var(--text)]">
          {vista === 'lista' ? 'Alertas' : 'Detalle de alerta'}
        </h2>

        <div className="flex flex-wrap gap-2">
          {vista === 'detalle' ? (
            <Button
              type="button"
              variant="outline"
              size="sm"
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
              onClick={() => {
                void cargarLista();
              }}
            >
              Actualizar
            </Button>
          ) : null}

          <Button type="button" variant="danger" size="sm" onClick={onClose}>
            Cerrar módulo
          </Button>
        </div>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}
      {errorAccion ? <AlertMessage>{errorAccion}</AlertMessage> : null}

      {vista === 'lista' && cargando ? <LoadingState label="Cargando alertas…" /> : null}

      {vista === 'lista' && !cargando ? (
        lista.length === 0 ? (
          <EmptyState title="Sin alertas registradas" description="Las alertas aparecerán cuando el sistema las genere desde el cálculo de riesgo." />
        ) : (
          <ul className="divide-y divide-[var(--border)]">
            {lista.map((item) => (
              <li key={item.id} className="flex flex-wrap items-center justify-between gap-2 py-3">
                <button
                  type="button"
                  className="text-left text-sm text-[var(--text)] hover:text-[var(--primary-dark)] hover:underline"
                  onClick={() => {
                    void abrirDetalle(item.id);
                  }}
                >
                  <span className="font-medium">
                    {item.estudiante?.apellidos}, {item.estudiante?.nombres}
                  </span>
                  <span className="block text-xs text-muted">
                    Estado: {etiquetaEstado(item.estado)} · Índice: {item.indice_riesgo?.indice ?? '—'} (
                    {item.indice_riesgo?.nivel ?? '—'})
                  </span>
                </button>
              </li>
            ))}
          </ul>
        )
      ) : null}

      {vista === 'detalle' && detalle ? (
        <div className="space-y-6 text-sm text-[var(--text)]">
          <div>
            <p className="text-xs uppercase tracking-wide text-muted">Estado</p>
            <p className="text-lg font-semibold text-[var(--text)]">{etiquetaEstado(detalle.estado)}</p>
          </div>

          <dl className="grid gap-3 sm:grid-cols-2">
            <div>
              <dt className="text-muted">Estudiante</dt>
              <dd className="font-medium">
                {detalle.estudiante?.apellidos}, {detalle.estudiante?.nombres} ({detalle.estudiante?.codigo})
              </dd>
            </div>
            <div>
              <dt className="text-muted">Grado / sección</dt>
              <dd>
                {detalle.estudiante?.grado} {detalle.estudiante?.seccion} · Año {detalle.estudiante?.anio_escolar}
              </dd>
            </div>
            <div>
              <dt className="text-muted">Índice de riesgo</dt>
              <dd>{detalle.indice_riesgo?.indice ?? '—'}</dd>
            </div>
            <div>
              <dt className="text-muted">Nivel (cálculo)</dt>
              <dd>{detalle.indice_riesgo?.nivel ?? '—'}</dd>
            </div>
          </dl>

          <div>
            <dt className="text-xs uppercase tracking-wide text-muted">Recomendación</dt>
            <dd className="mt-1 whitespace-pre-wrap text-[var(--text)]">{detalle.recomendacion ?? '—'}</dd>
          </div>

          {detalle.estado === 'cerrada' ? (
            <div className="rounded-lg border border-[var(--border)] bg-[var(--background)] p-3">
              <p className="text-xs uppercase tracking-wide text-muted">Cierre</p>
              <p className="mt-1 whitespace-pre-wrap">{detalle.resultado_cierre ?? '—'}</p>
              <p className="mt-2 text-xs text-muted">
                Fecha cierre: {fechaLegible(detalle.fecha_cierre)} · Por: {detalle.cerrada_por?.email ?? '—'}
              </p>
            </div>
          ) : null}

          <div>
            <h3 className="mb-2 text-sm font-semibold text-[var(--text)]">Historial de intervenciones</h3>
            {detalle.intervenciones?.length ? (
              <ul className="divide-y divide-[var(--border)] rounded-lg border border-[var(--border)]">
                {detalle.intervenciones.map((row) => (
                  <li key={row.id} className="p-3">
                    <p className="font-medium capitalize">{row.tipo}</p>
                    <p className="text-muted">{row.descripcion}</p>
                    <p className="mt-1 text-xs text-muted">
                      {fechaLegible(row.fecha)} · {row.registrado_por?.email ?? '—'}
                    </p>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-muted">Sin intervenciones registradas.</p>
            )}
          </div>

          {puedeRegistrar && detalle.estado !== 'cerrada' ? (
            <form
              className="space-y-3 rounded-lg border border-[var(--border)] bg-[var(--background)]/80 p-3"
              onSubmit={(e) => {
                e.preventDefault();
                void enviarIntervencion();
              }}
            >
              <h3 className="text-sm font-semibold text-[var(--text)]">Registrar intervención</h3>

              <div className="space-y-1">
                <label className="text-xs font-medium text-muted">Tipo</label>
                <select
                  className="sb-field"
                  value={fmInter.tipo}
                  onChange={(e) => setFmInter((v) => ({ ...v, tipo: e.target.value }))}
                >
                  <option value="academica">Académica</option>
                  <option value="emocional">Emocional</option>
                  <option value="familiar">Familiar</option>
                </select>
              </div>

              <div className="space-y-1">
                <label className="text-xs font-medium text-muted">Descripción</label>
                <textarea
                  required
                  className="sb-field"
                  rows={3}
                  value={fmInter.descripcion}
                  onChange={(e) => setFmInter((v) => ({ ...v, descripcion: e.target.value }))}
                />
              </div>

              <div className="space-y-1">
                <label className="text-xs font-medium text-muted">Fecha</label>
                <input
                  type="date"
                  required
                  className="sb-field"
                  value={fmInter.fecha}
                  onChange={(e) => setFmInter((v) => ({ ...v, fecha: e.target.value }))}
                />
              </div>

              <Button type="submit" variant="primary" size="sm" disabled={guardando}>
                {guardando ? 'Guardando…' : 'Guardar intervención'}
              </Button>
            </form>
          ) : null}

          {puedeRegistrar && detalle.estado !== 'cerrada' ? (
            <form
              className="space-y-3 rounded-lg border border-amber-200 bg-amber-50/50 p-3"
              onSubmit={(e) => {
                e.preventDefault();
                void enviarCierre();
              }}
            >
              <h3 className="text-sm font-semibold text-[var(--text)]">Cerrar alerta</h3>
              <p className="text-xs text-muted">
                Solo se permite si ya existe al menos una intervención. Describe el resultado del cierre.
              </p>

              <textarea
                required
                className="sb-field"
                rows={3}
                placeholder="Resultado del cierre"
                value={resultadoCierre}
                onChange={(e) => setResultadoCierre(e.target.value)}
              />

              <Button type="submit" variant="outline" size="sm" disabled={guardando}>
                {guardando ? 'Cerrando…' : 'Cerrar alerta'}
              </Button>
            </form>
          ) : null}
        </div>
      ) : null}
    </Card>
  );
}
