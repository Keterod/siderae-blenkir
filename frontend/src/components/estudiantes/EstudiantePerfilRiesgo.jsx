import { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { postProcesarRiesgo } from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const NIVEL_VARIANT = {
  Alto: 'danger',
  Medio: 'warning',
  Bajo: 'success',
};

function mensajeErrorAmigable(err) {
  if (err.status === 403) {
    return 'Sin permiso para procesar el riesgo académico.';
  }

  if (err.status === 422 && err.payload?.errors) {
    const errores = Object.values(err.payload.errors).flat();
    if (errores.length > 0) {
      return errores.join(' ');
    }
    return err.payload.message || 'El riesgo no puede procesarse todavía porque faltan notas curriculares o asistencia.';
  }

  if (err.status === 503) {
    return err.payload?.message || 'El servicio de cálculo de riesgo no está disponible. Intente más tarde.';
  }

  return err.message || 'No se pudo procesar el riesgo académico.';
}

export default function EstudiantePerfilRiesgo({
  estudianteId,
  anioEscolar,
  ultimoIndiceRiesgo,
  nivel,
}) {
  const { permissions } = useAuth();
  const puedeProcesar = permissions.includes('procesar_riesgo');
  const esInicial = nivel === 'inicial';

  const [riesgo, setRiesgo] = useState(ultimoIndiceRiesgo);
  const [cargando, setCargando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);

  const handleProcesar = async () => {
    setCargando(true);
    setError(null);
    setExito(null);

    try {
      const resultado = await postProcesarRiesgo(estudianteId, { bimestre: '1' });
      setRiesgo(resultado);
      setExito('Riesgo académico procesado correctamente.');
      window.dispatchEvent(new CustomEvent('siderae-riesgo-procesado', {
        detail: { estudianteId, anioEscolar },
      }));
    } catch (err) {
      setError(mensajeErrorAmigable(err));
      setRiesgo(ultimoIndiceRiesgo);
    } finally {
      setCargando(false);
    }
  };

  const tieneRiesgo = riesgo && typeof riesgo.indice === 'number';
  const textoBoton = tieneRiesgo ? 'Actualizar riesgo' : 'Procesar riesgo';

  return (
    <Card
      className="space-y-4 border-[var(--border)] ring-1 ring-[var(--border)]/70"
      data-testid="perfil-riesgo"
    >
      <div className="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border)]/80 pb-4">
        <div>
          <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Riesgo académico</h3>
          <p className="mt-1.5 text-sm leading-relaxed text-muted">
            Cálculo determinístico basado en notas curriculares, asistencia y reportes conductuales opcionales. No usa
            variables socioeconómicas ni Fast Test.
          </p>
        </div>
        {puedeProcesar && !esInicial ? (
          <Button
            type="button"
            variant="primary"
            size="sm"
            data-testid="perfil-riesgo-procesar"
            onClick={handleProcesar}
            disabled={cargando}
          >
            {cargando ? 'Procesando…' : textoBoton}
          </Button>
        ) : null}
      </div>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      {cargando && !tieneRiesgo ? (
        <LoadingState label="Procesando riesgo académico…" />
      ) : null}

      {esInicial ? (
        <EmptyState
          title="Riesgo no disponible"
          description="El cálculo de riesgo académico no está disponible para el nivel Inicial en esta versión."
        />
      ) : null}

      {!esInicial && !tieneRiesgo && !cargando ? (
        <EmptyState
          title="Sin riesgo calculado"
          description="Aún no hay un índice de riesgo para este estudiante. Presione 'Procesar riesgo' si tiene permiso y existen notas y asistencia curriculares."
        />
      ) : null}

      {!esInicial && tieneRiesgo ? (
        <div className="rounded-lg border border-[var(--border)] bg-[var(--background)]/30 px-4 py-5">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">Índice de riesgo</p>
              <p className="mt-2 text-2xl font-semibold tabular-nums text-[var(--text)]">
                {riesgo.indice.toFixed(4)}
              </p>
            </div>
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">Nivel</p>
              <p className="mt-2">
                <Badge variant={NIVEL_VARIANT[riesgo.nivel] ?? 'neutral'} className="normal-case">
                  {riesgo.nivel}
                </Badge>
              </p>
            </div>
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">Periodo</p>
              <p className="mt-2 text-sm text-[var(--text)]">
                {riesgo.anio_escolar ?? anioEscolar ?? '—'} / Bimestre {riesgo.bimestre ?? '1'}
              </p>
            </div>
          </div>

          {riesgo.variables_utilizadas ? (
            <div className="mt-4 border-t border-[var(--border)]/70 pt-4">
              <p className="text-xs font-semibold uppercase tracking-wide text-muted">Variables utilizadas</p>
              <ul className="mt-2 flex flex-wrap gap-2 text-sm text-[var(--text)]">
                {Object.entries(riesgo.variables_utilizadas)
                  .filter(([, v]) => Boolean(v))
                  .map(([k]) => (
                    <li key={k} className="rounded-full border border-[var(--border)] bg-[var(--surface)] px-2.5 py-0.5 text-xs">
                      {formatearVariable(k)}
                    </li>
                  ))}
              </ul>
            </div>
          ) : null}
        </div>
      ) : null}
    </Card>
  );
}

function formatearVariable(clave) {
  const map = {
    notas: 'Notas',
    asistencia: 'Asistencia',
    reportes_conductuales: 'Reportes conductuales',
    variables_socioeconomicas: 'Variables socioeconómicas',
    fast_test: 'Fast Test',
    detalle_academico: 'Detalle académico',
    detalle_asistencia: 'Detalle asistencia',
    detalle_conductual: 'Detalle conductual',
  };
  return map[clave] ?? clave;
}
