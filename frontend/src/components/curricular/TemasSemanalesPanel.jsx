import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getCapacidadesPorCompetencia,
  getCompetenciasPorArea,
  getCurricularAreas,
  getCurricularPeriodos,
  getCurricularSemanas,
  getMallaCurricularPorGrado,
  getTemasSemanales,
  patchDesactivarTemaSemanal,
  patchTemaSemanal,
  postTemaSemanal,
} from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import { resolverCalendarioActivoParaFiltros } from '../../lib/calendarioAcademico';
import { etiquetaNivelCurricular } from '../../lib/academicoCurricular';
import AlertMessage from '../ui/AlertMessage';
import Card from '../ui/Card';
import LoadingState from '../ui/LoadingState';
import AgregarCriterioPanel from './criterios/AgregarCriterioPanel';
import CompetenciaCriteriosCard from './criterios/CompetenciaCriteriosCard';
import CriteriosEstadoVacio from './criterios/CriteriosEstadoVacio';
import CriteriosFiltros from './criterios/CriteriosFiltros';
import CriteriosResumen from './criterios/CriteriosResumen';
import {
  construirResumenRegistrado,
  FORM_CRITERIO_INICIAL,
  nombreCursoMalla,
  obtenerMensajeError,
} from './criterios/utils';

export default function TemasSemanalesPanel() {
  const [criterios, setCriterios] = useState([]);
  const [areas, setAreas] = useState([]);
  const [mallaCursos, setMallaCursos] = useState([]);
  const [periodos, setPeriodos] = useState([]);
  const [semanasForm, setSemanasForm] = useState([]);
  const [competenciasForm, setCompetenciasForm] = useState([]);
  const [capacidadesForm, setCapacidadesForm] = useState([]);
  const [cargandoCriterios, setCargandoCriterios] = useState(false);
  const [cargandoForm, setCargandoForm] = useState(false);
  const [error, setError] = useState(null);
  const [formAbierto, setFormAbierto] = useState(false);

  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: 'primaria',
    grado: '2do',
    area_id: '',
    malla_curso_id: '',
    periodo_academico_id: '',
  });

  const [form, setForm] = useState({ ...FORM_CRITERIO_INICIAL });

  useEffect(() => {
    void resolverCalendarioActivoParaFiltros().then((cal) => {
      if (!cal?.anio) return;
      setFiltros((prev) => ({
        ...prev,
        anio_escolar: cal.anio,
        periodo_academico_id: cal.periodoVigenteId || prev.periodo_academico_id,
      }));
    });
  }, []);

  const cargarCriterios = useCallback(async () => {
    if (!filtros.malla_curso_id) {
      setCriterios([]);
      return;
    }
    setCargandoCriterios(true);
    try {
      const params = { malla_curso_id: filtros.malla_curso_id };
      if (filtros.periodo_academico_id) {
        params.periodo_academico_id = filtros.periodo_academico_id;
      }
      setCriterios(await getTemasSemanales(params));
    } catch {
      setError('No se pudieron cargar los criterios registrados.');
      setCriterios([]);
    } finally {
      setCargandoCriterios(false);
    }
  }, [filtros.malla_curso_id, filtros.periodo_academico_id]);

  useEffect(() => {
    void cargarCriterios();
  }, [cargarCriterios]);

  useEffect(() => {
    if (!filtros.nivel) return;
    void getCurricularAreas({ nivel: filtros.nivel }).then((data) =>
      setAreas(Array.isArray(data) ? data : []),
    );
  }, [filtros.nivel]);

  useEffect(() => {
    if (!filtros.grado) {
      setMallaCursos([]);
      return;
    }
    void getMallaCurricularPorGrado({
      anio_escolar: filtros.anio_escolar,
      nivel: filtros.nivel,
      grado: filtros.grado,
    }).then((malla) => {
      setMallaCursos((malla.malla_cursos ?? malla.mallaCursos ?? []).filter((c) => c.activo));
    });
  }, [filtros.anio_escolar, filtros.nivel, filtros.grado]);

  useEffect(() => {
    void getCurricularPeriodos({ anio_escolar: filtros.anio_escolar }).then((data) => {
      const lista = Array.isArray(data) ? data : [];
      setPeriodos(lista);
      setFiltros((prev) => ({
        ...prev,
        periodo_academico_id: prev.periodo_academico_id
          || String(lista.find((p) => p.es_vigente)?.id ?? lista[0]?.id ?? ''),
      }));
    });
  }, [filtros.anio_escolar]);

  useEffect(() => {
    if (!filtros.periodo_academico_id) {
      setSemanasForm([]);
      return;
    }
    void getCurricularSemanas(filtros.periodo_academico_id).then(setSemanasForm);
  }, [filtros.periodo_academico_id]);

  useEffect(() => {
    if (!filtros.area_id || !formAbierto) {
      setCompetenciasForm([]);
      setCapacidadesForm([]);
      return;
    }
    setCargandoForm(true);
    void getCompetenciasPorArea(filtros.area_id)
      .then((lista) => setCompetenciasForm(Array.isArray(lista) ? lista : []))
      .finally(() => setCargandoForm(false));
  }, [filtros.area_id, formAbierto]);

  useEffect(() => {
    if (!form.competencia_id) {
      setCapacidadesForm([]);
      return;
    }
    void getCapacidadesPorCompetencia(form.competencia_id).then((lista) =>
      setCapacidadesForm(Array.isArray(lista) ? lista : []),
    );
  }, [form.competencia_id]);

  const cursosFiltrados = useMemo(
    () => mallaCursos.filter((c) => String(c.area_id ?? c.area?.id) === String(filtros.area_id)),
    [mallaCursos, filtros.area_id],
  );

  const cursoSeleccionado = useMemo(
    () => cursosFiltrados.find((c) => String(c.id) === String(filtros.malla_curso_id)),
    [cursosFiltrados, filtros.malla_curso_id],
  );

  const resumenRegistrado = useMemo(() => construirResumenRegistrado(criterios), [criterios]);

  const estadisticas = useMemo(() => {
    const activos = criterios.filter((c) => c.activo).length;
    return {
      totalActivos: activos,
      totalRegistrados: criterios.length,
    };
  }, [criterios]);

  const sinCompetenciasForm = useMemo(
    () => formAbierto && !cargandoForm && competenciasForm.length === 0 && Boolean(filtros.area_id),
    [cargandoForm, competenciasForm.length, filtros.area_id, formAbierto],
  );

  const puedeRegistrar = Boolean(filtros.malla_curso_id && filtros.periodo_academico_id);

  const cambiarFiltros = useCallback((partial) => {
    setFiltros((prev) => ({ ...prev, ...partial }));
  }, []);

  const cambiarArea = useCallback((areaId) => {
    setFiltros((prev) => ({ ...prev, area_id: areaId, malla_curso_id: '' }));
    setForm({ ...FORM_CRITERIO_INICIAL });
  }, []);

  const limpiarFormulario = useCallback(() => {
    setForm({ ...FORM_CRITERIO_INICIAL });
  }, []);

  const toggleFormAbierto = useCallback(() => {
    setFormAbierto((v) => !v);
  }, []);

  const cambiarCompetenciaForm = useCallback((competenciaId) => {
    setForm((prev) => ({ ...prev, competencia_id: competenciaId, capacidad_id: '' }));
  }, []);

  const cambiarCapacidadForm = useCallback((capacidadId) => {
    setForm((prev) => ({ ...prev, capacidad_id: capacidadId }));
  }, []);

  const cambiarCampoForm = useCallback((campo, valor) => {
    setForm((prev) => ({ ...prev, [campo]: valor }));
  }, []);

  const crearCriterio = useCallback(
    async (e) => {
      e.preventDefault();
      if (!filtros.malla_curso_id || !filtros.periodo_academico_id) {
        setError('Seleccione curso y bimestre antes de registrar.');
        return;
      }
      if (!form.competencia_id || !form.capacidad_id) {
        setError('Seleccione competencia y capacidad.');
        return;
      }
      setError(null);
      try {
        const payload = {
          malla_curso_id: Number(filtros.malla_curso_id),
          periodo_academico_id: Number(filtros.periodo_academico_id),
          titulo: form.titulo.trim(),
          descripcion: form.descripcion.trim() || null,
          competencia_ids: [Number(form.competencia_id)],
          capacidad_ids: [Number(form.capacidad_id)],
        };
        if (form.semana_academica_id) {
          payload.semana_academica_id = Number(form.semana_academica_id);
        }
        await postTemaSemanal(payload);
        limpiarFormulario();
        setFormAbierto(false);
        await cargarCriterios();
      } catch (err) {
        setError(obtenerMensajeError(err, 'No se pudo registrar el criterio de evaluación.'));
      }
    },
    [cargarCriterios, filtros.malla_curso_id, filtros.periodo_academico_id, form, limpiarFormulario],
  );

  const guardarEdicion = useCallback(
    async (criterioId, datos) => {
      setError(null);
      try {
        await patchTemaSemanal(criterioId, {
          titulo: datos.titulo,
          descripcion: datos.descripcion,
        });
        await cargarCriterios();
      } catch (err) {
        setError(obtenerMensajeError(err, 'No se pudo editar el criterio.'));
        throw err;
      }
    },
    [cargarCriterios],
  );

  const desactivarCriterio = useCallback(
    async (criterioId) => {
      setError(null);
      try {
        await patchDesactivarTemaSemanal(criterioId);
        await cargarCriterios();
      } catch {
        setError('No se pudo desactivar el criterio.');
      }
    },
    [cargarCriterios],
  );

  const nivelLabel = etiquetaNivelCurricular(filtros.nivel);
  const nombreCurso = nombreCursoMalla(cursoSeleccionado);

  return (
    <div className="flex flex-col gap-6">
      <header className="space-y-1">
        <h2 className="text-2xl font-bold tracking-tight text-[var(--text)]">
          Criterios de evaluación del curso
        </h2>
        <p className="max-w-3xl text-sm leading-relaxed text-muted">
          Registre los criterios por curso, competencia y capacidad. Puede evaluar solo algunos;
          la semana es referencial y opcional.
        </p>
      </header>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}

      <CriteriosFiltros
        filtros={filtros}
        areas={areas}
        cursosFiltrados={cursosFiltrados}
        periodos={periodos}
        onChangeFiltros={cambiarFiltros}
        onChangeArea={cambiarArea}
      />

      {filtros.malla_curso_id ? (
        <CriteriosResumen
          nivelLabel={nivelLabel}
          grado={filtros.grado}
          anioEscolar={filtros.anio_escolar}
          nombreCurso={nombreCurso}
          totalActivos={estadisticas.totalActivos}
          totalRegistrados={estadisticas.totalRegistrados}
        />
      ) : null}

      <AgregarCriterioPanel
        abierto={formAbierto}
        onToggle={toggleFormAbierto}
        puedeRegistrar={puedeRegistrar}
        form={form}
        competencias={competenciasForm}
        capacidades={capacidadesForm}
        semanas={semanasForm}
        cargandoCompetencias={cargandoForm}
        sinCompetencias={sinCompetenciasForm}
        onChangeCompetencia={cambiarCompetenciaForm}
        onChangeCapacidad={cambiarCapacidadForm}
        onChangeCampo={cambiarCampoForm}
        onSubmit={(e) => void crearCriterio(e)}
      />

      <Card className="p-5 sm:p-6">
        <h3 className="text-sm font-semibold text-[var(--text)]">Criterios registrados</h3>
        <p className="mt-1 text-xs text-muted">
          Solo se muestran competencias y capacidades con criterios ya registrados.
        </p>

        {!filtros.malla_curso_id ? (
          <CriteriosEstadoVacio
            title="Selecciona un curso"
            description="Selecciona un curso para ver sus criterios."
          />
        ) : null}

        {filtros.malla_curso_id && cargandoCriterios ? (
          <LoadingState label="Cargando criterios…" />
        ) : null}

        {filtros.malla_curso_id && !cargandoCriterios && resumenRegistrado.length === 0 ? (
          <CriteriosEstadoVacio
            title="Sin criterios registrados"
            description="Aún no hay criterios registrados para este curso y bimestre."
          />
        ) : null}

        {filtros.malla_curso_id && !cargandoCriterios && resumenRegistrado.length > 0 ? (
          <div className="mt-4 space-y-4">
            {resumenRegistrado.map(
              ({
                competencia,
                capacidades,
                totalCapacidades,
                totalActivos,
                totalCriterios,
              }) => (
                <CompetenciaCriteriosCard
                  key={competencia.id}
                  competencia={competencia}
                  capacidades={capacidades}
                  totalCapacidades={totalCapacidades}
                  totalActivos={totalActivos}
                  totalCriterios={totalCriterios}
                  onGuardar={guardarEdicion}
                  onDesactivar={desactivarCriterio}
                />
              ),
            )}
          </div>
        ) : null}
      </Card>
    </div>
  );
}
