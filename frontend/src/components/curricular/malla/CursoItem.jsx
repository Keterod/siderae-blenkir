import { memo } from 'react';
import Badge from '../../ui/Badge';
import Button from '../../ui/Button';
import { IconEdit, IconPower } from './icons';
import { FIELD, nombreCursoMalla } from './utils';

function CursoItem({
  curso,
  catalogoArea,
  puedeGestionar,
  enEdicion,
  editCursoCatalogoId,
  onEditCursoCatalogoIdChange,
  onGuardarEdicion,
  onCancelarEdicion,
  onIniciarEdicion,
  onToggleActivo,
}) {
  const nombre = nombreCursoMalla(curso);

  return (
    <li
      className={`rounded-lg border px-3 py-2.5 transition ${
        curso.activo
          ? 'border-[var(--border)]/80 bg-[var(--surface)]'
          : 'border-[var(--border)]/60 bg-background/80 opacity-90'
      }`}
    >
      {enEdicion ? (
        <div className="space-y-2">
          <label className="block text-xs font-medium text-muted">
            Cambiar curso (catálogo institucional)
            <select
              className={`${FIELD} mt-1`}
              value={editCursoCatalogoId}
              onChange={(e) => onEditCursoCatalogoIdChange(e.target.value)}
            >
              {catalogoArea.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.nombre}
                </option>
              ))}
            </select>
          </label>
          <div className="flex flex-wrap gap-2">
            <Button type="button" size="sm" variant="secondary" onClick={() => onGuardarEdicion(curso)}>
              Guardar
            </Button>
            <Button type="button" size="sm" variant="outline" onClick={onCancelarEdicion}>
              Cancelar
            </Button>
          </div>
        </div>
      ) : (
        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div className="min-w-0 flex-1">
            <p className="truncate font-medium text-[var(--text)]">{nombre}</p>
            <div className="mt-1">
              <Badge variant={curso.activo ? 'success' : 'neutral'}>
                {curso.activo ? 'Activo' : 'Inactivo'}
              </Badge>
            </div>
          </div>
          {puedeGestionar ? (
            <div className="flex shrink-0 flex-wrap gap-1.5">
              <Button
                type="button"
                size="sm"
                variant="secondary"
                className="!px-2.5"
                title="Editar curso"
                aria-label={`Editar ${nombre}`}
                onClick={() => onIniciarEdicion(curso)}
              >
                <IconEdit />
                <span className="sr-only sm:not-sr-only sm:ml-1">Editar</span>
              </Button>
              <Button
                type="button"
                size="sm"
                variant={curso.activo ? 'danger' : 'outline'}
                className={
                  !curso.activo
                    ? '!border-[var(--success)] !text-[var(--success)] hover:!bg-[#e6f7ef]'
                    : '!px-2.5'
                }
                title={curso.activo ? 'Desactivar curso' : 'Reactivar curso'}
                aria-label={curso.activo ? `Desactivar ${nombre}` : `Reactivar ${nombre}`}
                onClick={() => onToggleActivo(curso)}
              >
                <IconPower />
                <span className="sr-only sm:not-sr-only sm:ml-1">
                  {curso.activo ? 'Desactivar' : 'Reactivar'}
                </span>
              </Button>
            </div>
          ) : null}
        </div>
      )}
    </li>
  );
}

export default memo(CursoItem);
