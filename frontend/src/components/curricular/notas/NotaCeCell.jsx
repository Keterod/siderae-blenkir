import { memo } from 'react';
import { calcularCePreview, calcularCePreviewDinamico } from '../../../lib/notasCurricular';

function NotaCeCell({ fila, pesos, modoDinamico = false, componentes = [] }) {
  const ce = modoDinamico
    ? calcularCePreviewDinamico(fila?.componentes, componentes)
    : calcularCePreview(fila?.nota_cuaderno, fila?.nota_libro, fila?.nota_tarea, pesos);
  const ceInvalido = ce === 'invalid';

  return (
    <td className="px-0 py-0.5 text-center align-middle text-[10px] font-semibold tabular-nums text-[var(--primary-dark)]">
      {ceInvalido ? (
        <span className="text-red-600">!</span>
      ) : ce != null ? (
        ce
      ) : (
        <span className="text-muted">—</span>
      )}
    </td>
  );
}

export default memo(NotaCeCell);
