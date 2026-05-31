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
import { resolverCalendarioActivoParaFiltros } from '../../lib/calendarioAcademico';
import { useOpcionesSeccionAula } from '../../lib/seccionesAula';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';
import { FIELD } from './malla/utils';

const DEMO_ASIGNACION = {
  nivel: 'primaria',
  sede: 'chilca',
  grado: '2do',
  seccion: 'AMISTAD',
  docenteEmail: 'docente@siderae.test',
};
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
  const [cargandoAnioActivo, setCargandoAnioActivo] = useState(true);
  const [sinAnioActivo, setSinAnioActivo] = useState(false);
  const [busqueda, setBusqueda] = useState('');
  const [docentes, setDocentes] = useState([]);
  const [docenteSeleccionado, setDocenteSeleccionado] = useState(null);
  const [grado, setGrado] = useState('2do');
  const [seccion, setSeccion] = useState('AMISTAD');
  const [mallaCursos, setMallaCursos] = useState([]);
  const [mallaProvisionada, setMallaProvisionada] = useState(true);
  const [asignacionesContexto, setAsignacionesContexto] = useState([]);
  const [resumenDocente, setResumenDocente] = useState([]);
  const [resumenGeneral, setResumenGeneral] = useState([]);
  const [marcados, setMarcados] = useState(new Set());
  const [cargandoDocentes, setCargandoDocentes] = useState(true);
  const [cargandoPanel, setCargandoPanel] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);

  const seccionesLegacy = useMemo(() => {
    const nombres = new Set();
    for (const grupo of resumenDocente) {
      if (grupo.grado === grado && grupo.seccion) {
        nombres.add(grupo.seccion);
      }
    }
    for (const asignacion of asignacionesContexto) {
      if (
        asignacion.seccion
        && asignacion.grado === grado
        && asignacion.nivel === filtros.nivel
      ) {
        nombres.add(asignacion.seccion);
      }
    }
    return [...nombres];
  }, [resumenDocente, asignacionesContexto, grado, filtros.nivel]);

  const opcionesSeccion = useOpcionesSeccionAula({
    nivel: filtros.nivel,
    grado,
    gradoFormato: 'curricular',
    legacy: seccionesLegacy,
    valorActual: seccion,
  });

  const puedeOperar = !sinAnioActivo && !cargandoAnioActivo;

  useEffect(() => {
    if (!seccion) {
      return;
    }
    const valores = opcionesSeccion.map((o) => o.value);
    if (valores.length > 0 && !valores.includes(seccion)) {
      setSeccion('');
    }
  }, [opcionesSeccion, seccion]);

  useEffect(() => {
    let cancelado = false;

    async function cargarAnioActivo() {
      setCargandoAnioActivo(true);
      try {
        const calendario = await resolverCalendarioActivoParaFiltros();
        if (cancelado) {
          return;
        }
        if (calendario?.anio) {
          setFiltros((prev) => ({ ...prev, anio_escolar: calendario.anio }));
          setSinAnioActivo(false);
        } else {
          setSinAnioActivo(true);
        }
      } catch {
        if (!cancelado) {
          setSinAnioActivo(true);
        }
      } finally {
        if (!cancelado) {
          setCargandoAnioActivo(false);
        }
      }
    }

    void cargarAnioActivo();

    return () => {
      cancelado = true;
    };
  }, []);

  const cargarDocentes = useCallback(async () => {
    if (!puedeOperar) {
      setDocentes([]);
      setCargandoDocentes(false);
      return;
    }

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
      setError('No se pudieron cargar los docentes activos.');
      setDocentes([]);
    } finally {
      setCargandoDocentes(false);
    }
  }, [busqueda, filtros.anio_escolar, filtros.nivel, filtros.sede, puedeOperar]);

  const cargarResumenDocente = useCallback(async (docenteId) => {
    if (!docenteId || !puedeOperar) {
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
  }, [filtros.anio_escolar, filtros.nivel, filtros.sede, puedeOperar]);

  const cargarPanelDocente = useCallback(async () => {
    if (!docenteSeleccionado || !grado || !puedeOperar) {
      setMallaCursos([]);
      setMallaProvisionada(true);
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
      setMallaProvisionada(Boolean(malla?.id));
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
      setError('No se pudo cargar la malla curricular o las asignaciones del grado.');
      setMallaProvisionada(false);
      setMallaCursos([]);
      setAsignacionesContexto([]);
      setMarcados(new Set());
    } finally {
      setCargandoPanel(false);
    }
  }, [docenteSeleccionado, filtros.anio_escolar, filtros.nivel, filtros.sede, grado, seccion, puedeOperar]);

  const cargarResumenGeneral = useCallback(async () => {
    if (!grado || !seccion || !puedeOperar) {
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
  }, [filtros.anio_escolar, filtros.nivel, filtros.sede, grado, seccion, puedeOperar]);

  useEffect(() => {
    if (cargandoAnioActivo) {
      return;
    }
    const timer = setTimeout(() => {
      void cargarDocentes();
    }, busqueda.trim() ? 300 : 0);
    return () => clearTimeout(timer);
  }, [cargarDocentes, busqueda, cargandoAnioActivo]);

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

  const cursosSeleccionables = useMemo(() => {
    return mallaCursos.filter((curso) => {
      const asignacion = asignacionPorCurso.get(curso.id);
      return !asignacion || asignacion.user_id === docenteSeleccionado?.id;
    });
  }, [mallaCursos, asignacionPorCurso, docenteSeleccionado?.id]);

  const totalSeleccionados = marcados.size;
  const totalSeleccionables = cursosSeleccionables.length;

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

  function marcarTodos() {
    setMarcados(new Set(cursosSeleccionables.map((c) => c.id)));
  }

  function desmarcarTodos() {
    setMarcados(new Set());
  }

  async function ejecutarGuardado() {
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
      setExito('Asignaciones guardadas correctamente para el año escolar activo.');
      await Promise.all([
        cargarPanelDocente(),
        cargarResumenDocente(docenteSeleccionado.id),
        cargarResumenGeneral(),
        cargarDocentes(),
      ]);
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setGuardando(false);
    }
  }

  async function guardar() {
    if (!docenteSeleccionado || !puedeOperar) {
      return;
    }

    if (marcados.size === 0) {
      const confirmar = window.confirm(
        'Esto desactivará las asignaciones activas de este docente en esta aula. ¿Continuar?',
      );
      if (!confirmar) {
        return;
      }
    }

    await ejecutarGuardado();
  }

  function aplicarDemoAsignaciones() {
    if (!puedeOperar) {
      return;
    }
    setFiltros((prev) => ({
      ...prev,
      nivel: DEMO_ASIGNACION.nivel,
      sede: DEMO_ASIGNACION.sede,
    }));
    setGrado(DEMO_ASIGNACION.grado);
    setSeccion(DEMO_ASIGNACION.seccion);
    const docenteDemo = docentes.find((d) => d.email === DEMO_ASIGNACION.docenteEmail);
    if (docenteDemo) {
      setDocenteSeleccionado(docenteDemo);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <Card className="p-6">
        <h2 className="text-lg font-semibold">Asignación docente</h2>
        <p className="mt-1 text-sm text-muted">
          Asigne cursos de la malla curricular por grado y sección. Usa el año escolar activo; no aplica bimestre ni materias legacy.
        </p>
      </Card>

      {sinAnioActivo && !cargandoAnioActivo ? (
        <AlertMessage variant="warning">
          No hay año escolar activo configurado. Active un año en Periodos académicos antes de asignar docentes.
        </AlertMessage>
      ) : null}

      <Card className="p-5 sm:p-6">
        <h3 className="text-sm font-semibold text-[var(--text)]">Filtros generales</h3>
        <div className="mt-4 grid gap-4 sm:grid-cols-3">
          <div className="block text-sm font-medium text-[var(--text)]">
            <span>Año escolar activo</span>
            <p className={`${FIELD} mt-1 flex items-center bg-[var(--surface-muted)] text-[var(--text)]`}>
              {cargandoAnioActivo ? 'Cargando…' : (filtros.anio_escolar || '—')}
            </p>
            <p className="mt-1 text-xs text-muted">Definido en Periodos académicos (año con estado activo).</p>
          </div>
          <label className="block text-sm font-medium text-[var(--text)]">
            Nivel
            <select
              className={FIELD}
              value={filtros.nivel}
              disabled={!puedeOperar}
              onChange={(e) => {
                const nivel = e.target.value;
                const grados = gradosCurricularesPorNivel(nivel);
                setFiltros((prev) => ({ ...prev, nivel }));
                setGrado(grados[0] ?? '');
                setSeccion('');
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
              disabled={!puedeOperar}
              onChange={(e) => setFiltros((prev) => ({ ...prev, sede: e.target.value }))}
            >
              {SEDES.map((s) => (
                <option key={s.value} value={s.value}>{s.label}</option>
              ))}
            </select>
          </label>
        </div>
        {puedeOperar ? (
          <div className="mt-4">
            <Button type="button" variant="outline" size="sm" onClick={aplicarDemoAsignaciones}>
              Ver asignaciones demo
            </Button>
            <p className="mt-2 text-xs text-muted">
              Preset: {filtros.anio_escolar} · Primaria {DEMO_ASIGNACION.grado} {DEMO_ASIGNACION.seccion} · Chilca
            </p>
          </div>
        ) : null}
      </Card>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <div className="grid gap-6 lg:grid-cols-[minmax(280px,360px)_1fr]">
        <Card className="flex flex-col p-5 sm:p-6">
          <h3 className="text-sm font-semibold text-[var(--text)]">Docentes activos</h3>
          <input
            className={`${FIELD} mt-3`}
            placeholder="Buscar docente por nombre o correo"
            value={busqueda}
            disabled={!puedeOperar}
            onChange={(e) => setBusqueda(e.target.value)}
          />
          <div className="mt-4 flex-1 overflow-y-auto">
            {cargandoDocentes ? <LoadingState /> : null}
            {!cargandoDocentes && puedeOperar && docentes.length === 0 ? (
              <EmptyState
                title="Sin docentes activos"
                description="No hay usuarios con rol docente y cuenta activa para los filtros actuales. Revise el módulo Usuarios."
              />
            ) : null}
            <ul className="space-y-2">
              {docentes.map((docente) => {
                const seleccionado = docenteSeleccionado?.id === docente.id;
                return (
                  <li key={docente.id}>
                    <button
                      type="button"
                      disabled={!puedeOperar}
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
                description="Elija un docente activo de la lista para asignar cursos por grado y sección."
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
                  <select
                    className={FIELD}
                    value={grado}
                    disabled={!puedeOperar}
                    onChange={(e) => {
                      setGrado(e.target.value);
                      setSeccion('');
                    }}
                  >
                    {gradosCurricularesPorNivel(filtros.nivel).map((g) => (
                      <option key={g} value={g}>{g}</option>
                    ))}
                  </select>
                </label>
                <label className="block text-sm font-medium text-[var(--text)]">
                  Sección
                  <select
                    className={FIELD}
                    value={seccion}
                    disabled={!puedeOperar}
                    onChange={(e) => setSeccion(e.target.value)}
                  >
                    {opcionesSeccion.map((s) => (
                      <option key={s.value} value={s.value}>{s.label}</option>
                    ))}
                  </select>
                </label>
              </div>

              {cargandoPanel ? <LoadingState className="mt-6" /> : null}

              {!cargandoPanel && !mallaProvisionada ? (
                <AlertMessage variant="warning" className="mt-6">
                  No hay malla curricular para {filtros.nivel} {grado} en el año {filtros.anio_escolar}.
                  Configure la malla curricular antes de asignar docentes.
                </AlertMessage>
              ) : null}

              {!cargandoPanel && mallaProvisionada && cursosPorArea.length === 0 ? (
                <EmptyState
                  className="mt-6"
                  title="Sin cursos activos en la malla"
                  description="La malla existe pero no tiene cursos activos para este grado. Active cursos en Malla curricular."
                />
              ) : null}

              {!cargandoPanel && cursosPorArea.length > 0 ? (
                <div className="mt-6 space-y-5">
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    <p className="text-sm text-muted">
                      {totalSeleccionados} de {totalSeleccionables} curso(s) seleccionado(s)
                    </p>
                    <div className="flex flex-wrap gap-2">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={!puedeOperar || totalSeleccionables === 0}
                        onClick={marcarTodos}
                      >
                        Marcar todos
                      </Button>
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={!puedeOperar || totalSeleccionados === 0}
                        onClick={desmarcarTodos}
                      >
                        Desmarcar todos
                      </Button>
                    </div>
                  </div>
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
                                  disabled={Boolean(otroDocente) || !puedeOperar}
                                  onChange={() => toggleCurso(curso.id)}
                                />
                                <span>{nombreCurso}</span>
                              </label>
                              {otroDocente ? (
                                <span className="text-xs text-amber-700 dark:text-amber-300 sm:pl-6">
                                  Bloqueado: asignado a {asignacion.user?.name ?? 'otro docente'}
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
                <Button
                  type="button"
                  variant="primary"
                  disabled={guardando || cargandoPanel || !puedeOperar}
                  onClick={() => void guardar()}
                >
                  {guardando ? 'Guardando…' : 'Guardar asignación'}
                </Button>
              </div>
            </Card>
          )}

          {docenteSeleccionado ? (
            <Card className="p-5 sm:p-6">
              <h3 className="text-sm font-semibold text-[var(--text)]">Asignaciones actuales — {docenteSeleccionado.name}</h3>
              {resumenDocente.length === 0 ? (
                <p className="mt-3 text-sm text-muted">
                  Este docente no tiene cursos asignados en el año {filtros.anio_escolar} para la sede y nivel seleccionados.
                </p>
              ) : (
                <ul className="mt-3 space-y-4">
                  {resumenDocente.map((grupo) => (
                    <li key={`${grupo.grado}-${grupo.seccion}`} className="rounded-lg border border-[var(--border)]/70 px-4 py-3">
                      <p className="font-medium text-[var(--text)]">
                        {grupo.grado} {grupo.seccion}
                        <span className="ml-2 text-xs font-normal text-muted">
                          · {(grupo.cursos ?? []).length} curso(s)
                        </span>
                      </p>
                      <ul className="mt-2 space-y-1 text-sm text-muted">
                        {(grupo.cursos ?? []).map((c) => (
                          <li key={c.malla_curso_id}>
                            <span className="text-[var(--text)]">{c.curso}</span>
                            <span className="text-muted"> — {c.area}</span>
                          </li>
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
                    <li key={a.id} className="rounded-md border border-[var(--border)]/60 px-3 py-2">
                      <span className="font-medium text-[var(--text)]">{a.malla_curso?.curso_catalogo?.nombre ?? 'Curso'}</span>
                      <span className="text-muted"> · {a.malla_curso?.area?.nombre ?? 'Sin área'}</span>
                      <span className="block text-xs text-muted mt-0.5">Docente: {a.user?.name ?? '—'}</span>
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
