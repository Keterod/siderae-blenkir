import { useCallback, useState } from 'react';
import { postMallaCurso } from '../../../lib/api';
import Button from '../../ui/Button';
import {
  buscarCatalogoPorNombre,
  construirPayloadAgregarPorCatalogo,
  construirPayloadAgregarPorNombre,
  FIELD,
  idCatalogoCurso,
  idEnCatalogoDisponible,
  normalizarNombreCurso,
  obtenerMensajeError,
} from './utils';

export default function AgregarCursoBox({
  area,
  catalogo,
  disponibles,
  cursosArea,
  mallaId,
  onClose,
  onExito,
  onError,
  onLimpiarMensajes,
  onRecargarCatalogo,
  onRecargarMalla,
}) {
  const [modoCrearNuevoCurso, setModoCrearNuevoCurso] = useState(false);
  const [cursoCatalogoIdSeleccionado, setCursoCatalogoIdSeleccionado] = useState('');
  const [nombreNuevoCurso, setNombreNuevoCurso] = useState('');

  const cerrar = useCallback(() => {
    setModoCrearNuevoCurso(false);
    setCursoCatalogoIdSeleccionado('');
    setNombreNuevoCurso('');
    onClose();
  }, [onClose]);

  const agregarCursoCatalogo = useCallback(
    async (e) => {
      e.preventDefault();
      if (!idEnCatalogoDisponible(disponibles, cursoCatalogoIdSeleccionado)) return;

      onLimpiarMensajes();
      try {
        const data = await postMallaCurso(
          mallaId,
          construirPayloadAgregarPorCatalogo(area.id, cursoCatalogoIdSeleccionado),
        );
        cerrar();
        onExito(data?.message ?? 'Curso agregado correctamente.');
        await onRecargarMalla();
      } catch (err) {
        onError(obtenerMensajeError(err, 'No se pudo agregar el curso.'));
      }
    },
    [
      area.id,
      cerrar,
      cursoCatalogoIdSeleccionado,
      disponibles,
      mallaId,
      onError,
      onExito,
      onLimpiarMensajes,
      onRecargarMalla,
    ],
  );

  const crearCursoNuevo = useCallback(
    async (e) => {
      e.preventDefault();
      const nombre = normalizarNombreCurso(nombreNuevoCurso);
      if (!nombre) return;

      const existenteCatalogo = buscarCatalogoPorNombre(catalogo, nombre);
      if (existenteCatalogo) {
        const enMalla = cursosArea.find(
          (c) => String(idCatalogoCurso(c)) === String(existenteCatalogo.id),
        );
        if (enMalla?.activo) {
          onError('El curso ya existe activo en la malla.');
          return;
        }
        if (enMalla && !enMalla.activo) {
          onError('El curso ya existe inactivo; use Reactivar.');
          return;
        }
      }

      onLimpiarMensajes();
      try {
        const data = await postMallaCurso(
          mallaId,
          construirPayloadAgregarPorNombre(area.id, nombre),
        );
        cerrar();
        onExito(data?.message ?? 'Curso creado y agregado correctamente.');
        await onRecargarCatalogo();
        await onRecargarMalla();
      } catch (err) {
        onError(obtenerMensajeError(err, 'No se pudo crear y agregar el curso.'));
      }
    },
    [
      area.id,
      catalogo,
      cerrar,
      cursosArea,
      mallaId,
      nombreNuevoCurso,
      onError,
      onExito,
      onLimpiarMensajes,
      onRecargarCatalogo,
      onRecargarMalla,
    ],
  );

  return (
    <div className="mt-2 space-y-3 rounded-lg border border-[var(--secondary)]/30 bg-[#eaf2fb]/40 p-3">
      <p className="text-sm font-medium text-[var(--text)]">Agregar curso a {area.nombre}</p>

      {!modoCrearNuevoCurso ? (
        <form className="space-y-3" onSubmit={(e) => void agregarCursoCatalogo(e)}>
          {disponibles.length > 0 ? (
            <>
              <label className="block text-xs font-medium text-[var(--text)]">
                Seleccionar curso existente
                <select
                  className={FIELD}
                  value={cursoCatalogoIdSeleccionado}
                  onChange={(e) => setCursoCatalogoIdSeleccionado(e.target.value)}
                  autoFocus
                >
                  <option value="">Seleccione curso…</option>
                  {disponibles.map((cat) => (
                    <option key={cat.id} value={cat.id}>
                      {cat.nombre}
                    </option>
                  ))}
                </select>
              </label>
              <p className="text-xs text-muted">o</p>
            </>
          ) : catalogo.length === 0 ? (
            <p className="text-sm text-muted">
              No hay cursos en el catálogo de esta área. Puede crear uno nuevo.
            </p>
          ) : (
            <p className="text-sm text-muted">Todos los cursos del catálogo ya están en la malla.</p>
          )}

          <div className="flex flex-wrap gap-2">
            {disponibles.length > 0 ? (
              <Button
                type="submit"
                size="sm"
                variant="primary"
                disabled={!idEnCatalogoDisponible(disponibles, cursoCatalogoIdSeleccionado)}
              >
                Agregar curso
              </Button>
            ) : null}
            <Button
              type="button"
              size="sm"
              variant="outline"
              onClick={() => {
                setModoCrearNuevoCurso(true);
                setCursoCatalogoIdSeleccionado('');
                onLimpiarMensajes();
              }}
            >
              Crear curso nuevo
            </Button>
            <Button type="button" size="sm" variant="outline" onClick={cerrar}>
              Cancelar
            </Button>
          </div>
        </form>
      ) : (
        <form className="space-y-3" onSubmit={(e) => void crearCursoNuevo(e)}>
          <label className="block text-xs font-medium text-[var(--text)]">
            Nombre del nuevo curso
            <input
              className={FIELD}
              value={nombreNuevoCurso}
              onChange={(e) => setNombreNuevoCurso(e.target.value)}
              placeholder="Ej. Estadística"
              required
              autoFocus
            />
          </label>
          <div className="flex flex-wrap gap-2">
            <Button type="submit" size="sm" variant="primary" disabled={!nombreNuevoCurso.trim()}>
              Crear y agregar
            </Button>
            <Button
              type="button"
              size="sm"
              variant="outline"
              onClick={() => {
                setModoCrearNuevoCurso(false);
                setNombreNuevoCurso('');
              }}
            >
              Volver a seleccionar existente
            </Button>
            <Button type="button" size="sm" variant="outline" onClick={cerrar}>
              Cancelar
            </Button>
          </div>
        </form>
      )}
    </div>
  );
}
