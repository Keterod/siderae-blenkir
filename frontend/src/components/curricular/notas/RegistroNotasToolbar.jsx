import { useRef } from 'react';
import Button from '../../ui/Button';

export default function RegistroNotasToolbar({
  resumen,
  guardando,
  cargandoFormulario,
  puedeGuardar,
  ocultarGuardar = false,
  formId = 'registro-notas-form',
  descargandoPlantilla = false,
  importandoPlantilla = false,
  puedeDescargarPlantilla = false,
  puedeImportarPlantilla = false,
  modoPlantilla = 'vacia',
  onCambiarModoPlantilla,
  onDescargarPlantilla,
  onImportarPlantilla,
}) {
  const inputArchivoRef = useRef(null);

  function abrirSelectorArchivo() {
    inputArchivoRef.current?.click();
  }

  function manejarArchivoSeleccionado(event) {
    const archivo = event.target.files?.[0];
    event.target.value = '';
    if (archivo) {
      void onImportarPlantilla?.(archivo);
    }
  }

  return (
    <div className="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--border)]/80 bg-[var(--surface)] py-1.5">
      <p className="min-w-0 truncate text-[11px] text-muted">{resumen}</p>
      <div className="flex shrink-0 flex-wrap items-center justify-end gap-2">
        {puedeDescargarPlantilla ? (
          <>
            <label className="flex items-center gap-1 text-[10px] text-muted">
              <span className="sr-only">Tipo de plantilla</span>
              <select
                className="rounded border border-[var(--border)] bg-[var(--surface)] px-1.5 py-0.5 text-[10px] text-[var(--text)]"
                value={modoPlantilla}
                onChange={(e) => onCambiarModoPlantilla?.(e.target.value)}
                disabled={descargandoPlantilla || importandoPlantilla || cargandoFormulario}
                data-testid="registro-notas-modo-plantilla"
              >
                <option value="vacia">Vacía</option>
                <option value="con_notas">Con notas actuales</option>
              </select>
            </label>
            <Button
              type="button"
              variant="outline"
              size="sm"
              className="px-3 py-1 text-xs"
              disabled={descargandoPlantilla || importandoPlantilla || cargandoFormulario}
              onClick={() => void onDescargarPlantilla?.()}
              data-testid="registro-notas-descargar-plantilla"
            >
              {descargandoPlantilla ? 'Generando…' : 'Descargar plantilla Excel'}
            </Button>
            {puedeImportarPlantilla ? (
              <>
                <input
                  ref={inputArchivoRef}
                  type="file"
                  accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                  className="sr-only"
                  onChange={manejarArchivoSeleccionado}
                  data-testid="registro-notas-input-importar-plantilla"
                />
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  className="px-3 py-1 text-xs"
                  disabled={importandoPlantilla || descargandoPlantilla || cargandoFormulario}
                  onClick={abrirSelectorArchivo}
                  data-testid="registro-notas-importar-plantilla"
                >
                  {importandoPlantilla ? 'Importando…' : 'Importar Excel'}
                </Button>
              </>
            ) : null}
          </>
        ) : null}
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
    </div>
  );
}
