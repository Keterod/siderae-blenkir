import { useCallback, useEffect, useMemo, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  descargarPlantillaRegistroAuxiliarExcel,
  getContextosConsultaGlobales,
  getCurricularPeriodos,
  getDocenteAulasCursos,
  getFormularioNotasSemanales,
  importarPlantillaRegistroAuxiliarExcel,
  postNotasSemanalesBulk,
} from '../../lib/api';
import {
  ADVERTENCIA_NO_ELIMINAR_NOTA_EVAL_BIM,
  getFormularioEvaluacionBimestral,
  postEvaluacionBimestralBulk,
} from '../../lib/evaluacionBimestral';
import { anioEscolarActual } from '../../lib/academico';
import { resolverCalendarioActivoParaFiltros } from '../../lib/calendarioAcademico';
import { cargarOpcionesSeccionAula, combinarOpcionesSeccion } from '../../lib/seccionesAula';
import {
  nombreEstudiante,
  obtenerMensajeErrorNotas,
} from '../../lib/notasCurricular';
import AlertMessage from '../ui/AlertMessage';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';
import { construirResumenRegistrado } from './criterios/utils';
import RegistroNotasAulaTable from './notas/RegistroNotasAulaTable';
import RegistroNotasEstudianteView from './notas/RegistroNotasEstudianteView';
import RegistroNotasFiltros from './notas/RegistroNotasFiltros';
import RegistroNotasToolbar from './notas/RegistroNotasToolbar';
import EvaluacionBimestralBlock from './notas/bimestral/EvaluacionBimestralBlock';
import EvaluacionBimestralEstudianteCard from './notas/bimestral/EvaluacionBimestralEstudianteCard';
import ConclusionDescriptivaModal from './notas/bimestral/ConclusionDescriptivaModal';
import {
  construirPayloadEvalBim,
  initFilaEvalBimEstudiante,
  initMatrizEvalBim,
  mergeResultadosEnFormulario,
  validarMatrizEvalBim,
} from './notas/bimestral/evaluacionBimestralUtils';
import {
  ADVERTENCIA_ELIMINAR_NOTA,
  construirOpcionesAreasFiltro,
  construirPayloadAula,
  construirPayloadEstudiante,
  esCampoComponente,
  esModoCalificacionDinamica,
  initFilasEstudiante,
  initMatrizAula,
  obtenerComponentesCalificacion,
  parseComponenteIdDesdeCampo,
  validarFilasEnRango,
  validarMatrizEnRango,
} from './notas/notasUtils';
import { MENSAJE_CALIFICACION_DINAMICA, MENSAJE_EXCEL_DINAMICO, MENSAJE_PLANTILLA_EXCEL } from '../../lib/notasCurricular';
import { SEDE_OPERATIVA, conSedeOperativa } from '../../lib/sedeOperativa';

function nombreCursoAsignacion(a) {
  return a.malla_curso?.curso_catalogo?.nombre ?? a.mallaCurso?.cursoCatalogo?.nombre ?? 'Curso';
}

function areaIdAsignacion(a) {
  return a.malla_curso?.area_id ?? a.mallaCurso?.area_id ?? a.malla_curso?.area?.id ?? a.mallaCurso?.area?.id;
}

/** Coordinador / directivo: consulta institucional solo lectura. */
function puedeConsultaInstitucional(permissions, roles) {
  return (
    permissions.includes('gestionar_asignaciones_docente')
    || (roles ?? []).includes('directivo')
  );
}

function esRolAdministrador(roles) {
  return (roles ?? []).includes('administrador');
}

