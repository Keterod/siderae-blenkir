import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getAsignacionesDocente,
  getAsignacionesDocentePorDocente,
  getCurricularDocentes,
  getMallaCurricularPorGrado,
  postAsignacionDocenteBulk,
} from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import { gradosCurricularesPorNivel, NIVELES_CURRICULARES } from '../../lib/academicoCurricular';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';
import { FIELD } from './malla/utils';

const SECCIONES = ['A', 'B', 'C'];
const SEDES = [
  { value: 'chilca', label: 'Chilca' },
  { value: 'auquimarca', label: 'Auquimarca' },
];

function agruparCursosPorArea(cursos) {
  const mapa = new Map();
  for (const curso of cursos) {
    const areaNombre = curso.area?.nombre ?? 'Sin área';
    if (!mapa.has(areaNombre)) {
      mapa.set(areaNombre, []);
    }
    mapa.get(areaNombre).push(curso);
  }
  return [...mapa.entries()].sort(([a], [b]) => a.localeCompare(b, 'es'));
}

function obtenerMensajeError(err) {
  if (err?.payload?.message) return err.payload.message;
  if (err?.payload?.errors) {
    const first = Object.values(err.payload.errors)[0];
    if (Array.isArray(first) && first[0]) return first[0];
  }
  return 'No se pudo completar la operación.';
}

