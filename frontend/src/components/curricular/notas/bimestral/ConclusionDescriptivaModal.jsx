import { useEffect, useState } from 'react';
import Button from '../../../ui/Button';

export default function ConclusionDescriptivaModal({
  abierto,
  estudiante,
  valorInicial,
  soloLectura,
  onCerrar,
  onGuardar,
}) {
  const [texto, setTexto] = useState(valorInicial ?? '');

  useEffect(() => {
    if (abierto) {
      setTexto(valorInicial ?? '');
    }
  }, [abierto, valorInicial]);

  if (!abierto) return null;

  const nombre = estudiante
    ? `${estudiante.apellidos ?? ''}, ${estudiante.nombres ?? ''}`.trim()
    : 'Estudiante';

  return (
    <div
      className="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="conclusion-bim-titulo"
      tabIndex={-1}
      onKeyDown={(e) => { if (e.key === 'Escape') onCerrar(); }}
      onClick={(e) => { if (e.target === e.currentTarget) onCerrar(); }}
    >
      <div
        className="w-full max-w-md rounded-lg border border-[var(--border)] bg-[var(--surface)] p-4 shadow-lg"
      >
        <h3 id="conclusion-bim-titulo" className="text-sm font-semibold text-[var(--text)]">
          Conclusión descriptiva
        </h3>
        <p className="mt-0.5 truncate text-[11px] text-muted" title={nombre}>
          {nombre}
        </p>
        <textarea
          className="mt-2 min-h-[7rem] w-full resize-y rounded border border-[var(--border)] bg-[var(--surface)] px-2 py-1.5 text-xs text-[var(--text)] outline-none focus-visible:ring-1 focus-visible:ring-[var(--primary)]"
          value={texto}
          onChange={(e) => setTexto(e.target.value)}
          readOnly={soloLectura}
          placeholder={soloLectura ? 'Sin conclusión registrada.' : 'Conclusión opcional…'}
          maxLength={4000}
        />
        <div className="mt-3 flex justify-end gap-2">
          <Button type="button" variant="ghost" size="sm" onClick={onCerrar}>
            Cerrar
          </Button>
          {!soloLectura ? (
            <Button
              type="button"
              variant="primary"
              size="sm"
              onClick={() => {
                onGuardar(texto);
                onCerrar();
              }}
            >
              Aplicar
            </Button>
          ) : null}
        </div>
      </div>
    </div>
  );
}
