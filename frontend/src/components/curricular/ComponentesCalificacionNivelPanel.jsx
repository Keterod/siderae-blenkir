import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getComponentesCalificacionPorNivel,
  patchComponenteCalificacion,
  patchDesactivarComponenteCalificacion,
  patchReactivarComponenteCalificacion,
  postComponenteCalificacion,
} from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import { resolverCalendarioActivoParaFiltros } from '../../lib/calendarioAcademico';
import {
  etiquetaNivelCurricular,
  NIVELES_CURRICULARES,
} from '../../lib/academicoCurricular';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

function obtenerMensajeError(err) {
  const payload = err?.payload;
  if (!payload) return 'No se pudo completar la operación.';
  if (payload.errors) {
    const first = Object.values(payload.errors).flat()[0];
    if (first) return first;
  }
  return payload.message ?? 'No se pudo completar la operación.';
}

export default function ComponentesCalificacionNivelPanel() {
  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: 'primaria',
  });
  const [data, setData] = useState(null);
  const [cargando, setCargando] = useState(false);
  const [procesandoId, setProcesandoId] = useState(null);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [formNuevo, setFormNuevo] = useState({ nombre: '', peso: '', activo: false });
  const [guardandoNuevo, setGuardandoNuevo] = useState(false);

  useEffect(() => {
    void resolverCalendarioActivoParaFiltros().then((cal) => {
      if (!cal?.anio) return;
      setFiltros((prev) => ({ ...prev, anio_escolar: cal.anio }));
    });
  }, []);

  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      const resp = await getComponentesCalificacionPorNivel(filtros.nivel, {
        anio_escolar: filtros.anio_escolar,
      });
      setData(resp);
    } catch (err) {
      setData(null);
      setError(obtenerMensajeError(err));
    } finally {
      setCargando(false);
    }
  }, [filtros.anio_escolar, filtros.nivel]);

  useEffect(() => {
    void cargar();
  }, [cargar]);

  const componentes = data?.componentes ?? [];
  const validacion = data?.validacion;
  const sumaValida = validacion?.valido === true;

  const filasOrdenadas = useMemo(
    () => [...componentes].sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0)),
    [componentes],
  );

  async function guardarCampo(id, campo, valor) {
    setProcesandoId(id);
    setError(null);
    setExito(null);
    try {
      await patchComponenteCalificacion(id, { [campo]: valor });
      setExito('Componente actualizado.');
      await cargar();
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setProcesandoId(null);
    }
  }

  async function toggleActivo(componente) {
    setProcesandoId(componente.id);
    setError(null);
    setExito(null);
    try {
      if (componente.activo) {
        await patchDesactivarComponenteCalificacion(componente.id);
        setExito('Componente desactivado.');
      } else {
        const peso = Number(componente.peso) > 0 ? Number(componente.peso) : 0;
        const input = window.prompt('Indique el peso (%) para reactivar el componente:', String(peso || ''));
        if (input == null || input === '') {
          return;
        }
        await patchReactivarComponenteCalificacion(componente.id, { peso: Number(input) });
        setExito('Componente reactivado.');
      }
      await cargar();
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setProcesandoId(null);
    }
  }

  async function crearComponente(e) {
    e.preventDefault();
    setGuardandoNuevo(true);
    setError(null);
    setExito(null);
    try {
      const payload = {
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        nombre: formNuevo.nombre.trim(),
        activo: formNuevo.activo,
      };
      if (formNuevo.peso !== '') {
        payload.peso = Number(formNuevo.peso);
      }
      await postComponenteCalificacion(payload);
      setFormNuevo({ nombre: '', peso: '', activo: false });
      setExito('Componente creado.');
      await cargar();
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setGuardandoNuevo(false);
    }
  }

  return (
    <div className="space-y-4">
      <div>
        <h2 className="text-lg font-semibold text-[var(--text)]">Componentes de calificación</h2>
        <p className="mt-1 text-sm text-muted">
          Configure los componentes y pesos por nivel educativo. Los componentes activos deben sumar 100%.
        </p>
      </div>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <Card className="p-3 sm:p-4">
        <div className="grid gap-3 sm:grid-cols-3">
          <label className="block text-sm">
            <span className="mb-1 block font-medium text-[var(--text)]">Año escolar</span>
            <input
              type="text"
              className="w-full rounded border border-[var(--border)] px-2 py-1.5 text-sm"
              value={filtros.anio_escolar}
              onChange={(e) => setFiltros((prev) => ({ ...prev, anio_escolar: e.target.value }))}
            />
          </label>
          <label className="block text-sm sm:col-span-2">
            <span className="mb-1 block font-medium text-[var(--text)]">Nivel</span>
            <select
              className="w-full rounded border border-[var(--border)] px-2 py-1.5 text-sm"
              value={filtros.nivel}
              onChange={(e) => setFiltros((prev) => ({ ...prev, nivel: e.target.value }))}
            >
              {NIVELES_CURRICULARES.map((nivel) => (
                <option key={nivel} value={nivel}>{etiquetaNivelCurricular(nivel)}</option>
              ))}
            </select>
          </label>
        </div>
      </Card>

      {cargando ? (
        <LoadingState label="Cargando componentes…" />
      ) : filasOrdenadas.length === 0 ? (
        <EmptyState title="Sin componentes" description="No hay componentes configurados para este año y nivel." />
      ) : (
        <Card className="overflow-hidden p-0">
          <div className="border-b border-[var(--border)] px-3 py-2 sm:px-4">
            <p className={`text-sm font-medium ${sumaValida ? 'text-emerald-700' : 'text-amber-700'}`}>
              Suma activos: {validacion?.suma ?? '—'}%
              {sumaValida ? ' · válida' : ' · debe ser 100%'}
            </p>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full border-collapse text-sm">
              <thead>
                <tr className="border-b bg-[var(--surface-muted)] text-left text-xs uppercase tracking-wide text-muted">
                  <th className="px-3 py-2">Orden</th>
                  <th className="px-3 py-2">Código</th>
                  <th className="px-3 py-2">Nombre</th>
                  <th className="px-3 py-2">Peso %</th>
                  <th className="px-3 py-2">Estado</th>
                  <th className="px-3 py-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {filasOrdenadas.map((c) => (
                  <tr key={c.id} className="border-b last:border-b-0">
                    <td className="px-3 py-2 tabular-nums">{c.orden}</td>
                    <td className="px-3 py-2 font-mono text-xs">{c.codigo}</td>
                    <td className="px-3 py-2">
                      <input
                        type="text"
                        defaultValue={c.nombre}
                        disabled={procesandoId === c.id}
                        className="w-full min-w-[8rem] rounded border border-[var(--border)] px-2 py-1 text-sm"
                        onBlur={(e) => {
                          if (e.target.value.trim() !== c.nombre) {
                            void guardarCampo(c.id, 'nombre', e.target.value.trim());
                          }
                        }}
                      />
                    </td>
                    <td className="px-3 py-2">
                      <input
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        defaultValue={c.peso}
                        disabled={!c.activo || procesandoId === c.id}
                        className="w-20 rounded border border-[var(--border)] px-2 py-1 text-sm tabular-nums"
                        onBlur={(e) => {
                          const valor = Number(e.target.value);
                          if (!Number.isNaN(valor) && valor !== Number(c.peso)) {
                            void guardarCampo(c.id, 'peso', valor);
                          }
                        }}
                      />
                    </td>
                    <td className="px-3 py-2">
                      <span className={c.activo ? 'text-emerald-700' : 'text-muted'}>
                        {c.activo ? 'Activo' : 'Inactivo'}
                      </span>
                    </td>
                    <td className="px-3 py-2">
                      <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        disabled={procesandoId === c.id}
                        onClick={() => void toggleActivo(c)}
                      >
                        {c.activo ? 'Desactivar' : 'Reactivar'}
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>
      )}

      <Card className="p-3 sm:p-4">
        <h3 className="text-sm font-semibold text-[var(--text)]">Agregar componente</h3>
        <p className="mt-1 text-xs text-muted">
          Cree el componente como inactivo si aún debe ajustar los pesos de los demás antes de activarlo.
        </p>
        <form className="mt-3 grid gap-3 sm:grid-cols-4" onSubmit={crearComponente}>
          <input
            type="text"
            required
            placeholder="Nombre (ej. Observación)"
            className="rounded border border-[var(--border)] px-2 py-1.5 text-sm sm:col-span-2"
            value={formNuevo.nombre}
            onChange={(e) => setFormNuevo((prev) => ({ ...prev, nombre: e.target.value }))}
          />
          <input
            type="number"
            min="0"
            max="100"
            step="0.01"
            placeholder="Peso %"
            className="rounded border border-[var(--border)] px-2 py-1.5 text-sm"
            value={formNuevo.peso}
            onChange={(e) => setFormNuevo((prev) => ({ ...prev, peso: e.target.value }))}
          />
          <label className="flex items-center gap-2 text-sm">
            <input
              type="checkbox"
              checked={formNuevo.activo}
              onChange={(e) => setFormNuevo((prev) => ({ ...prev, activo: e.target.checked }))}
            />
            Activo al crear
          </label>
          <div className="sm:col-span-4">
            <Button type="submit" disabled={guardandoNuevo}>
              {guardandoNuevo ? 'Guardando…' : 'Agregar componente'}
            </Button>
          </div>
        </form>
      </Card>
    </div>
  );
}
