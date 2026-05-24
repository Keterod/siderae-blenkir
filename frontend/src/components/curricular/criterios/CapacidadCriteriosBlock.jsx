import { memo } from 'react';
import CriterioItem from './CriterioItem';

function CapacidadCriteriosBlock({ capacidad, criterios, tone, onGuardar, onDesactivar }) {
  return (
    <div className={`rounded-lg border border-[var(--border)]/35 bg-white shadow-sm ${tone.capacidadAccent}`}>
      <div className="border-b border-[var(--border)]/25 bg-slate-50/80 px-3 py-2.5 sm:px-4">
        <p className={`text-[10px] font-semibold uppercase tracking-wide ${tone.capacidadLabel}`}>
          Capacidad
        </p>
        <p className="mt-0.5 text-sm font-medium leading-snug text-[var(--text)]">
          {capacidad.nombre}
        </p>
      </div>
      <ul className="space-y-2 p-3 sm:p-4">
        {criterios.map((criterio) => (
          <CriterioItem
            key={criterio.id}
            criterio={criterio}
            tone={tone}
            onGuardar={onGuardar}
            onDesactivar={onDesactivar}
          />
        ))}
      </ul>
    </div>
  );
}

export default memo(CapacidadCriteriosBlock);
