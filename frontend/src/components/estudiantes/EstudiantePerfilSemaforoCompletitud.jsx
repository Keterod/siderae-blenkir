import { useEffect, useState } from 'react';
import { getSemaforoCompletitud } from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Card from '../ui/Card';

const COLOR_STYLES = {
  verde: {
    border: 'border-green-200',
    bg: 'bg-green-50',
    text: 'text-green-900',
    dot: 'bg-green-500',
    label: 'Datos suficientes',
  },
  amarillo: {
    border: 'border-amber-200',
    bg: 'bg-amber-50',
    text: 'text-amber-950',
    dot: 'bg-amber-500',
    label: 'Datos parciales',
  },
  rojo: {
    border: 'border-red-200',
    bg: 'bg-red-50',
    text: 'text-red-900',
    dot: 'bg-red-500',
    label: 'Datos insuficientes',
  },
};

function formatearDato(dato) {
  const map = {
    notas_curriculares: 'Notas curriculares',
    asistencia_curricular: 'Asistencia curricular',
    reportes_conductuales: 'Reportes conductuales',
    indice_riesgo: 'Índice de riesgo',
  };
  return map[dato] ?? dato;
}

export default function EstudiantePerfilSemaforoCompletitud({ estudianteId }) {
  const [semaforo, setSemaforo] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let cancelado = false;
    setCargando(true);
    setError(null);
    setSemaforo(null);

    getSemaforoCompletitud(estudianteId)
      .then((data) => {
        if (!cancelado) setSemaforo(data);
      })
      .catch((err) => {
        if (!cancelado) {
          setError(
            err.status === 403
              ? 'Sin permiso para ver el semáforo de completitud.'
              : 'No se pudo cargar el semáforo de completitud.',
          );
        }
      })
      .finally(() => {
        if (!cancelado) setCargando(false);
      });

    return () => {
      cancelado = true;
    };
  }, [estudianteId]);

  if (cargando) {
    return (
      <Card className='space-y-3 border-[var(--border)]' data-testid='perfil-semaforo-completitud-cargando'>
        <h3 className='text-[13px] font-semibold uppercase tracking-wide text-muted'>Completitud de datos</h3>
        <p className='text-sm text-muted'>Cargando…</p>
      </Card>
    );
  }

  if (error) {
    return (
      <Card className='border-[var(--border)]'>
        <AlertMessage variant='warning'>{error}</AlertMessage>
      </Card>
    );
  }

  if (!semaforo) {
    return null;
  }

  const styles = COLOR_STYLES[semaforo.color] ?? COLOR_STYLES.rojo;
  const razones = Array.isArray(semaforo.razones) ? semaforo.razones : [];

  return (
    <Card className={`space-y-4 border-[var(--border)] ${styles.bg}`} data-testid='perfil-semaforo-completitud'>
      <div className='border-b border-[var(--border)]/80 pb-3'>
        <h3 className='text-[13px] font-semibold uppercase tracking-wide text-muted'>Completitud de datos</h3>
      </div>

      <div className={`flex items-start gap-3 rounded-lg border ${styles.border} bg-white/60 px-4 py-3`}>
        <span className={`mt-1 h-3 w-3 shrink-0 rounded-full ${styles.dot}`} aria-hidden='true' />
        <div>
          <p className={`text-sm font-semibold ${styles.text}`}>{semaforo.etiqueta || styles.label}</p>
          <p className='mt-0.5 text-sm leading-relaxed text-[var(--text)]'>{semaforo.mensaje}</p>
        </div>
      </div>

      {razones.length > 0 ? (
        <ul className='space-y-1.5'>
          {razones.map((razon) => (
            <li key={razon.dato} className='flex items-start gap-2 text-sm'>
              <span className={razon.presente ? 'text-green-600' : 'text-red-600'} aria-hidden='true'>
                {razon.presente ? '✓' : '✗'}
              </span>
              <span className='text-[var(--text)]'>{razon.mensaje || formatearDato(razon.dato)}</span>
            </li>
          ))}
        </ul>
      ) : null}
    </Card>
  );
}
