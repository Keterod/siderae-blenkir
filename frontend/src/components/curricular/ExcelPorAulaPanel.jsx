import { useCallback, useEffect, useMemo, useState } from 'react';
import { descargarExcelAula, getCurricularPeriodos } from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import { NIVELES_CURRICULARES, gradosCurricularesPorNivel } from '../../lib/academicoCurricular';
import { resolverCalendarioActivoParaFiltros } from '../../lib/calendarioAcademico';
import { obtenerMensajeErrorNotas } from '../../lib/notasCurricular';
import { cargarOpcionesSeccionAula, combinarOpcionesSeccion } from '../../lib/seccionesAula';
import { conSedeOperativa } from '../../lib/sedeOperativa';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';

function etiquetaBimestre(periodo) {
  const n = Number(periodo?.bimestre ?? 0);
  const romanos = { 1: 'I', 2: 'II', 3: 'III', 4: 'IV' };
  return romanos[n] ?? String(periodo?.bimestre ?? '');
}

export default function ExcelPorAulaPanel() {
  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: '',
    grado: '',
    seccion: '',
    periodo_academico_id: '',
  });
  const [periodos, setPeriodos] = useState([]);
  const [opcionesSeccion, setOpcionesSeccion] = useState([]);
  const [cargandoPeriodos, setCargandoPeriodos] = useState(false);
  const [descargando, setDescargando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);
  const [sinCalendarioActivo, setSinCalendarioActivo] = useState(false);

  useEffect(() => {
    void resolverCalendarioActivoParaFiltros().then((cal) => {
      if (!cal?.anio) {
        setSinCalendarioActivo(true);
        return;
      }
      setSinCalendarioActivo(false);
      setFiltros((prev) => ({ ...prev, anio_escolar: cal.anio }));
    });
  }, []);

  useEffect(() => {
    if (!filtros.anio_escolar) {
      setPeriodos([]);
      return;
    }
    setCargandoPeriodos(true);
    getCurricularPeriodos({ anio_escolar: filtros.anio_escolar })
      .then((data) => setPeriodos(Array.isArray(data) ? data : []))
      .catch(() => setPeriodos([]))
      .finally(() => setCargandoPeriodos(false));
  }, [filtros.anio_escolar]);

  useEffect(() => {
    if (!filtros.nivel || !filtros.grado) {
      setOpcionesSeccion([]);
      return;
    }
    void cargarOpcionesSeccionAula({
      nivel: filtros.nivel,
      grado: filtros.grado,
      gradoFormato: 'curricular',
    }).then((opts) => setOpcionesSeccion(opts));
  }, [filtros.nivel, filtros.grado]);

  const grados = useMemo(
    () => gradosCurricularesPorNivel(filtros.nivel),
    [filtros.nivel],
  );

  const puedeDescargar = Boolean(
    filtros.anio_escolar
      && filtros.nivel
      && filtros.grado
      && filtros.seccion
      && filtros.periodo_academico_id,
  );

  const cambiarFiltros = useCallback((partial) => {
    setFiltros((prev) => conSedeOperativa({ ...prev, ...partial }));
    setError(null);
    setExito(null);
  }, []);

  const descargar = useCallback(async () => {
    if (!puedeDescargar) {
      setError('Complete todos los filtros antes de descargar.');
      return;
    }

    setDescargando(true);
    setError(null);
    setExito(null);

    try {
      const { blob, filename } = await descargarExcelAula({
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        grado: filtros.grado,
        seccion: filtros.seccion,
        periodo_academico_id: filtros.periodo_academico_id,
        modo: 'sin_datos',
      });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
      setExito('Excel del aula descargado correctamente.');
    } catch (err) {
      setError(obtenerMensajeErrorNotas(err, 'No se pudo generar el Excel del aula.'));
    } finally {
      setDescargando(false);
    }
  }, [puedeDescargar, filtros]);

  const opcionesSeccionRender = combinarOpcionesSeccion(opcionesSeccion, [], filtros.seccion);

  return (
    <div className="flex flex-col gap-6">
      <header>
        <h2 className="text-2xl font-bold tracking-tight text-[var(--text)]">Excel por aula</h2>
        <p className="mt-1 max-w-2xl text-sm text-muted">
          Descarga un archivo Excel con la lista de estudiantes y una hoja por cada curso activo de la malla.
          Plantilla vacía (sin notas) para captura offline.
        </p>
      </header>

      {sinCalendarioActivo ? (
        <AlertMessage variant="warning">
          No hay año escolar activo. Configure el calendario académico antes de exportar.
        </AlertMessage>
      ) : null}

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <Card className="p-4">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Año escolar</span>
            <input
              type="text"
              className="sb-field min-w-0"
              value={filtros.anio_escolar}
              onChange={(e) => cambiarFiltros({ anio_escolar: e.target.value })}
            />
          </label>

          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Nivel</span>
            <select
              className="sb-field min-w-0"
              value={filtros.nivel}
              onChange={(e) => cambiarFiltros({ nivel: e.target.value, grado: '', seccion: '', periodo_academico_id: '' })}
            >
              <option value="">Seleccione…</option>
              {NIVELES_CURRICULARES.map((n) => (
                <option key={n.value} value={n.value}>
                  {n.label}
                </option>
              ))}
            </select>
          </label>

          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Grado</span>
            <select
              className="sb-field min-w-0"
              value={filtros.grado}
              disabled={!filtros.nivel}
              onChange={(e) => cambiarFiltros({ grado: e.target.value, seccion: '', periodo_academico_id: '' })}
            >
              <option value="">Seleccione…</option>
              {grados.map((g) => (
                <option key={g} value={g}>
                  {g}
                </option>
              ))}
            </select>
          </label>

          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Sección</span>
            <select
              className="sb-field min-w-0"
              value={filtros.seccion}
              disabled={!filtros.nivel || !filtros.grado}
              onChange={(e) => cambiarFiltros({ seccion: e.target.value })}
            >
              <option value="">Seleccione…</option>
              {opcionesSeccionRender.map((s) => (
                <option key={s.value} value={s.value}>
                  {s.label}
                </option>
              ))}
            </select>
          </label>

          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Bimestre</span>
            <select
              className="sb-field min-w-0"
              value={filtros.periodo_academico_id}
              disabled={cargandoPeriodos || !filtros.anio_escolar}
              onChange={(e) => cambiarFiltros({ periodo_academico_id: e.target.value })}
            >
              <option value="">Seleccione…</option>
              {periodos.map((p) => (
                <option key={p.id} value={String(p.id)}>
                  {etiquetaBimestre(p)} — {p.nombre ?? `Bimestre ${p.bimestre}`}
                </option>
              ))}
            </select>
          </label>
        </div>

        <p className="mt-3 text-xs text-muted">
          Sede operativa: Chilca. Modo: plantilla sin datos (40 filas para nombres de estudiantes).
        </p>

        <div className="mt-4 flex justify-end">
          <Button
            type="button"
            variant="primary"
            disabled={!puedeDescargar || descargando}
            onClick={() => void descargar()}
            data-testid="excel-aula-descargar"
          >
            {descargando ? 'Generando…' : 'Descargar Excel del aula'}
          </Button>
        </div>
      </Card>
    </div>
  );
}
