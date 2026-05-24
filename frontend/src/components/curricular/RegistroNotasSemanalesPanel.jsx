import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getCurricularPeriodos,
  getDocenteAulasCursos,
  getFormularioNotasSemanales,
  postNotasSemanalesBulk,
} from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
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
import {
  ADVERTENCIA_ELIMINAR_NOTA,
  construirPayloadAula,
  construirPayloadEstudiante,
  initFilasEstudiante,
  initMatrizAula,
  validarFilasEnRango,
  validarMatrizEnRango,
} from './notas/notasUtils';

function nombreCursoAsignacion(a) {
  return a.malla_curso?.curso_catalogo?.nombre ?? a.mallaCurso?.cursoCatalogo?.nombre ?? 'Curso';
}

function areaIdAsignacion(a) {
  return a.malla_curso?.area_id ?? a.mallaCurso?.area_id ?? a.malla_curso?.area?.id ?? a.mallaCurso?.area?.id;
}

function areaNombreAsignacion(a) {
  return a.malla_curso?.area?.nombre ?? a.mallaCurso?.area?.nombre ?? '';
}

export default function RegistroNotasSemanalesPanel() {
  const [aulas, setAulas] = useState([]);
  const [periodos, setPeriodos] = useState([]);
  const [formulario, setFormulario] = useState(null);
  const [filas, setFilas] = useState({});
  const [matriz, setMatriz] = useState({});
  const [vista, setVista] = useState('aula');
  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: '',
    sede: '',
    grado: '',
    seccion: '',
    area_id: '',
    asignacion_id: '',
    periodo_academico_id: '',
    estudiante_id: '',
  });
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [advertencia, setAdvertencia] = useState(null);
  const [cargandoInicial, setCargandoInicial] = useState(true);
  const [cargandoFormulario, setCargandoFormulario] = useState(false);
  const [guardando, setGuardando] = useState(false);

  useEffect(() => {
    void (async () => {
      try {
        const data = await getDocenteAulasCursos({ anio_escolar: anioEscolarActual() });
        const lista = Array.isArray(data) ? data : [];
        setAulas(lista);
        if (lista.length > 0) {
          const primera = lista[0];
          setFiltros((prev) => ({
            ...prev,
            anio_escolar: primera.anio_escolar ?? prev.anio_escolar,
            nivel: primera.nivel ?? '',
            sede: primera.sede ?? '',
            grado: primera.grado ?? '',
            seccion: primera.seccion ?? '',
            area_id: String(areaIdAsignacion(primera) ?? ''),
            asignacion_id: String(primera.id),
          }));
        }
      } catch {
        setError('No tiene asignaciones activas para registrar notas.');
      } finally {
        setCargandoInicial(false);
      }
    })();
  }, []);

  useEffect(() => {
    if (!filtros.anio_escolar) return;
    void getCurricularPeriodos({ anio_escolar: filtros.anio_escolar }).then((data) => {
      const lista = Array.isArray(data) ? data : [];
      setPeriodos(lista);
      setFiltros((prev) => ({
        ...prev,
        periodo_academico_id: prev.periodo_academico_id || String(lista[0]?.id ?? ''),
      }));
    });
  }, [filtros.anio_escolar]);

  const aulasFiltradas = useMemo(() => {
    return aulas.filter((a) => {
      if (filtros.anio_escolar && a.anio_escolar !== filtros.anio_escolar) return false;
      if (filtros.nivel && a.nivel !== filtros.nivel) return false;
      if (filtros.sede && a.sede !== filtros.sede) return false;
      if (filtros.grado && a.grado !== filtros.grado) return false;
      if (filtros.seccion && a.seccion !== filtros.seccion) return false;
      if (filtros.area_id && String(areaIdAsignacion(a)) !== String(filtros.area_id)) return false;
      return true;
    });
  }, [aulas, filtros]);

  const opciones = useMemo(() => {
    const base = filtros.anio_escolar
      ? aulas.filter((a) => a.anio_escolar === filtros.anio_escolar)
      : aulas;

    const uniq = (arr, key) => [...new Set(arr.map(key).filter(Boolean))];

    const niveles = uniq(base, (a) => a.nivel);
    const sedes = uniq(base, (a) => a.sede);
    const grados = uniq(base, (a) => a.grado);
    const secciones = uniq(
      base.filter((a) => (!filtros.nivel || a.nivel === filtros.nivel) && (!filtros.grado || a.grado === filtros.grado)),
      (a) => a.seccion,
    );

    const areasMap = new Map();
    for (const a of base) {
      const id = areaIdAsignacion(a);
      if (id) areasMap.set(String(id), areaNombreAsignacion(a));
    }

    return { niveles, sedes, grados, secciones, areas: [...areasMap.entries()] };
  }, [aulas, filtros.anio_escolar, filtros.nivel, filtros.grado]);

  const cargarFormulario = useCallback(async (params, modoVista) => {
    const { asignacion_id, periodo_academico_id, estudiante_id } = params;
    if (!asignacion_id || !periodo_academico_id) {
      setFormulario(null);
      setFilas({});
      setMatriz({});
      return;
    }

    setCargandoFormulario(true);
    setError(null);
    try {
      const query = {
        asignacion_docente_id: asignacion_id,
        periodo_academico_id,
      };
      if (modoVista === 'estudiante' && estudiante_id) {
        query.estudiante_id = estudiante_id;
      }

      const data = await getFormularioNotasSemanales(query);
      setFormulario(data);

      const criterios = data.criterios ?? [];
      const estudiantes = data.estudiantes ?? [];

      if (modoVista === 'estudiante') {
        let estudianteActivo = estudiante_id;
        if (!estudianteActivo && estudiantes.length > 0) {
          estudianteActivo = String(estudiantes[0].id);
          setFiltros((prev) => ({ ...prev, estudiante_id: estudianteActivo }));
        }
        setFilas(initFilasEstudiante(criterios, data.notas_por_criterio ?? {}));
        setMatriz(initMatrizAula(estudiantes, criterios, data.notas_por_estudiante_criterio ?? {}));
      } else {
        setMatriz(initMatrizAula(estudiantes, criterios, data.notas_por_estudiante_criterio ?? {}));
        setFilas({});
      }
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err));
      setFormulario(null);
      setFilas({});
      setMatriz({});
    } finally {
      setCargandoFormulario(false);
    }
  }, []);

  useEffect(() => {
    if (!filtros.asignacion_id || !filtros.periodo_academico_id) return;
    void cargarFormulario(filtros, vista);
  }, [filtros.asignacion_id, filtros.periodo_academico_id, filtros.estudiante_id, vista, cargarFormulario]);

  const estructura = useMemo(() => {
    if (!formulario?.criterios?.length) return [];
    return construirResumenRegistrado(formulario.criterios);
  }, [formulario]);

  const asignacionActual = useMemo(
    () => aulas.find((a) => String(a.id) === String(filtros.asignacion_id)),
    [aulas, filtros.asignacion_id],
  );

  const estudiantes = formulario?.estudiantes ?? [];
  const criterios = formulario?.criterios ?? [];

  const resumenToolbar = useMemo(() => {
    const curso = formulario?.curso?.nombre ?? nombreCursoAsignacion(asignacionActual ?? {});
    if (vista === 'estudiante' && filtros.estudiante_id) {
      const est = estudiantes.find((e) => String(e.id) === String(filtros.estudiante_id));
      return `${curso} · ${nombreEstudiante(est ?? {})}`;
    }
    return `${curso} · ${estudiantes.length} estudiante${estudiantes.length === 1 ? '' : 's'}`;
  }, [formulario, asignacionActual, vista, filtros.estudiante_id, estudiantes]);

  const puedeGuardar = vista === 'aula'
    ? Boolean(filtros.asignacion_id && filtros.periodo_academico_id && estudiantes.length)
    : Boolean(filtros.asignacion_id && filtros.periodo_academico_id && filtros.estudiante_id);

  function cambiarFiltro(partial) {
    setExito(null);
    setAdvertencia(null);
    setFiltros((prev) => ({ ...prev, ...partial }));
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
    setFilas((prev) => ({
      ...prev,
      [criterioId]: { ...prev[criterioId], [campo]: valor },
    }));
  }, []);

  const cambiarNotaAula = useCallback((estudianteId, criterioId, campo, valor) => {
    setMatriz((prev) => ({
      ...prev,
      [estudianteId]: {
        ...prev[estudianteId],
        [criterioId]: {
          ...(prev[estudianteId]?.[criterioId] ?? {}),
          [campo]: valor,
        },
      },
    }));
  }, []);

  async function guardar(e) {
    e.preventDefault();
    setError(null);
    setExito(null);
    setAdvertencia(null);

    if (!filtros.asignacion_id || !filtros.periodo_academico_id) {
      setError('Seleccione curso y bimestre.');
      return;
    }

    setGuardando(true);
    try {
      if (vista === 'aula') {
        const errRango = validarMatrizEnRango(matriz, estudiantes, criterios, nombreEstudiante);
        if (errRango) {
          setError(errRango);
          return;
        }

        const { registrosPorEstudiante, intentoBorrar } = construirPayloadAula(matriz, estudiantes, criterios);
        if (registrosPorEstudiante.length === 0) {
          setError('Debe registrar al menos una nota (C, L o T) en algún criterio.');
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

        const errRango = validarFilasEnRango(filas, criterios, (c) => c.titulo);
        if (errRango) {
          setError(errRango);
          return;
        }

        const { registros, intentoBorrar } = construirPayloadEstudiante(filas, criterios);
        if (registros.length === 0) {
          setError('Debe registrar al menos una nota (C, L o T) en algún criterio.');
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
      await cargarFormulario(filtros, vista);
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err));
    } finally {
      setGuardando(false);
    }
  }

  if (cargandoInicial) return <LoadingState label="Cargando asignaciones…" />;

  const tieneAlertas = Boolean(error || exito || advertencia);

  return (
    <div className="flex flex-col gap-0">
      <h2 className="sr-only">Registro de notas</h2>

      {tieneAlertas ? (
        <div className="mb-1 flex flex-col gap-1">
          {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
          {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}
          {advertencia ? <AlertMessage variant="warning">{advertencia}</AlertMessage> : null}
        </div>
      ) : null}

      {aulas.length === 0 ? (
        <EmptyState title="Sin cursos asignados" description="Solicite asignación al coordinador." />
      ) : (
        <>
          <div
            className="sticky top-0 z-40 -mx-4 border-b border-[var(--border)] bg-[var(--surface)] px-3 py-1.5 shadow-sm sm:-mx-6 sm:px-4 lg:-mx-10 lg:px-6"
            data-testid="registro-notas-filtros-sticky"
          >
            <RegistroNotasFiltros
              filtros={filtros}
              opciones={opciones}
              aulas={aulas}
              aulasFiltradas={aulasFiltradas}
              periodos={periodos}
              formulario={formulario}
              vista={vista}
              onCambiarFiltro={cambiarFiltro}
              onCambiarVista={cambiarVista}
              nombreCursoAsignacion={nombreCursoAsignacion}
            />

            {!cargandoFormulario && estructura.length > 0 ? (
              <RegistroNotasToolbar
                resumen={resumenToolbar}
                guardando={guardando}
                cargandoFormulario={cargandoFormulario}
                puedeGuardar={puedeGuardar}
              />
            ) : null}
          </div>

          {cargandoFormulario ? <LoadingState label="Cargando criterios…" /> : null}

          {!cargandoFormulario && filtros.asignacion_id && filtros.periodo_academico_id && !estructura.length ? (
            <EmptyState
              title="Sin criterios activos"
              description="No hay criterios registrados para este curso y bimestre. Solicite al coordinador que los configure."
            />
          ) : null}

          {!cargandoFormulario && estructura.length > 0 ? (
            <form id="registro-notas-form" onSubmit={guardar}>
              {vista === 'aula' ? (
                <RegistroNotasAulaTable
                  estructura={estructura}
                  estudiantes={estudiantes}
                  matriz={matriz}
                  pesos={formulario?.pesos}
                  onChangeNota={cambiarNotaAula}
                />
              ) : (
                <RegistroNotasEstudianteView
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
        </>
      )}
    </div>
  );
}
