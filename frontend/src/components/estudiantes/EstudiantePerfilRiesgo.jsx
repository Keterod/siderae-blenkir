import { useState } from 'react';
import { getEstudiante, postProcesarRiesgo } from '../../lib/api';

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

export default function EstudiantePerfilRiesgo({
  estudianteId,
  ultimoIndice,
  puedeProcesar,
  onDetalleRefrescado,
}) {
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
          (Array.isArray(mensajes) ? mensajes : [String(mensajes)]).map((m) => `${clave}: ${m}`)
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
    <div className="space-y-3 border-t border-slate-200 pt-4">
      <h3 className="text-base font-semibold text-slate-900">Riesgo académico</h3>

      {ultimoIndice ? (
        <dl className="grid gap-2 rounded border border-slate-100 bg-slate-50/80 p-3 text-sm sm:grid-cols-3">
          <div>
            <dt className="text-slate-500">Índice</dt>
            <dd className="font-medium text-slate-900">{ultimoIndice.indice}</dd>
          </div>
          <div>
            <dt className="text-slate-500">Nivel</dt>
            <dd className="font-medium text-slate-900">{ultimoIndice.nivel}</dd>
          </div>
          <div>
            <dt className="text-slate-500">Procesado</dt>
            <dd className="font-medium text-slate-900">{formatearFechaProcesamiento(ultimoIndice.created_at)}</dd>
          </div>
        </dl>
      ) : (
        <p className="text-sm text-slate-600">Aún no hay un cálculo de riesgo registrado para este estudiante.</p>
      )}

      {puedeProcesar ? (
        <button
          type="button"
          disabled={procesando}
          className="rounded bg-slate-900 px-3 py-2 text-sm text-white disabled:opacity-60"
          onClick={() => {
            void procesar();
          }}
        >
          {procesando ? 'Procesando…' : 'Procesar riesgo'}
        </button>
      ) : null}

      {error ? <p className="text-sm text-red-600">{error}</p> : null}
    </div>
  );
}
