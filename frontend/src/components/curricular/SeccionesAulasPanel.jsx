import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  activarSeccionAula,
  createSeccionAula,
  desactivarSeccionAula,
  getSeccionesAulas,
  updateSeccionAula,
} from '../../lib/api';
import {
  NIVELES_CURRICULARES,
  etiquetaNivelCurricular,
  gradosCurricularesPorNivel,
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

export default function SeccionesAulasPanel() {
  const [filtros, setFiltros] = useState({
    nivel: 'inicial',
    grado: '3 años',
    q: '',
    incluir_inactivas: false,
  });
  const [secciones, setSecciones] = useState([]);
  const [cargando, setCargando] = useState(false);
  const [procesandoId, setProcesandoId] = useState(null);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [formNuevo, setFormNuevo] = useState({ nombre: '', orden: '' });
  const [guardandoNuevo, setGuardandoNuevo] = useState(false);

  const gradosDisponibles = useMemo(
    () => gradosCurricularesPorNivel(filtros.nivel),
    [filtros.nivel],
  );

  const cargar = useCallback(async () => {
    if (!filtros.nivel || !filtros.grado) {
      setSecciones([]);
      return;
    }

    setCargando(true);
    setError(null);
    try {
      const params = {
        nivel: filtros.nivel,
        grado: filtros.grado,
      };
      if (filtros.incluir_inactivas) {
        params.incluir_inactivas = 1;
      }
      if (filtros.q.trim()) {
        params.q = filtros.q.trim();
      }
      const resp = await getSeccionesAulas(params);
      setSecciones(Array.isArray(resp) ? resp : []);
    } catch (err) {
      setSecciones([]);
      setError(obtenerMensajeError(err));
    } finally {
      setCargando(false);
    }
  }, [filtros.grado, filtros.incluir_inactivas, filtros.nivel, filtros.q]);

  useEffect(() => {
    void cargar();
  }, [cargar]);

  useEffect(() => {
    const grados = gradosCurricularesPorNivel(filtros.nivel);
    if (grados.length > 0 && !grados.includes(filtros.grado)) {
      setFiltros((prev) => ({ ...prev, grado: grados[0] }));
    }
  }, [filtros.grado, filtros.nivel]);

  async function guardarCampo(id, campo, valor) {
    setProcesandoId(id);
    setError(null);
    setExito(null);
    try {
      await updateSeccionAula(id, { [campo]: valor });
      setExito('Sección actualizada.');
      await cargar();
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setProcesandoId(null);
    }
  }

  async function toggleActivo(seccion) {
    setProcesandoId(seccion.id);
    setError(null);
    setExito(null);
    try {
      if (seccion.activo) {
        await desactivarSeccionAula(seccion.id);
        setExito('Sección desactivada.');
      } else {
        await activarSeccionAula(seccion.id);
        setExito('Sección reactivada.');
      }
      await cargar();
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setProcesandoId(null);
    }
  }

  async function crearSeccion(e) {
    e.preventDefault();
    setGuardandoNuevo(true);
    setError(null);
    setExito(null);
    try {
      const payload = {
        nivel: filtros.nivel,
        grado: filtros.grado,
        nombre: formNuevo.nombre.trim(),
      };
      if (formNuevo.orden !== '') {
        payload.orden = Number(formNuevo.orden);
      }
      await createSeccionAula(payload);
      setFormNuevo({ nombre: '', orden: '' });
      setExito('Sección creada.');
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
        <h2 className="text-lg font-semibold text-[var(--text)]">Secciones / Aulas</h2>
        <p className="mt-1 text-sm text-muted">
          Catálogo nominal de secciones por nivel y grado. Las demás pantallas seguirán usando el valor de sección almacenado en cada registro.
        </p>
      </div>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <Card className="p-3 sm:p-4">
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <label className="block text-sm">
            <span className="mb-1 block font-medium text-[var(--text)]">Nivel</span>
            <select
              className="w-full rounded border border-[var(--border)] px-2 py-1.5 text-sm"
              value={filtros.nivel}
              onChange={(e) => setFiltros((prev) => ({
                ...prev,
                nivel: e.target.value,
                grado: gradosCurricularesPorNivel(e.target.value)[0] ?? '',
              }))}
            >
              {NIVELES_CURRICULARES.map((opcion) => (
                <option key={opcion.value} value={opcion.value}>{opcion.label}</option>
              ))}
            </select>
          </label>

          <label className="block text-sm">
            <span className="mb-1 block font-medium text-[var(--text)]">Grado</span>
            <select
              className="w-full rounded border border-[var(--border)] px-2 py-1.5 text-sm"
              value={filtros.grado}
              onChange={(e) => setFiltros((prev) => ({ ...prev, grado: e.target.value }))}
            >
              {gradosDisponibles.map((grado) => (
                <option key={grado} value={grado}>{grado}</option>
              ))}
            </select>
          </label>

          <label className="block text-sm">
            <span className="mb-1 block font-medium text-[var(--text)]">Buscar</span>
            <input
              type="search"
              className="w-full rounded border border-[var(--border)] px-2 py-1.5 text-sm"
              placeholder="Nombre"
              value={filtros.q}
              onChange={(e) => setFiltros((prev) => ({ ...prev, q: e.target.value }))}
            />
          </label>

          <label className="flex items-end gap-2 text-sm">
            <input
              type="checkbox"
              checked={filtros.incluir_inactivas}
              onChange={(e) => setFiltros((prev) => ({ ...prev, incluir_inactivas: e.target.checked }))}
            />
            <span>Incluir inactivas</span>
          </label>
        </div>
      </Card>

      {cargando ? (
        <LoadingState label="Cargando secciones…" />
      ) : secciones.length === 0 ? (
        <EmptyState
          title="Sin secciones"
          description={`No hay secciones para ${etiquetaNivelCurricular(filtros.nivel)} · ${filtros.grado}.`}
        />
      ) : (
        <Card className="overflow-hidden p-0">
          <div className="overflow-x-auto">
            <table className="min-w-full border-collapse text-sm">
              <thead>
                <tr className="border-b bg-[var(--surface-muted)] text-left text-xs uppercase tracking-wide text-muted">
                  <th className="px-3 py-2">Orden</th>
                  <th className="px-3 py-2">Nombre</th>
                  <th className="px-3 py-2">Nivel</th>
                  <th className="px-3 py-2">Grado</th>
                  <th className="px-3 py-2">Estado</th>
                  <th className="px-3 py-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {secciones.map((seccion) => (
                  <tr key={seccion.id} className="border-b last:border-b-0">
                    <td className="px-3 py-2">
                      <input
                        type="number"
                        min="0"
                        defaultValue={seccion.orden}
                        disabled={procesandoId === seccion.id}
                        className="w-16 rounded border border-[var(--border)] px-2 py-1 text-sm tabular-nums"
                        onBlur={(e) => {
                          const valor = Number(e.target.value);
                          if (!Number.isNaN(valor) && valor !== Number(seccion.orden)) {
                            void guardarCampo(seccion.id, 'orden', valor);
                          }
                        }}
                      />
                    </td>
                    <td className="px-3 py-2">
                      <input
                        type="text"
                        defaultValue={seccion.nombre}
                        disabled={procesandoId === seccion.id}
                        className="w-full min-w-[10rem] rounded border border-[var(--border)] px-2 py-1 text-sm"
                        onBlur={(e) => {
                          if (e.target.value.trim() !== seccion.nombre) {
                            void guardarCampo(seccion.id, 'nombre', e.target.value.trim());
                          }
                        }}
                      />
                    </td>
                    <td className="px-3 py-2">{etiquetaNivelCurricular(seccion.nivel)}</td>
                    <td className="px-3 py-2">{seccion.grado}</td>
                    <td className="px-3 py-2">
                      <span className={seccion.activo ? 'text-emerald-700' : 'text-muted'}>
                        {seccion.activo ? 'Activa' : 'Inactiva'}
                      </span>
                    </td>
                    <td className="px-3 py-2">
                      <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        disabled={procesandoId === seccion.id}
                        onClick={() => void toggleActivo(seccion)}
                      >
                        {seccion.activo ? 'Desactivar' : 'Reactivar'}
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
        <h3 className="text-sm font-semibold text-[var(--text)]">Nueva sección</h3>
        <form className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" onSubmit={crearSeccion}>
          <label className="block text-sm sm:col-span-2">
            <span className="mb-1 block font-medium text-[var(--text)]">Nombre</span>
            <input
              type="text"
              required
              className="w-full rounded border border-[var(--border)] px-2 py-1.5 text-sm"
              value={formNuevo.nombre}
              onChange={(e) => setFormNuevo((prev) => ({ ...prev, nombre: e.target.value }))}
            />
          </label>
          <label className="block text-sm">
            <span className="mb-1 block font-medium text-[var(--text)]">Orden (opcional)</span>
            <input
              type="number"
              min="0"
              className="w-full rounded border border-[var(--border)] px-2 py-1.5 text-sm"
              value={formNuevo.orden}
              onChange={(e) => setFormNuevo((prev) => ({ ...prev, orden: e.target.value }))}
            />
          </label>
          <div className="sm:col-span-2 lg:col-span-3">
            <Button type="submit" disabled={guardandoNuevo || !filtros.nivel || !filtros.grado}>
              {guardandoNuevo ? 'Guardando…' : 'Crear sección'}
            </Button>
          </div>
        </form>
      </Card>
    </div>
  );
}
