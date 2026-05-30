import { NIVELES_ESTUDIANTE, anioEscolarActual, gradosPorNivel } from '../../../lib/academico';
import { fechaHoyIso } from './asistenciaUtils';

export default function AsistenciaFiltros({ filtros, onChange, deshabilitado = false }) {
  const grados = gradosPorNivel(filtros.nivel);

  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
      <label className="flex flex-col gap-1 text-sm">
        <span className="text-muted">Año escolar</span>
        <input
          type="text"
          className="sb-field min-w-0"
          value={filtros.anio_escolar}
          disabled={deshabilitado}
          onChange={(ev) => onChange({ anio_escolar: ev.target.value })}
        />
      </label>

      <label className="flex flex-col gap-1 text-sm">
        <span className="text-muted">Nivel</span>
        <select
          className="sb-field min-w-0"
          value={filtros.nivel}
          disabled={deshabilitado}
          onChange={(ev) => onChange({ nivel: ev.target.value, grado: '' })}
        >
          <option value="">Seleccione…</option>
          {NIVELES_ESTUDIANTE.map((n) => (
            <option key={n.value} value={n.value}>
              {n.label}
            </option>
          ))}
        </select>
      </label>

      <label className="flex flex-col gap-1 text-sm">
        <span className="text-muted">Sede</span>
        <select
          className="sb-field min-w-0"
          value={filtros.sede}
          disabled={deshabilitado}
          onChange={(ev) => onChange({ sede: ev.target.value })}
        >
          <option value="">Seleccione…</option>
          <option value="chilca">Chilca</option>
          <option value="auquimarca">Auquimarca</option>
        </select>
      </label>

      <label className="flex flex-col gap-1 text-sm">
        <span className="text-muted">Grado</span>
        <select
          className="sb-field min-w-0"
          value={filtros.grado}
          disabled={deshabilitado || !filtros.nivel}
          onChange={(ev) => onChange({ grado: ev.target.value })}
        >
          <option value="">Seleccione…</option>
          {grados.map((grado) => (
            <option key={grado} value={grado}>
              {grado}
            </option>
          ))}
        </select>
      </label>

      <label className="flex flex-col gap-1 text-sm">
        <span className="text-muted">Sección</span>
        <input
          type="text"
          className="sb-field min-w-0"
          placeholder="p. ej. A"
          value={filtros.seccion}
          disabled={deshabilitado}
          onChange={(ev) => onChange({ seccion: ev.target.value })}
        />
      </label>

      <label className="flex flex-col gap-1 text-sm">
        <span className="text-muted">Fecha</span>
        <input
          type="date"
          className="sb-field min-w-0"
          value={filtros.fecha}
          disabled={deshabilitado}
          onChange={(ev) => onChange({ fecha: ev.target.value })}
        />
      </label>
    </div>
  );
}

export function filtrosAsistenciaIniciales() {
  return {
    anio_escolar: anioEscolarActual(),
    nivel: '',
    sede: 'chilca',
    grado: '',
    seccion: '',
    fecha: fechaHoyIso(),
  };
}
