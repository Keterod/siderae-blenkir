import { useEffect, useState } from 'react';
import { getHistorialRiesgo } from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const NIVEL_VARIANT = {
  Alto: 'danger',
  Medio: 'warning',
  Bajo: 'success',
};

function fechaLegible(valor) {
  if (!valor) {
    return '—';
  }
  const d = new Date(valor);
  return Number.isNaN(d.getTime()) ? String(valor) : d.toLocaleDateString('es-PE');
}

function formatearVariables(variables) {
  if (variables === null || variables === undefined) {
    return '—';
  }

  if (Array.isArray(variables)) {
    return variables.length ? variables.join(', ') : '—';
  }

  if (typeof variables === 'object') {
    const claves = Object.entries(variables)
      .filter(([, v]) => Boolean(v))
      .map(([k]) => String(k));
    return claves.length ? claves.join(', ') : '—';
  }

  return String(variables);
}

export default function EstudiantePerfilHistorialRiesgo({ estudianteId }) {
  const [historial, setHistorial] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);

  useEffect(() => {
    const recargar = () => {
      if (estudianteId) {
        setRefreshKey((k) => k + 1);
      }
    };
    window.addEventListener('siderae-riesgo-procesado', recargar);
    return () => window.removeEventListener('siderae-riesgo-procesado', recargar);
  }, [estudianteId]);

  useEffect(() => {
    let cancelado = false;
    setCargando(true);
    setError(null);
    setHistorial([]);

    getHistorialRiesgo(estudianteId)
      .then((data) => {
        if (!cancelado) {
          setHistorial(Array.isArray(data?.historial) ? data.historial : []);
        }
      })
      .catch((err) => {
        if (!cancelado) {
          setError(
            err.status === 403
              ? 'Sin permiso para ver el historial de riesgo.'
              : 'No se pudo cargar el historial de riesgo.',
          );
        }
      })
      .finally(() => {
        if (!cancelado) {
          setCargando(false);
        }
      });

    return () => {
      cancelado = true;
    };
  }, [estudianteId, refreshKey]);

  if (cargando) {
    return (
      <Card className="space-y-3 border-[var(--border)]" data-testid="perfil-historial-riesgo-cargando">
        <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Historial de riesgo académico</h3>
        <LoadingState message="Cargando historial…" />
      </Card>
    );
  }

  if (error) {
    return (
      <Card className="border-[var(--border)]" data-testid="perfil-historial-riesgo-error">
        <AlertMessage variant="warning">{error}</AlertMessage>
      </Card>
    );
  }

  return (
    <Card
      className="space-y-4 border-[var(--border)] ring-1 ring-[var(--border)]/70"
      data-testid="perfil-historial-riesgo"
    >
      <div className="border-b border-[var(--border)]/80 pb-4">
        <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Historial de riesgo académico</h3>
        <p className="mt-1.5 text-sm leading-relaxed text-muted">
          Evolución del índice de riesgo por periodo. Se muestran registros existentes; no se recalcula ni predice riesgo.
        </p>
      </div>

      {historial.length === 0 ? (
        <EmptyState
          title="Sin historial de riesgo"
          description="No hay registros de riesgo previos para este estudiante."
        />
      ) : (
        <div className="overflow-x-auto rounded-lg border border-[var(--border)]">
          <table className="min-w-full divide-y divide-[var(--border)] text-sm">
            <thead className="bg-[var(--background)]/50">
              <tr>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Fecha
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Año escolar
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Bimestre
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Índice
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Nivel
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Variables utilizadas
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[var(--border)]">
              {historial.map((item) => (
                <tr key={item.id} data-testid={`historial-riesgo-${item.id}`}>
                  <td className="whitespace-nowrap px-4 py-3 tabular-nums">{fechaLegible(item.fecha)}</td>
                  <td className="px-4 py-3">{item.anio_escolar ?? '—'}</td>
                  <td className="px-4 py-3">{item.bimestre ?? '—'}</td>
                  <td className="px-4 py-3 tabular-nums">{item.indice ?? '—'}</td>
                  <td className="px-4 py-3">
                    <Badge variant={NIVEL_VARIANT[item.nivel] ?? 'neutral'} className="normal-case">
                      {item.nivel ?? '—'}
                    </Badge>
                  </td>
                  <td className="px-4 py-3 text-muted">{formatearVariables(item.variables_utilizadas)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </Card>
  );
}
