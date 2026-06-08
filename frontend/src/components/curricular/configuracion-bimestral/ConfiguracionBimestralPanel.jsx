import { useCallback, useEffect, useMemo, useState } from 'react';
import { anioEscolarActual } from '../../../lib/academico';
import { resolverCalendarioActivoParaFiltros } from '../../../lib/calendarioAcademico';
import {
  getCurricularAreas,
  getCurricularPeriodos,
  getMallaCurricularPorGrado,
} from '../../../lib/api';
import {
  getEvaluacionBimestralConfig,
  patchEvalBimComponente,
  patchEvalBimEta,
  postAplicarConfiguracionBimestralGrado,
  postEvalBimComponente,
  postEvalBimEta,
} from '../../../lib/evaluacionBimestral';
import AlertMessage from '../../ui/AlertMessage';
import Button from '../../ui/Button';
import Card from '../../ui/Card';
import EmptyState from '../../ui/EmptyState';
import LoadingState from '../../ui/LoadingState';
import ComponentesEvaluacionTable from './ComponentesEvaluacionTable';
import ConfiguracionBimestralFiltros from './ConfiguracionBimestralFiltros';
import EtasConfigTable from './EtasConfigTable';
import {
  crearPlantillaBimestralPorDefecto,
  obtenerMensajeError,
  pesosValidos,
  redistribuirEquitativo,
  serializarPlantillaParaApi,
  slugPersonalizado,
  sumaPesos,
} from './configuracionBimestralUtils';