export default function RegistroNotasSemanalesPanel() {
  const { permissions, roles } = useAuth();
  const [contextosConsulta, setContextosConsulta] = useState([]);
  const [aulasDocente, setAulasDocente] = useState([]);
  const [periodos, setPeriodos] = useState([]);
  const [formulario, setFormulario] = useState(null);
  const [filas, setFilas] = useState({});
  const [matriz, setMatriz] = useState({});
  const [vista, setVista] = useState('aula');

  /** Prioriza consulta global salvo que un admin/competencias con registre elijan modo docente. */
  const [forzarModoDocente, setForzarModoDocente] = useState(false);

  const puedeAlternarDocente = permissions.includes('registrar_notas_semanales')
    && permissions.includes('gestionar_asignaciones_docente');

  const esAdministrador = useMemo(() => esRolAdministrador(roles), [roles]);

  const modoConsultaGlobal = useMemo(
    () => permissions.includes('ver_notas_academicas')
      && puedeConsultaInstitucional(permissions, roles)
      && !forzarModoDocente,
    [permissions, roles, forzarModoDocente],
  );

  /** Coordinador y directivo siguen en solo lectura; el administrador puede registrar en consulta global. */
  const modoConsultaGlobalSoloLectura = modoConsultaGlobal && !esAdministrador;

  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: '',
    sede: SEDE_OPERATIVA,
    grado: '',
    seccion: '',
    area_id: '',
    asignacion_id: '',
    /** Clave única API (aula+malla_curso) en modo consulta. */
    consulta_contexto_clave: '',
    periodo_academico_id: '',
    estudiante_id: '',
  });
  const [seccionesCatalogo, setSeccionesCatalogo] = useState([]);

  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [advertencia, setAdvertencia] = useState(null);
  const [cargandoInicial, setCargandoInicial] = useState(true);
  const [cargandoFormulario, setCargandoFormulario] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [evalBimFormulario, setEvalBimFormulario] = useState(null);
  const [evalBimMatriz, setEvalBimMatriz] = useState({});
  const [cargandoEvalBim, setCargandoEvalBim] = useState(false);
  const [guardandoEvalBim, setGuardandoEvalBim] = useState(false);
  const [descargandoPlantilla, setDescargandoPlantilla] = useState(false);
  const [importandoPlantilla, setImportandoPlantilla] = useState(false);
  const [modoPlantilla, setModoPlantilla] = useState('vacia');
  const [modalConclusion, setModalConclusion] = useState({
    abierto: false,
    estudiante: null,
    valorInicial: '',
  });
  const [sinCalendarioActivo, setSinCalendarioActivo] = useState(false);

  useEffect(() => {
    void resolverCalendarioActivoParaFiltros().then((cal) => {
      if (!cal?.anio) {
        setSinCalendarioActivo(true);
        return;
      }
      setSinCalendarioActivo(false);
      setFiltros((prev) => ({
        ...prev,
        anio_escolar: cal.anio,
        periodo_academico_id: cal.periodoVigenteId || prev.periodo_academico_id,
      }));
    });
  }, []);

  const aulas = modoConsultaGlobal ? [] : aulasDocente;

  /** Carga listado inicial según modo. */
  useEffect(() => {
    let activo = true;
    void (async () => {
      setCargandoInicial(true);
      setError(null);
      try {
        if (modoConsultaGlobal) {
          const data = await getContextosConsultaGlobales();
          if (!activo) return;
          const lista = (Array.isArray(data) ? data : []).filter((c) => c.sede === SEDE_OPERATIVA);
          setContextosConsulta(lista);
          if (lista.length > 0) {
            const c0 = lista[0];
            setFiltros((prev) => conSedeOperativa({
              ...prev,
              anio_escolar: c0.anio_escolar ?? prev.anio_escolar,
              nivel: c0.nivel ?? '',
              grado: c0.grado ?? '',
              seccion: c0.seccion ?? '',
              area_id: c0.area_id != null ? String(c0.area_id) : '',
              consulta_contexto_clave: c0.clave ?? '',
              asignacion_id: '',
            }));
          }
        } else {
          const data = await getDocenteAulasCursos({ anio_escolar: anioEscolarActual() });
          if (!activo) return;
          const lista = (Array.isArray(data) ? data : []).filter((a) => a.sede === SEDE_OPERATIVA);
          setAulasDocente(lista);
          if (lista.length > 0) {
            const primera = lista[0];
            setFiltros((prev) => conSedeOperativa({
              ...prev,
              anio_escolar: primera.anio_escolar ?? prev.anio_escolar,
              nivel: primera.nivel ?? '',
              grado: primera.grado ?? '',
              seccion: primera.seccion ?? '',
              area_id: String(areaIdAsignacion(primera) ?? ''),
              asignacion_id: String(primera.id),
              consulta_contexto_clave: '',
            }));
          }
        }
      } catch {
        if (activo) {
          setError(
            modoConsultaGlobal
              ? 'No se pudieron cargar los contextos de aula para consulta.'
              : 'No tiene asignaciones activas para registrar notas.',
          );
        }
      } finally {
        if (activo) setCargandoInicial(false);
      }
    })();
    return () => {
      activo = false;
    };
  }, [modoConsultaGlobal]);

  useEffect(() => {
    if (!filtros.anio_escolar) return;
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

  const contextosFiltrados = useMemo(() => {
    if (!modoConsultaGlobal) return [];
    return contextosConsulta.filter((c) => {
      if (filtros.anio_escolar && c.anio_escolar !== filtros.anio_escolar) return false;
      if (filtros.nivel && c.nivel !== filtros.nivel) return false;
      if (c.sede !== SEDE_OPERATIVA) return false;
      if (filtros.grado && c.grado !== filtros.grado) return false;
      if (filtros.seccion && c.seccion !== filtros.seccion) return false;
      if (filtros.area_id && String(c.area_id) !== String(filtros.area_id)) return false;
      return true;
    });
  }, [modoConsultaGlobal, contextosConsulta, filtros.anio_escolar, filtros.nivel, filtros.sede, filtros.grado, filtros.seccion, filtros.area_id]);

  const aulasFiltradas = useMemo(() => {
    if (modoConsultaGlobal) return [];
    return aulas.filter((a) => {
      if (filtros.anio_escolar && a.anio_escolar !== filtros.anio_escolar) return false;
      if (filtros.nivel && a.nivel !== filtros.nivel) return false;
      if (a.sede !== SEDE_OPERATIVA) return false;
      if (filtros.grado && a.grado !== filtros.grado) return false;
      if (filtros.seccion && a.seccion !== filtros.seccion) return false;
      if (filtros.area_id && String(areaIdAsignacion(a)) !== String(filtros.area_id)) return false;
      return true;
    });
  }, [modoConsultaGlobal, aulas, filtros]);

  useEffect(() => {
    let cancelado = false;

    if (!filtros.nivel || !filtros.grado) {
      setSeccionesCatalogo([]);
      return undefined;
    }

    void cargarOpcionesSeccionAula({
      nivel: filtros.nivel,
      grado: filtros.grado,
      gradoFormato: 'curricular',
    }).then((opcionesCatalogo) => {
      if (!cancelado) {
        setSeccionesCatalogo(opcionesCatalogo.map((o) => o.value));
      }
    });

    return () => {
      cancelado = true;
    };
  }, [filtros.nivel, filtros.grado]);

  const opciones = useMemo(() => {
    const mergeSecciones = (desdeContexto) => combinarOpcionesSeccion(
      seccionesCatalogo,
      desdeContexto,
      filtros.seccion,
    ).map((o) => o.value);

    if (modoConsultaGlobal) {
      const base = filtros.anio_escolar
        ? contextosConsulta.filter((c) => c.anio_escolar === filtros.anio_escolar)
        : contextosConsulta;

      const uniq = (arr, key) => [...new Set(arr.map(key).filter(Boolean))];
      const anios = uniq(contextosConsulta, (c) => c.anio_escolar).sort();

      const niveles = uniq(base, (c) => c.nivel);
      const grados = uniq(base, (c) => c.grado);
      const secciones = uniq(
        base.filter((c) => (!filtros.nivel || c.nivel === filtros.nivel)
          && (!filtros.grado || c.grado === filtros.grado)),
        (c) => c.seccion,
      );

      const areas = construirOpcionesAreasFiltro(
        base.map((c) => ({
          nivel: c.nivel,
          area_id: c.area_id,
          area_nombre: c.area_nombre ?? '',
        })),
        filtros.nivel,
      );

      const cursosOpciones = contextosFiltrados.map((c) => ({
        clave: c.clave,
        etiqueta: c.titulo_opcion ?? `${c.curso_nombre}`,
      }));

      return {
        modo: 'consulta',
        anios,
        niveles,
        sedes: [SEDE_OPERATIVA],
        grados,
        secciones: mergeSecciones(secciones),
        areas,
        cursosOpciones,
      };
    }

    const base = filtros.anio_escolar
      ? aulas.filter((a) => a.anio_escolar === filtros.anio_escolar)
      : aulas;

    const uniq = (arr, key) => [...new Set(arr.map(key).filter(Boolean))];

    const niveles = uniq(base, (a) => a.nivel);
    const grados = uniq(base, (a) => a.grado);
    const secciones = uniq(
      base.filter((a) => (!filtros.nivel || a.nivel === filtros.nivel)
        && (!filtros.grado || a.grado === filtros.grado)),
      (a) => a.seccion,
    );

    const areas = construirOpcionesAreasFiltro(
      base.map((a) => ({
        nivel: a.nivel,
        area_id: areaIdAsignacion(a),
        area_nombre: a.mallaCurso?.area?.nombre ?? a.malla_curso?.area?.nombre ?? '',
      })),
      filtros.nivel,
    );

    return {
      modo: 'docente',
      anios: [...new Set(aulas.map((a) => a.anio_escolar))].filter(Boolean).sort(),
      niveles,
      sedes: [SEDE_OPERATIVA],
      grados,
      secciones: mergeSecciones(secciones),
      areas,
      cursosOpciones: [],
    };
  }, [modoConsultaGlobal, contextosConsulta, contextosFiltrados, aulas, filtros.anio_escolar, filtros.nivel, filtros.grado, filtros.seccion, seccionesCatalogo]);

  useEffect(() => {
    if (!filtros.seccion || !filtros.nivel || !filtros.grado) {
      return;
    }
    const valores = opciones.secciones ?? [];
    if (valores.length > 0 && !valores.includes(filtros.seccion)) {
      setFiltros((prev) => ({
        ...prev,
        seccion: '',
        ...(modoConsultaGlobal
          ? { consulta_contexto_clave: '' }
          : { asignacion_id: '' }),
      }));
    }
  }, [opciones.secciones, filtros.seccion, filtros.nivel, filtros.grado, modoConsultaGlobal]);

  useEffect(() => {
    if (!modoConsultaGlobal || !filtros.consulta_contexto_clave) return;
    const tiene = opciones.cursosOpciones?.some((o) => o.clave === filtros.consulta_contexto_clave);
    if (!tiene) {
      setFiltros((prev) => ({ ...prev, consulta_contexto_clave: '' }));
    }
  }, [modoConsultaGlobal, filtros.consulta_contexto_clave, opciones.cursosOpciones]);

  const construirQueryEvalBim = useCallback((params, consultaInstitucional) => {
    if (consultaInstitucional) {
      const cx = params._contextoRow;
      if (!cx?.malla_curso_id || !params.periodo_academico_id) return null;
      return {
        consulta_global: '1',
        anio_escolar: cx.anio_escolar,
        nivel: cx.nivel,
        sede: cx.sede,
        grado: cx.grado,
        seccion: cx.seccion,
        malla_curso_id: cx.malla_curso_id,
        periodo_academico_id: params.periodo_academico_id,
      };
    }
    if (!params.asignacion_id || !params.periodo_academico_id) return null;
    return {
      asignacion_docente_id: params.asignacion_id,
      periodo_academico_id: params.periodo_academico_id,
    };
  }, []);

  const cargarEvalBimestral = useCallback(async (params, consultaInstitucional, estudiantesData) => {
    const query = construirQueryEvalBim(params, consultaInstitucional);
    if (!query) {
      setEvalBimFormulario(null);
      setEvalBimMatriz({});
      return;
    }

    setCargandoEvalBim(true);
    try {
      const data = await getFormularioEvaluacionBimestral(query);
      const asignacionId = data?.contexto?.asignacion_docente_id;
      if (consultaInstitucional && asignacionId) {
        setFiltros((prev) => ({ ...prev, asignacion_id: String(asignacionId) }));
      }
      setEvalBimFormulario(data);
      const lista = estudiantesData?.length ? estudiantesData : (data.estudiantes ?? []);
      setEvalBimMatriz(initMatrizEvalBim(lista, data));
    } catch (err) {
      setEvalBimFormulario(null);
      setEvalBimMatriz({});
      if (!consultaInstitucional) {
        setError(obtenerMensajeErrorNotas(err));
      }
    } finally {
      setCargandoEvalBim(false);
    }
  }, [construirQueryEvalBim]);

  const cargarFormulario = useCallback(async (params, modoVista, consultaInstitucional) => {
    setCargandoFormulario(true);
    setError(null);
    try {
      let query;
      if (consultaInstitucional) {
        const cx = params._contextoRow;
        if (!cx?.malla_curso_id || !params.periodo_academico_id) {
          setFormulario(null);
          setFilas({});
          setMatriz({});
          return;
        }
        query = {
          consulta_global: '1',
          anio_escolar: cx.anio_escolar,
          nivel: cx.nivel,
          sede: cx.sede,
          grado: cx.grado,
          seccion: cx.seccion,
          malla_curso_id: cx.malla_curso_id,
          periodo_academico_id: params.periodo_academico_id,
        };
        if (cx.area_id != null && cx.area_id !== '') {
          query.area_id = cx.area_id;
        }
        if (modoVista === 'estudiante' && params.estudiante_id) {
          query.estudiante_id = params.estudiante_id;
        }
      } else {
        const { asignacion_id: asignacionId, periodo_academico_id: periodoId, estudiante_id: estudianteId } = params;
        if (!asignacionId || !periodoId) {
          setFormulario(null);
          setFilas({});
          setMatriz({});
          return;
        }
        query = {
          asignacion_docente_id: asignacionId,
          periodo_academico_id: periodoId,
        };
        if (modoVista === 'estudiante' && estudianteId) {
          query.estudiante_id = estudianteId;
        }
      }

      const data = await getFormularioNotasSemanales(query);
      setFormulario(data);

      const asignacionId = data?.asignacion?.id ?? data?.asignacion_docente_id;
      if (consultaInstitucional && asignacionId) {
        setFiltros((prev) => ({ ...prev, asignacion_id: String(asignacionId) }));
      }

      const criteriosData = data.criterios ?? [];
      const estudiantesData = data.estudiantes ?? [];

      if (modoVista === 'estudiante') {
        let estudianteActivo = params.estudiante_id;
        if (!estudianteActivo && estudiantesData.length > 0) {
          estudianteActivo = String(estudiantesData[0].id);
          setFiltros((prev) => ({ ...prev, estudiante_id: estudianteActivo }));
        }
        setFilas(initFilasEstudiante(criteriosData, data.notas_por_criterio ?? {}, data));
        setMatriz(initMatrizAula(estudiantesData, criteriosData, data.notas_por_estudiante_criterio ?? {}, data));
      } else {
        setMatriz(initMatrizAula(estudiantesData, criteriosData, data.notas_por_estudiante_criterio ?? {}, data));
        setFilas({});
      }
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err));
      setFormulario(null);
      setFilas({});
      setMatriz({});
      setEvalBimFormulario(null);
      setEvalBimMatriz({});
    } finally {
      setCargandoFormulario(false);
    }
  }, []);

  useEffect(() => {
    if (modoConsultaGlobal) {
      const row = filtros.consulta_contexto_clave
        ? (contextosConsulta.find((c) => c.clave === filtros.consulta_contexto_clave) ?? null)
        : null;

      const params = {
        ...filtros,
        _contextoRow: row,
        periodo_academico_id: filtros.periodo_academico_id,
      };

      if (!row || !filtros.periodo_academico_id) {
        setFormulario(null);
        setFilas({});
        setMatriz({});
        setEvalBimFormulario(null);
        setEvalBimMatriz({});
        return;
      }

      void cargarFormulario(params, vista, true);
      void cargarEvalBimestral(params, true, null);

      return;
    }

    if (!filtros.asignacion_id || !filtros.periodo_academico_id) {
      setFormulario(null);
      setFilas({});
      setMatriz({});
      setEvalBimFormulario(null);
      setEvalBimMatriz({});
      return;
    }
    void cargarFormulario(filtros, vista, false);
    void cargarEvalBimestral(filtros, false, null);
  }, [
    modoConsultaGlobal,
    filtros.consulta_contexto_clave,
    filtros.asignacion_id,
    filtros.periodo_academico_id,
    filtros.estudiante_id,
    vista,
    cargarFormulario,
    cargarEvalBimestral,
    contextosConsulta,
  ]);

  const estructura = useMemo(() => {
    if (!formulario?.criterios?.length) return [];
    return construirResumenRegistrado(formulario.criterios);
  }, [formulario]);

  const asignacionActual = useMemo(
    () => aulas.find((a) => String(a.id) === String(filtros.asignacion_id)),
    [aulas, filtros.asignacion_id],
  );

  const estudiantes = formulario?.estudiantes ?? evalBimFormulario?.estudiantes ?? [];
  const criterios = formulario?.criterios ?? [];
  const modoCalificacionDinamica = esModoCalificacionDinamica(formulario);
  const componentesCalificacion = obtenerComponentesCalificacion(formulario);
  const soloLectura = Boolean(formulario?.readonly ?? evalBimFormulario?.readonly);
  const soloLecturaEvalBim = Boolean(evalBimFormulario?.readonly ?? soloLectura);

  const resumenToolbar = useMemo(() => {
    let curso = formulario?.curso?.nombre
      ?? evalBimFormulario?.contexto?.curso?.nombre;
    if (!curso) {
      curso = nombreCursoAsignacion(asignacionActual ?? {});
    }
    if (vista === 'estudiante' && filtros.estudiante_id) {
      const est = estudiantes.find((e) => String(e.id) === String(filtros.estudiante_id));
      return `${curso} · ${nombreEstudiante(est ?? {})}`;
    }
    return `${curso} · ${estudiantes.length} estudiante${estudiantes.length === 1 ? '' : 's'}`;
  }, [formulario, evalBimFormulario, asignacionActual, vista, filtros.estudiante_id, estudiantes]);

  const puedeGuardarEvalBim = !soloLecturaEvalBim
    && !modoConsultaGlobalSoloLectura
    && Boolean(
      (modoConsultaGlobal ? filtros.consulta_contexto_clave : filtros.asignacion_id)
        && filtros.asignacion_id
        && filtros.periodo_academico_id
        && estudiantes.length,
    );

  const puedeGuardar = !soloLectura
    && (modoCalificacionDinamica ? componentesCalificacion.length > 0 : true)
    && (vista === 'aula'
    ? Boolean(
      (modoConsultaGlobal ? filtros.consulta_contexto_clave : filtros.asignacion_id)
          && filtros.periodo_academico_id && estudiantes.length,
    )
    : Boolean(
      (modoConsultaGlobal ? filtros.consulta_contexto_clave : filtros.asignacion_id)
          && filtros.periodo_academico_id && filtros.estudiante_id,
    ));

  const puedeDescargarPlantilla = Boolean(
    (modoConsultaGlobal ? filtros.consulta_contexto_clave : filtros.asignacion_id)
      && filtros.periodo_academico_id,
  );

  const puedeImportarPlantilla = puedeDescargarPlantilla
    && !modoConsultaGlobalSoloLectura
    && !soloLectura
    && Boolean(filtros.asignacion_id);

  const descargarPlantillaExcel = useCallback(async () => {
    if (!puedeDescargarPlantilla) {
      setError('Seleccione curso y bimestre antes de descargar la plantilla.');
      return;
    }

    let query;
    if (modoConsultaGlobal) {
      const cx = contextosConsulta.find((c) => c.clave === filtros.consulta_contexto_clave);
      if (!cx?.malla_curso_id) {
        setError('Seleccione curso y bimestre antes de descargar la plantilla.');
        return;
      }
      query = {
        consulta_global: '1',
        anio_escolar: cx.anio_escolar,
        nivel: cx.nivel,
        sede: cx.sede,
        grado: cx.grado,
        seccion: cx.seccion,
        malla_curso_id: cx.malla_curso_id,
        periodo_academico_id: filtros.periodo_academico_id,
        incluir_notas: modoPlantilla === 'con_notas' ? '1' : '0',
      };
      if (cx.area_id != null && cx.area_id !== '') {
        query.area_id = cx.area_id;
      }
    } else {
      query = {
        asignacion_docente_id: filtros.asignacion_id,
        periodo_academico_id: filtros.periodo_academico_id,
        incluir_notas: modoPlantilla === 'con_notas' ? '1' : '0',
      };
    }

    setDescargandoPlantilla(true);
    setError(null);
    try {
      const { blob, filename } = await descargarPlantillaRegistroAuxiliarExcel(query);
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
      setExito('Plantilla Excel descargada.');
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err, 'No se pudo generar la plantilla Excel.'));
    } finally {
      setDescargandoPlantilla(false);
    }
  }, [
    puedeDescargarPlantilla,
    modoConsultaGlobal,
    contextosConsulta,
    filtros.consulta_contexto_clave,
    filtros.asignacion_id,
    filtros.periodo_academico_id,
    modoPlantilla,
  ]);

  const importarPlantillaExcel = useCallback(async (archivo) => {
    if (!puedeImportarPlantilla) {
      setError('Seleccione curso y bimestre antes de importar la plantilla.');
      return;
    }

    if (!filtros.asignacion_id || !filtros.periodo_academico_id) {
      setError('Seleccione curso y bimestre antes de importar la plantilla.');
      return;
    }

    setImportandoPlantilla(true);
    setError(null);
    setExito(null);
    setAdvertencia(null);

    try {
      const formData = new FormData();
      formData.append('asignacion_docente_id', String(filtros.asignacion_id));
      formData.append('periodo_academico_id', String(filtros.periodo_academico_id));
      formData.append('archivo', archivo);

      const resp = await importarPlantillaRegistroAuxiliarExcel(formData);

      if (resp?.advertencias?.length) {
        setAdvertencia(resp.advertencias.join(' '));
      }

      const importadosCriterios = resp?.importados_criterios ?? resp?.importados ?? 0;
      const importadosBimestral = resp?.importados_bimestral ?? 0;

      if (importadosBimestral > 0) {
        setExito(
          `Importación completada: ${importadosCriterios} registros de criterios y ${importadosBimestral} registros bimestrales actualizados.`,
        );
      } else {
        setExito(`Se importaron ${importadosCriterios} nota(s) desde Excel.`);
      }

      await cargarFormulario(filtros, vista, modoConsultaGlobal);
      await cargarEvalBimestral(filtros, modoConsultaGlobal, null);
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err, 'No se pudo importar la plantilla Excel.'));
    } finally {
      setImportandoPlantilla(false);
    }
  }, [
    puedeImportarPlantilla,
    filtros,
    vista,
    cargarFormulario,
    cargarEvalBimestral,
  ]);

  function cambiarFiltro(partial) {
    setExito(null);
    setAdvertencia(null);
    const nextConsultaReset = modoConsultaGlobal && (
      'anio_escolar' in partial
      || 'nivel' in partial
      || 'sede' in partial
      || 'grado' in partial
      || 'seccion' in partial
      || 'area_id' in partial
    );
    setFiltros((prev) => conSedeOperativa({
      ...prev,
      ...partial,
      ...(nextConsultaReset ? { consulta_contexto_clave: '' } : {}),
    }));
  }

  function cambiarContextoConsultaPorClave(clave) {
    setExito(null);
    setAdvertencia(null);
    const row = contextosConsulta.find((c) => c.clave === clave)
      ?? contextosFiltrados.find((c) => c.clave === clave);

    setFiltros((prev) => conSedeOperativa({
      ...prev,
      consulta_contexto_clave: clave,
      ...(row
        ? {
            anio_escolar: row.anio_escolar,
            nivel: row.nivel,
            grado: row.grado,
            seccion: row.seccion,
            area_id: row.area_id != null ? String(row.area_id) : '',
            asignacion_id: row.asignacion_docente_id != null ? String(row.asignacion_docente_id) : '',
          }
        : { asignacion_id: '' }),
      estudiante_id: '',
    }));
  }

  function cambiarVista(nuevaVista) {
    setExito(null);
    setAdvertencia(null);
    setVista(nuevaVista);
    if (nuevaVista === 'aula') {
      setFiltros((prev) => ({ ...prev, estudiante_id: '' }));
    }
  }

  const cambiarNotaEstudiante = useCallback((criterioId, campo, valor) => {
    if (soloLectura) return;
    setFilas((prev) => {
      const filaActual = prev[criterioId] ?? {};
      if (esCampoComponente(campo)) {
        const componenteId = parseComponenteIdDesdeCampo(campo);
        return {
          ...prev,
          [criterioId]: {
            ...filaActual,
            componentes: {
              ...(filaActual.componentes ?? {}),
              [componenteId]: valor,
            },
          },
        };
      }
      return {
        ...prev,
        [criterioId]: { ...filaActual, [campo]: valor },
      };
    });
  }, [soloLectura]);

  const cambiarNotaAula = useCallback((estudianteId, criterioId, campo, valor) => {
    if (soloLectura) return;
    setMatriz((prev) => {
      const filaActual = prev[estudianteId]?.[criterioId] ?? {};
      if (esCampoComponente(campo)) {
        const componenteId = parseComponenteIdDesdeCampo(campo);
        return {
          ...prev,
          [estudianteId]: {
            ...prev[estudianteId],
            [criterioId]: {
              ...filaActual,
              componentes: {
                ...(filaActual.componentes ?? {}),
                [componenteId]: valor,
              },
            },
          },
        };
      }
      return {
        ...prev,
        [estudianteId]: {
          ...prev[estudianteId],
          [criterioId]: {
            ...filaActual,
            [campo]: valor,
          },
        },
      };
    });
  }, [soloLectura]);

  const cambiarNotaEvalBim = useCallback((estudianteId, tipo, valor, idExtra) => {
    if (soloLecturaEvalBim) return;
    setEvalBimMatriz((prev) => {
      const fila = { ...(prev[estudianteId] ?? {}) };
      if (tipo === 'oral') {
        fila.oral = valor;
      } else if (tipo === 'examen_bimestral') {
        fila.examen_bimestral = valor;
      } else if (tipo === 'eta') {
        fila.etas = { ...(fila.etas ?? {}), [idExtra]: valor };
      } else if (tipo === 'personalizado') {
        fila.personalizados = { ...(fila.personalizados ?? {}), [idExtra]: valor };
      } else if (tipo === 'conclusion') {
        fila.conclusion = valor;
      }
      return { ...prev, [estudianteId]: fila };
    });
  }, [soloLecturaEvalBim]);

  const abrirModalConclusion = useCallback((estudiante) => {
    const fila = evalBimMatriz[estudiante.id] ?? {};
    setModalConclusion({
      abierto: true,
      estudiante,
      valorInicial: fila.conclusion ?? '',
    });
  }, [evalBimMatriz]);

  const cerrarModalConclusion = useCallback(() => {
    setModalConclusion({ abierto: false, estudiante: null, valorInicial: '' });
  }, []);

  const aplicarConclusionModal = useCallback((texto) => {
    if (!modalConclusion.estudiante) return;
    cambiarNotaEvalBim(modalConclusion.estudiante.id, 'conclusion', texto);
  }, [modalConclusion.estudiante, cambiarNotaEvalBim]);

  async function guardarEvalBimestral() {
    if (soloLecturaEvalBim || modoConsultaGlobalSoloLectura) return;

    setError(null);
    setExito(null);
    setAdvertencia(null);

    if (!filtros.asignacion_id || !filtros.periodo_academico_id) {
      setError('Seleccione curso y bimestre.');
      return;
    }

    const listaEst = evalBimFormulario?.estudiantes ?? estudiantes;
    const errRango = validarMatrizEvalBim(evalBimMatriz, listaEst, nombreEstudiante);
    if (errRango) {
      setError(errRango);
      return;
    }

    const { registrosPorEstudiante, intentoBorrar } = construirPayloadEvalBim(
      evalBimMatriz,
      listaEst,
      evalBimFormulario?.componentes ?? [],
      evalBimFormulario?.etas ?? [],
    );

    if (registrosPorEstudiante.length === 0) {
      setError('Registre al menos una nota bimestral, ETA o conclusión.');
      return;
    }
    if (intentoBorrar) {
      setAdvertencia(ADVERTENCIA_NO_ELIMINAR_NOTA_EVAL_BIM);
    }

    setGuardandoEvalBim(true);
    try {
      const resp = await postEvaluacionBimestralBulk({
        asignacion_docente_id: Number(filtros.asignacion_id),
        periodo_academico_id: Number(filtros.periodo_academico_id),
        registros_por_estudiante: registrosPorEstudiante,
      });

      if (resp?.advertencias?.length) {
        setAdvertencia(resp.advertencias.join(' '));
      }

      setExito('Evaluación bimestral guardada correctamente.');

      const rowConsulta = modoConsultaGlobal && filtros.consulta_contexto_clave
        ? (contextosConsulta.find((c) => c.clave === filtros.consulta_contexto_clave) ?? null)
        : null;
      const params = modoConsultaGlobal ? { ...filtros, _contextoRow: rowConsulta } : filtros;

      const data = await getFormularioEvaluacionBimestral(construirQueryEvalBim(params, modoConsultaGlobal));
      const merged = mergeResultadosEnFormulario(data, resp?.resultados);
      setEvalBimFormulario(merged);
      setEvalBimMatriz(initMatrizEvalBim(listaEst, merged));
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err));
    } finally {
      setGuardandoEvalBim(false);
    }
  }

  async function guardar(e) {
    e.preventDefault();
    if (soloLectura || modoConsultaGlobalSoloLectura) return;

    setError(null);
    setExito(null);
    setAdvertencia(null);

    if (!filtros.asignacion_id || !filtros.periodo_academico_id) {
      setError('Seleccione curso y bimestre.');
      return;
    }

    setGuardando(true);
    try {
      const mensajeSinNotas = modoCalificacionDinamica
        ? 'Debe registrar al menos una nota en algún componente de calificación.'
        : 'Debe registrar al menos una nota (C, L o T) en algún criterio.';

      if (vista === 'aula') {
        const errRango = validarMatrizEnRango(matriz, estudiantes, criterios, nombreEstudiante, formulario);
        if (errRango) {
          setError(errRango);
          return;
        }

        const { registrosPorEstudiante, intentoBorrar } = construirPayloadAula(matriz, estudiantes, criterios, formulario);
        if (registrosPorEstudiante.length === 0) {
          setError(mensajeSinNotas);
          return;
        }
        if (intentoBorrar) setAdvertencia(ADVERTENCIA_ELIMINAR_NOTA);

        const resp = await postNotasSemanalesBulk({
          asignacion_docente_id: Number(filtros.asignacion_id),
          registros_por_estudiante: registrosPorEstudiante,
        });
        if (resp?.advertencias?.length) {
          setAdvertencia(resp.advertencias.join(' '));
        }
      } else {
        if (!filtros.estudiante_id) {
          setError('Seleccione un estudiante.');
          return;
        }

        const errRango = validarFilasEnRango(filas, criterios, (c) => c.titulo, formulario);
        if (errRango) {
          setError(errRango);
          return;
        }

        const { registros, intentoBorrar } = construirPayloadEstudiante(filas, criterios, formulario);
        if (registros.length === 0) {
          setError(mensajeSinNotas);
          return;
        }
        if (intentoBorrar) setAdvertencia(ADVERTENCIA_ELIMINAR_NOTA);

        const resp = await postNotasSemanalesBulk({
          asignacion_docente_id: Number(filtros.asignacion_id),
          estudiante_id: Number(filtros.estudiante_id),
          registros,
        });
        if (resp?.advertencias?.length) {
          setAdvertencia(resp.advertencias.join(' '));
        }
      }

      setExito('Notas guardadas correctamente.');
      const rowConsulta = modoConsultaGlobal && filtros.consulta_contexto_clave
        ? (contextosConsulta.find((c) => c.clave === filtros.consulta_contexto_clave) ?? null)
        : null;
      const paramsRecarga = modoConsultaGlobal ? { ...filtros, _contextoRow: rowConsulta } : filtros;
      await cargarFormulario(paramsRecarga, vista, modoConsultaGlobal);
      if (!modoConsultaGlobalSoloLectura) {
        await cargarEvalBimestral(paramsRecarga, modoConsultaGlobal, null);
      }
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err));
    } finally {
      setGuardando(false);
    }
  }

  if (cargandoInicial) return <LoadingState label="Cargando…" />;

  const tieneAlertas = Boolean(error || exito || advertencia);

  const vacioConsultaSinContextos = modoConsultaGlobal && contextosConsulta.length === 0;
  const vacioDocenteSinAulas = !modoConsultaGlobal && aulasDocente.length === 0;

  return (
    <div className="flex flex-col gap-0">
      <h2 className="sr-only">Registro de notas</h2>

      {puedeAlternarDocente ? (
        <div className="-mx-4 mb-1 flex justify-end px-3 sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6">
          <label className="flex cursor-pointer items-center gap-1.5 text-[11px] text-muted">
            <input
              type="checkbox"
              className="h-3 w-3 rounded border-[var(--border)]"
              checked={forzarModoDocente}
              onChange={(ev) => {
                setForzarModoDocente(ev.target.checked);
                setFormulario(null);
                setFilas({});
                setMatriz({});
                setEvalBimFormulario(null);
                setEvalBimMatriz({});
              }}
            />
            Registrar como docente (mis cursos asignados)
          </label>
        </div>
      ) : null}

      {modoConsultaGlobalSoloLectura && (soloLectura || soloLecturaEvalBim) ? (
        <div
          role="note"
          className="-mx-4 mb-1 rounded-md border border-amber-400/70 bg-amber-50 px-3 py-1.5 text-[11px] text-amber-950 sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6"
          data-testid="registro-notas-modo-consulta-banner"
        >
          Modo consulta: las notas y la evaluación bimestral solo pueden ser registradas por el docente asignado.
        </div>
      ) : null}

      {esAdministrador && modoConsultaGlobal && !modoConsultaGlobalSoloLectura ? (
        <div
          role="note"
          className="-mx-4 mb-1 rounded-md border border-sky-400/60 bg-sky-50 px-3 py-1.5 text-[11px] text-sky-950 sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6"
          data-testid="registro-notas-modo-admin-banner"
        >
          Modo administrador: puede registrar y editar notas en cualquier curso con asignación docente activa. El docente asignado del curso no cambia.
        </div>
      ) : null}

      {modoCalificacionDinamica ? (
        <div
          role="note"
          className="-mx-4 mb-1 rounded-md border border-sky-400/60 bg-sky-50 px-3 py-1.5 text-[11px] text-sky-950 sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6"
          data-testid="registro-notas-calificacion-dinamica-banner"
        >
          {MENSAJE_CALIFICACION_DINAMICA}
          {' '}
          {MENSAJE_EXCEL_DINAMICO}
        </div>
      ) : null}

      {puedeDescargarPlantilla ? (
        <div
          role="note"
          className="-mx-4 mb-1 rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-1.5 text-[11px] text-muted sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6"
          data-testid="registro-notas-excel-info-banner"
        >
          {MENSAJE_PLANTILLA_EXCEL}
        </div>
      ) : null}

      {formulario?.calificacion_dinamica_disponible === false
        && (formulario?.componentes_calificacion?.length ?? 0) > 0 ? (
          <div
            role="note"
            className="-mx-4 mb-1 rounded-md border border-red-400/60 bg-red-50 px-3 py-1.5 text-[11px] text-red-950 sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6"
            data-testid="registro-notas-config-invalida-banner"
          >
            La configuración de componentes de calificación para este nivel no es válida (suma distinta de 100). Se usa el registro legacy Cuaderno/Libro/Tarea hasta que se corrija.
          </div>
      ) : null}

      {tieneAlertas ? (
        <div className="mb-1 flex flex-col gap-1">
          {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
          {sinCalendarioActivo ? (
            <AlertMessage variant="info">
              No hay año escolar activo configurado. Seleccione manualmente año y bimestre o configure el calendario académico.
            </AlertMessage>
          ) : null}
          {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}
          {advertencia ? <AlertMessage variant="warning">{advertencia}</AlertMessage> : null}
        </div>
      ) : null}

      {vacioConsultaSinContextos ? (
        <EmptyState
          title="Sin asignaciones docentes para consultar"
          description="Aún no hay cursos activos asignados a aulas."
        />
      ) : vacioDocenteSinAulas ? (
        <EmptyState title="Sin cursos asignados" description="Solicite asignación al coordinador." />
      ) : (
        <>
          <div
            className="sticky top-0 z-40 -mx-4 border-b border-[var(--border)] bg-[var(--surface)] px-3 py-1.5 shadow-sm sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6"
            data-testid="registro-notas-filtros-sticky"
          >
            <RegistroNotasFiltros
              modoConsultaGlobal={modoConsultaGlobal}
              filtros={filtros}
              opciones={opciones}
              aulas={aulas}
              aulasFiltradas={aulasFiltradas}
              periodos={periodos}
              formulario={formulario}
              vista={vista}
              onCambiarFiltro={cambiarFiltro}
              onCambiarContextoConsulta={cambiarContextoConsultaPorClave}
              onCambiarVista={cambiarVista}
              nombreCursoAsignacion={nombreCursoAsignacion}
            />

            {!cargandoFormulario
            && (estructura.length > 0 || evalBimFormulario || cargandoEvalBim) ? (
              <RegistroNotasToolbar
                resumen={resumenToolbar}
                guardando={guardando}
                cargandoFormulario={cargandoFormulario}
                puedeGuardar={puedeGuardar}
                ocultarGuardar={soloLectura}
                descargandoPlantilla={descargandoPlantilla}
                importandoPlantilla={importandoPlantilla}
                puedeDescargarPlantilla={puedeDescargarPlantilla}
                puedeImportarPlantilla={puedeImportarPlantilla}
                modoPlantilla={modoPlantilla}
                onCambiarModoPlantilla={setModoPlantilla}
                onDescargarPlantilla={descargarPlantillaExcel}
                onImportarPlantilla={importarPlantillaExcel}
              />
            ) : null}
          </div>

          {cargandoFormulario ? <LoadingState label="Cargando criterios…" /> : null}

          {!cargandoFormulario
            && (modoConsultaGlobal ? filtros.consulta_contexto_clave : filtros.asignacion_id)
            && filtros.periodo_academico_id
            && !estructura.length
            && !evalBimFormulario
            && !cargandoEvalBim ? (
              <EmptyState
                title="Sin criterios activos"
                description="No hay criterios registrados para este curso y bimestre. Solicite al coordinador que los configure."
              />
              ) : null}

          {!cargandoFormulario
            && (modoConsultaGlobal ? filtros.consulta_contexto_clave : filtros.asignacion_id)
            && filtros.periodo_academico_id
            && (estructura.length > 0 || evalBimFormulario || cargandoEvalBim) ? (
              <>
                {estructura.length > 0 ? (
                  <form id="registro-notas-form" onSubmit={guardar}>
                    {vista === 'aula' ? (
                      <RegistroNotasAulaTable
                        soloLectura={soloLectura}
                        estructura={estructura}
                        estudiantes={estudiantes}
                        matriz={matriz}
                        pesos={formulario?.pesos}
                        componentes={componentesCalificacion}
                        modoDinamico={modoCalificacionDinamica}
                        onChangeNota={cambiarNotaAula}
                      />
                    ) : (
                      <RegistroNotasEstudianteView
                        soloLectura={soloLectura}
                        estructura={estructura}
                        formulario={formulario}
                        filas={filas}
                        filtros={filtros}
                        onCambiarNota={cambiarNotaEstudiante}
                        guardando={guardando}
                        cargandoFormulario={cargandoFormulario}
                      />
                    )}
                  </form>
                ) : null}

                {vista === 'aula' ? (
                  <EvaluacionBimestralBlock
                    formulario={evalBimFormulario}
                    estudiantes={evalBimFormulario?.estudiantes ?? estudiantes}
                    matriz={evalBimMatriz}
                    soloLectura={soloLecturaEvalBim}
                    cargando={cargandoEvalBim}
                    guardando={guardandoEvalBim}
                    puedeGuardar={puedeGuardarEvalBim}
                    onChangeCampo={cambiarNotaEvalBim}
                    onGuardar={() => void guardarEvalBimestral()}
                    modalConclusion={{
                      abierto: modalConclusion.abierto,
                      estudiante: modalConclusion.estudiante,
                      valorInicial: modalConclusion.valorInicial,
                      onAbrir: abrirModalConclusion,
                    }}
                    onCerrarConclusion={cerrarModalConclusion}
                    onGuardarConclusion={aplicarConclusionModal}
                  />
                ) : null}

                {vista === 'estudiante' && filtros.estudiante_id && evalBimFormulario ? (
                  <>
                    <EvaluacionBimestralEstudianteCard
                      estudiante={estudiantes.find(
                        (e) => String(e.id) === String(filtros.estudiante_id),
                      )}
                      formulario={evalBimFormulario}
                      fila={
                        evalBimMatriz[filtros.estudiante_id]
                        ?? initFilaEvalBimEstudiante(Number(filtros.estudiante_id), evalBimFormulario)
                      }
                      matriz={evalBimMatriz}
                      estudiantes={evalBimFormulario?.estudiantes ?? estudiantes}
                      soloLectura={soloLecturaEvalBim}
                      onChangeCampo={cambiarNotaEvalBim}
                      onAbrirConclusion={abrirModalConclusion}
                    />
                    {!soloLecturaEvalBim ? (
                      <div className="mt-2 flex justify-end">
                        <button
                          type="button"
                          className="rounded bg-[var(--primary)] px-3 py-1 text-xs font-medium text-white disabled:opacity-50"
                          disabled={guardandoEvalBim || !puedeGuardarEvalBim}
                          onClick={() => void guardarEvalBimestral()}
                          data-testid="eval-bim-guardar-estudiante"
                        >
                          {guardandoEvalBim ? 'Guardando…' : 'Guardar evaluación bimestral'}
                        </button>
                      </div>
                    ) : null}
                    <ConclusionDescriptivaModal
                      abierto={modalConclusion.abierto}
                      estudiante={modalConclusion.estudiante}
                      valorInicial={modalConclusion.valorInicial}
                      soloLectura={soloLecturaEvalBim}
                      onCerrar={cerrarModalConclusion}
                      onGuardar={aplicarConclusionModal}
                    />
                  </>
                ) : null}
              </>
            ) : null}
        </>
      )}
    </div>
  );
}
