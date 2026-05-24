import { memo } from 'react';
import Card from '../../ui/Card';
import CapacidadCriteriosBlock from './CapacidadCriteriosBlock';
import { getCompetenciaTone } from './utils';

function CompetenciaCriteriosCard({
  competencia,
  capacidades,
  totalCapacidades,
  totalActivos,
  totalCriterios,
  onGuardar,
  onDesactivar,
}) {
  const tone = getCompetenciaTone(competencia.id);

  return (
    <Card
      className={`overflow-hidden border border-[var(--border)]/40 p-0 shadow-sm ${tone.card}`}
      padding={false}
    >
      <div className={`border-b border-[var(--border)]/40 px-4 py-3.5 sm:px-5 ${tone.header}`}>
        <div className="flex flex-wrap items-start justify-between gap-2">
          <div className="min-w-0 flex-1">
            <p className={`text-[10px] font-semibold uppercase tracking-wide ${tone.label}`}>
              Competencia
            </p>
            <h4 className="mt-1 text-sm font-bold leading-snug text-[var(--text)] sm:text-base">
              {competencia.nombre}
            </h4>
          </div>
          <span
            className={`inline-flex shrink-0 items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ${tone.badge}`}
          >
            {totalCapacidades} cap. · {totalActivos} activo{totalActivos === 1 ? '' : 's'}
          </span>
        </div>
        <p className="mt-1.5 text-xs text-muted">
          {totalCapacidades} {totalCapacidades === 1 ? 'capacidad' : 'capacidades'} ·{' '}
          {totalCriterios} criterio{totalCriterios === 1 ? '' : 's'} registrado
          {totalCriterios === 1 ? '' : 's'}
        </p>
      </div>
      <div className="space-y-3 bg-white/50 p-4 sm:p-5">
        {capacidades.map(({ capacidad, criterios }) => (
          <CapacidadCriteriosBlock
            key={capacidad.id}
            capacidad={capacidad}
            criterios={criterios}
            tone={tone}
            onGuardar={onGuardar}
            onDesactivar={onDesactivar}
          />
        ))}
      </div>
    </Card>
  );
}

export default memo(CompetenciaCriteriosCard);
