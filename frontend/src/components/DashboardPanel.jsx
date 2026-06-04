import { useCallback, useEffect, useState } from 'react';
import { exportDashboardPdf, getDashboard } from '../lib/api';
import {
  ETIQUETA_SEDE_OPERATIVA,
  filtrosConSedeOperativa,
  tieneFiltrosAdemasDeSede,
} from '../lib/sedeOperativa';
import AlertMessage from './ui/AlertMessage';
import Badge from './ui/Badge';
import Button from './ui/Button';
import Card from './ui/Card';
import EmptyState from './ui/EmptyState';
import LoadingState from './ui/LoadingState';

function riesgoVariant(nivel) {
  if (nivel === 'alto') {
    return 'danger';
  }
  if (nivel === 'medio') {
    return 'warning';
  }
  if (nivel === 'bajo') {
    return 'success';
  }
  return 'neutral';
}

function alertaVariant(estadoClave) {
  if (estadoClave === 'pendiente') {
    return 'warning';
  }
  if (estadoClave === 'en_atencion') {
    return 'info';
  }
  if (estadoClave === 'cerrada') {
    return 'success';
  }
  return 'neutral';
}

function etiquetaAlerta(estadoClave) {
  if (estadoClave === 'en_atencion') {
    return 'En atención';
  }
  if (estadoClave === 'pendiente') {
    return 'Pendiente';
  }
  if (estadoClave === 'cerrada') {
    return 'Cerrada';
  }
  return estadoClave;
}

function etiquetasFiltrosHumano(filtros) {
  if (!filtros || typeof filtros !== 'object') {
    return [];
  }
  const partes = [];
  if (filtros.nivel) {
    partes.push(`nivel ${filtros.nivel}`);
  }
  if (filtros.grado) {
    partes.push(`grado «${filtros.grado}»`);
  }
  if (filtros.seccion) {
    partes.push(`sección «${filtros.seccion}»`);
  }
  if (filtros.nivel_riesgo) {
    partes.push(`riesgo último índice: ${filtros.nivel_riesgo}`);
  }
  return partes;
}

function BarraPct({ etiqueta, porcentaje, colorClass }) {
  const pct = Math.min(100, Math.max(0, Number(porcentaje) || 0));
  return (
    <div>
      <div className="mb-1 flex justify-between text-xs font-medium text-[var(--text)]">
        <span className="text-muted">{etiqueta}</span>
        <span>{pct}%</span>
      </div>
      <div className="h-2 overflow-hidden rounded-full border border-[var(--border)] bg-[var(--background)]">
        <div className={`h-full rounded-full ${colorClass}`} style={{ width: `${pct}%` }} />
      </div>
    </div>
  );
}

const FILTROS_DASHBOARD_INICIAL = filtrosConSedeOperativa();

