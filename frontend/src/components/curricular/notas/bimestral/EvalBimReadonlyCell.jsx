import { memo } from 'react';
import { formatoNotaDisplay } from './evaluacionBimestralUtils';

function EvalBimReadonlyCell({ value, title, className = '' }) {
  return (
    <span
      className={`inline-block min-w-[2.125rem] px-0.5 py-0 text-center text-[11px] tabular-nums text-muted ${className}`}
      title={title}
    >
      {formatoNotaDisplay(value)}
    </span>
  );
}

export default memo(EvalBimReadonlyCell);
