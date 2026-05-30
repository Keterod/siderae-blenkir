import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getCurricularAreas,
  getMallaCurricularPorGrado,
  patchDesactivarMallaCurso,
  patchMallaCurso,
  patchReactivarMallaCurso,
} from '../../lib/api';
import { useAuth } from '../../context/AuthContext';
import { anioEscolarActual } from '../../lib/academico';
import { resolverCalendarioActivoParaFiltros } from '../../lib/calendarioAcademico';
import { etiquetaNivelCurricular } from '../../lib/academicoCurricular';
import AlertMessage from '../ui/AlertMessage';
import Card from '../ui/Card';
import LoadingState from '../ui/LoadingState';
import AreaCursosCard from './malla/AreaCursosCard';
import MallaEstadoVacio from './malla/MallaEstadoVacio';
import MallaFiltros from './malla/MallaFiltros';
import MallaResumen from './malla/MallaResumen';
import {
  catalogoDisponibleParaAgregar,
  obtenerMensajeError,
  resolverCatalogoArea,
} from './malla/utils';

export default function MallaCurricularPanel() {
  const { permissions } = useAuth();
  const puedeGestionar = permissions.includes('gestionar_malla_curricular');

  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: 'primaria',
    grado: '2do',
  });
  const [malla, setMalla] = useState(null);
  const [cargando, setCargando] = useState(false);
  const [preparando, setPreparando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [areas, setAreas] = useState([]);
  const [editandoCursoId, setEditandoCursoId] = useState(null);
  const [editCursoCatalogoId, setEditCursoCatalogoId] = useState('');
  const [agregandoEnAreaId, setAgregandoEnAreaId] = useState(null);
  const [sinAnioActivo, setSinAnioActivo] = useState(false);

  useEffect(() => {
    void resolverCalendarioActivoParaFiltros().then((cal) => {
      if (cal?.anio) {
        setFiltros((prev) => ({ ...prev, anio_escolar: cal.anio }));
        setSinAnioActivo(false);
      } else {
        setSinAnioActivo(true);
      }
    });
  }, []);

  const cambiarFiltros = useCallback((partial) => {
    setFiltros((prev) => ({ ...prev, ...partial }));
  }, []);

  const recargarCatalogoAreas = useCallback(async () => {
    if (!filtros.nivel) return;
    const data = await getCurricularAreas({ nivel: filtros.nivel, incluir_cursos: true });
    setAreas(Array.isArray(data) ? data : []);
  }, [filtros.nivel]);

  const cargarMalla = useCallback(async () => {
    if (!filtros.grado) {
      setMalla(null);
      return;
    }
    setCargando(true);
    setPreparando(true);
    setError(null);
    setExito(null);
    try {
      const data = await getMallaCurricularPorGrado({
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        grado: filtros.grado,
      });
      setMalla(data);
    } catch (e) {
      setError(
        e.status === 403
          ? 'Sin permiso para ver la malla curricular.'
          : 'No se pudo cargar los cursos del grado.',
      );
      setMalla(null);
    } finally {
      setCargando(false);
      setPreparando(false);
    }
  }, [filtros]);

  useEffect(() => {
    void cargarMalla();
  }, [cargarMalla]);

  useEffect(() => {
    if (!filtros.nivel) return;
    void getCurricularAreas({ nivel: filtros.nivel, incluir_cursos: true }).then((data) =>
      setAreas(Array.isArray(data) ? data : []),
    );
  }, [filtros.nivel]);

  const cursos = useMemo(
    () => malla?.malla_cursos ?? malla?.mallaCursos ?? [],
    [malla],
  );

  const catalogoPorAreaId = useMemo(() => {
    const map = new Map();
    for (const area of areas) {
      map.set(String(area.id), resolverCatalogoArea(area));
    }
    return map;
  }, [areas]);

  const cursosPorArea = useMemo(() => {
    const porAreaId = new Map();
    for (const area of areas) {
      porAreaId.set(String(area.id), { area, cursos: [] });
    }
    for (const curso of cursos) {
      const areaId = curso.area_id ?? curso.area?.id;
      if (!areaId) continue;
      const key = String(areaId);
      if (!porAreaId.has(key)) {
        porAreaId.set(key, {
          area: curso.area ?? { id: areaId, nombre: 'Área' },
          cursos: [],
        });
      }
      porAreaId.get(key).cursos.push(curso);
    }
    return [...porAreaId.values()].sort((a, b) =>
      (a.area?.nombre ?? '').localeCompare(b.area?.nombre ?? '', 'es'),
    );
  }, [areas, cursos]);

  const areasConDatos = useMemo(
    () =>
      cursosPorArea.map(({ area, cursos: cursosArea }) => {
        const catalogo = catalogoPorAreaId.get(String(area.id)) ?? [];
        const disponibles = catalogoDisponibleParaAgregar(catalogo, cursosArea);
        const activosCount = cursosArea.filter((c) => c.activo).length;
        return { area, cursosArea, catalogo, disponibles, activosCount };
      }),
    [catalogoPorAreaId, cursosPorArea],
  );

  const resumen = useMemo(() => {
    const totalCursosActivos = cursos.filter((c) => c.activo).length;
    return {
      totalAreas: areas.length,
      totalCursosActivos,
    };
  }, [areas.length, cursos]);

  const limpiarMensajes = useCallback(() => {
    setError(null);
    setExito(null);
  }, []);

  const handleExito = useCallback((mensaje) => {
    setError(null);
    setExito(mensaje);
  }, []);

  const handleError = useCallback((mensaje) => {
    setExito(null);
    setError(mensaje);
  }, []);

  const cerrarAgregar = useCallback(() => {
    setAgregandoEnAreaId(null);
  }, []);

  const abrirAgregarEnArea = useCallback(
    (areaId) => {
      setAgregandoEnAreaId(areaId);
      setEditandoCursoId(null);
      limpiarMensajes();
    },
    [limpiarMensajes],
  );

  const toggleActivo = useCallback(
    async (curso) => {
      if (!malla || !puedeGestionar) return;
      try {
        if (curso.activo) {
          await patchDesactivarMallaCurso(malla.id, curso.id);
        } else {
          await patchReactivarMallaCurso(malla.id, curso.id);
        }
        await cargarMalla();
      } catch {
        handleError('No se pudo actualizar el estado del curso.');
      }
    },
    [cargarMalla, handleError, malla, puedeGestionar],
  );

  const iniciarEdicion = useCallback((curso) => {
    setEditandoCursoId(curso.id);
    setEditCursoCatalogoId(String(curso.curso_catalogo_id ?? curso.curso_catalogo?.id ?? ''));
    setAgregandoEnAreaId(null);
  }, []);

  const cancelarEdicion = useCallback(() => {
    setEditandoCursoId(null);
    setEditCursoCatalogoId('');
  }, []);

  const guardarEdicion = useCallback(
    async (curso) => {
      if (!malla || !puedeGestionar || !editCursoCatalogoId) return;
      limpiarMensajes();
      try {
        await patchMallaCurso(malla.id, curso.id, {
          curso_catalogo_id: Number(editCursoCatalogoId),
        });
        cancelarEdicion();
        await cargarMalla();
      } catch (err) {
        handleError(obtenerMensajeError(err, 'No se pudo editar el curso.'));
      }
    },
    [
      cancelarEdicion,
      cargarMalla,
      editCursoCatalogoId,
      handleError,
      limpiarMensajes,
      malla,
      puedeGestionar,
    ],
  );

  const mostrarContenido = !cargando && !preparando && filtros.grado && malla;
  const mostrarPreparando = preparando || (cargando && filtros.grado);
  const nivelLabel = etiquetaNivelCurricular(filtros.nivel);

  return (
    <div className="flex flex-col gap-6">
      <header className="space-y-1">
        <h2 className="text-2xl font-bold tracking-tight text-[var(--text)]">Malla curricular</h2>
        <p className="max-w-2xl text-sm leading-relaxed text-muted">
          Gestiona los cursos institucionales por nivel, grado y año escolar.
        </p>
      </header>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}
      {sinAnioActivo ? (
        <AlertMessage variant="info">
          No hay año escolar activo configurado. Se usa el año calendario actual; configure el calendario académico si corresponde.
        </AlertMessage>
      ) : null}

      <MallaFiltros
        filtros={filtros}
        cargando={cargando}
        onChangeFiltros={cambiarFiltros}
        onSubmit={cargarMalla}
      />

      {filtros.grado ? (
        <MallaResumen
          filtros={filtros}
          nivelLabel={nivelLabel}
          totalAreas={resumen.totalAreas}
          totalCursosActivos={resumen.totalCursosActivos}
          mostrarContenido={mostrarContenido}
        />
      ) : null}

      {mostrarPreparando ? (
        <Card className="p-8">
          <LoadingState label="Preparando malla predeterminada del grado…" />
        </Card>
      ) : null}

      {!cargando && !preparando && !filtros.grado ? (
        <MallaEstadoVacio
          title="Seleccione un grado"
          description="Elija año escolar, nivel y grado para ver la malla curricular."
        />
      ) : null}

      {!cargando && !preparando && filtros.grado && !malla ? (
        <MallaEstadoVacio
          title="Malla no disponible"
          description="No se pudo obtener la malla para los filtros seleccionados."
        />
      ) : null}

      {mostrarContenido && cursos.length === 0 ? (
        <MallaEstadoVacio
          title="Sin cursos en la malla"
          description="La malla predeterminada no tiene cursos configurados para este grado."
        />
      ) : null}

      {mostrarContenido && cursos.length > 0 ? (
        <div className="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
          {areasConDatos.map(({ area, cursosArea, catalogo, disponibles, activosCount }) => (
            <AreaCursosCard
              key={area.id}
              area={area}
              cursosArea={cursosArea}
              activosCount={activosCount}
              catalogo={catalogo}
              disponibles={disponibles}
              puedeGestionar={puedeGestionar}
              mallaId={malla.id}
              agregandoAbierto={agregandoEnAreaId === area.id}
              onAbrirAgregar={() => abrirAgregarEnArea(area.id)}
              onCerrarAgregar={cerrarAgregar}
              onExito={handleExito}
              onError={handleError}
              onLimpiarMensajes={limpiarMensajes}
              onRecargarCatalogo={recargarCatalogoAreas}
              onRecargarMalla={cargarMalla}
              editandoCursoId={editandoCursoId}
              editCursoCatalogoId={editCursoCatalogoId}
              onIniciarEdicion={iniciarEdicion}
              onCancelarEdicion={cancelarEdicion}
              onEditCursoCatalogoIdChange={setEditCursoCatalogoId}
              onGuardarEdicion={guardarEdicion}
              onToggleActivo={toggleActivo}
            />
          ))}
        </div>
      ) : null}
    </div>
  );
}
