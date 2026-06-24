import { useEffect, useState } from 'react';
import { getDashboardInstitucional } from '../../lib/api';
import { useAuth } from '../../context/AuthContext';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

function riesgoVariant(nivel) {
  if (nivel === 'Alto') {
    return 'danger';
  }
  if (nivel === 'Medio') {
    return 'warning';
  }
  if (nivel === 'Bajo') {
    return 'success';
  }
  return 'neutral';
}

const FILTROS_INICIALES = {
  anio_escolar: '',
  bimestre: '',
  grado: '',
  seccion: '',
};

function DashboardInstitucionalContent() {
  const { permissions } = useAuth();
  const [appliedFilters, setAppliedFilters] = useState(FILTROS_INICIALES);
  const [draftFilters, setDraftFilters] = useState(FILTROS_INICIALES);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);
  const [datos, setDatos] = useState(null);

  const puedeVerReportes = permissions.includes('ver_reportes_riesgo');

  useEffect(() => {
    let active = true;

    getDashboardInstitucional(appliedFilters)
      .then((payload) => {
        if (!active) return;
        setDatos(payload);
        setError(null);
      })
      .catch((err) => {
        if (!active) return;
        setError(err.message || 'No se pudo cargar el dashboard institucional');
        setDatos(null);
      })
      .finally(() => {
        if (active) {
          setCargando(false);
        }
      });

    return () => {
      active = false;
    };
  }, [appliedFilters]);

  const handleAplicar = () => {
    setCargando(true);
    setAppliedFilters({ ...draftFilters });
  };

  const handleLimpiar = () => {
    setCargando(true);
    setDraftFilters(FILTROS_INICIALES);
    setAppliedFilters(FILTROS_INICIALES);
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      handleAplicar();
    }
  };

  if (cargando && !datos) {
    return (
      <Card>
        <LoadingState label="Cargando dashboard institucional…" />
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
        <EmptyState
          title="Sin datos disponibles"
          description="No hubo respuesta del servidor para armar los indicadores institucionales."
        />
      </Card>
    );
  }

  const { resumen, completitud, por_grado_seccion: porGradoSeccion, ultimos_riesgos: ultimosRiesgos } = datos;
  const sinDatosGlobales =
    (resumen?.total_estudiantes ?? 0) === 0 &&
    (ultimosRiesgos?.length ?? 0) === 0;

  const hayFiltrosActivos = Object.values(appliedFilters).some((v) => v !== '');

  const inputClass =
    'mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-2 py-1.5 text-sm text-[var(--text)] outline-none transition focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-1';

  return (
    <section className="space-y-6" data-testid="dashboard-institucional-panel">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">Dashboard institucional</h2>
          <p className="mt-1 max-w-3xl text-sm leading-relaxed text-muted">
            Métricas agregadas de riesgo académico por grado y sección. Alcance V1: sede Chilca, datos existentes,
            sin recalcular riesgo ni llamar a modelos externos.
          </p>
        </div>
        {puedeVerReportes ? (
          <Button
            type="button"
            variant="secondary"
            size="sm"
            data-testid="dashboard-institucional-ir-reportes"
            onClick={() => window.dispatchEvent(new CustomEvent('siderae-nav-reportes-riesgo'))}
          >
            Ir a Reportes de riesgo
          </Button>
        ) : null}
      </div>

      <Card className="shadow-card !border-[var(--border)] !p-5 sm:!p-6">
        <div className="mb-5 border-b border-[var(--border)] pb-4">
          <h3 className="text-base font-semibold text-[var(--text)]">Filtros</h3>
          <p className="mt-1.5 text-sm text-muted">Acote el dashboard por año escolar, bimestre, grado o sección.</p>
        </div>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <label className="block text-xs text-muted">
            Año escolar
            <input
              type="text"
              inputMode="numeric"
              className={inputClass}
              value={draftFilters.anio_escolar}
              onChange={(e) => setDraftFilters((p) => ({ ...p, anio_escolar: e.target.value }))}
              onKeyDown={handleKeyDown}
              placeholder="Ej: 2026"
            />
          </label>
          <label className="block text-xs text-muted">
            Bimestre
            <input
              type="text"
              inputMode="numeric"
              className={inputClass}
              value={draftFilters.bimestre}
              onChange={(e) => setDraftFilters((p) => ({ ...p, bimestre: e.target.value }))}
              onKeyDown={handleKeyDown}
              placeholder="Ej: 2"
            />
          </label>
          <label className="block text-xs text-muted">
            Grado
            <input
              type="text"
              className={inputClass}
              value={draftFilters.grado}
              onChange={(e) => setDraftFilters((p) => ({ ...p, grado: e.target.value }))}
              onKeyDown={handleKeyDown}
              placeholder="Ej: 5°"
            />
          </label>
          <label className="block text-xs text-muted">
            Sección
            <input
              type="text"
              className={inputClass}
              value={draftFilters.seccion}
              onChange={(e) => setDraftFilters((p) => ({ ...p, seccion: e.target.value }))}
              onKeyDown={handleKeyDown}
              placeholder="Ej: A"
            />
          </label>
        </div>

        <div className="mt-4 flex flex-wrap gap-2">
          <Button
            type="button"
            variant="primary"
            size="sm"
            data-testid="dashboard-institucional-aplicar"
            onClick={handleAplicar}
            disabled={cargando}
          >
            Buscar
          </Button>
          <Button
            type="button"
            variant="outline"
            size="sm"
            data-testid="dashboard-institucional-limpiar"
            onClick={handleLimpiar}
            disabled={cargando}
          >
            Limpiar filtros
          </Button>
        </div>

        {hayFiltrosActivos ? (
          <p className="mt-3 rounded-md bg-[var(--background)] px-3 py-2 text-sm text-[var(--text)]">
            <span className="font-medium text-muted">Filtros aplicados: </span>
            <span>
              {Object.entries(appliedFilters)
                .filter(([, v]) => v !== '')
                .map(([k, v]) => `${k}=${v}`)
                .join(' · ')}
            </span>
          </p>
        ) : null}
      </Card>

      {cargando ? <p className="text-sm text-muted">Actualizando datos…</p> : null}

      {sinDatosGlobales ? (
        <AlertMessage variant="info">
          {hayFiltrosActivos
            ? 'No hay estudiantes que coincidan con los filtros seleccionados.'
            : 'Aún no hay datos registrados. Los indicadores aparecerán cuando existan estudiantes e índices procesados.'}
        </AlertMessage>
      ) : null}

      <Card className="overflow-hidden !border-[var(--border)] !p-0 shadow-card">
        <div className="border-b border-[var(--border)] bg-[var(--background)]/80 px-4 py-4 sm:px-5">
          <h3 className="text-base font-semibold text-[var(--text)]">Resumen institucional</h3>
          <p className="mt-1 text-sm text-muted">Totales dentro del alcance filtrado.</p>
        </div>
        <div className="grid grid-cols-1 divide-y divide-[var(--border)] sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-5 lg:divide-x">
          {[
            { label: 'Total estudiantes', valor: resumen?.total_estudiantes ?? 0 },
            { label: 'Con riesgo', valor: resumen?.con_riesgo ?? 0 },
            { label: 'Riesgo bajo', valor: resumen?.riesgo_bajo ?? 0, color: 'text-[var(--success)]' },
            { label: 'Riesgo medio', valor: resumen?.riesgo_medio ?? 0, color: 'text-warning' },
            { label: 'Riesgo alto', valor: resumen?.riesgo_alto ?? 0, color: 'text-[var(--danger)]' },
          ].map(({ label, valor, color }) => (
            <div key={label} className="p-4 sm:p-5">
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">{label}</p>
              <p className={`mt-2 text-2xl font-semibold tabular-nums ${color ?? 'text-[var(--text)]'}`}>{valor}</p>
            </div>
          ))}
        </div>
      </Card>

      <Card className="overflow-hidden !border-[var(--border)] !p-0 shadow-card">
        <div className="border-b border-[var(--border)] bg-[var(--background)]/80 px-4 py-4 sm:px-5">
          <h3 className="text-base font-semibold text-[var(--text)]">Completitud de riesgo</h3>
          <p className="mt-1 text-sm text-muted">Estudiantes con al menos un índice de riesgo registrado.</p>
        </div>
        <div className="grid grid-cols-1 divide-y divide-[var(--border)] sm:grid-cols-3 sm:divide-x sm:divide-y-0">
          {[
            { label: 'Con riesgo', valor: completitud?.con_riesgo ?? 0 },
            { label: 'Sin riesgo', valor: completitud?.sin_riesgo ?? 0 },
            { label: 'Porcentaje con riesgo', valor: `${completitud?.porcentaje_con_riesgo ?? 0}%` },
          ].map(({ label, valor }) => (
            <div key={label} className="p-4 sm:p-5">
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">{label}</p>
              <p className="mt-2 text-2xl font-semibold tabular-nums text-[var(--text)]">{valor}</p>
            </div>
          ))}
        </div>
      </Card>

      <Card className="overflow-hidden !border-[var(--border)] !p-0 shadow-card">
        <div className="border-b border-[var(--border)] bg-[var(--background)]/80 px-4 py-4 sm:px-5">
          <h3 className="text-base font-semibold text-[var(--text)]">Distribución por grado y sección</h3>
        </div>
        {(porGradoSeccion?.length ?? 0) === 0 ? (
          <EmptyState
            title="Sin datos para distribuir"
            description="No hay estudiantes con grado/sección en el universo filtrado."
          />
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full text-left text-sm text-[var(--text)]">
              <thead className="border-b border-[var(--border)] bg-[var(--background)] text-xs uppercase text-muted">
                <tr>
                  <th className="px-3 py-2">Grado</th>
                  <th className="px-3 py-2">Sección</th>
                  <th className="px-3 py-2">Total estudiantes</th>
                  <th className="px-3 py-2">Riesgo bajo</th>
                  <th className="px-3 py-2">Riesgo medio</th>
                  <th className="px-3 py-2">Riesgo alto</th>
                </tr>
              </thead>
              <tbody>
                {porGradoSeccion.map((fila, idx) => (
                  <tr key={`${fila.grado}-${fila.seccion}-${idx}`} className="border-b border-[var(--border)]/70 last:border-0">
                    <td className="px-3 py-2">{fila.grado || '—'}</td>
                    <td className="px-3 py-2">{fila.seccion || '—'}</td>
                    <td className="px-3 py-2">{fila.total_estudiantes ?? 0}</td>
                    <td className="px-3 py-2 text-[var(--success)]">{fila.riesgo_bajo ?? 0}</td>
                    <td className="px-3 py-2 text-warning">{fila.riesgo_medio ?? 0}</td>
                    <td className="px-3 py-2 text-[var(--danger)]">{fila.riesgo_alto ?? 0}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </Card>

      <div className="space-y-3">
        <div>
          <h3 className="text-base font-semibold text-[var(--text)]">Últimos índices de riesgo</h3>
          <p className="mt-1 text-sm text-muted">Registros más recientes dentro del alcance filtrado.</p>
        </div>
        {(ultimosRiesgos?.length ?? 0) === 0 ? (
          <EmptyState title="Sin registros de riesgo" description="No hay índices en el universo filtrado." />
        ) : (
          <Card padding={false} className="overflow-x-auto">
            <table className="min-w-full text-left text-sm text-[var(--text)]">
              <thead className="border-b border-[var(--border)] bg-[var(--background)] text-xs uppercase text-muted">
                <tr>
                  <th className="px-3 py-2">Estudiante</th>
                  <th className="px-3 py-2">Grado</th>
                  <th className="px-3 py-2">Sección</th>
                  <th className="px-3 py-2">Año escolar</th>
                  <th className="px-3 py-2">Bimestre</th>
                  <th className="px-3 py-2">Índice</th>
                  <th className="px-3 py-2">Nivel</th>
                  <th className="px-3 py-2">Fecha</th>
                </tr>
              </thead>
              <tbody>
                {ultimosRiesgos.map((fila) => (
                  <tr key={`${fila.estudiante_id}-${fila.fecha}-${fila.indice}`} className="border-b border-[var(--border)]/70 last:border-0">
                    <td className="px-3 py-2">{fila.estudiante || '—'}</td>
                    <td className="px-3 py-2">{fila.grado || '—'}</td>
                    <td className="px-3 py-2">{fila.seccion || '—'}</td>
                    <td className="px-3 py-2">{fila.anio_escolar ?? '—'}</td>
                    <td className="px-3 py-2">{fila.bimestre ?? '—'}</td>
                    <td className="px-3 py-2 font-mono text-xs">{fila.indice?.toFixed?.(4) ?? fila.indice}</td>
                    <td className="px-3 py-2">
                      <Badge variant={riesgoVariant(fila.nivel)}>{fila.nivel}</Badge>
                    </td>
                    <td className="px-3 py-2 text-xs text-muted">{fila.fecha || '—'}</td>
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

export default function DashboardInstitucionalPanel() {
  const { permissions } = useAuth();
  const tienePermiso = permissions.includes('ver_dashboard_institucional');

  if (!tienePermiso) {
    return (
      <Card className="border-red-200 bg-red-50/40">
        <h2 className="text-lg font-semibold text-red-950">Acceso restringido</h2>
        <AlertMessage variant="error" className="mt-2">
          No tiene permiso para ver el dashboard institucional. Contacte al administrador del sistema.
        </AlertMessage>
      </Card>
    );
  }

  return <DashboardInstitucionalContent />;
}
