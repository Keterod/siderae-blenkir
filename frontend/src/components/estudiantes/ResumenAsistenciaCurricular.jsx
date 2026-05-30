import { useCallback, useEffect, useState } from 'react';
import { getAsistenciaDiariaResumen } from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import { etiquetaEstadoAsistencia, mensajeErrorAsistenciaApi } from '../curricular/asistencia/asistenciaUtils';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

function formatearPorcentaje(valor) {
  if (valor === null || valor === undefined || Number.isNaN(Number(valor))) {
    return '—';
  }
  return `${Number(valor).toFixed(1)}%`;
}

export default function ResumenAsistenciaCurricular({ estudianteId, anioEscolarPorDefecto }) {
  const [filtros, setFiltros] = useState({
    anio_escolar: anioEscolarPorDefecto || anioEscolarActual(),
    fecha_desde: '',
    fecha_hasta: '',
  });
  const [resumen, setResumen] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(false);

  const cargarResumen = useCallback(async () => {
    if (!estudianteId) {
      return;
    }

    setCargando(true);
    setError(null);

    const params = {
      estudiante_id: estudianteId,
      anio_escolar: filtros.anio_escolar.trim(),
    };
    if (filtros.fecha_desde) {
      params.fecha_desde = filtros.fecha_desde;
    }
    if (filtros.fecha_hasta) {
      params.fecha_hasta = filtros.fecha_hasta;
    }

    try {
      const data = await getAsistenciaDiariaResumen(params);
      setResumen(data);
    } catch (e) {
      setResumen(null);
      setError(mensajeErrorAsistenciaApi(e));
    } finally {
      setCargando(false);
    }
  }, [estudianteId, filtros]);

  useEffect(() => {
    setFiltros((prev) => ({
      ...prev,
      anio_escolar: anioEscolarPorDefecto || prev.anio_escolar || anioEscolarActual(),
    }));
  }, [anioEscolarPorDefecto]);

  useEffect(() => {
    void cargarResumen();
  }, [cargarResumen]);

  if (!estudianteId) {
    return null;
  }

  const totales = resumen?.totales;
  const totalRegistros = totales?.total_registros ?? 0;
  const porcentaje = Number(totales?.porcentaje_asistencia_efectiva ?? 0);
  const porcentajeBarra = Math.max(0, Math.min(100, porcentaje));

  const contadores = [
    { key: 'presente', valor: totales?.presente ?? 0 },
    { key: 'tarde', valor: totales?.tarde ?? 0 },
    { key: 'falta', valor: totales?.falta ?? 0 },
    { key: 'justificado', valor: totales?.justificado ?? 0 },
  ];

  return (
    <div className="space-y-4" data-testid="perfil-resumen-asistencia-curricular">
      <Card className="border-[var(--border)]/90 bg-[var(--background)]/40 p-4 shadow-sm">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Año escolar</span>
            <input
              type="text"
              className="sb-field min-w-0"
              value={filtros.anio_escolar}
              onChange={(ev) => setFiltros((prev) => ({ ...prev, anio_escolar: ev.target.value }))}
            />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Fecha desde (opcional)</span>
            <input
              type="date"
              className="sb-field min-w-0"
              value={filtros.fecha_desde}
              onChange={(ev) => setFiltros((prev) => ({ ...prev, fecha_desde: ev.target.value }))}
            />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Fecha hasta (opcional)</span>
            <input
              type="date"
              className="sb-field min-w-0"
              value={filtros.fecha_hasta}
              onChange={(ev) => setFiltros((prev) => ({ ...prev, fecha_hasta: ev.target.value }))}
            />
          </label>
          <div className="flex items-end">
            <Button type="button" size="sm" variant="outline" disabled={cargando} onClick={() => void cargarResumen()}>
              {cargando ? 'Actualizando…' : 'Actualizar resumen'}
            </Button>
          </div>
        </div>
        <p className="mt-3 text-xs text-muted">
          Sin rango de fechas se consideran todos los registros del año escolar indicado.
        </p>
      </Card>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}

      {cargando ? <LoadingState label="Cargando asistencia curricular…" /> : null}

      {!cargando && !error && resumen ? (
        totalRegistros === 0 ? (
          <EmptyState
            title="Sin asistencia registrada"
            description="Aún no hay asistencia curricular registrada para este estudiante."
          />
        ) : (
          <Card className="space-y-5 border-[var(--border)] p-5 shadow-sm">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <p className="text-sm font-medium text-muted">Asistencia efectiva</p>
                <p className="mt-1 text-4xl font-semibold tabular-nums tracking-tight text-[var(--text)]">
                  {formatearPorcentaje(porcentaje)}
                </p>
              </div>
              <p className="text-sm text-muted">
                Basado en <span className="font-semibold text-[var(--text)]">{totalRegistros}</span> registro
                {totalRegistros === 1 ? '' : 's'} de asistencia diaria.
              </p>
            </div>

            <div
              className="h-3 w-full overflow-hidden rounded-full bg-[var(--background)]"
              role="progressbar"
              aria-valuenow={porcentajeBarra}
              aria-valuemin={0}
              aria-valuemax={100}
              aria-label="Porcentaje de asistencia efectiva"
            >
              <div
                className="h-full rounded-full bg-[var(--primary)] transition-all duration-300"
                style={{ width: `${porcentajeBarra}%` }}
              />
            </div>

            <div className="flex flex-wrap gap-2">
              {contadores.map((item) => (
                <span
                  key={item.key}
                  className="inline-flex items-center gap-1.5 rounded-full border border-[var(--border)] bg-[var(--surface)] px-3 py-1.5 text-sm"
                >
                  <span className="text-muted">{etiquetaEstadoAsistencia(item.key)}:</span>
                  <span className="font-semibold tabular-nums text-[var(--text)]">{item.valor}</span>
                </span>
              ))}
            </div>
          </Card>
        )
      ) : null}
    </div>
  );
}