export default function ConfiguracionBimestralPanel() {
  const [modo, setModo] = useState('curso');
  const [config, setConfig] = useState(null);
  const [plantilla, setPlantilla] = useState(() => crearPlantillaBimestralPorDefecto());
  const [areas, setAreas] = useState([]);
  const [mallaCursos, setMallaCursos] = useState([]);
  const [periodos, setPeriodos] = useState([]);
  const [cargando, setCargando] = useState(false);
  const [aplicandoGrado, setAplicandoGrado] = useState(false);
  const [procesandoId, setProcesandoId] = useState(null);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [resultadoGrado, setResultadoGrado] = useState(null);

  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: 'primaria',
    grado: '2do',
    area_id: '',
    malla_curso_id: '',
    periodo_academico_id: '',
  });

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

  const cursosActivosGrado = useMemo(
    () => [...mallaCursos].sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0)),
    [mallaCursos],
  );

  const puedeCargarCurso = modo === 'curso' && Boolean(filtros.malla_curso_id && filtros.periodo_academico_id);
  const puedeConfigurarGrado =
    modo === 'grado' && Boolean(filtros.grado && filtros.periodo_academico_id && cursosActivosGrado.length > 0);

  const cursosFiltrados = useMemo(
    () => mallaCursos.filter((c) => String(c.area_id ?? c.area?.id) === String(filtros.area_id)),
    [mallaCursos, filtros.area_id],
  );

  const plantillaValida = useMemo(() => {
    const activos = (plantilla?.componentes ?? []).filter((c) => c.activo);
    if (!pesosValidos(sumaPesos(activos, 'peso'))) return false;
    const etaActiva = activos.some((c) => c.codigo === 'promedio_eta');
    if (!etaActiva) return true;
    const etasActivas = (plantilla?.etas ?? []).filter((e) => e.activo);
    if (etasActivas.length === 0) return true;
    return pesosValidos(sumaPesos(etasActivas, 'peso_interno'));
  }, [plantilla]);

  const cargarConfig = useCallback(async () => {
    if (!puedeCargarCurso) {
      setConfig(null);
      return;
    }
    setCargando(true);
    setError(null);
    try {
      const data = await getEvaluacionBimestralConfig({
        malla_curso_id: filtros.malla_curso_id,
        periodo_academico_id: filtros.periodo_academico_id,
      });
      setConfig(data);
    } catch (err) {
      setConfig(null);
      setError(obtenerMensajeError(err, 'No se pudo cargar la configuración bimestral.'));
    } finally {
      setCargando(false);
    }
  }, [filtros.malla_curso_id, filtros.periodo_academico_id, puedeCargarCurso]);

  useEffect(() => {
    if (modo === 'curso') {
      void cargarConfig();
    }
  }, [cargarConfig, modo]);

  useEffect(() => {
    if (modo !== 'curso' || !filtros.nivel) return;
    void getCurricularAreas({ nivel: filtros.nivel }).then((data) =>
      setAreas(Array.isArray(data) ? data : []),
    );
  }, [filtros.nivel, modo]);

  useEffect(() => {
    if (!filtros.grado) {
      setMallaCursos([]);
      return;
    }
    void getMallaCurricularPorGrado({
      anio_escolar: filtros.anio_escolar,
      nivel: filtros.nivel,
      grado: filtros.grado,
    })
      .then((malla) => {
        const cursos = (malla?.malla_cursos ?? malla?.mallaCursos ?? []).filter((c) => c.activo);
        setMallaCursos(cursos);
      })
      .catch(() => setMallaCursos([]));
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
    if (!exito) return undefined;
    const t = setTimeout(() => setExito(null), 3500);
    return () => clearTimeout(t);
  }, [exito]);

  const cambiarFiltros = useCallback((partial) => {
    setFiltros((prev) => ({ ...prev, ...partial }));
    setResultadoGrado(null);
  }, []);

  const cambiarArea = useCallback((areaId) => {
    setFiltros((prev) => ({ ...prev, area_id: areaId, malla_curso_id: '' }));
  }, []);

  const cambiarModo = useCallback((nuevoModo) => {
    setModo(nuevoModo);
    setError(null);
    setResultadoGrado(null);
    if (nuevoModo === 'grado') {
      setPlantilla(crearPlantillaBimestralPorDefecto());
      setConfig(null);
    }
  }, []);

  async function ejecutarAccion(id, accion, mensajeExito) {
    setProcesandoId(id);
    setError(null);
    try {
      await accion();
      await cargarConfig();
      setExito(mensajeExito);
    } catch (err) {
      setError(obtenerMensajeError(err, 'No se pudo guardar el cambio.'));
      throw err;
    } finally {
      setProcesandoId(null);
    }
  }

  const toggleComponente = useCallback(
    (componente) =>
      ejecutarAccion(
        componente.id,
        () => patchEvalBimComponente(componente.id, { activo: !componente.activo }),
        componente.activo ? 'Componente desactivado.' : 'Componente activado.',
      ),
    [cargarConfig],
  );

  const guardarPesoComponente = useCallback(
    (id, peso) =>
      ejecutarAccion(id, () => patchEvalBimComponente(id, { peso }), 'Peso de componente actualizado.'),
    [cargarConfig],
  );

  const guardarNombreComponente = useCallback(
    (id, nombre) =>
      ejecutarAccion(id, () => patchEvalBimComponente(id, { nombre }), 'Nombre actualizado.'),
    [cargarConfig],
  );

  const agregarComponente = useCallback(
    (nombre) =>
      ejecutarAccion(
        'nuevo-componente',
        () =>
          postEvalBimComponente({
            malla_curso_id: Number(filtros.malla_curso_id),
            periodo_academico_id: Number(filtros.periodo_academico_id),
            nombre,
          }),
        'Componente personalizado agregado.',
      ),
    [cargarConfig, filtros.malla_curso_id, filtros.periodo_academico_id],
  );

  const toggleEta = useCallback(
    (eta) =>
      ejecutarAccion(
        eta.id,
        () => patchEvalBimEta(eta.id, { activo: !eta.activo }),
        eta.activo ? 'ETA desactivada.' : 'ETA activada.',
      ),
    [cargarConfig],
  );

  const guardarPesoEta = useCallback(
    (id, peso_interno) =>
      ejecutarAccion(id, () => patchEvalBimEta(id, { peso_interno }), 'Peso interno de ETA actualizado.'),
    [cargarConfig],
  );

  const guardarNombreEta = useCallback(
    (id, nombre) =>
      ejecutarAccion(id, () => patchEvalBimEta(id, { nombre }), 'Nombre de ETA actualizado.'),
    [cargarConfig],
  );

  const agregarEta = useCallback(
    (nombre) =>
      ejecutarAccion(
        'nueva-eta',
        () =>
          postEvalBimEta({
            malla_curso_id: Number(filtros.malla_curso_id),
            periodo_academico_id: Number(filtros.periodo_academico_id),
            nombre,
          }),
        'ETA agregada.',
      ),
    [cargarConfig, filtros.malla_curso_id, filtros.periodo_academico_id],
  );

  const toggleComponentePlantilla = useCallback((componente) => {
    setPlantilla((prev) => {
      const componentes = prev.componentes.map((c) =>
        c.id === componente.id ? { ...c, activo: !c.activo } : c,
      );
      return { ...prev, componentes: redistribuirEquitativo(componentes, 'peso') };
    });
  }, []);

  const guardarPesoComponentePlantilla = useCallback((id, peso) => {
    setPlantilla((prev) => ({
      ...prev,
      componentes: prev.componentes.map((c) => (c.id === id ? { ...c, peso } : c)),
    }));
    return Promise.resolve();
  }, []);

  const guardarNombreComponentePlantilla = useCallback((id, nombre) => {
    setPlantilla((prev) => ({
      ...prev,
      componentes: prev.componentes.map((c) =>
        c.id === id
          ? { ...c, nombre, codigo: c.tipo === 'personalizado' ? slugPersonalizado(nombre) : c.codigo }
          : c,
      ),
    }));
    return Promise.resolve();
  }, []);

  const agregarComponentePlantilla = useCallback((nombre) => {
    const codigo = slugPersonalizado(nombre);
    const maxOrden = Math.max(0, ...plantilla.componentes.map((c) => c.orden ?? 0));
    setPlantilla((prev) => {
      const componentes = [
        ...prev.componentes,
        {
          id: codigo,
          codigo,
          tipo: 'personalizado',
          nombre,
          peso: 0,
          orden: maxOrden + 1,
          activo: true,
        },
      ];
      return { ...prev, componentes: redistribuirEquitativo(componentes, 'peso') };
    });
    return Promise.resolve();
  }, [plantilla.componentes]);

  const toggleEtaPlantilla = useCallback((eta) => {
    setPlantilla((prev) => {
      const etas = prev.etas.map((e) => (e.id === eta.id ? { ...e, activo: !e.activo } : e));
      return { ...prev, etas: redistribuirEquitativo(etas, 'peso_interno') };
    });
  }, []);

  const guardarPesoEtaPlantilla = useCallback((id, peso_interno) => {
    setPlantilla((prev) => ({
      ...prev,
      etas: prev.etas.map((e) => (e.id === id ? { ...e, peso_interno } : e)),
    }));
    return Promise.resolve();
  }, []);

  const guardarNombreEtaPlantilla = useCallback((id, nombre) => {
    setPlantilla((prev) => ({
      ...prev,
      etas: prev.etas.map((e) => (e.id === id ? { ...e, nombre } : e)),
    }));
    return Promise.resolve();
  }, []);

  const agregarEtaPlantilla = useCallback((nombre) => {
    const maxOrden = Math.max(0, ...plantilla.etas.map((e) => e.orden ?? 0));
    setPlantilla((prev) => {
      const etas = [
        ...prev.etas,
        {
          id: `eta-${maxOrden + 1}`,
          nombre,
          peso_interno: 0,
          orden: maxOrden + 1,
          activo: true,
        },
      ];
      return { ...prev, etas: redistribuirEquitativo(etas, 'peso_interno') };
    });
    return Promise.resolve();
  }, [plantilla.etas]);

  async function aplicarConfiguracionAlGrado() {
    if (!puedeConfigurarGrado || !plantillaValida) return;
    setAplicandoGrado(true);
    setError(null);
    setResultadoGrado(null);
    try {
      const resultado = await postAplicarConfiguracionBimestralGrado({
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        grado: filtros.grado,
        periodo_academico_id: Number(filtros.periodo_academico_id),
        plantilla: serializarPlantillaParaApi(plantilla),
      });
      setResultadoGrado(resultado);
      setExito(`Configuración aplicada a ${resultado.total_afectados ?? 0} cursos.`);
    } catch (err) {
      setError(obtenerMensajeError(err, 'No se pudo aplicar la configuración al grado.'));
    } finally {
      setAplicandoGrado(false);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <Card className="p-6">
        <h2 className="text-lg font-semibold text-[var(--text)]">Configuración bimestral</h2>
        <p className="mt-1 text-sm text-muted">
          Configure la fórmula final del bimestre (promedio de criterios, oral, promedio ETA, examen
          bimestral y personalizados) y las ETAs por curso y bimestre.
        </p>
      </Card>

      <Card className="border-[var(--border)] bg-[var(--surface-muted)]/30 p-4 text-sm text-muted">
        <ul className="list-inside list-disc space-y-1">
          <li>Esta configuración aplica al curso y bimestre seleccionados en la sede Chilca.</li>
          <li>
            Los componentes bimestrales definen cómo se compone la nota final del bimestre a partir del
            promedio de criterios, oral, ETAs y examen.
          </li>
          <li>
            Cuaderno, Libro y Tarea (C/L/T) no se configuran aquí: pertenecen al módulo futuro
            «Componentes para criterios por nivel/grado», que alimenta la nota de cada criterio.
          </li>
          <li>Los cambios de pesos afectan el cálculo del nivel de logro bimestral.</li>
          <li>Al activar/desactivar componentes o ETAs, los pesos se redistribuyen automáticamente.</li>
          <li>El 0 explícito en ETA cuenta como nota cargada al registrar evaluación por aula.</li>
        </ul>
      </Card>

      <ConfiguracionBimestralFiltros
        modo={modo}
        filtros={filtros}
        areas={areas}
        cursosFiltrados={cursosFiltrados}
        periodos={periodos}
        onChangeModo={cambiarModo}
        onChangeFiltros={cambiarFiltros}
        onChangeArea={cambiarArea}
      />

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      {modo === 'grado' ? (
        <>
          {filtros.grado && filtros.periodo_academico_id ? (
            <Card className="border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950">
              <p className="font-medium">
                Se aplicará la misma configuración bimestral a todos los cursos activos de este grado.
              </p>
              <p className="mt-2 text-xs">
                Edite cada peso con el botón «Peso» y confirme con «OK». La suma de activos debe ser 100%
                antes de aplicar (se valida al enviar, no en cada paso).
              </p>
              {cursosActivosGrado.length > 0 ? (
                <ul className="mt-2 list-inside list-disc text-xs">
                  {cursosActivosGrado.map((c) => (
                    <li key={c.id}>
                      {c.curso_catalogo?.nombre ?? c.cursoCatalogo?.nombre ?? `Curso #${c.id}`}
                      {c.area?.nombre ? ` (${c.area.nombre})` : ''}
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="mt-2 text-xs">No hay cursos activos en la malla de este grado.</p>
              )}
            </Card>
          ) : null}

          {puedeConfigurarGrado ? (
            <div className="flex flex-wrap items-center gap-3">
              <Button
                type="button"
                disabled={!plantillaValida || aplicandoGrado}
                onClick={() => void aplicarConfiguracionAlGrado()}
              >
                {aplicandoGrado ? 'Aplicando…' : 'Aplicar configuración al grado'}
              </Button>
              {!plantillaValida ? (
                <span className="text-xs text-amber-800">
                  Ajuste los pesos activos para que sumen 100% antes de aplicar.
                </span>
              ) : null}
            </div>
          ) : null}

          {resultadoGrado?.total_afectados != null ? (
            <AlertMessage variant="success">
              Configuración aplicada a {resultadoGrado.total_afectados} curso
              {resultadoGrado.total_afectados === 1 ? '' : 's'}.
            </AlertMessage>
          ) : null}

          {puedeConfigurarGrado ? (
            <div className="grid gap-6 lg:grid-cols-2">
              <ComponentesEvaluacionTable
                componentes={plantilla.componentes}
                procesando={procesandoId}
                onToggleActivo={toggleComponentePlantilla}
                onGuardarPeso={guardarPesoComponentePlantilla}
                onGuardarNombre={guardarNombreComponentePlantilla}
                onAgregar={agregarComponentePlantilla}
                esPlantillaGrado
              />
              <EtasConfigTable
                etas={plantilla.etas}
                procesando={procesandoId}
                onToggleActivo={toggleEtaPlantilla}
                onGuardarPeso={guardarPesoEtaPlantilla}
                onGuardarNombre={guardarNombreEtaPlantilla}
                onAgregar={agregarEtaPlantilla}
                esPlantillaGrado
              />
            </div>
          ) : (
            <EmptyState
              title="Seleccione grado y bimestre"
              description="Elija año, nivel, grado y bimestre para definir la plantilla común."
            />
          )}
        </>
      ) : null}

      {modo === 'curso' && !puedeCargarCurso ? (
        <EmptyState
          title="Seleccione curso y bimestre"
          description="Elija año, nivel, grado, área, curso y bimestre para cargar la configuración."
        />
      ) : null}

      {modo === 'curso' && puedeCargarCurso && cargando ? (
        <LoadingState label="Cargando configuración…" />
      ) : null}

      {modo === 'curso' && puedeCargarCurso && !cargando && config ? (
        <div className="grid gap-6 lg:grid-cols-2">
          <ComponentesEvaluacionTable
            componentes={config.componentes ?? []}
            procesando={procesandoId}
            onToggleActivo={toggleComponente}
            onGuardarPeso={guardarPesoComponente}
            onGuardarNombre={guardarNombreComponente}
            onAgregar={agregarComponente}
          />
          <EtasConfigTable
            etas={config.etas ?? []}
            procesando={procesandoId}
            onToggleActivo={toggleEta}
            onGuardarPeso={guardarPesoEta}
            onGuardarNombre={guardarNombreEta}
            onAgregar={agregarEta}
          />
        </div>
      ) : null}
    </div>
  );
}
