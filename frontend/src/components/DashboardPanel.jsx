import { useCallback, useEffect, useState } from 'react';
import { exportDashboardPdf, getDashboard } from '../lib/api';

function badgeRiesgoClases(nivel) {
  if (nivel === 'alto') {
    return 'bg-red-100 text-red-800 border-red-200';
  }
  if (nivel === 'medio') {
    return 'bg-amber-100 text-amber-900 border-amber-200';
  }
  if (nivel === 'bajo') {
    return 'bg-emerald-100 text-emerald-800 border-emerald-200';
  }
  return 'bg-slate-100 text-slate-700 border-slate-200';
}

function badgeAlertaClases(estadoClave) {
  if (estadoClave === 'pendiente') {
    return 'bg-orange-100 text-orange-900 border-orange-200';
  }
  if (estadoClave === 'en_atencion') {
    return 'bg-amber-100 text-amber-900 border-amber-200';
  }
  if (estadoClave === 'cerrada') {
    return 'bg-emerald-100 text-emerald-800 border-emerald-200';
  }
  return 'bg-slate-100 text-slate-600 border-slate-200';
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

export default function DashboardPanel() {
  const [appliedFilters, setAppliedFilters] = useState({});
  const [draftFilters, setDraftFilters] = useState({});
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
    setAppliedFilters({ ...draftFilters });
  };

  const handleLimpiar = () => {
    setDraftFilters({});
    setAppliedFilters({});
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
      setExportError(
        err.payload?.message || err.message || 'No se pudo generar el PDF'
      );
    } finally {
      setExportando(false);
    }
  };

  if (cargando && !datos) {
    return (
      <section className="space-y-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p className="text-sm text-slate-600">Cargando dashboard...</p>
      </section>
    );
  }

  if (error) {
    return (
      <section className="space-y-4 rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
        <h2 className="text-lg font-medium text-red-800">Error</h2>
        <p className="text-sm text-red-700">{error}</p>
      </section>
    );
  }

  if (!datos) {
    return (
      <section className="space-y-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p className="text-sm text-slate-600">Sin datos disponibles.</p>
      </section>
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
  } = datos;

  const sinResultadosFiltros =
    total_estudiantes === 0 &&
    (ultimos_riesgos?.length ?? 0) === 0 &&
    Object.keys(appliedFilters).length > 0;

  const sinDatosOperativosGlobales =
    total_estudiantes === 0 &&
    (ultimos_riesgos?.length ?? 0) === 0 &&
    (riesgos_por_nivel?.alto ?? 0) + (riesgos_por_nivel?.medio ?? 0) + (riesgos_por_nivel?.bajo ?? 0) === 0 &&
    Object.keys(appliedFilters).length === 0;

  const opc = opciones || {};
  const mostrarSede = Array.isArray(opc.sedes) && opc.sedes.length > 0;
  const mostrarNivelEdu = Array.isArray(opc.niveles) && opc.niveles.length > 0;
  const mostrarGrado = Array.isArray(opc.grados) && opc.grados.length > 0;
  const mostrarSeccion = Array.isArray(opc.secciones) && opc.secciones.length > 0;
  const mostrarNivelRiesgo = Array.isArray(opc.niveles_riesgo) && opc.niveles_riesgo.length > 0;

  return (
    <section className="space-y-6 rounded-lg border border-slate-200 bg-[#F2F2F2] p-4 shadow-sm">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="text-xl font-semibold text-[#333333]">Dashboard</h2>
          <p className="mt-1 text-sm text-[#88726B]">
            Indicadores filtrables según sede, nivel educativo, grado, sección y nivel de riesgo (último índice por
            estudiante).
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button
            type="button"
            onClick={handleExportarPdf}
            disabled={exportando || cargando}
            className="rounded border border-[#1E63B5] bg-white px-3 py-1.5 text-sm text-[#1E63B5] disabled:opacity-50"
          >
            {exportando ? 'Exportando PDF…' : 'Exportar PDF'}
          </button>
        </div>
      </div>

      {exportError ? (
        <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{exportError}</p>
      ) : null}

      <div className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
        <h3 className="mb-3 text-sm font-medium text-[#333333]">Filtros</h3>
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {mostrarSede ? (
            <label className="block text-xs text-[#88726B]">
              Sede
              <select
                className="mt-1 w-full rounded border border-[#CCCCCC] px-2 py-1.5 text-sm text-slate-800"
                value={draftFilters.sede ?? ''}
                onChange={(e) => setDraftFilters((p) => ({ ...p, sede: e.target.value || undefined }))}
              >
                <option value="">Todas</option>
                {opc.sedes.map((s) => (
                  <option key={s} value={s}>
                    {s}
                  </option>
                ))}
              </select>
            </label>
          ) : null}

          {mostrarNivelEdu ? (
            <label className="block text-xs text-[#88726B]">
              Nivel educativo
              <select
                className="mt-1 w-full rounded border border-[#CCCCCC] px-2 py-1.5 text-sm text-slate-800"
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
            <label className="block text-xs text-[#88726B]">
              Grado
              <select
                className="mt-1 w-full rounded border border-[#CCCCCC] px-2 py-1.5 text-sm text-slate-800"
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
            <label className="block text-xs text-[#88726B]">
              Sección
              <select
                className="mt-1 w-full rounded border border-[#CCCCCC] px-2 py-1.5 text-sm text-slate-800"
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
            <label className="block text-xs text-[#88726B]">
              Nivel de riesgo (último índice)
              <select
                className="mt-1 w-full rounded border border-[#CCCCCC] px-2 py-1.5 text-sm text-slate-800"
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
          <button
            type="button"
            onClick={handleAplicar}
            className="rounded bg-[#F05A0E] px-3 py-1.5 text-sm font-medium text-white hover:bg-[#F47C2A]"
          >
            Aplicar filtros
          </button>
          <button
            type="button"
            onClick={handleLimpiar}
            className="rounded border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700"
          >
            Limpiar filtros
          </button>
        </div>

        {Object.keys(filtrosDelBackend || {}).length > 0 ? (
          <p className="mt-2 text-xs text-[#88726B]">
            Filtros activos en servidor:{' '}
            <span className="font-mono text-slate-700">{JSON.stringify(filtrosDelBackend)}</span>
          </p>
        ) : null}
      </div>

      {cargando ? (
        <p className="text-sm text-slate-600">Actualizando datos…</p>
      ) : null}

      {sinDatosOperativosGlobales ? (
        <p className="rounded border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
          Aún no hay datos registrados. Los indicadores aparecerán cuando existan estudiantes e índices procesados.
        </p>
      ) : null}

      {sinResultadosFiltros ? (
        <p className="rounded border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
          No hay estudiantes que coincidan con los filtros seleccionados.
        </p>
      ) : null}

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Total estudiantes (universo)</p>
          <p className="mt-1 text-2xl font-semibold text-[#333333]">{total_estudiantes}</p>
        </article>
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Riesgo alto</p>
          <p className="mt-1 text-2xl font-semibold text-[#DC2626]">{riesgos_por_nivel?.alto ?? 0}</p>
          <p className="mt-1 text-xs text-[#88726B]">{porcentajes_riesgo?.alto ?? 0}% del total con índice</p>
        </article>
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Riesgo medio</p>
          <p className="mt-1 text-2xl font-semibold text-[#F59E0B]">{riesgos_por_nivel?.medio ?? 0}</p>
          <p className="mt-1 text-xs text-[#88726B]">{porcentajes_riesgo?.medio ?? 0}% del total con índice</p>
        </article>
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Riesgo bajo</p>
          <p className="mt-1 text-2xl font-semibold text-[#2FAF7B]">{riesgos_por_nivel?.bajo ?? 0}</p>
          <p className="mt-1 text-xs text-[#88726B]">{porcentajes_riesgo?.bajo ?? 0}% del total con índice</p>
        </article>
      </div>

      <div>
        <h3 className="mb-2 text-sm font-medium text-[#333333]">Alertas por estado</h3>
        <div className="flex flex-wrap gap-2">
          {['pendiente', 'en_atencion', 'cerrada'].map((clave) => (
            <span
              key={clave}
              className={`inline-flex items-center rounded-full border px-3 py-1 text-sm ${badgeAlertaClases(clave)}`}
            >
              {etiquetaAlerta(clave)}: {alertas_por_estado?.[clave] ?? 0} (
              {porcentajes_alertas?.[clave] ?? 0}%)
            </span>
          ))}
        </div>
      </div>

      <div>
        <h3 className="mb-2 text-sm font-medium text-[#333333]">Últimos índices de riesgo registrados</h3>
        {(ultimos_riesgos?.length ?? 0) === 0 ? (
          <p className="rounded border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
            No hay registros de riesgo en el universo filtrado.
          </p>
        ) : (
          <div className="overflow-x-auto rounded-lg border border-[#CCCCCC] bg-white">
            <table className="min-w-full text-left text-sm text-slate-800">
              <thead className="border-b border-[#CCCCCC] bg-slate-50 text-xs uppercase text-[#88726B]">
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
                  <tr key={fila.id} className="border-b border-slate-100 last:border-0">
                    <td className="px-3 py-2">{fila.estudiante || '—'}</td>
                    <td className="px-3 py-2 font-mono text-xs">{fila.codigo || '—'}</td>
                    <td className="px-3 py-2">{fila.indice?.toFixed?.(4) ?? fila.indice}</td>
                    <td className="px-3 py-2">
                      <span
                        className={`inline-flex rounded-full border px-2 py-0.5 text-xs font-medium capitalize ${badgeRiesgoClases(fila.nivel)}`}
                      >
                        {fila.nivel}
                      </span>
                    </td>
                    <td className="px-3 py-2 text-xs text-slate-600">
                      {fila.fecha ? new Date(fila.fecha).toLocaleString() : '—'}
                    </td>
                    <td className="px-3 py-2 text-xs">
                      {fila.anio_escolar ?? '—'} / {fila.bimestre ?? '—'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </section>
  );
}
