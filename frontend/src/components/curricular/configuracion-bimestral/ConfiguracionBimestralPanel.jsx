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
  postEvalBimComponente,
  postEvalBimEta,
} from '../../../lib/evaluacionBimestral';
import AlertMessage from '../../ui/AlertMessage';
import Card from '../../ui/Card';
import EmptyState from '../../ui/EmptyState';
import LoadingState from '../../ui/LoadingState';
import ComponentesEvaluacionTable from './ComponentesEvaluacionTable';
import ConfiguracionBimestralFiltros from './ConfiguracionBimestralFiltros';
import EtasConfigTable from './EtasConfigTable';
import { obtenerMensajeError } from './configuracionBimestralUtils';

export default function ConfiguracionBimestralPanel() {
  const [config, setConfig] = useState(null);
  const [areas, setAreas] = useState([]);
  const [mallaCursos, setMallaCursos] = useState([]);
  const [periodos, setPeriodos] = useState([]);
  const [cargando, setCargando] = useState(false);
  const [procesandoId, setProcesandoId] = useState(null);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);

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

  const puedeCargar = Boolean(filtros.malla_curso_id && filtros.periodo_academico_id);

  const cursosFiltrados = useMemo(
    () => mallaCursos.filter((c) => String(c.area_id ?? c.area?.id) === String(filtros.area_id)),
    [mallaCursos, filtros.area_id],
  );

  const cargarConfig = useCallback(async () => {
    if (!puedeCargar) {
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
  }, [filtros.malla_curso_id, filtros.periodo_academico_id, puedeCargar]);

  useEffect(() => {
    void cargarConfig();
  }, [cargarConfig]);

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
    if (!exito) return undefined;
    const t = setTimeout(() => setExito(null), 3500);
    return () => clearTimeout(t);
  }, [exito]);

  const cambiarFiltros = useCallback((partial) => {
    setFiltros((prev) => ({ ...prev, ...partial }));
  }, []);

  const cambiarArea = useCallback((areaId) => {
    setFiltros((prev) => ({ ...prev, area_id: areaId, malla_curso_id: '' }));
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
          <li>Esta configuración aplica al curso y bimestre seleccionados para todas las sedes.</li>
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
        filtros={filtros}
        areas={areas}
        cursosFiltrados={cursosFiltrados}
        periodos={periodos}
        onChangeFiltros={cambiarFiltros}
        onChangeArea={cambiarArea}
      />

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      {!puedeCargar ? (
        <EmptyState
          title="Seleccione curso y bimestre"
          description="Elija año, nivel, grado, área, curso y bimestre para cargar la configuración."
        />
      ) : null}

      {puedeCargar && cargando ? <LoadingState label="Cargando configuración…" /> : null}

      {puedeCargar && !cargando && config ? (
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
