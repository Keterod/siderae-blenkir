import { memo } from 'react';
import Badge from '../../ui/Badge';
import Button from '../../ui/Button';
import Card from '../../ui/Card';
import AgregarCursoBox from './AgregarCursoBox';
import CursoItem from './CursoItem';
import { IconPlus } from './icons';

function AreaCursosCard({
  area,
  cursosArea,
  activosCount,
  catalogo,
  disponibles,
  puedeGestionar,
  mallaId,
  agregandoAbierto,
  onAbrirAgregar,
  onCerrarAgregar,
  onExito,
  onError,
  onLimpiarMensajes,
  onRecargarCatalogo,
  onRecargarMalla,
  editandoCursoId,
  editCursoCatalogoId,
  onIniciarEdicion,
  onCancelarEdicion,
  onEditCursoCatalogoIdChange,
  onGuardarEdicion,
  onToggleActivo,
}) {
  return (
    <Card
      className="flex flex-col overflow-hidden border-[var(--border)]/90 p-0 shadow-card"
      padding={false}
    >
      <div className="border-b border-[var(--border)]/70 bg-[var(--surface)] px-4 py-3 sm:px-5">
        <div className="flex flex-wrap items-start justify-between gap-2">
          <div>
            <h3 className="text-base font-semibold text-[var(--text)]">{area.nombre}</h3>
            <p className="mt-0.5 text-xs text-muted">
              {activosCount} {activosCount === 1 ? 'curso activo' : 'cursos activos'}
              {cursosArea.length > activosCount ? ` · ${cursosArea.length} en total` : ''}
            </p>
          </div>
          <Badge variant="info">{cursosArea.length} en malla</Badge>
        </div>
      </div>

      <div className="flex flex-1 flex-col gap-2 p-4 sm:p-5">
        {cursosArea.length === 0 ? (
          <p className="rounded-md border border-dashed border-[var(--border)] bg-background/60 px-3 py-4 text-center text-sm text-muted">
            Esta área aún no tiene cursos configurados.
          </p>
        ) : (
          <ul className="space-y-2">
            {cursosArea.map((curso) => (
              <CursoItem
                key={curso.id}
                curso={curso}
                catalogoArea={catalogo}
                puedeGestionar={puedeGestionar}
                enEdicion={editandoCursoId === curso.id}
                editCursoCatalogoId={editCursoCatalogoId}
                onEditCursoCatalogoIdChange={onEditCursoCatalogoIdChange}
                onGuardarEdicion={onGuardarEdicion}
                onCancelarEdicion={onCancelarEdicion}
                onIniciarEdicion={onIniciarEdicion}
                onToggleActivo={onToggleActivo}
              />
            ))}
          </ul>
        )}

        {puedeGestionar && agregandoAbierto ? (
          <AgregarCursoBox
            area={area}
            catalogo={catalogo}
            disponibles={disponibles}
            cursosArea={cursosArea}
            mallaId={mallaId}
            onClose={onCerrarAgregar}
            onExito={onExito}
            onError={onError}
            onLimpiarMensajes={onLimpiarMensajes}
            onRecargarCatalogo={onRecargarCatalogo}
            onRecargarMalla={onRecargarMalla}
          />
        ) : null}

        {puedeGestionar && !agregandoAbierto ? (
          <Button
            type="button"
            variant="outline"
            size="sm"
            className="mt-1 w-full border-dashed sm:w-auto"
            onClick={onAbrirAgregar}
          >
            <IconPlus />
            Agregar curso a esta área
          </Button>
        ) : null}
      </div>
    </Card>
  );
}

export default memo(AreaCursosCard);
