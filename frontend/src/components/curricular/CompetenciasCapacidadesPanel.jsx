import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getCapacidadesPorCompetencia,
  getCompetenciasPorArea,
  getCurricularAreas,
  leerPrefillCompetenciasCapacidades,
  patchCapacidad,
  patchCompetencia,
  patchDesactivarCapacidad,
  patchDesactivarCompetencia,
  patchReactivarCapacidad,
  patchReactivarCompetencia,
  postCapacidad,
  postCompetencia,
} from '../../lib/api';
import { NIVELES_CURRICULARES, etiquetaNivelCurricular } from '../../lib/academicoCurricular';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import LoadingState from '../ui/LoadingState';
import { FIELD, filtroActivoApi, obtenerMensajeError } from './competencias/utils';

const ESTADO_FILTRO = [
  { value: 'activas', label: 'Solo activas' },
  { value: 'inactivas', label: 'Solo inactivas' },
  { value: 'todas', label: 'Todas' },
];

export default function CompetenciasCapacidadesPanel() {
  const [filtros, setFiltros] = useState({
    nivel: 'primaria',
    area_id: '',
    estado: 'todas',
  });
  const [areas, setAreas] = useState([]);
  const [competencias, setCompetencias] = useState([]);
  const [capacidadesPorCompetencia, setCapacidadesPorCompetencia] = useState({});
  const [expandidaId, setExpandidaId] = useState(null);
  const [cargando, setCargando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);

  const [formCompetencia, setFormCompetencia] = useState(null);
  const [formCapacidad, setFormCapacidad] = useState(null);

  useEffect(() => {
    const prefill = leerPrefillCompetenciasCapacidades();
    if (prefill?.nivel) {
      setFiltros((prev) => ({
        ...prev,
        nivel: prefill.nivel,
        area_id: prefill.area_id ? String(prefill.area_id) : prev.area_id,
      }));
    }
  }, []);

  useEffect(() => {
    if (!filtros.nivel) return;
    void getCurricularAreas({ nivel: filtros.nivel }).then((data) =>
      setAreas(Array.isArray(data) ? data : []),
    );
  }, [filtros.nivel]);

  const cargarCompetencias = useCallback(async () => {
    if (!filtros.area_id) {
      setCompetencias([]);
      return;
    }
    setCargando(true);
    setError(null);
    try {
      const data = await getCompetenciasPorArea(filtros.area_id, {
        ...filtroActivoApi(filtros.estado),
        incluir_capacidades: 1,
        conteo_uso: 1,
      });
      const lista = Array.isArray(data) ? data : [];
      setCompetencias(lista);
      const capsMap = {};
      for (const comp of lista) {
        if (comp.capacidades) {
          capsMap[String(comp.id)] = comp.capacidades;
        }
      }
      setCapacidadesPorCompetencia(capsMap);
    } catch {
      setError('No se pudieron cargar las competencias.');
      setCompetencias([]);
    } finally {
      setCargando(false);
    }
  }, [filtros.area_id, filtros.estado]);

  useEffect(() => {
    void cargarCompetencias();
  }, [cargarCompetencias]);

  const cargarCapacidades = useCallback(
    async (competenciaId) => {
      try {
        const data = await getCapacidadesPorCompetencia(competenciaId, {
          ...filtroActivoApi(filtros.estado),
          conteo_uso: 1,
        });
        setCapacidadesPorCompetencia((prev) => ({
          ...prev,
          [String(competenciaId)]: Array.isArray(data) ? data : [],
        }));
      } catch {
        setError('No se pudieron cargar las capacidades.');
      }
    },
    [filtros.estado],
  );

  const toggleExpandir = useCallback(
    (competenciaId) => {
      const key = String(competenciaId);
      setExpandidaId((prev) => (prev === key ? null : key));
      if (!capacidadesPorCompetencia[key]) {
        void cargarCapacidades(competenciaId);
      }
    },
    [capacidadesPorCompetencia, cargarCapacidades],
  );

  const areaSeleccionada = useMemo(
    () => areas.find((a) => String(a.id) === String(filtros.area_id)),
    [areas, filtros.area_id],
  );

  const guardarCompetencia = useCallback(
    async (e) => {
      e.preventDefault();
      if (!filtros.area_id || !formCompetencia) return;
      setError(null);
      try {
        const payload = {
          nombre: formCompetencia.nombre.trim(),
          descripcion: formCompetencia.descripcion.trim() || null,
          codigo: formCompetencia.codigo.trim() || null,
        };
        if (formCompetencia.id) {
          await patchCompetencia(formCompetencia.id, payload);
          setExito('Competencia actualizada.');
        } else {
          await postCompetencia(filtros.area_id, payload);
          setExito('Competencia creada.');
        }
        setFormCompetencia(null);
        await cargarCompetencias();
      } catch (err) {
        setError(obtenerMensajeError(err, 'No se pudo guardar la competencia.'));
      }
    },
    [cargarCompetencias, filtros.area_id, formCompetencia],
  );

  const guardarCapacidad = useCallback(
    async (e) => {
      e.preventDefault();
      if (!formCapacidad?.competencia_id) return;
      setError(null);
      try {
        const payload = {
          nombre: formCapacidad.nombre.trim(),
          descripcion: formCapacidad.descripcion.trim() || null,
        };
        if (formCapacidad.id) {
          await patchCapacidad(formCapacidad.id, payload);
          setExito('Capacidad actualizada.');
        } else {
          await postCapacidad(formCapacidad.competencia_id, payload);
          setExito('Capacidad creada.');
        }
        setFormCapacidad(null);
        await cargarCompetencias();
        if (expandidaId) {
          await cargarCapacidades(expandidaId);
        }
      } catch (err) {
        setError(obtenerMensajeError(err, 'No se pudo guardar la capacidad.'));
      }
    },
    [cargarCapacidades, cargarCompetencias, expandidaId, formCapacidad],
  );

  const toggleActivoCompetencia = useCallback(
    async (competencia) => {
      setError(null);
      setExito(null);
      try {
        if (competencia.activo) {
          await patchDesactivarCompetencia(competencia.id);
          setExito('Competencia desactivada.');
        } else {
          await patchReactivarCompetencia(competencia.id);
          setExito('Competencia reactivada.');
        }
        await cargarCompetencias();
      } catch (err) {
        setError(obtenerMensajeError(err, 'No se pudo cambiar el estado de la competencia.'));
      }
    },
    [cargarCompetencias],
  );

  const toggleActivoCapacidad = useCallback(
    async (capacidad) => {
      setError(null);
      setExito(null);
      try {
        if (capacidad.activo) {
          await patchDesactivarCapacidad(capacidad.id);
          setExito('Capacidad desactivada.');
        } else {
          await patchReactivarCapacidad(capacidad.id);
          setExito('Capacidad reactivada.');
        }
        await cargarCompetencias();
        if (expandidaId) {
          await cargarCapacidades(expandidaId);
        }
      } catch (err) {
        setError(obtenerMensajeError(err, 'No se pudo cambiar el estado de la capacidad.'));
      }
    },
    [cargarCapacidades, cargarCompetencias, expandidaId],
  );

  const nivelLabel = etiquetaNivelCurricular(filtros.nivel);

  return (
    <div className="flex flex-col gap-6">
      <header className="space-y-1">
        <h2 className="text-2xl font-bold tracking-tight text-[var(--text)]">
          Competencias y capacidades
        </h2>
        <p className="max-w-3xl text-sm leading-relaxed text-muted">
          Gestione el catálogo institucional por área. Desactivar un elemento lo oculta en nuevos
          criterios; los criterios ya registrados conservan el vínculo histórico.
        </p>
      </header>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <Card className="p-5 sm:p-6">
        <h3 className="text-sm font-semibold text-[var(--text)]">Filtros</h3>
        <div className="mt-4 grid gap-4 sm:grid-cols-3">
          <label className="block text-sm font-medium text-[var(--text)]">
            Nivel
            <select
              className={FIELD}
              value={filtros.nivel}
              onChange={(e) =>
                setFiltros({ nivel: e.target.value, area_id: '', estado: filtros.estado })
              }
            >
              {NIVELES_CURRICULARES.map((n) => (
                <option key={n.value} value={n.value}>
                  {n.label}
                </option>
              ))}
            </select>
          </label>
          <label className="block text-sm font-medium text-[var(--text)]">
            Área
            <select
              className={FIELD}
              value={filtros.area_id}
              onChange={(e) => setFiltros((prev) => ({ ...prev, area_id: e.target.value }))}
            >
              <option value="">Seleccione área</option>
              {areas.map((a) => (
                <option key={a.id} value={a.id}>
                  {a.nombre}
                </option>
              ))}
            </select>
          </label>
          <label className="block text-sm font-medium text-[var(--text)]">
            Estado
            <select
              className={FIELD}
              value={filtros.estado}
              onChange={(e) => setFiltros((prev) => ({ ...prev, estado: e.target.value }))}
              disabled={!filtros.area_id}
            >
              {ESTADO_FILTRO.map((o) => (
                <option key={o.value} value={o.value}>
                  {o.label}
                </option>
              ))}
            </select>
          </label>
        </div>
        {areaSeleccionada ? (
          <p className="mt-3 text-xs text-muted">
            {nivelLabel} · {areaSeleccionada.nombre}
          </p>
        ) : null}
      </Card>

      {filtros.area_id ? (
        <div className="flex flex-wrap gap-2">
          <Button
            type="button"
            variant="primary"
            size="sm"
            onClick={() =>
              setFormCompetencia({ id: null, nombre: '', descripcion: '', codigo: '' })
            }
          >
            Nueva competencia
          </Button>
        </div>
      ) : null}

      {formCompetencia ? (
        <Card className="p-5 sm:p-6">
          <h3 className="text-sm font-semibold text-[var(--text)]">
            {formCompetencia.id ? 'Editar competencia' : 'Nueva competencia'}
          </h3>
          <form className="mt-4 space-y-3" onSubmit={(e) => void guardarCompetencia(e)}>
            <label className="block text-xs font-medium text-[var(--text)]">
              Nombre
              <input
                className={FIELD}
                value={formCompetencia.nombre}
                onChange={(e) =>
                  setFormCompetencia((p) => ({ ...p, nombre: e.target.value }))
                }
                required
              />
            </label>
            <label className="block text-xs font-medium text-[var(--text)]">
              Código (opcional)
              <input
                className={FIELD}
                value={formCompetencia.codigo}
                onChange={(e) => setFormCompetencia((p) => ({ ...p, codigo: e.target.value }))}
              />
            </label>
            <label className="block text-xs font-medium text-[var(--text)]">
              Descripción (opcional)
              <textarea
                className={`${FIELD} min-h-[56px]`}
                rows={2}
                value={formCompetencia.descripcion}
                onChange={(e) =>
                  setFormCompetencia((p) => ({ ...p, descripcion: e.target.value }))
                }
              />
            </label>
            <div className="flex flex-wrap gap-2">
              <Button type="submit" size="sm" variant="primary">
                Guardar
              </Button>
              <Button type="button" size="sm" variant="outline" onClick={() => setFormCompetencia(null)}>
                Cancelar
              </Button>
            </div>
          </form>
        </Card>
      ) : null}

      {formCapacidad ? (
        <Card className="p-5 sm:p-6">
          <h3 className="text-sm font-semibold text-[var(--text)]">
            {formCapacidad.id ? 'Editar capacidad' : 'Nueva capacidad'}
          </h3>
          <form className="mt-4 space-y-3" onSubmit={(e) => void guardarCapacidad(e)}>
            <label className="block text-xs font-medium text-[var(--text)]">
              Nombre
              <input
                className={FIELD}
                value={formCapacidad.nombre}
                onChange={(e) => setFormCapacidad((p) => ({ ...p, nombre: e.target.value }))}
                required
              />
            </label>
            <label className="block text-xs font-medium text-[var(--text)]">
              Descripción (opcional)
              <textarea
                className={`${FIELD} min-h-[56px]`}
                rows={2}
                value={formCapacidad.descripcion}
                onChange={(e) =>
                  setFormCapacidad((p) => ({ ...p, descripcion: e.target.value }))
                }
              />
            </label>
            <div className="flex flex-wrap gap-2">
              <Button type="submit" size="sm" variant="primary">
                Guardar
              </Button>
              <Button type="button" size="sm" variant="outline" onClick={() => setFormCapacidad(null)}>
                Cancelar
              </Button>
            </div>
          </form>
        </Card>
      ) : null}

      {!filtros.area_id ? (
        <Card className="p-8 text-center text-sm text-muted">Seleccione un área para comenzar.</Card>
      ) : null}

      {filtros.area_id && cargando ? <LoadingState label="Cargando competencias…" /> : null}

      {filtros.area_id && !cargando && competencias.length === 0 ? (
        <Card className="p-8 text-center text-sm text-muted">
          No hay competencias para los filtros seleccionados.
        </Card>
      ) : null}

      {filtros.area_id && !cargando && competencias.length > 0 ? (
        <div className="space-y-3">
          {competencias.map((competencia) => {
            const key = String(competencia.id);
            const expandida = expandidaId === key;
            const capacidades =
              capacidadesPorCompetencia[key] ?? competencia.capacidades ?? [];

            return (
              <Card key={competencia.id} className="overflow-hidden p-0" padding={false}>
                <div className="border-b border-[var(--border)]/70 bg-[var(--surface)] px-4 py-3 sm:px-5">
                  <div className="flex flex-wrap items-start justify-between gap-2">
                    <button
                      type="button"
                      className="min-w-0 flex-1 text-left"
                      onClick={() => toggleExpandir(competencia.id)}
                    >
                      <p className="font-semibold text-[var(--text)]">{competencia.nombre}</p>
                      {competencia.descripcion ? (
                        <p className="mt-1 text-xs text-muted">{competencia.descripcion}</p>
                      ) : null}
                    </button>
                    <div className="flex flex-wrap items-center gap-1.5">
                      <Badge variant={competencia.activo ? 'success' : 'neutral'}>
                        {competencia.activo ? 'Activa' : 'Inactiva'}
                      </Badge>
                      {(competencia.conteo_uso ?? 0) > 0 ? (
                        <Badge variant="warning">En uso</Badge>
                      ) : null}
                      <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        onClick={() =>
                          setFormCompetencia({
                            id: competencia.id,
                            nombre: competencia.nombre,
                            descripcion: competencia.descripcion ?? '',
                            codigo: competencia.codigo ?? '',
                          })
                        }
                      >
                        Editar
                      </Button>
                      <Button
                        type="button"
                        size="sm"
                        variant={competencia.activo ? 'danger' : 'outline'}
                        onClick={() => void toggleActivoCompetencia(competencia)}
                      >
                        {competencia.activo ? 'Desactivar' : 'Reactivar'}
                      </Button>
                    </div>
                  </div>
                </div>

                {expandida ? (
                  <div className="space-y-2 p-4 sm:p-5">
                    <div className="flex justify-end">
                      <Button
                        type="button"
                        size="sm"
                        variant="secondary"
                        onClick={() =>
                          setFormCapacidad({
                            id: null,
                            competencia_id: competencia.id,
                            nombre: '',
                            descripcion: '',
                          })
                        }
                      >
                        Nueva capacidad
                      </Button>
                    </div>
                    {capacidades.length === 0 ? (
                      <p className="text-sm text-muted">Sin capacidades en este filtro.</p>
                    ) : (
                      <ul className="space-y-2">
                        {capacidades.map((cap) => (
                          <li
                            key={cap.id}
                            className="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-[var(--border)]/80 px-3 py-2"
                          >
                            <div className="min-w-0">
                              <p className="font-medium text-[var(--text)]">{cap.nombre}</p>
                              {cap.descripcion ? (
                                <p className="text-xs text-muted">{cap.descripcion}</p>
                              ) : null}
                            </div>
                            <div className="flex flex-wrap items-center gap-1.5">
                              <Badge variant={cap.activo ? 'success' : 'neutral'}>
                                {cap.activo ? 'Activa' : 'Inactiva'}
                              </Badge>
                              {(cap.conteo_uso ?? 0) > 0 ? (
                                <Badge variant="warning">En uso</Badge>
                              ) : null}
                              <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                onClick={() =>
                                  setFormCapacidad({
                                    id: cap.id,
                                    competencia_id: competencia.id,
                                    nombre: cap.nombre,
                                    descripcion: cap.descripcion ?? '',
                                  })
                                }
                              >
                                Editar
                              </Button>
                              <Button
                                type="button"
                                size="sm"
                                variant={cap.activo ? 'danger' : 'outline'}
                                onClick={() => void toggleActivoCapacidad(cap)}
                              >
                                {cap.activo ? 'Desactivar' : 'Reactivar'}
                              </Button>
                            </div>
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                ) : null}
              </Card>
            );
          })}
        </div>
      ) : null}
    </div>
  );
}
