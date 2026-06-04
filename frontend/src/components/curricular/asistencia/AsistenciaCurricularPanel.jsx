import { useCallback, useEffect, useMemo, useState } from 'react';
import { useAuth } from '../../../context/AuthContext';
import { getAsistenciaDiariaFormulario, getDocenteAulasCursos, postAsistenciaDiariaBulk } from '../../../lib/api';
import {
  AULA_ASISTENCIA_DEMO,
  deduplicarAulasDocente,
  etiquetaNivelEstudiante,
  gradoEsValidoParaNivel,
} from '../../../lib/academico';
import { resolverCalendarioActivoParaFiltros } from '../../../lib/calendarioAcademico';
import { useOpcionesSeccionAula } from '../../../lib/seccionesAula';
import AlertMessage from '../../ui/AlertMessage';
import Button from '../../ui/Button';
import Card from '../../ui/Card';
import EmptyState from '../../ui/EmptyState';
import LoadingState from '../../ui/LoadingState';
import AsistenciaFiltros, { filtrosAsistenciaIniciales } from './AsistenciaFiltros';
import AsistenciaTabla from './AsistenciaTabla';
import AsistenciaToolbar from './AsistenciaToolbar';
import {
  ESTADOS_ASISTENCIA_FALLBACK,
  contadoresAsistencia,
  filasDesdeFormulario,
  filasParaBulk,
  mensajeErrorAsistenciaApi,
} from './asistenciaUtils';

function contextoCompleto(filtros) {
  return Boolean(
    filtros.anio_escolar?.trim()
      && filtros.nivel
      && filtros.sede
      && filtros.grado
      && filtros.seccion?.trim()
      && filtros.fecha,
  );
}

function puedeSeleccionManualGlobal(permissions, roles) {
  return (
    permissions.includes('gestionar_asignaciones_docente')
    || (roles ?? []).includes('directivo')
    || (roles ?? []).includes('administrador')
  );
}

function filtrosVacios(filtros) {
  return !filtros.nivel && !filtros.grado && !filtros.seccion?.trim();
}