export default function AsignacionDocentePanel() {
  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: 'primaria',
    sede: 'chilca',
  });
  const [busqueda, setBusqueda] = useState('');
  const [docentes, setDocentes] = useState([]);
  const [docenteSeleccionado, setDocenteSeleccionado] = useState(null);
  const [grado, setGrado] = useState('2do');
  const [seccion, setSeccion] = useState('A');
  const [mallaCursos, setMallaCursos] = useState([]);
  const [asignacionesContexto, setAsignacionesContexto] = useState([]);
  const [resumenDocente, setResumenDocente] = useState([]);
  const [resumenGeneral, setResumenGeneral] = useState([]);
  const [marcados, setMarcados] = useState(new Set());
  const [cargandoDocentes, setCargandoDocentes] = useState(true);
  const [cargandoPanel, setCargandoPanel] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);

  const cargarDocentes = useCallback(async () => {
    setCargandoDocentes(true);
    setError(null);
    try {
      const data = await getCurricularDocentes({
        search: busqueda.trim() || undefined,
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        sede: filtros.sede,
      });
      setDocentes(Array.isArray(data) ? data : []);
    } catch {
      setError('No se pudieron cargar los docentes.');
      setDocentes([]);
    } finally {
      setCargandoDocentes(false);
    }
  }, [busqueda, filtros.anio_escolar, filtros.nivel, filtros.sede]);

  const cargarResumenDocente = useCallback(async (docenteId) => {
    if (!docenteId) {
      setResumenDocente([]);
      return;
    }
    try {
      const data = await getAsignacionesDocentePorDocente(docenteId, {
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        sede: filtros.sede,
      });
      setResumenDocente(Array.isArray(data?.resumen) ? data.resumen : []);
    } catch {
      setResumenDocente([]);
    }
  }, [filtros.anio_escolar, filtros.nivel, filtros.sede]);

  const cargarPanelDocente = useCallback(async () => {
    if (!docenteSeleccionado || !grado) {
      setMallaCursos([]);
      setAsignacionesContexto([]);
      setMarcados(new Set());
      return;
    }

    setCargandoPanel(true);
    setError(null);
    try {
      const [malla, asignaciones] = await Promise.all([
        getMallaCurricularPorGrado({
          anio_escolar: filtros.anio_escolar,
          nivel: filtros.nivel,
          grado,
        }),
        getAsignacionesDocente({
          anio_escolar: filtros.anio_escolar,
          nivel: filtros.nivel,
          sede: filtros.sede,
          grado,
          seccion,
          activo: true,
        }),
      ]);

      const activos = (malla?.malla_cursos ?? []).filter((c) => c.activo);
      setMallaCursos(activos);
      setAsignacionesContexto(Array.isArray(asignaciones) ? asignaciones : []);

      const iniciales = new Set();
      for (const curso of activos) {
        const asignacion = (asignaciones ?? []).find((a) => a.malla_curso_id === curso.id);
        if (asignacion?.user_id === docenteSeleccionado.id) {
          iniciales.add(curso.id);
        }
      }
      setMarcados(iniciales);
    } catch {
      setError('No se pudo cargar la malla o las asignaciones del grado.');
      setMallaCursos([]);
      setAsignacionesContexto([]);
      setMarcados(new Set());
    } finally {
      setCargandoPanel(false);
    }
  }, [docenteSeleccionado, filtros.anio_escolar, filtros.nivel, filtros.sede, grado, seccion]);

  const cargarResumenGeneral = useCallback(async () => {
    if (!grado || !seccion) {
      setResumenGeneral([]);
      return;
    }
    try {
      const asignaciones = await getAsignacionesDocente({
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        sede: filtros.sede,
        grado,
        seccion,
        activo: true,
      });
      setResumenGeneral(Array.isArray(asignaciones) ? asignaciones : []);
    } catch {
      setResumenGeneral([]);
    }
  }, [filtros.anio_escolar, filtros.nivel, filtros.sede, grado, seccion]);

  useEffect(() => {
    const timer = setTimeout(() => {
      void cargarDocentes();
    }, busqueda.trim() ? 300 : 0);
    return () => clearTimeout(timer);
  }, [cargarDocentes, busqueda]);

  useEffect(() => {
    void cargarPanelDocente();
    void cargarResumenGeneral();
  }, [cargarPanelDocente, cargarResumenGeneral]);

  useEffect(() => {
    void cargarResumenDocente(docenteSeleccionado?.id);
  }, [docenteSeleccionado?.id, cargarResumenDocente]);

  const cursosPorArea = useMemo(() => agruparCursosPorArea(mallaCursos), [mallaCursos]);

  const asignacionPorCurso = useMemo(() => {
    const mapa = new Map();
    for (const a of asignacionesContexto) {
      mapa.set(a.malla_curso_id, a);
    }
    return mapa;
  }, [asignacionesContexto]);

  function toggleCurso(cursoId) {
    const asignacion = asignacionPorCurso.get(cursoId);
    if (asignacion && asignacion.user_id !== docenteSeleccionado?.id) {
      return;
    }
    setMarcados((prev) => {
      const next = new Set(prev);
      if (next.has(cursoId)) next.delete(cursoId);
      else next.add(cursoId);
      return next;
    });
  }

  async function guardar() {
    if (!docenteSeleccionado) return;
    setGuardando(true);
    setError(null);
    setExito(null);
    try {
      await postAsignacionDocenteBulk({
        docente_id: docenteSeleccionado.id,
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        grado,
        seccion,
        sede: filtros.sede,
        malla_curso_ids: [...marcados],
      });
      setExito('Asignaciones guardadas correctamente.');
      await Promise.all([cargarPanelDocente(), cargarResumenDocente(docenteSeleccionado.id), cargarResumenGeneral(), cargarDocentes()]);
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setGuardando(false);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <Card className="p-6">
        <h2 className="text-lg font-semibold">Asignación docente</h2>
        <p className="mt-1 text-sm text-muted">
          Seleccione un docente y marque los cursos que dictará por grado y sección. Un curso activo solo puede tener un docente por sección.
        </p>
      </Card>

      <Card className="p-5 sm:p-6">
        <h3 className="text-sm font-semibold text-[var(--text)]">Filtros generales</h3>
        <div className="mt-4 grid gap-4 sm:grid-cols-3">
          <label className="block text-sm font-medium text-[var(--text)]">
            Año escolar
            <input
              className={FIELD}
              value={filtros.anio_escolar}
              onChange={(e) => setFiltros((prev) => ({ ...prev, anio_escolar: e.target.value }))}
            />
          </label>
          <label className="block text-sm font-medium text-[var(--text)]">
            Nivel
            <select
              className={FIELD}
              value={filtros.nivel}
              onChange={(e) => {
                const nivel = e.target.value;
                const grados = gradosCurricularesPorNivel(nivel);
                setFiltros((prev) => ({ ...prev, nivel }));
                setGrado(grados[0] ?? '');
              }}
            >
              {NIVELES_CURRICULARES.map((n) => (
                <option key={n.value} value={n.value}>{n.label}</option>
              ))}
            </select>
          </label>
          <label className="block text-sm font-medium text-[var(--text)]">
            Sede
            <select
              className={FIELD}
              value={filtros.sede}
              onChange={(e) => setFiltros((prev) => ({ ...prev, sede: e.target.value }))}
            >
              {SEDES.map((s) => (
                <option key={s.value} value={s.value}>{s.label}</option>
              ))}
            </select>
          </label>
        </div>
      </Card>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <div className="grid gap-6 lg:grid-cols-[minmax(280px,360px)_1fr]">
        <Card className="flex flex-col p-5 sm:p-6">
          <h3 className="text-sm font-semibold text-[var(--text)]">Docentes</h3>
          <input
            className={`${FIELD} mt-3`}
            placeholder="Buscar docente por nombre o correo"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
          />
          <div className="mt-4 flex-1 overflow-y-auto">
            {cargandoDocentes ? <LoadingState /> : null}
            {!cargandoDocentes && docentes.length === 0 ? (
              <EmptyState title="Sin docentes" description="No hay usuarios con rol docente para los filtros actuales." />
            ) : null}
            <ul className="space-y-2">
              {docentes.map((docente) => {
                const seleccionado = docenteSeleccionado?.id === docente.id;
                return (
                  <li key={docente.id}>
                    <button
                      type="button"
                      onClick={() => setDocenteSeleccionado(docente)}
                      className={`w-full rounded-lg border px-3 py-3 text-left transition ${
                        seleccionado
                          ? 'border-[var(--primary)] bg-[var(--primary-soft)] ring-1 ring-[var(--primary)]'
                          : 'border-[var(--border)] hover:border-[var(--primary)]/40'
                      }`}
                    >
                      <p className="font-medium text-[var(--text)]">{docente.name}</p>
                      <p className="mt-0.5 text-xs text-muted">{docente.email}</p>
                      <p className="mt-1 text-xs text-muted">
                        {docente.asignaciones_activas_count ?? 0} curso(s) asignado(s) en este contexto
                      </p>
                    </button>
                  </li>
                );
              })}
            </ul>
          </div>
        </Card>

        <div className="flex flex-col gap-6">
          {!docenteSeleccionado ? (
            <Card className="p-6">
              <EmptyState
                title="Seleccione un docente"
                description="Elija un docente de la lista para asignar cursos por grado y sección."
              />
            </Card>
          ) : (
            <Card className="p-5 sm:p-6">
              <h3 className="text-sm font-semibold text-[var(--text)]">Docente seleccionado</h3>
              <div className="mt-2 rounded-lg bg-[var(--surface-muted)] px-4 py-3">
                <p className="font-medium">{docenteSeleccionado.name}</p>
                <p className="text-sm text-muted">{docenteSeleccionado.email}</p>
              </div>

              <div className="mt-4 grid gap-4 sm:grid-cols-2">
                <label className="block text-sm font-medium text-[var(--text)]">
                  Grado
                  <select className={FIELD} value={grado} onChange={(e) => setGrado(e.target.value)}>
                    {gradosCurricularesPorNivel(filtros.nivel).map((g) => (
                      <option key={g} value={g}>{g}</option>
                    ))}
                  </select>
                </label>
                <label className="block text-sm font-medium text-[var(--text)]">
                  Sección
                  <select className={FIELD} value={seccion} onChange={(e) => setSeccion(e.target.value)}>
                    {SECCIONES.map((s) => (
                      <option key={s} value={s}>{s}</option>
                    ))}
                  </select>
                </label>
              </div>

              {cargandoPanel ? <LoadingState className="mt-6" /> : null}

              {!cargandoPanel && cursosPorArea.length === 0 ? (
                <EmptyState className="mt-6" title="Sin cursos activos" description="No hay cursos activos en la malla para este grado." />
              ) : null}

              {!cargandoPanel && cursosPorArea.length > 0 ? (
                <div className="mt-6 space-y-5">
                  {cursosPorArea.map(([areaNombre, cursos]) => (
                    <section key={areaNombre}>
                      <h4 className="text-sm font-semibold text-[var(--text)]">{areaNombre}</h4>
                      <ul className="mt-2 space-y-2">
                        {cursos.map((curso) => {
                          const asignacion = asignacionPorCurso.get(curso.id);
                          const esPropio = asignacion?.user_id === docenteSeleccionado.id;
                          const otroDocente = asignacion && !esPropio;
                          const marcado = marcados.has(curso.id);
                          const nombreCurso = curso.curso_catalogo?.nombre ?? `Curso #${curso.id}`;

                          return (
                            <li key={curso.id} className="flex flex-col gap-0.5 sm:flex-row sm:items-center sm:justify-between">
                              <label className={`flex items-center gap-2 text-sm ${otroDocente ? 'text-muted' : 'text-[var(--text)]'}`}>
                                <input
                                  type="checkbox"
                                  checked={marcado}
                                  disabled={Boolean(otroDocente)}
                                  onChange={() => toggleCurso(curso.id)}
                                />
                                <span>{nombreCurso}</span>
                              </label>
                              {otroDocente ? (
                                <span className="text-xs text-muted sm:pl-6">
                                  Asignado a: {asignacion.user?.name ?? 'otro docente'}
                                </span>
                              ) : null}
                            </li>
                          );
                        })}
                      </ul>
                    </section>
                  ))}
                </div>
              ) : null}

              <div className="mt-6">
                <Button type="button" variant="primary" disabled={guardando || cargandoPanel} onClick={() => void guardar()}>
                  {guardando ? 'Guardando…' : 'Guardar asignación'}
                </Button>
              </div>
            </Card>
          )}

          {docenteSeleccionado ? (
            <Card className="p-5 sm:p-6">
              <h3 className="text-sm font-semibold text-[var(--text)]">Asignaciones actuales — {docenteSeleccionado.name}</h3>
              {resumenDocente.length === 0 ? (
                <p className="mt-3 text-sm text-muted">Este docente no tiene cursos asignados en el contexto seleccionado.</p>
              ) : (
                <ul className="mt-3 space-y-4">
                  {resumenDocente.map((grupo) => (
                    <li key={`${grupo.grado}-${grupo.seccion}`}>
                      <p className="font-medium text-[var(--text)]">{grupo.grado} {grupo.seccion}</p>
                      <ul className="mt-1 list-disc pl-5 text-sm text-muted">
                        {(grupo.cursos ?? []).map((c) => (
                          <li key={c.malla_curso_id}>{c.area} / {c.curso}</li>
                        ))}
                      </ul>
                    </li>
                  ))}
                </ul>
              )}
            </Card>
          ) : null}

          {grado && seccion ? (
            <Card className="p-5 sm:p-6">
              <h3 className="text-sm font-semibold text-[var(--text)]">Resumen general — {grado} {seccion}</h3>
              {resumenGeneral.length === 0 ? (
                <p className="mt-3 text-sm text-muted">No hay asignaciones activas para este grado y sección.</p>
              ) : (
                <ul className="mt-3 space-y-2 text-sm">
                  {resumenGeneral.map((a) => (
                    <li key={a.id} className="text-muted">
                      {a.malla_curso?.area?.nombre} / {a.malla_curso?.curso_catalogo?.nombre}
                      {' — '}
                      <span className="text-[var(--text)]">{a.user?.name}</span>
                    </li>
                  ))}
                </ul>
              )}
            </Card>
          ) : null}
        </div>
      </div>
    </div>
  );
}
