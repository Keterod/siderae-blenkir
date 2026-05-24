import { NIVELES_CURRICULARES } from '../../../lib/academicoCurricular';
import { etiquetaBimestre, nombreEstudiante } from '../../../lib/notasCurricular';
import { FIELD_COMPACT, LABEL_COMPACT } from './notasUtils';
import RegistroNotasVistaToggle from './RegistroNotasVistaToggle';

const SEDES = [
  { value: 'chilca', label: 'Chilca' },
  { value: 'auquimarca', label: 'Auquimarca' },
];

function FiltroSelect({ label, children }) {
  return (
    <label className={LABEL_COMPACT}>
      {label}
      {children}
    </label>
  );
}

export default function RegistroNotasFiltros({
  filtros,
  opciones,
  aulas,
  aulasFiltradas,
  periodos,
  formulario,
  vista,
  onCambiarFiltro,
  onCambiarVista,
  nombreCursoAsignacion,
}) {
  return (
    <div className="space-y-1.5">
      <div className="grid grid-cols-2 gap-x-2 gap-y-1.5 sm:grid-cols-3 lg:grid-cols-5">
        <FiltroSelect label="Año">
          <select
            className={FIELD_COMPACT}
            value={filtros.anio_escolar}
            onChange={(e) => onCambiarFiltro({ anio_escolar: e.target.value, periodo_academico_id: '' })}
          >
            {[...new Set(aulas.map((a) => a.anio_escolar))].map((anio) => (
              <option key={anio} value={anio}>{anio}</option>
            ))}
          </select>
        </FiltroSelect>
        <FiltroSelect label="Nivel">
          <select
            className={FIELD_COMPACT}
            value={filtros.nivel}
            onChange={(e) => onCambiarFiltro({ nivel: e.target.value, asignacion_id: '', area_id: '' })}
          >
            <option value="">Todos</option>
            {opciones.niveles.map((n) => (
              <option key={n} value={n}>
                {NIVELES_CURRICULARES.find((x) => x.value === n)?.label ?? n}
              </option>
            ))}
          </select>
        </FiltroSelect>
        <FiltroSelect label="Sede">
          <select className={FIELD_COMPACT} value={filtros.sede} onChange={(e) => onCambiarFiltro({ sede: e.target.value, asignacion_id: '' })}>
            <option value="">Todas</option>
            {opciones.sedes.map((s) => (
              <option key={s} value={s}>{SEDES.find((x) => x.value === s)?.label ?? s}</option>
            ))}
          </select>
        </FiltroSelect>
        <FiltroSelect label="Grado">
          <select className={FIELD_COMPACT} value={filtros.grado} onChange={(e) => onCambiarFiltro({ grado: e.target.value, asignacion_id: '' })}>
            <option value="">Todos</option>
            {opciones.grados.map((g) => <option key={g} value={g}>{g}</option>)}
          </select>
        </FiltroSelect>
        <FiltroSelect label="Sección">
          <select className={FIELD_COMPACT} value={filtros.seccion} onChange={(e) => onCambiarFiltro({ seccion: e.target.value, asignacion_id: '' })}>
            <option value="">Todas</option>
            {opciones.secciones.map((s) => <option key={s} value={s}>{s}</option>)}
          </select>
        </FiltroSelect>
      </div>

      <div className="grid grid-cols-2 gap-x-2 gap-y-1.5 sm:grid-cols-3 lg:grid-cols-5">
        <FiltroSelect label="Área">
          <select
            className={FIELD_COMPACT}
            value={filtros.area_id}
            onChange={(e) => onCambiarFiltro({ area_id: e.target.value, asignacion_id: '' })}
          >
            <option value="">Todas</option>
            {opciones.areas.map(([id, nombre]) => (
              <option key={id} value={id}>{nombre}</option>
            ))}
          </select>
        </FiltroSelect>
        <FiltroSelect label="Curso">
          <select
            className={FIELD_COMPACT}
            value={filtros.asignacion_id}
            onChange={(e) => onCambiarFiltro({ asignacion_id: e.target.value })}
          >
            <option value="">Seleccione</option>
            {aulasFiltradas.map((a) => (
              <option key={a.id} value={a.id}>{nombreCursoAsignacion(a)}</option>
            ))}
          </select>
        </FiltroSelect>
        <FiltroSelect label="Bimestre">
          <select
            className={FIELD_COMPACT}
            value={filtros.periodo_academico_id}
            onChange={(e) => onCambiarFiltro({ periodo_academico_id: e.target.value })}
          >
            <option value="">Seleccione</option>
            {periodos.map((p) => (
              <option key={p.id} value={p.id}>{etiquetaBimestre(p)}</option>
            ))}
          </select>
        </FiltroSelect>
        <FiltroSelect label="Vista">
          <div className="mt-0.5">
            <RegistroNotasVistaToggle vista={vista} onChange={onCambiarVista} />
          </div>
        </FiltroSelect>
        {vista === 'estudiante' ? (
          <FiltroSelect label="Estudiante">
            <select
              className={FIELD_COMPACT}
              value={filtros.estudiante_id}
              onChange={(e) => onCambiarFiltro({ estudiante_id: e.target.value })}
              disabled={!formulario?.estudiantes?.length}
            >
              <option value="">Seleccione</option>
              {(formulario?.estudiantes ?? []).map((est) => (
                <option key={est.id} value={est.id}>{nombreEstudiante(est)}</option>
              ))}
            </select>
          </FiltroSelect>
        ) : null}
      </div>
    </div>
  );
}
