import { useEffect, useState } from 'react';
import { getDashboard } from '../lib/api';

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
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);
  const [datos, setDatos] = useState(null);

  useEffect(() => {
    let cancelado = false;

    async function cargar() {
      setCargando(true);
      setError(null);
      try {
        const payload = await getDashboard();
        if (!cancelado) {
          setDatos(payload);
        }
      } catch (err) {
        if (!cancelado) {
          setError(err.message || 'No se pudo cargar el dashboard');
          setDatos(null);
        }
      } finally {
        if (!cancelado) {
          setCargando(false);
        }
      }
    }

    cargar();

    return () => {
      cancelado = true;
    };
  }, []);

  if (cargando) {
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

  const { total_estudiantes, riesgos_por_nivel, alertas_por_estado, ultimos_riesgos } = datos;
  const sinDatosOperativos =
    total_estudiantes === 0 &&
    (ultimos_riesgos?.length ?? 0) === 0 &&
    (riesgos_por_nivel?.alto ?? 0) + (riesgos_por_nivel?.medio ?? 0) + (riesgos_por_nivel?.bajo ?? 0) === 0;

  return (
    <section className="space-y-6 rounded-lg border border-slate-200 bg-[#F2F2F2] p-4 shadow-sm">
      <div>
        <h2 className="text-xl font-semibold text-[#333333]">Dashboard</h2>
        <p className="mt-1 text-sm text-[#88726B]">
          Resumen de estudiantes, distribución del último índice de riesgo por estudiante y estado de alertas.
        </p>
      </div>

      {sinDatosOperativos ? (
        <p className="rounded border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
          Aún no hay datos registrados. Los indicadores aparecerán cuando existan estudiantes e índices procesados.
        </p>
      ) : null}

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Total estudiantes</p>
          <p className="mt-1 text-2xl font-semibold text-[#333333]">{total_estudiantes}</p>
        </article>
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Riesgo alto</p>
          <p className="mt-1 text-2xl font-semibold text-[#DC2626]">{riesgos_por_nivel?.alto ?? 0}</p>
        </article>
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Riesgo medio</p>
          <p className="mt-1 text-2xl font-semibold text-[#F59E0B]">{riesgos_por_nivel?.medio ?? 0}</p>
        </article>
        <article className="rounded-lg border border-[#CCCCCC] bg-white p-4 shadow-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-[#88726B]">Riesgo bajo</p>
          <p className="mt-1 text-2xl font-semibold text-[#2FAF7B]">{riesgos_por_nivel?.bajo ?? 0}</p>
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
              {etiquetaAlerta(clave)}: {alertas_por_estado?.[clave] ?? 0}
            </span>
          ))}
        </div>
      </div>

      <div>
        <h3 className="mb-2 text-sm font-medium text-[#333333]">Últimos índices de riesgo registrados</h3>
        {(ultimos_riesgos?.length ?? 0) === 0 ? (
          <p className="rounded border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
            No hay registros de riesgo todavía.
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
