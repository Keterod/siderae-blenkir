import { memo } from 'react';
import { filtrarEntradaNota, INPUT_NOTA } from './notasUtils';

function NotaInputCell({ value, onChange, disabled, invalid }) {
  return (
    <input
      type="text"
      inputMode="decimal"
      autoComplete="off"
      spellCheck={false}
      pattern="[0-9]*[.]?[0-9]*"
      className={`${INPUT_NOTA} ${invalid ? 'border-red-500 bg-red-50' : ''}`}
      value={value ?? ''}
      onChange={(e) => {
        const filtrado = filtrarEntradaNota(e.target.value);
        if (filtrado === null) return;
        onChange(filtrado);
      }}
      disabled={disabled}
      aria-invalid={invalid || undefined}
    />
  );
}

export default memo(NotaInputCell);
