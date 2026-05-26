import { memo } from 'react';
import { formatoNotaDisplay } from './evaluacionBimestralUtils';

function EvalBimPreviewValue({
  value,
  isPreview = false,
  className = '',
  title,
  children,
}) {
  const display = children ?? formatoNotaDisplay(value);

  return (
    <span
      className={`inline-flex items-center justify-center gap-0.5 ${className}`}
      title={isPreview ? 'Previsualización (sin guardar)' : title}
    >
      {display}
      {isPreview ? (
        <span
          className="inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"
          aria-label="preview"
          title="Previsualización"
        />
      ) : null}
    </span>
  );
}

export default memo(EvalBimPreviewValue);
