import { memo } from 'react';
import { nombreEstudiante } from '../../../lib/notasCurricular';
import NotaCeCell from './NotaCeCell';
import NotaInputCell from './NotaInputCell';
import { notaFueraDeRango } from './notasUtils';

function RegistroNotasAlumnoRow({
  estudiante, criterios, filas, pesos, onChangeNota, soloLectura = false,
}) {
  return (
    <tr className="border-b last:border-b-0 hover:bg-orange-50/40">
      <td className="sticky left-0 z-10 w-[8rem] min-w-[8rem] max-w-[8rem] border-r border-[var(--border)] bg-[var(--surface)] px-2 py-0.5 text-[11px] font-medium leading-tight text-[var(--text)] shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]">
        <span className="line-clamp-2" title={nombreEstudiante(estudiante)}>
          {nombreEstudiante(estudiante)}
        </span>
      </td>
      {criterios.map((criterio) => {
        const fila = filas[criterio.id] ?? {};
        return (
          <CriterioCells
            key={criterio.id}
            criterioId={criterio.id}
            fila={fila}
            pesos={pesos}
            soloLectura={soloLectura}
            onChangeNota={onChangeNota}
            estudianteId={estudiante.id}
          />
        );
      })}
    </tr>
  );
}

function CriterioCells({ criterioId, fila, pesos, onChangeNota, estudianteId, soloLectura }) {
  return (
    <>
      {['nota_cuaderno', 'nota_libro', 'nota_tarea'].map((campo) => (
        <td key={campo} className="border-l border-[var(--border)]/40 px-0 py-0 text-center align-middle">
          <NotaInputCell
            value={fila[campo]}
            invalid={notaFueraDeRango(fila[campo])}
            disabled={soloLectura}
            onChange={(valor) => onChangeNota(estudianteId, criterioId, campo, valor)}
          />
        </td>
      ))}
      <NotaCeCell fila={fila} pesos={pesos} />
    </>
  );
}

export default memo(RegistroNotasAlumnoRow);
