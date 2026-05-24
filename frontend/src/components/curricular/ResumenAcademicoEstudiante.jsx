import { useEffect, useState } from 'react';
import { getResumenAcademico } from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import AlertMessage from '../ui/AlertMessage';
import Card from '../ui/Card';
import LoadingState from '../ui/LoadingState';

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

  return (
    <Card className="mt-6 p-6">
      <h3 className="text-base font-semibold text-[var(--text)]">Resumen académico curricular</h3>
      <p className="text-xs text-muted">Año {resumen.anio_escolar} · solo lectura</p>

      <section className="mt-4">
        <h4 className="text-sm font-medium">Promedio bimestral (CE)</h4>
        <ul className="mt-1 text-sm">
          {(resumen.promedios_bimestrales ?? []).map((b) => (
            <li key={b.periodo_academico_id}>Bimestre {b.bimestre}: {b.promedio_ce}</li>
          ))}
        </ul>
      </section>

      <section className="mt-4">
        <h4 className="text-sm font-medium">Por área</h4>
        <ul className="mt-1 text-sm">
          {(resumen.promedios_por_area ?? []).map((a) => (
            <li key={a.area_id}>{a.area}: {a.promedio_ce}</li>
          ))}
        </ul>
      </section>

      <section className="mt-4">
        <h4 className="text-sm font-medium">Por curso</h4>
        <ul className="mt-1 text-sm">
          {(resumen.promedios_por_curso ?? []).map((c) => (
            <li key={c.malla_curso_id}>{c.curso}: {c.promedio_ce}</li>
          ))}
        </ul>
      </section>

      <section className="mt-4">
        <h4 className="text-sm font-medium">CE por tema / semana</h4>
        <ul className="mt-1 max-h-40 overflow-auto text-sm">
          {(resumen.ce_por_tema ?? []).map((t) => (
            <li key={t.tema_semanal_id}>{t.curso} — {t.titulo} (B{t.bimestre} S{t.numero_semana}): {t.ce_calculado}</li>
          ))}
        </ul>
      </section>
    </Card>
  );
}
