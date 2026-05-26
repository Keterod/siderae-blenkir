import Button from '../../ui/Button';

export default function RegistroNotasToolbar({
  resumen,
  guardando,
  cargandoFormulario,
  puedeGuardar,
  ocultarGuardar = false,
  formId = 'registro-notas-form',
}) {
  return (
    <div className="flex items-center justify-between gap-2 border-t border-[var(--border)]/80 bg-[var(--surface)] py-1.5">
      <p className="min-w-0 truncate text-[11px] text-muted">{resumen}</p>
      {!ocultarGuardar ? (
        <Button
          type="submit"
          form={formId}
          variant="primary"
          size="sm"
          className="shrink-0 px-3 py-1 text-xs"
          disabled={guardando || !puedeGuardar || cargandoFormulario}
          data-testid="registro-notas-guardar-sticky"
        >
          {guardando ? 'Guardando…' : 'Guardar notas'}
        </Button>
      ) : (
        <span className="shrink-0 text-[10px] font-medium uppercase tracking-wide text-muted">Solo lectura</span>
      )}
    </div>
  );
}