export default function AsistenciaCurricularPanel() {
  const { permissions, roles } = useAuth();
  const modoGlobal = puedeSeleccionManualGlobal(permissions, roles);

  const [filtros, setFiltros] = useState(filtrosAsistenciaIniciales);
  const [aulasDocente, setAulasDocente] = useState([]);
  const [cargandoAulas, setCargandoAulas] = useState(!modoGlobal);
  const [formulario, setFormulario] = useState(null);
  const [filasPorId, setFilasPorId] = useState({});

  const [cargando, setCargando] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [listaSolicitada, setListaSolicitada] = useState(false);
  const [sinAnioActivo, setSinAnioActivo] = useState(false);

  const seccionesDesdeAulas = useMemo(
    () => [...new Set(aulasDocente.map((a) => a.seccion).filter(Boolean))],
    [aulasDocente],
  );

  const opcionesSeccion = useOpcionesSeccionAula({
    nivel: filtros.nivel,
    grado: filtros.grado,
    gradoFormato: 'estudiante',
    legacy: seccionesDesdeAulas,
    valorActual: filtros.seccion,
  });

  useEffect(() => {
    if (!filtros.seccion || !filtros.nivel || !filtros.grado) {
      return;
    }
    const valores = opcionesSeccion.map((o) => o.value);
    if (valores.length > 0 && !valores.includes(filtros.seccion)) {
      setFiltros((prev) => ({ ...prev, seccion: '' }));
    }
  }, [opcionesSeccion, filtros.seccion, filtros.nivel, filtros.grado]);

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

  const puedeCargar = useMemo(() => {
    if (!contextoCompleto(filtros)) {
      return false;
    }
    return gradoEsValidoParaNivel(filtros.nivel, filtros.grado);
  }, [filtros]);

  const estadosPermitidos = formulario?.estados_permitidos ?? ESTADOS_ASISTENCIA_FALLBACK;
  const estudiantes = formulario?.estudiantes ?? [];
  const soloLectura = Boolean(formulario?.readonly);
  const puedeRegistrar = Boolean(formulario?.puede_registrar) && !soloLectura;
  const registradosDia = formulario?.totales?.registrados ?? 0;

  const contadores = useMemo(
    () => contadoresAsistencia(filasPorId, estadosPermitidos),
    [filasPorId, estadosPermitidos],
  );

  const aplicarAula = useCallback((aula) => {
    setFiltros((prev) => ({
      ...prev,
      anio_escolar: aula.anio_escolar ?? prev.anio_escolar,
      nivel: aula.nivel ?? '',
      sede: aula.sede ?? prev.sede,
      grado: aula.grado ?? '',
      seccion: aula.seccion ?? '',
    }));
    setFormulario(null);
    setFilasPorId({});
    setExito(null);
    setError(null);
    setListaSolicitada(false);
  }, []);

  useEffect(() => {
    if (modoGlobal) {
      setCargandoAulas(false);
      return;
    }

    let activo = true;
    void (async () => {
      setCargandoAulas(true);
      try {
        const data = await getDocenteAulasCursos({ anio_escolar: filtros.anio_escolar || undefined });
        if (!activo) return;
        const aulas = deduplicarAulasDocente(Array.isArray(data) ? data : []);
        setAulasDocente(aulas);
        if (aulas.length === 1) {
          aplicarAula(aulas[0]);
        }
      } catch {
        if (activo) {
          setAulasDocente([]);
        }
      } finally {
        if (activo) setCargandoAulas(false);
      }
    })();

    return () => {
      activo = false;
    };
  }, [modoGlobal, filtros.anio_escolar, aplicarAula]);

  const actualizarFiltros = useCallback((parcial) => {
    setFiltros((prev) => ({ ...prev, ...parcial }));
    setFormulario(null);
    setFilasPorId({});
    setExito(null);
    setError(null);
    setListaSolicitada(false);
  }, []);

  const cargarFormulario = useCallback(async () => {
    if (!puedeCargar) {
      return;
    }

    setCargando(true);
    setListaSolicitada(true);
    setError(null);
    setExito(null);
    setFormulario(null);
    setFilasPorId({});

    try {
      const params = {
        anio_escolar: filtros.anio_escolar.trim(),
        nivel: filtros.nivel,
        sede: filtros.sede,
        grado: filtros.grado,
        seccion: filtros.seccion.trim(),
        fecha: filtros.fecha,
      };
      const data = await getAsistenciaDiariaFormulario(params);
      setFormulario(data);
      setFilasPorId(filasDesdeFormulario(data.estudiantes ?? []));
    } catch (e) {
      setError(mensajeErrorAsistenciaApi(e));
      setFormulario(null);
      setFilasPorId({});
    } finally {
      setCargando(false);
    }
  }, [filtros, puedeCargar]);

  const cambiarEstado = useCallback((estudianteId, estado) => {
    setFilasPorId((prev) => ({
      ...prev,
      [estudianteId]: {
        ...(prev[estudianteId] ?? { observacion: '' }),
        estado,
      },
    }));
  }, []);

  const cambiarObservacion = useCallback((estudianteId, observacion) => {
    setFilasPorId((prev) => ({
      ...prev,
      [estudianteId]: {
        ...(prev[estudianteId] ?? { estado: null }),
        observacion,
      },
    }));
  }, []);

  const marcarTodosPresentes = useCallback(() => {
    if (!estadosPermitidos.includes('presente')) {
      return;
    }
    setFilasPorId((prev) => {
      const next = { ...prev };
      estudiantes.forEach((est) => {
        next[est.id] = {
          ...(next[est.id] ?? { observacion: '' }),
          estado: 'presente',
        };
      });
      return next;
    });
  }, [estudiantes, estadosPermitidos]);

  const limpiarObservaciones = useCallback(() => {
    setFilasPorId((prev) => {
      const next = { ...prev };
      Object.keys(next).forEach((id) => {
        next[id] = { ...next[id], observacion: '' };
      });
      return next;
    });
  }, []);

  const guardar = useCallback(async () => {
    if (!formulario || !puedeRegistrar) {
      return;
    }

    const filas = filasParaBulk(filasPorId);
    if (filas.length === 0) {
      setError('Marque al menos un estudiante antes de guardar.');
      return;
    }

    setGuardando(true);
    setError(null);
    setExito(null);

    try {
      const ctx = formulario.contexto ?? filtros;
      const resultado = await postAsistenciaDiariaBulk({
        anio_escolar: ctx.anio_escolar,
        nivel: ctx.nivel,
        sede: ctx.sede,
        grado: ctx.grado,
        seccion: ctx.seccion,
        fecha: ctx.fecha,
        filas,
      });

      setExito(
        `Asistencia guardada: ${resultado.guardados ?? filas.length} registro(s) ` +
          `(${resultado.creados ?? 0} nuevo(s), ${resultado.actualizados ?? 0} actualizado(s)).`,
      );

      await cargarFormulario();
    } catch (e) {
      setError(mensajeErrorAsistenciaApi(e));
    } finally {
      setGuardando(false);
    }
  }, [formulario, puedeRegistrar, filasPorId, filtros, cargarFormulario]);

  const resumenAula = formulario?.contexto;
  const primeraAulaDocente = aulasDocente[0] ?? null;
  const mostrarBotonAulaDocente = !modoGlobal && aulasDocente.length > 0;
  const mostrarBotonDemo = modoGlobal && filtrosVacios(filtros);

  return (
    <div className="flex flex-col gap-6" data-testid="asistencia-curricular-panel">
      <div>
        <h2 className="text-xl font-semibold text-[var(--text)]">Asistencia curricular</h2>
        <p className="mt-1 text-sm text-muted">
          Registro diario por aula (Inicial, Primaria y Secundaria). Los datos se guardan en el módulo
          curricular institucional.
        </p>
      </div>

      <Card className="sticky top-0 z-10 border-[var(--border)] bg-[var(--surface)]/95 p-4 shadow-card backdrop-blur-sm sm:p-6">
        <AsistenciaFiltros
          filtros={filtros}
          onChange={actualizarFiltros}
          deshabilitado={cargando || guardando}
          opcionesSeccion={opcionesSeccion}
        />
        <div className="mt-4 flex flex-wrap items-center gap-3">
          <Button
            type="button"
            variant="primary"
            size="sm"
            disabled={!puedeCargar || cargando || guardando}
            onClick={() => void cargarFormulario()}
            data-testid="asistencia-cargar"
          >
            {cargando ? 'Cargando…' : 'Cargar asistencia'}
          </Button>

          {mostrarBotonAulaDocente ? (
            <Button
              type="button"
              variant="secondary"
              size="sm"
              disabled={cargandoAulas || cargando || guardando || !primeraAulaDocente}
              onClick={() => primeraAulaDocente && aplicarAula(primeraAulaDocente)}
              data-testid="asistencia-usar-aula-docente"
            >
              Usar mi aula asignada
            </Button>
          ) : null}

          {mostrarBotonDemo ? (
            <Button
              type="button"
              variant="outline"
              size="sm"
              disabled={cargando || guardando}
              onClick={() => aplicarAula(AULA_ASISTENCIA_DEMO)}
              data-testid="asistencia-usar-aula-demo"
            >
              Usar aula demo
            </Button>
          ) : null}

          {!puedeCargar ? (
            <p className="text-xs text-muted">Complete año, nivel, sede, grado, sección y fecha.</p>
          ) : null}
        </div>

        {!modoGlobal && !cargandoAulas && aulasDocente.length === 0 ? (
          <AlertMessage variant="warning" className="mt-3">
            No tiene aulas asignadas activas. Solicite una asignación docente al coordinador académico.
          </AlertMessage>
        ) : null}
      </Card>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {sinAnioActivo ? (
        <AlertMessage variant="info">
          No hay año escolar activo configurado. Se usa el año calendario por defecto en los filtros.
        </AlertMessage>
      ) : null}
      {exito ? (
        <AlertMessage variant="success" className="border-success/40">
          {exito}
        </AlertMessage>
      ) : null}

      {soloLectura ? (
        <AlertMessage variant="info">Solo lectura para esta aula y fecha.</AlertMessage>
      ) : null}

      {formulario && !puedeRegistrar && !soloLectura ? (
        <AlertMessage variant="warning">
          No tiene permiso para registrar asistencia en esta aula. Puede consultar los datos cargados.
        </AlertMessage>
      ) : null}

      {cargando ? <LoadingState label="Cargando estudiantes y asistencia del día…" /> : null}

      {!listaSolicitada && !cargando && !formulario ? (
        <EmptyState
          title="Seleccione aula y pulse Cargar asistencia"
          description={
            modoGlobal
              ? 'Elija año, nivel, sede, grado, sección y fecha; luego pulse «Cargar asistencia».'
              : 'Use «Usar mi aula asignada» o complete los filtros y pulse «Cargar asistencia».'
          }
        />
      ) : null}

      {formulario && !cargando ? (
        <Card className="border-[var(--border)] bg-[var(--surface)] p-4 shadow-card sm:p-6">
          {resumenAula ? (
            <div className="mb-4 rounded-lg border border-[var(--border)]/80 bg-[var(--background)]/40 px-4 py-3 text-sm">
              <p className="font-medium text-[var(--text)]">
                {etiquetaNivelEstudiante(resumenAula.nivel)} · {resumenAula.grado} · Sección{' '}
                {resumenAula.seccion}
              </p>
              <p className="mt-0.5 text-muted">
                Chilca · Año {resumenAula.anio_escolar} · Fecha{' '}
                {resumenAula.fecha}
                {formulario.totales
                  ? ` · ${formulario.totales.registrados ?? 0} de ${formulario.totales.alumnos ?? 0} con registro`
                  : ''}
              </p>
            </div>
          ) : null}

          {estudiantes.length > 0 ? (
            <>
              {registradosDia === 0 ? (
                <AlertMessage variant="info" className="mb-4">
                  Hay estudiantes, pero aún no hay asistencia registrada para esta fecha.
                </AlertMessage>
              ) : null}
              <AsistenciaTabla
                estudiantes={estudiantes}
                filasPorId={filasPorId}
                estadosPermitidos={estadosPermitidos}
                onCambiarEstado={cambiarEstado}
                onCambiarObservacion={cambiarObservacion}
                soloLectura={soloLectura || !puedeRegistrar}
              />
              <AsistenciaToolbar
                contadores={contadores}
                estadosPermitidos={estadosPermitidos}
                onMarcarTodosPresentes={marcarTodosPresentes}
                onLimpiarObservaciones={limpiarObservaciones}
                onGuardar={() => void guardar()}
                guardando={guardando}
                puedeGuardar={puedeRegistrar && filasParaBulk(filasPorId).length > 0}
                soloLectura={soloLectura || !puedeRegistrar}
              />
            </>
          ) : (
            <EmptyState
              title="No hay estudiantes activos para estos filtros"
              description="Verifique el aula seleccionada o confirme que existan alumnos activos matriculados."
            />
          )}
        </Card>
      ) : null}

      {listaSolicitada && !cargando && !formulario && !error ? (
        <EmptyState
          title="Sin datos para mostrar"
          description="Verifique el contexto del aula o sus permisos de acceso."
        />
      ) : null}
    </div>
  );
}
