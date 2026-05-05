import { useState } from 'react';
import { getEstudiante, postProcesarRiesgo } from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';

function formatearFechaProcesamiento(valor) {
  if (!valor) {
    return '—';
  }
  const fecha = new Date(valor);
  if (Number.isNaN(fecha.getTime())) {
    return String(valor);
  }
  return fecha.toLocaleString('es-PE', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
}

function nivelABadgeVariant(nivel) {
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

export default function EstudiantePerfilRiesgo({ estudianteId, ultimoIndice, puedeProcesar, onDetalleRefrescado }) {
  const [procesando, setProcesando] = useState(false);
  const [error, setError] = useState(null);

  async function procesar() {
    setError(null);
    setProcesando(true);

    try {
      await postProcesarRiesgo(estudianteId, { bimestre: '1' });
      const detalle = await getEstudiante(estudianteId);
      onDetalleRefrescado(detalle);
    } catch (err) {
      if (err.status === 422 && err.payload?.errors) {
        const partes = Object.entries(err.payload.errors).flatMap(([clave, mensajes]) =>
          (Array.isArray(mensajes) ? mensajes : [String(mensajes)]).map((m) => `${clave}: ${m}`),
        );
        setError(partes.join(' ') || err.payload.message || 'Datos insuficientes.');
      } else if (err.status === 503 && err.payload?.message) {
        setError(err.payload.message);
      } else if (err.payload?.message) {
        setError(err.payload.message);
      } else {
        setError('No se pudo procesar el riesgo.');
      }
    } finally {
      setProcesando(false);
    }
  }

  return (
    <Card
      className="space-y-5 border-[var(--border)] ring-1 ring-[var(--border)]/70"
      data-testid="perfil-riesgo"
    >
      <div className="border-b border-[var(--border)]/80 pb-4">
        <h3 className="text-sm font-semibold uppercase tracking-wide text-muted">Riesgo académico</h3>
        <p className="mt-1.5 text-xs leading-relaxed text-muted">
          Último índice devuelto por el backend para este estudiante. No se inventa historial ni desgloses RF-19 mientras la API no
          los provea.
        </p>
      </div>

      {ultimoIndice ? (
        <dl className="grid gap-4 rounded-xl border border-[var(--border)] bg-[var(--background)]/55 p-4 text-sm shadow-inner sm:grid-cols-3">
          <div>
            <dt className="text-xs font-semibold uppercase tracking-wide text-muted">Índice</dt>
            <dd className="mt-1.5 text-lg font-semibold tabular-nums text-[var(--text)]">{ultimoIndice.indice}</dd>
          </div>
          <div>
            <dt className="text-xs font-semibold uppercase tracking-wide text-muted">Procesado</dt>
            <dd className="mt-1.5 font-medium text-[var(--text)]">{formatearFechaProcesamiento(ultimoIndice.created_at)}</dd>
          </div>
          <div>
            <dt className="text-xs font-semibold uppercase tracking-wide text-muted">Clasificación</dt>
            <dd className="mt-1.5">
              <Badge variant={nivelABadgeVariant(ultimoIndice.nivel)} className="normal-case">
                {ultimoIndice.nivel}
              </Badge>
            </dd>
          </div>
        </dl>
      ) : (
        <div className="rounded-lg border border-dashed border-[var(--border)] bg-[var(--background)]/30 px-4 py-6 text-center">
          <p className="text-sm text-muted">Aún no hay un cálculo de riesgo registrado para este estudiante.</p>
        </div>
      )}

      {puedeProcesar ? (
        <div className="flex flex-col gap-2 border-t border-[var(--border)]/80 pt-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
          <Button
            type="button"
            variant="primary"
            size="sm"
            disabled={procesando}
            onClick={() => {
              void procesar();
            }}
            data-testid="riesgo-procesar"
          >
            {procesando ? 'Procesando…' : 'Procesar riesgo'}
          </Button>
          <span className="text-xs text-muted" title="El prototipo mantiene el mismo cuerpo de solicitud hacia backend.">
            Se envía bimestre <span className="font-mono font-medium">1</span> (contrato preservado).
          </span>
        </div>
      ) : (
        <p className="text-xs text-muted">Sin permiso para ejecutar el procesamiento de riesgo.</p>
      )}

      {error ? (
        <AlertMessage variant="error" className="text-sm">
          {error}
        </AlertMessage>
      ) : null}
    </Card>
  );
}
