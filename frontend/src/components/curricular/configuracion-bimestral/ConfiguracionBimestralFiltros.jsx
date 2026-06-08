import {
  gradosCurricularesPorNivel,
  NIVELES_CURRICULARES,
} from '../../../lib/academicoCurricular';
import Card from '../../ui/Card';
import { FIELD } from './configuracionBimestralUtils';

const MODOS = [
  { value: 'curso', label: 'Por curso' },
  { value: 'grado', label: 'Por grado completo' },
];

export default function ConfiguracionBimestralFiltros({
  modo,
  filtros,
  areas,
  cursosFiltrados,
  periodos,
  onChangeModo,
  onChangeFiltros,
  onChangeArea,
}) {
  const esModoCurso = modo === 'curso';

  return (
    <Card className="p-5 sm:p-6">
      <h3 className="text-sm font-semibold text-[var(--text)]">Filtros</h3>
      <p className="mt-1 text-xs text-muted">
        {esModoCurso
          ? 'La configuración aplica al curso y bimestre seleccionados (sede operativa Chilca).'
          : 'La misma plantilla se aplicará a todos los cursos activos del grado (sede operativa Chilca).'}
      </p>

      <fieldset className="mt-4">
        <legend className="text-sm font-medium text-[var(--text)]">Modo de configuración</legend>
        <div className="mt-2 flex flex-wrap gap-4">
          {MODOS.map((opcion) => (
            <label key={opcion.value} className="flex items-center gap-2 text-sm">
              <input
                type="radio"
                name="modo-config-bimestral"
                value={opcion.value}
                checked={modo === opcion.value}
                onChange={() => onChangeModo(opcion.value)}
              />
              {opcion.label}
            </label>
          ))}
        </div>
      </fieldset>

      <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <label className="block text-sm font-medium text-[var(--text)]">
          Año escolar
          <input
            className={FIELD}
            value={filtros.anio_escolar}
            onChange={(e) => onChangeFiltros({ anio_escolar: e.target.value, periodo_academico_id: '' })}
          />
        </label>
        <label className="block text-sm font-medium text-[var(--text)]">
          Nivel
          <select
            className={FIELD}
            value={filtros.nivel}
            onChange={(e) =>
              onChangeFiltros({
                nivel: e.target.value,
                grado: '',
                area_id: '',
                malla_curso_id: '',
              })
            }
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
            onChange={(e) =>
              onChangeFiltros({ grado: e.target.value, area_id: '', malla_curso_id: '' })
            }
          >
            <option value="">Seleccione</option>
            {gradosCurricularesPorNivel(filtros.nivel).map((g) => (
              <option key={g} value={g}>
                {g}
              </option>
            ))}
          </select>
        </label>
        {esModoCurso ? (
          <>
            <label className="block text-sm font-medium text-[var(--text)]">
              Área
              <select
                className={FIELD}
                value={filtros.area_id}
                onChange={(e) => onChangeArea(e.target.value)}
                disabled={!filtros.grado}
              >
                <option value="">Seleccione</option>
                {areas.map((a) => (
                  <option key={a.id} value={a.id}>
                    {a.nombre}
                  </option>
                ))}
              </select>
            </label>
            <label className="block text-sm font-medium text-[var(--text)]">
              Curso
              <select
                className={FIELD}
                value={filtros.malla_curso_id}
                onChange={(e) => onChangeFiltros({ malla_curso_id: e.target.value })}
                disabled={!filtros.area_id}
              >
                <option value="">Seleccione</option>
                {cursosFiltrados.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.curso_catalogo?.nombre ?? c.cursoCatalogo?.nombre}
                  </option>
                ))}
              </select>
            </label>
          </>
        ) : null}
        <label className="block text-sm font-medium text-[var(--text)]">
          Bimestre
          <select
            className={FIELD}
            value={filtros.periodo_academico_id}
            onChange={(e) => onChangeFiltros({ periodo_academico_id: e.target.value })}
          >
            <option value="">Seleccione</option>
            {periodos.map((p) => (
              <option key={p.id} value={p.id}>
                Bimestre {p.bimestre}
              </option>
            ))}
          </select>
        </label>
      </div>
    </Card>
  );
}
