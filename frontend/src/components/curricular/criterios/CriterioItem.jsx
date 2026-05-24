import { memo, useCallback, useState } from 'react';
import Badge from '../../ui/Badge';
import Button from '../../ui/Button';
import { IconEdit, IconPower } from './icons';
import { bimestreLabel, FIELD, semanaReferencialLabel } from './utils';

function CriterioItem({ criterio, tone, onGuardar, onDesactivar }) {
  const [editando, setEditando] = useState(false);
  const [titulo, setTitulo] = useState('');
  const [descripcion, setDescripcion] = useState('');

  const iniciarEdicion = useCallback(() => {
    setTitulo(criterio.titulo ?? '');
    setDescripcion(criterio.descripcion ?? '');
    setEditando(true);
  }, [criterio.descripcion, criterio.titulo]);

  const cancelarEdicion = useCallback(() => {
    setEditando(false);
  }, []);

  const guardar = useCallback(async () => {
    try {
      await onGuardar(criterio.id, {
        titulo: titulo.trim(),
        descripcion: descripcion.trim() || null,
      });
      setEditando(false);
    } catch {
      // Mantener edición si falla el guardado
    }
  }, [criterio.id, descripcion, onGuardar, titulo]);

  const meta = `${semanaReferencialLabel(criterio)} · ${bimestreLabel(criterio)}`;
  const bordeCriterio = criterio.activo ? tone.criterioBorderActivo : tone.criterioBorderInactivo;

  return (
    <li
      className={`rounded-md border border-[var(--border)]/40 bg-white px-3 py-2.5 shadow-sm transition ${bordeCriterio} ${
        criterio.activo ? '' : 'bg-slate-50/90 opacity-90'
      }`}
    >
      {editando ? (
        <div className="space-y-2">
          <label className="block text-xs font-medium text-muted">
            Título del criterio
            <input
              className={`${FIELD} mt-1`}
              value={titulo}
              onChange={(e) => setTitulo(e.target.value)}
              autoFocus
            />
          </label>
          <label className="block text-xs font-medium text-muted">
            Descripción (opcional)
            <textarea
              className={`${FIELD} mt-1 min-h-[56px]`}
              value={descripcion}
              onChange={(e) => setDescripcion(e.target.value)}
              rows={2}
            />
          </label>
          <div className="flex flex-wrap gap-1.5">
            <Button type="button" size="sm" variant="secondary" onClick={() => void guardar()}>
              Guardar
            </Button>
            <Button type="button" size="sm" variant="outline" onClick={cancelarEdicion}>
              Cancelar
            </Button>
          </div>
        </div>
      ) : (
        <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div className="min-w-0 flex-1">
            <p className="font-semibold leading-snug text-[var(--text)]">{criterio.titulo}</p>
            {criterio.descripcion ? (
              <p className="mt-1 text-sm leading-relaxed text-muted">{criterio.descripcion}</p>
            ) : null}
            <p className="mt-1.5 text-[11px] text-muted/90">{meta}</p>
          </div>
          <div className="flex shrink-0 flex-wrap items-center gap-1.5 sm:justify-end">
            <Badge variant={criterio.activo ? 'success' : 'neutral'}>
              {criterio.activo ? 'Activo' : 'Inactivo'}
            </Badge>
            {criterio.activo ? (
              <>
                <Button
                  type="button"
                  size="sm"
                  variant="secondary"
                  className="!border-[#c5d9f0] !bg-[#eaf2fb] !px-2 !text-[var(--secondary)] hover:!bg-[#dceaf8]"
                  title="Editar"
                  onClick={iniciarEdicion}
                >
                  <IconEdit />
                  <span className="sr-only sm:not-sr-only sm:ml-1">Editar</span>
                </Button>
                <Button
                  type="button"
                  size="sm"
                  variant="danger"
                  className="!px-2"
                  title="Desactivar"
                  onClick={() => void onDesactivar(criterio.id)}
                >
                  <IconPower />
                  <span className="sr-only sm:not-sr-only sm:ml-1">Desactivar</span>
                </Button>
              </>
            ) : null}
          </div>
        </div>
      )}
    </li>
  );
}

export default memo(CriterioItem);
