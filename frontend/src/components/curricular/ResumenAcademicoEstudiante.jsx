import { useEffect, useState } from 'react';
import { getResumenAcademico } from '../../lib/api';
import { anioEscolarActual, etiquetaNivelEstudiante } from '../../lib/academico';
import AlertMessage from '../ui/AlertMessage';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

function etiquetaEstadoCalculo(estado) {
  if (estado === 'completo') return 'Completo';
  if (estado === 'pendiente') return 'Pendiente';
  return estado ?? '—';
}

function formatearNota(valor) {
  if (valor === null || valor === undefined) return '—';
  return Number(valor).toFixed(2);
}

export default function ResumenAcademicoEstudiante({ estudianteId, anioEscolar }) {
  const [resumen, setResumen] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(false);

  useEffect(() => {
    if (!estudianteId) return;
    setCargando(true);
    setError(null);
    void getResumenAcademico(estudianteId, { anio_escolar: anioEscolar || anioEscolarActual() })
      .then(setResumen)
      .catch((e) => {
        if (e.status === 403) setError('Sin permiso para ver notas académicas curriculares.');
        else setError('No se pudo cargar el resumen académico.');
      })
      .finally(() => setCargando(false));
  }, [estudianteId, anioEscolar]);

  if (!estudianteId) return null;
  if (cargando) return <LoadingState label="Cargando resumen curricular…" />;
  if (error) return <AlertMessage variant="error">{error}</AlertMessage>;
  if (!resumen) return null;

  const evaluaciones = resumen.evaluaciones_bimestrales ?? [];
  const sinDatos = !resumen.tiene_datos;

  return (
    <Card className="p-6">
      <h3 className="text-base font-semibold text-[var(--text)]">Resumen académico curricular</h3>
      <p className="text-xs text-muted">
        Año {resumen.anio_escolar}
        {resumen.nivel ? ` · ${etiquetaNivelEstudiante(resumen.nivel)}` : ''}
        {resumen.grado ? ` · ${resumen.grado}` : ''}
        {' · solo lectura'}
      </p>

      {sinDatos ? (
        <div className="mt-4">
          <EmptyState
            title="Sin evaluación registrada"
            description="Aún no hay evaluación bimestral registrada para este estudiante."
          />
        </div>
      ) : null}

      {evaluaciones.length > 0 ? (
        <section className="mt-5 space-y-4">
          <h4 className="text-sm font-medium text-[var(--text)]">Evaluación bimestral por curso</h4>
          {evaluaciones.map((ev) => (
            <div
              key={`${ev.malla_curso_id}-${ev.periodo_academico_id}`}
              className="rounded-lg border border-[var(--border)] bg-[var(--background)]/40 p-4 text-sm"
            >
              <p className="font-medium text-[var(--text)]">
                {ev.curso}
                <span className="font-normal text-muted">
                  {' '}
                  · {ev.area} · Bimestre {ev.bimestre}
                </span>
              </p>
              <dl className="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                  <dt className="text-xs text-muted">Prom. criterios</dt>
                  <dd className="font-medium tabular-nums">{formatearNota(ev.promedio_criterios)}</dd>
                </div>
                <div>
                  <dt className="text-xs text-muted">Oral</dt>
                  <dd className="font-medium tabular-nums">{formatearNota(ev.oral)}</dd>
                </div>
                <div>
                  <dt className="text-xs text-muted">Prom. ETA</dt>
                  <dd className="font-medium tabular-nums">{formatearNota(ev.promedio_eta)}</dd>
                </div>
                <div>
                  <dt className="text-xs text-muted">Examen</dt>
                  <dd className="font-medium tabular-nums">{formatearNota(ev.examen_bimestral)}</dd>
                </div>
                <div>
                  <dt className="text-xs text-muted">Nivel numérico</dt>
                  <dd className="font-medium tabular-nums">{formatearNota(ev.nivel_logro_numerico)}</dd>
                </div>
                <div>
                  <dt className="text-xs text-muted">Literal</dt>
                  <dd className="font-medium">{ev.nivel_logro_literal ?? '—'}</dd>
                </div>
                <div>
                  <dt className="text-xs text-muted">Estado</dt>
                  <dd className="font-medium">{etiquetaEstadoCalculo(ev.estado_calculo)}</dd>
                </div>
              </dl>
              {ev.etas?.length > 0 ? (
                <div className="mt-3">
                  <p className="text-xs font-medium text-muted">ETAs</p>
                  <ul className="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                    {ev.etas.map((eta) => (
                      <li key={eta.nombre}>
                        {eta.nombre}: <span className="tabular-nums font-medium">{formatearNota(eta.nota)}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              ) : null}
              {ev.conclusion_descriptiva ? (
                <p className="mt-3 text-sm leading-relaxed text-[var(--text)]">
                  <span className="text-xs font-medium text-muted">Conclusión descriptiva: </span>
                  {ev.conclusion_descriptiva}
                </p>
              ) : null}
            </div>
          ))}
        </section>
      ) : null}

      {!sinDatos && (resumen.promedios_bimestrales ?? []).length > 0 ? (
        <section className="mt-5">
          <h4 className="text-sm font-medium">Promedio bimestral (CE)</h4>
          <ul className="mt-1 text-sm">
            {resumen.promedios_bimestrales.map((b) => (
              <li key={b.periodo_academico_id}>
                Bimestre {b.bimestre}: {b.promedio_ce}
              </li>
            ))}
          </ul>
        </section>
      ) : null}

      {!sinDatos && (resumen.promedios_por_area ?? []).length > 0 ? (
        <section className="mt-4">
          <h4 className="text-sm font-medium">Por área</h4>
          <ul className="mt-1 text-sm">
            {resumen.promedios_por_area.map((a) => (
              <li key={a.area_id}>
                {a.area}: {a.promedio_ce}
              </li>
            ))}
          </ul>
        </section>
      ) : null}

      {!sinDatos && (resumen.promedios_por_curso ?? []).length > 0 ? (
        <section className="mt-4">
          <h4 className="text-sm font-medium">Por curso</h4>
          <ul className="mt-1 text-sm">
            {resumen.promedios_por_curso.map((c) => (
              <li key={c.malla_curso_id}>
                {c.curso}: {c.promedio_ce}
              </li>
            ))}
          </ul>
        </section>
      ) : null}

      {!sinDatos && (resumen.ce_por_tema ?? []).length > 0 ? (
        <section className="mt-4">
          <h4 className="text-sm font-medium">CE por tema / semana</h4>
          <ul className="mt-1 max-h-40 overflow-auto text-sm">
            {resumen.ce_por_tema.map((t) => (
              <li key={t.tema_semanal_id}>
                {t.curso} — {t.titulo} (B{t.bimestre}
                {t.numero_semana != null ? ` S${t.numero_semana}` : ''}): {t.ce_calculado}
              </li>
            ))}
          </ul>
        </section>
      ) : null}
    </Card>
  );
}