export default function DashboardPanel() {
  const [appliedFilters, setAppliedFilters] = useState(FILTROS_DASHBOARD_INICIAL);
  const [draftFilters, setDraftFilters] = useState(FILTROS_DASHBOARD_INICIAL);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);
  const [datos, setDatos] = useState(null);
  const [exportando, setExportando] = useState(false);
  const [exportError, setExportError] = useState(null);

  const cargar = useCallback(async (filtros = {}) => {
    setCargando(true);
    setError(null);
    try {
      const payload = await getDashboard(filtros);
      setDatos(payload);
    } catch (err) {
      setError(err.message || 'No se pudo cargar el dashboard');
      setDatos(null);
    } finally {
      setCargando(false);
    }
  }, []);

  useEffect(() => {
    cargar(appliedFilters);
  }, [appliedFilters, cargar]);

  const handleAplicar = () => {
    setAppliedFilters(filtrosConSedeOperativa(draftFilters));
  };

  const handleLimpiar = () => {
    setDraftFilters(FILTROS_DASHBOARD_INICIAL);
    setAppliedFilters(FILTROS_DASHBOARD_INICIAL);
  };

  const handleExportarPdf = async () => {
    setExportando(true);
    setExportError(null);
    try {
      const blob = await exportDashboardPdf(appliedFilters);
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'dashboard-siderae-blenkir.pdf';
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    } catch (err) {
      setExportError(err.payload?.message || err.message || 'No se pudo generar el PDF');
    } finally {
      setExportando(false);
    }
  };

  if (cargando && !datos) {
    return (
      <Card>
        <LoadingState label="Cargando dashboard…" />
      </Card>
    );
  }

  if (error) {
    return (
      <Card className="border-red-200 bg-red-50/40">
        <h2 className="text-lg font-semibold text-red-950">No se puede mostrar el panel</h2>
        <AlertMessage variant="error" className="mt-2">
          {error}
        </AlertMessage>
      </Card>
    );
  }

  if (!datos) {
    return (
      <Card>
        <EmptyState title="Sin datos disponibles" description="No hubo respuesta del servidor para armar los indicadores." />
      </Card>
    );
  }

  const {
    total_estudiantes,
    riesgos_por_nivel,
    porcentajes_riesgo,
    alertas_por_estado,
    porcentajes_alertas,
    ultimos_riesgos,
    filtros_aplicados: filtrosDelBackend,
    opciones_filtros: opciones,
    indicadores_curriculares: indicadoresCurriculares,
  } = datos;

  const riesgoSinDatos =
    (ultimos_riesgos?.length ?? 0) === 0
    && (riesgos_por_nivel?.alto ?? 0) + (riesgos_por_nivel?.medio ?? 0) + (riesgos_por_nivel?.bajo ?? 0) === 0;

  const sinResultadosFiltros =
    total_estudiantes === 0 &&
    (ultimos_riesgos?.length ?? 0) === 0 &&
    tieneFiltrosAdemasDeSede(appliedFilters);

  const sinDatosOperativosGlobales =
    total_estudiantes === 0 &&
    (ultimos_riesgos?.length ?? 0) === 0 &&
    (riesgos_por_nivel?.alto ?? 0) + (riesgos_por_nivel?.medio ?? 0) + (riesgos_por_nivel?.bajo ?? 0) === 0 &&
    !tieneFiltrosAdemasDeSede(appliedFilters);

  const opc = opciones || {};
  const mostrarNivelEdu = Array.isArray(opc.niveles) && opc.niveles.length > 0;
  const mostrarGrado = Array.isArray(opc.grados) && opc.grados.length > 0;
  const mostrarSeccion = Array.isArray(opc.secciones) && opc.secciones.length > 0;
  const mostrarNivelRiesgo = Array.isArray(opc.niveles_riesgo) && opc.niveles_riesgo.length > 0;

  const inputSelectClass =
    'mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-2 py-1.5 text-sm text-[var(--text)] outline-none transition focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-1';

  return (
    <section className="space-y-6" data-testid="dashboard-panel">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">Dashboard</h2>
          <p className="mt-1 max-w-2xl text-sm leading-relaxed text-muted">
            Indicadores históricos de riesgo y alertas (el cálculo de riesgo está pendiente de actualización al flujo
            curricular). Alcance institucional: sede {ETIQUETA_SEDE_OPERATIVA}. Puede acotar por nivel, grado,
            sección y clasificación según el último índice calculado por estudiante.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button
            type="button"
            variant="secondary"
            size="sm"
            data-testid="dashboard-export-pdf"
            onClick={handleExportarPdf}
            disabled={exportando || cargando}
          >
            {exportando ? 'Exportando PDF…' : 'Exportar PDF'}
          </Button>
        </div>
      </div>

      {exportError ? (
        <AlertMessage variant="error">{exportError}</AlertMessage>
      ) : null}

      <Card className="shadow-card !border-[var(--border)] !p-5 sm:!p-6">
        <div className="mb-5 border-b border-[var(--border)] pb-4">
          <h3 className="text-base font-semibold text-[var(--text)]">Filtros del dashboard</h3>
          <p className="mt-1.5 text-sm text-muted">
            Elija alcance por nivel, grado o sección; los filtros vigentes también se aplican al exportar PDF.
          </p>
          <p className="mt-1 text-xs text-muted">Sede: {ETIQUETA_SEDE_OPERATIVA}</p>
        </div>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {mostrarNivelEdu ? (
            <label className="block text-xs text-muted">
              Nivel educativo
              <select
                className={inputSelectClass}
                value={draftFilters.nivel ?? ''}
                onChange={(e) => setDraftFilters((p) => ({ ...p, nivel: e.target.value || undefined }))}
              >
                <option value="">Todos</option>
                {opc.niveles.map((n) => (
                  <option key={n} value={n}>
                    {n}
                  </option>
                ))}
              </select>
            </label>
          ) : null}

          {mostrarGrado ? (
            <label className="block text-xs text-muted">
              Grado
              <select
                className={inputSelectClass}
                value={draftFilters.grado ?? ''}
                onChange={(e) => setDraftFilters((p) => ({ ...p, grado: e.target.value || undefined }))}
              >
                <option value="">Todos</option>
                {opc.grados.map((g) => (
                  <option key={g} value={g}>
                    {g}
                  </option>
                ))}
              </select>
            </label>
          ) : null}

          {mostrarSeccion ? (
            <label className="block text-xs text-muted">
              Sección
              <select
                className={inputSelectClass}
                value={draftFilters.seccion ?? ''}
                onChange={(e) => setDraftFilters((p) => ({ ...p, seccion: e.target.value || undefined }))}
              >
                <option value="">Todas</option>
                {opc.secciones.map((s) => (
                  <option key={s} value={s}>
                    {s}
                  </option>
                ))}
              </select>
            </label>
          ) : null}

          {mostrarNivelRiesgo ? (
            <label className="block text-xs text-muted">
              Nivel de riesgo (último índice)
              <select
                className={inputSelectClass}
                value={draftFilters.nivel_riesgo ?? ''}
                onChange={(e) =>
                  setDraftFilters((p) => ({ ...p, nivel_riesgo: e.target.value || undefined }))
                }
              >
                <option value="">Todos (con índice calculado)</option>
                {opc.niveles_riesgo.map((nr) => (
                  <option key={nr} value={nr}>
                    {nr}
                  </option>
                ))}
              </select>
            </label>
          ) : null}
        </div>

        <div className="mt-3 flex flex-wrap gap-2">
          <Button type="button" variant="primary" size="sm" data-testid="dashboard-aplicar-filtros" onClick={handleAplicar}>
            Aplicar filtros
          </Button>
          <Button type="button" variant="outline" size="sm" data-testid="dashboard-limpiar-filtros" onClick={handleLimpiar}>
            Limpiar filtros
          </Button>
        </div>

        {(() => {
          const fuenteLegible =
            filtrosDelBackend && Object.keys(filtrosDelBackend).length > 0 ? filtrosDelBackend : appliedFilters;
          const legibles = etiquetasFiltrosHumano(fuenteLegible);
          return legibles.length > 0 ? (
            <p className="mt-3 rounded-md bg-[var(--background)] px-3 py-2 text-sm text-[var(--text)]">
              <span className="font-medium text-muted">Filtro aplicado: </span>
              <span>{legibles.join(' · ')}</span>
            </p>
          ) : !tieneFiltrosAdemasDeSede(appliedFilters) ? (
            <p className="mt-3 text-sm text-muted">
              Vista institucional de la sede {ETIQUETA_SEDE_OPERATIVA} según datos del servidor.
            </p>
          ) : null;
        })()}
      </Card>

      {cargando ? (
        <p className="text-sm text-muted">Actualizando datos…</p>
      ) : null}

      {sinDatosOperativosGlobales ? (
        <AlertMessage variant="info">
          Aún no hay datos registrados. Los indicadores aparecerán cuando existan estudiantes e índices procesados.
        </AlertMessage>
      ) : null}

      {riesgoSinDatos ? (
        <AlertMessage variant="info">
          Riesgo académico pendiente de actualización.
        </AlertMessage>
      ) : null}

      <Card className="overflow-hidden !border-[var(--border)] !p-0 shadow-card">
        <div className="border-b border-[var(--border)] bg-[var(--background)]/80 px-4 py-4 sm:px-5">
          <h3 className="text-base font-semibold text-[var(--text)]">Indicadores curriculares</h3>
          <p className="mt-1 text-sm text-muted">
            Conteos operativos del módulo curricular (asistencia, evaluación, malla y asignaciones).
          </p>
        </div>
        <div className="grid grid-cols-1 divide-y divide-[var(--border)] sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-5 lg:divide-x">
          {[
            { label: 'Estudiantes activos', valor: indicadoresCurriculares?.total_estudiantes_activos ?? 0 },
            { label: 'Registros asistencia diaria', valor: indicadoresCurriculares?.registros_asistencia_diaria ?? 0 },
            { label: 'Resultados bimestrales', valor: indicadoresCurriculares?.resultados_bimestrales ?? 0 },
            { label: 'Cursos malla activos', valor: indicadoresCurriculares?.cursos_malla_activos ?? 0 },
            { label: 'Asignaciones docente', valor: indicadoresCurriculares?.asignaciones_docente_activas ?? 0 },
          ].map(({ label, valor }) => (
            <div key={label} className="p-4 sm:p-5">
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">{label}</p>
              <p className="mt-2 text-2xl font-semibold tabular-nums text-[var(--text)]">{valor}</p>
            </div>
          ))}
        </div>
      </Card>

      {sinResultadosFiltros ? (
        <AlertMessage variant="warning">
          No hay estudiantes que coincidan con los filtros seleccionados.
        </AlertMessage>
      ) : null}

      <Card className="overflow-hidden !border-[var(--border)] !p-0 shadow-card">
        <div className="border-b border-[var(--border)] bg-[var(--background)]/80 px-4 py-4 sm:px-5">
          <h3 className="text-base font-semibold text-[var(--text)]">Indicadores clave</h3>
          <p className="mt-1 text-sm text-muted">Cantidades dentro del mismo filtro utilizado más arriba.</p>
        </div>
        <div className="grid grid-cols-1 divide-y divide-[var(--border)] sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4 lg:divide-x">
          <div className="p-4 sm:p-5 lg:border-r-0">
            <p className="text-xs font-semibold uppercase tracking-wide text-muted">Total estudiantes (universo)</p>
            <p className="mt-2 text-2xl font-semibold tabular-nums text-[var(--text)]">{total_estudiantes}</p>
          </div>
          <div className="p-4 sm:p-5">
            <p className="text-xs font-semibold uppercase tracking-wide text-muted">Riesgo alto</p>
            <p className="mt-2 text-2xl font-semibold tabular-nums text-[var(--danger)]">{riesgos_por_nivel?.alto ?? 0}</p>
            <p className="mt-1 text-xs text-muted">{porcentajes_riesgo?.alto ?? 0}% del total con índice</p>
          </div>
          <div className="p-4 sm:p-5 lg:border-l lg:border-[var(--border)]">
            <p className="text-xs font-semibold uppercase tracking-wide text-muted">Riesgo medio</p>
            <p className="mt-2 text-2xl font-semibold tabular-nums text-warning">{riesgos_por_nivel?.medio ?? 0}</p>
            <p className="mt-1 text-xs text-muted">{porcentajes_riesgo?.medio ?? 0}% del total con índice</p>
          </div>
          <div className="p-4 sm:p-5">
            <p className="text-xs font-semibold uppercase tracking-wide text-muted">Riesgo bajo</p>
            <p className="mt-2 text-2xl font-semibold tabular-nums text-[var(--success)]">{riesgos_por_nivel?.bajo ?? 0}</p>
            <p className="mt-1 text-xs text-muted">{porcentajes_riesgo?.bajo ?? 0}% del total con índice</p>
          </div>
        </div>
      </Card>

      <Card className="space-y-4 border-[var(--border)] shadow-card">
        <h3 className="text-base font-semibold text-[var(--text)]">Distribución por nivel de riesgo</h3>
        <p className="text-sm text-muted">
          Barras proporcionales a los mismos porcentajes de las tarjetas anteriores (entre estudiantes con índice
          disponible).
        </p>
        <div className="grid gap-3 sm:grid-cols-3" data-testid="dashboard-distribucion-barras">
          <BarraPct
            etiqueta="Alto"
            porcentaje={porcentajes_riesgo?.alto}
            colorClass="bg-[var(--danger)]"
          />
          <BarraPct
            etiqueta="Medio"
            porcentaje={porcentajes_riesgo?.medio}
            colorClass="bg-warning"
          />
          <BarraPct
            etiqueta="Bajo"
            porcentaje={porcentajes_riesgo?.bajo}
            colorClass="bg-[var(--success)]"
          />
        </div>
      </Card>

      <Card className="space-y-4 border-[var(--border)] shadow-card">
        <div>
          <h3 className="text-base font-semibold text-[var(--text)]">Alertas por estado</h3>
          <p className="mt-1 text-sm text-muted">Totales después de aplicar filtros iguales a los indicadores superiores.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          {['pendiente', 'en_atencion', 'cerrada'].map((clave) => (
            <Badge key={clave} variant={alertaVariant(clave)} className="px-3 py-2 normal-case">
              {`${etiquetaAlerta(clave)}: ${alertas_por_estado?.[clave] ?? 0} (${porcentajes_alertas?.[clave] ?? 0}%)`}
            </Badge>
          ))}
        </div>
      </Card>

      <div className="space-y-3">
        <div>
          <h3 className="text-base font-semibold text-[var(--text)]">Últimos índices de riesgo</h3>
          <p className="mt-1 text-sm text-muted">Lista acorde al informe cuando exporte PDF desde el botón superior.</p>
        </div>
        {(ultimos_riesgos?.length ?? 0) === 0 ? (
          <EmptyState
            title="Sin registros de riesgo"
            description="No hay estudiantes con índice en el universo filtrado."
          />
        ) : (
          <Card padding={false} className="overflow-x-auto">
            <table className="min-w-full text-left text-sm text-[var(--text)]">
              <thead className="border-b border-[var(--border)] bg-[var(--background)] text-xs uppercase text-muted">
                <tr>
                  <th className="px-3 py-2">Estudiante</th>
                  <th className="px-3 py-2">Código</th>
                  <th className="px-3 py-2">Índice</th>
                  <th className="px-3 py-2">Nivel</th>
                  <th className="px-3 py-2">Fecha</th>
                  <th className="px-3 py-2">Año / Bim.</th>
                </tr>
              </thead>
              <tbody>
                {ultimos_riesgos.map((fila) => (
                  <tr key={fila.id} className="border-b border-[var(--border)]/70 last:border-0">
                    <td className="px-3 py-2">{fila.estudiante || '—'}</td>
                    <td className="px-3 py-2 font-mono text-xs">{fila.codigo || '—'}</td>
                    <td className="px-3 py-2">{fila.indice?.toFixed?.(4) ?? fila.indice}</td>
                    <td className="px-3 py-2">
                      <Badge variant={riesgoVariant(fila.nivel)}>{fila.nivel}</Badge>
                    </td>
                    <td className="px-3 py-2 text-xs text-muted">
                      {fila.fecha ? new Date(fila.fecha).toLocaleString() : '—'}
                    </td>
                    <td className="px-3 py-2 text-xs">
                      {fila.anio_escolar ?? '—'} / {fila.bimestre ?? '—'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </Card>
        )}
      </div>
    </section>
  );
}
