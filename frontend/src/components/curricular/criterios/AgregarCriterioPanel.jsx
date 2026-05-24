import Button from '../../ui/Button';
import Card from '../../ui/Card';
import LoadingState from '../../ui/LoadingState';
import CompetenciaSelector from './CompetenciaSelector';
import { IconPlus } from './icons';
import { FIELD } from './utils';

export default function AgregarCriterioPanel({
  abierto,
  onToggle,
  puedeRegistrar,
  form,
  competencias,
  capacidades,
  semanas,
  cargandoCompetencias,
  sinCompetencias,
  onChangeCompetencia,
  onChangeCapacidad,
  onChangeCampo,
  onSubmit,
}) {
  return (
    <Card className="border-dashed border-[var(--border)] p-5 sm:p-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h3 className="text-sm font-semibold text-[var(--text)]">Agregar criterio de evaluación</h3>
          <p className="mt-1 text-xs text-muted">
            Competencia, capacidad y texto del criterio. Semana referencial opcional.
          </p>
        </div>
        <Button type="button" variant={abierto ? 'outline' : 'primary'} onClick={onToggle}>
          {abierto ? (
            'Ocultar formulario'
          ) : (
            <>
              <IconPlus />
              <span className="ml-1">Agregar criterio</span>
            </>
          )}
        </Button>
      </div>

      {abierto && !puedeRegistrar ? (
        <p className="mt-4 rounded-md border border-dashed border-[var(--border)] bg-background/50 px-4 py-3 text-sm text-muted">
          Seleccione curso y bimestre para registrar criterios.
        </p>
      ) : null}

      {abierto && puedeRegistrar ? (
        <form className="mt-4 space-y-4" onSubmit={onSubmit}>
          {sinCompetencias && !cargandoCompetencias ? (
            <p className="rounded-md border border-dashed border-[var(--border)] bg-background/50 px-4 py-3 text-sm text-muted">
              No hay competencias disponibles para esta área.
            </p>
          ) : null}

          <div className="grid gap-4 sm:grid-cols-2">
            <CompetenciaSelector
              competenciaId={form.competencia_id}
              capacidadId={form.capacidad_id}
              competencias={competencias}
              capacidades={capacidades}
              onChangeCompetencia={onChangeCompetencia}
              onChangeCapacidad={onChangeCapacidad}
            />
            <label className="block text-sm font-medium text-[var(--text)] sm:col-span-2">
              Criterio de evaluación
              <input
                className={FIELD}
                placeholder="Ej.: Las plantas y sus partes"
                value={form.titulo}
                onChange={(e) => onChangeCampo('titulo', e.target.value)}
                required
              />
            </label>
            <label className="block text-sm font-medium text-[var(--text)]">
              Semana referencial (opcional)
              <select
                className={FIELD}
                value={form.semana_academica_id}
                onChange={(e) => onChangeCampo('semana_academica_id', e.target.value)}
              >
                <option value="">Sin semana referencial</option>
                {semanas.map((s) => (
                  <option key={s.id} value={s.id}>
                    Semana {s.numero_semana}
                  </option>
                ))}
              </select>
            </label>
            <label className="block text-sm font-medium text-[var(--text)] sm:col-span-2">
              Descripción (opcional)
              <textarea
                className={`${FIELD} min-h-[64px]`}
                value={form.descripcion}
                onChange={(e) => onChangeCampo('descripcion', e.target.value)}
                rows={2}
              />
            </label>
          </div>

          {cargandoCompetencias ? <LoadingState label="Cargando competencias…" /> : null}

          <Button type="submit" variant="primary" disabled={sinCompetencias && !cargandoCompetencias}>
            Guardar criterio
          </Button>
        </form>
      ) : null}
    </Card>
  );
}
