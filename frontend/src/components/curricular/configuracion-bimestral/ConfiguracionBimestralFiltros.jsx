import {
  gradosCurricularesPorNivel,
  NIVELES_CURRICULARES,
} from '../../../lib/academicoCurricular';
import Card from '../../ui/Card';
import { FIELD } from './configuracionBimestralUtils';

export default function ConfiguracionBimestralFiltros({
  filtros,
  areas,
  cursosFiltrados,
  periodos,
  onChangeFiltros,
  onChangeArea,
}) {
  return (
    <Card className="p-5 sm:p-6">
      <h3 className="text-sm font-semibold text-[var(--text)]">Filtros</h3>
      <p className="mt-1 text-xs text-muted">
        La configuración aplica al curso y bimestre seleccionados (sede operativa Chilca).
      </p>
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
