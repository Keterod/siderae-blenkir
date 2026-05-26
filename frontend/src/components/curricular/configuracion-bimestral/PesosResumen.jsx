import { formatoPeso, pesosValidos, sumaPesos } from './configuracionBimestralUtils';

export default function PesosResumen({ titulo, activos, campo = 'peso' }) {
  const suma = sumaPesos(activos, campo);
  const valido = activos.length === 0 || pesosValidos(suma);

  return (
    <div
      className={`rounded-md border px-3 py-2 text-sm ${
        valido
          ? 'border-emerald-200 bg-emerald-50 text-emerald-950'
          : 'border-amber-300 bg-amber-50 text-amber-950'
      }`}
    >
      <span className="font-medium">{titulo}:</span>{' '}
      {activos.length === 0 ? (
        <span>Sin ítems activos</span>
      ) : (
        <>
          suma {formatoPeso(suma)}
          {!valido ? (
            <span className="ml-1 font-medium">— debe ser 100 %</span>
          ) : (
            <span className="ml-1 text-emerald-800">✓</span>
          )}
        </>
      )}
    </div>
  );
}
