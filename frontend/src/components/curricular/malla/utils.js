export const FIELD =
  'mt-1.5 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--text)] shadow-sm outline-none transition focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-1';

export function resolverCatalogoArea(area) {
  return area?.cursos_catalogo ?? area?.cursosCatalogo ?? [];
}

export function idCatalogoCurso(mallaCurso) {
  return (
    mallaCurso?.curso_catalogo_id ??
    mallaCurso?.curso_catalogo?.id ??
    mallaCurso?.cursoCatalogo?.id
  );
}

export function normalizarNombreCurso(nombre) {
  return nombre.trim().replace(/\s+/g, ' ');
}

/** Cursos del catálogo que aún no están en la malla del área (activos ni inactivos). */
export function catalogoDisponibleParaAgregar(catalogo, cursosArea) {
  const idsEnMalla = new Set(
    cursosArea.map((c) => String(idCatalogoCurso(c))).filter(Boolean),
  );
  return catalogo.filter((cat) => !idsEnMalla.has(String(cat.id)));
}

export function idsCatalogoEnMalla(cursosArea) {
  return new Set(cursosArea.map((c) => String(idCatalogoCurso(c))).filter(Boolean));
}

export function idEnCatalogoDisponible(disponibles, cursoCatalogoId) {
  return disponibles.some((c) => String(c.id) === String(cursoCatalogoId));
}

export function buscarCatalogoPorNombre(catalogo, nombre) {
  const texto = normalizarNombreCurso(nombre).toLowerCase();
  if (!texto) return null;
  return catalogo.find((c) => c.nombre.trim().toLowerCase() === texto) ?? null;
}

export function obtenerMensajeError(err, fallback) {
  const payload = err?.payload;
  if (!payload) return fallback;
  if (payload.message) return payload.message;
  const errors = payload.errors;
  if (errors) {
    const first = Object.values(errors).flat()[0];
    if (first) return first;
  }
  return fallback;
}

export function construirPayloadAgregarPorCatalogo(areaId, cursoCatalogoId) {
  return {
    area_id: Number(areaId),
    curso_catalogo_id: Number(cursoCatalogoId),
  };
}

export function construirPayloadAgregarPorNombre(areaId, nombre) {
  return {
    area_id: Number(areaId),
    nombre: normalizarNombreCurso(nombre),
  };
}

export function nombreCursoMalla(curso) {
  return curso.curso_catalogo?.nombre ?? curso.cursoCatalogo?.nombre ?? 'Curso';
}
