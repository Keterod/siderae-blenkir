import { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  getAlerta,
  getAlertas,
  postCerrarAlerta,
  postIntervencion,
} from '../../lib/api';

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
      <section className="space-y-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p className="text-sm text-slate-600">No tienes permiso para ver alertas.</p>
        <button type="button" onClick={onClose} className="rounded border border-slate-300 px-3 py-2 text-sm">
          Cerrar módulo
        </button>
      </section>
    );
  }

  return (
    <section className="space-y-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div className="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
        <h2 className="text-xl font-semibold text-slate-900">
          {vista === 'lista' ? 'Alertas' : 'Detalle de alerta'}
        </h2>

        <div className="flex flex-wrap gap-2">
          {vista === 'detalle' ? (
            <button
              type="button"
              className="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700"
              onClick={() => {
                setDetalle(null);
                setErrorAccion(null);
                setVista('lista');
                void cargarLista();
              }}
            >
              Volver al listado
            </button>
          ) : null}

          {vista === 'lista' ? (
            <button
              type="button"
              onClick={() => {
                void cargarLista();
              }}
              className="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700"
            >
              Actualizar
            </button>
          ) : null}

          <button type="button" onClick={onClose} className="rounded border border-red-100 px-3 py-2 text-sm text-red-700">
            Cerrar módulo
          </button>
        </div>
      </div>

      {errorGeneral ? <p className="text-sm text-red-600">{errorGeneral}</p> : null}
      {errorAccion ? <p className="text-sm text-red-600">{errorAccion}</p> : null}

      {vista === 'lista' && cargando ? <p className="text-sm text-slate-500">Cargando…</p> : null}

      {vista === 'lista' && !cargando ? (
        <ul className="divide-y divide-slate-100">
          {lista.length === 0 ? (
            <li className="py-3 text-sm text-slate-500">No hay alertas registradas.</li>
          ) : (
            lista.map((item) => (
              <li key={item.id} className="flex flex-wrap items-center justify-between gap-2 py-3">
                <button
                  type="button"
                  className="text-left text-sm text-slate-900 hover:underline"
                  onClick={() => {
                    void abrirDetalle(item.id);
                  }}
                >
                  <span className="font-medium">
                    {item.estudiante?.apellidos}, {item.estudiante?.nombres}
                  </span>
                  <span className="block text-xs text-slate-500">
                    Estado: {etiquetaEstado(item.estado)} · Índice: {item.indice_riesgo?.indice ?? '—'} (
                    {item.indice_riesgo?.nivel ?? '—'})
                  </span>
                </button>
              </li>
            ))
          )}
        </ul>
      ) : null}

      {vista === 'detalle' && detalle ? (
        <div className="space-y-6 text-sm text-slate-800">
          <div>
            <p className="text-xs uppercase tracking-wide text-slate-500">Estado</p>
            <p className="text-lg font-semibold text-slate-900">{etiquetaEstado(detalle.estado)}</p>
          </div>

          <dl className="grid gap-3 sm:grid-cols-2">
            <div>
              <dt className="text-slate-500">Estudiante</dt>
              <dd className="font-medium">
                {detalle.estudiante?.apellidos}, {detalle.estudiante?.nombres} ({detalle.estudiante?.codigo})
              </dd>
            </div>
            <div>
              <dt className="text-slate-500">Grado / sección</dt>
              <dd>
                {detalle.estudiante?.grado} {detalle.estudiante?.seccion} · Año {detalle.estudiante?.anio_escolar}
              </dd>
            </div>
            <div>
              <dt className="text-slate-500">Índice de riesgo</dt>
              <dd>{detalle.indice_riesgo?.indice ?? '—'}</dd>
            </div>
            <div>
              <dt className="text-slate-500">Nivel (cálculo)</dt>
              <dd>{detalle.indice_riesgo?.nivel ?? '—'}</dd>
            </div>
          </dl>

          <div>
            <dt className="text-xs uppercase tracking-wide text-slate-500">Recomendación</dt>
            <dd className="mt-1 whitespace-pre-wrap text-slate-800">{detalle.recomendacion ?? '—'}</dd>
          </div>

          {detalle.estado === 'cerrada' ? (
            <div className="rounded border border-slate-100 bg-slate-50 p-3">
              <p className="text-xs uppercase tracking-wide text-slate-500">Cierre</p>
              <p className="mt-1 whitespace-pre-wrap">{detalle.resultado_cierre ?? '—'}</p>
              <p className="mt-2 text-xs text-slate-500">
                Fecha cierre: {fechaLegible(detalle.fecha_cierre)} · Por: {detalle.cerrada_por?.email ?? '—'}
              </p>
            </div>
          ) : null}

          <div>
            <h3 className="mb-2 text-sm font-semibold text-slate-900">Historial de intervenciones</h3>
            {detalle.intervenciones?.length ? (
              <ul className="divide-y divide-slate-100 rounded border border-slate-100">
                {detalle.intervenciones.map((row) => (
                  <li key={row.id} className="p-3">
                    <p className="font-medium capitalize">{row.tipo}</p>
                    <p className="text-slate-600">{row.descripcion}</p>
                    <p className="mt-1 text-xs text-slate-500">
                      {fechaLegible(row.fecha)} · {row.registrado_por?.email ?? '—'}
                    </p>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-slate-500">Sin intervenciones registradas.</p>
            )}
          </div>

          {puedeRegistrar && detalle.estado !== 'cerrada' ? (
            <form
              className="space-y-3 rounded border border-slate-100 p-3"
              onSubmit={(e) => {
                e.preventDefault();
                void enviarIntervencion();
              }}
            >
              <h3 className="text-sm font-semibold text-slate-900">Registrar intervención</h3>

              <div className="space-y-1">
                <label className="text-xs text-slate-600">Tipo</label>
                <select
                  className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                  value={fmInter.tipo}
                  onChange={(e) => setFmInter((v) => ({ ...v, tipo: e.target.value }))}
                >
                  <option value="academica">Académica</option>
                  <option value="emocional">Emocional</option>
                  <option value="familiar">Familiar</option>
                </select>
              </div>

              <div className="space-y-1">
                <label className="text-xs text-slate-600">Descripción</label>
                <textarea
                  required
                  className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                  rows={3}
                  value={fmInter.descripcion}
                  onChange={(e) => setFmInter((v) => ({ ...v, descripcion: e.target.value }))}
                />
              </div>

              <div className="space-y-1">
                <label className="text-xs text-slate-600">Fecha</label>
                <input
                  type="date"
                  required
                  className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                  value={fmInter.fecha}
                  onChange={(e) => setFmInter((v) => ({ ...v, fecha: e.target.value }))}
                />
              </div>

              <button
                type="submit"
                disabled={guardando}
                className="rounded bg-slate-900 px-3 py-2 text-sm text-white disabled:opacity-60"
              >
                {guardando ? 'Guardando…' : 'Guardar intervención'}
              </button>
            </form>
          ) : null}

          {puedeRegistrar && detalle.estado !== 'cerrada' ? (
            <form
              className="space-y-3 rounded border border-amber-100 bg-amber-50/40 p-3"
              onSubmit={(e) => {
                e.preventDefault();
                void enviarCierre();
              }}
            >
              <h3 className="text-sm font-semibold text-slate-900">Cerrar alerta</h3>
              <p className="text-xs text-slate-600">
                Solo se permite si ya existe al menos una intervención. Describe el resultado del cierre.
              </p>

              <textarea
                required
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                rows={3}
                placeholder="Resultado del cierre"
                value={resultadoCierre}
                onChange={(e) => setResultadoCierre(e.target.value)}
              />

              <button
                type="submit"
                disabled={guardando}
                className="rounded border border-slate-800 px-3 py-2 text-sm text-slate-900 disabled:opacity-60"
              >
                {guardando ? 'Cerrando…' : 'Cerrar alerta'}
              </button>
            </form>
          ) : null}
        </div>
      ) : null}
    </section>
  );
}
