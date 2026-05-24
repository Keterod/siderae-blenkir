import { gradosCurricularesPorNivel, NIVELES_CURRICULARES } from '../../../lib/academicoCurricular';
import Button from '../../ui/Button';
import Card from '../../ui/Card';
import { FIELD } from './utils';

export default function MallaFiltros({ filtros, cargando, onChangeFiltros, onSubmit }) {
  return (
    <Card className="p-5 sm:p-6">
      <h3 className="text-sm font-semibold text-[var(--text)]">Filtros de consulta</h3>
      <form
        className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
        onSubmit={(e) => {
          e.preventDefault();
          onSubmit();
        }}
      >
        <label className="block text-sm font-medium text-[var(--text)]">
          Año escolar
          <input
            className={FIELD}
            value={filtros.anio_escolar}
            onChange={(e) => onChangeFiltros({ anio_escolar: e.target.value })}
          />
        </label>
        <label className="block text-sm font-medium text-[var(--text)]">
          Nivel
          <select
            className={FIELD}
            value={filtros.nivel}
            onChange={(e) => onChangeFiltros({ nivel: e.target.value, grado: '' })}
          >
            {NIVELES_CURRICULARES.map((n) => (
              <option key={n.value} value={n.value}>
                {n.label}
              </option>
            ))}
          </select>
        </label>
        <label className="block text-sm font-medium text-[var(--text)]">
          Grado
          <select
            className={FIELD}
            value={filtros.grado}
            onChange={(e) => onChangeFiltros({ grado: e.target.value })}
            required
          >
            <option value="">Seleccione</option>
            {gradosCurricularesPorNivel(filtros.nivel).map((g) => (
              <option key={g} value={g}>
                {g}
              </option>
            ))}
          </select>
        </label>
        <div className="flex items-end sm:col-span-2 lg:col-span-1">
          <Button
            type="submit"
            variant="primary"
            size="lg"
            className="w-full sm:w-auto"
            disabled={cargando || !filtros.grado}
          >
            Actualizar malla
          </Button>
        </div>
      </form>
    </Card>
  );
}
