import Button from '../../ui/Button';
import { etiquetaEstadoAsistencia } from './asistenciaUtils';

export default function AsistenciaToolbar({
  contadores,
  estadosPermitidos,
  onMarcarTodosPresentes,
  onLimpiarObservaciones,
  onGuardar,
  guardando = false,
  puedeGuardar = false,
  soloLectura = false,
}) {
  const items = (estadosPermitidos ?? []).map((estado) => ({
    key: estado,
    label: etiquetaEstadoAsistencia(estado),
    valor: contadores?.[estado] ?? 0,
  }));

  return (
    <div className="flex flex-col gap-4 border-t border-[var(--border)] pt-4">
      <div className="flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted">
        {items.map((item) => (
          <span key={item.key}>
            {item.label}: <strong className="font-semibold text-[var(--text)]">{item.valor}</strong>
          </span>
        ))}
        <span>
          Sin marcar:{' '}
          <strong className="font-semibold text-[var(--text)]">{contadores?.sin_marcar ?? 0}</strong>
        </span>
        <span>
          Total alumnos:{' '}
          <strong className="font-semibold text-[var(--text)]">{contadores?.total ?? 0}</strong>
        </span>
      </div>

      {!soloLectura ? (
        <div className="flex flex-wrap gap-2">
          <Button type="button" size="sm" variant="outline" onClick={onMarcarTodosPresentes}>
            Marcar todos presentes
          </Button>
          <Button type="button" size="sm" variant="ghost" onClick={onLimpiarObservaciones}>
            Limpiar observaciones
          </Button>
          <Button
            type="button"
            size="sm"
            variant="primary"
            disabled={!puedeGuardar || guardando}
            onClick={onGuardar}
            data-testid="asistencia-guardar"
          >
            {guardando ? 'Guardando…' : 'Guardar asistencia'}
          </Button>
        </div>
      ) : null}
    </div>
  );
}
