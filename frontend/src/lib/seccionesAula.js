import { useEffect, useMemo, useState } from 'react';
import { gradoEstudianteACurricular } from './academico';
import { getSeccionesAulas } from './api';

export function opcionesDesdeNombresSeccion(nombres) {
  return (nombres ?? [])
    .map((nombre) => String(nombre ?? '').trim())
    .filter(Boolean)
    .map((nombre) => ({ value: nombre, label: nombre }));
}

const SECCIONES_GENERICAS = ['A', 'B', 'C'];

function esSeccionGenericaFallback(nombre) {
  return SECCIONES_GENERICAS.includes(String(nombre ?? '').trim().toUpperCase());
}

function normalizarNombresSeccion(items) {
  return (items ?? [])
    .map((item) => String(typeof item === 'string' ? item : item?.value ?? '').trim())
    .filter(Boolean);
}

export function combinarOpcionesSeccion(catalogo = [], legacy = [], valorActual = '') {
  const vistos = new Set();
  const opciones = [];

  const agregar = (valor) => {
    const v = String(valor ?? '').trim();
    if (!v || vistos.has(v)) {
      return;
    }
    vistos.add(v);
    opciones.push({ value: v, label: v });
  };

  const catalogoNombres = normalizarNombresSeccion(catalogo);
  const legacyNombres = normalizarNombresSeccion(legacy);
  const actual = String(valorActual ?? '').trim();

  if (catalogoNombres.length > 0) {
    for (const nombre of catalogoNombres) {
      agregar(nombre);
    }
    for (const nombre of legacyNombres) {
      if (!esSeccionGenericaFallback(nombre)) {
        agregar(nombre);
      }
    }
    if (actual && !esSeccionGenericaFallback(actual)) {
      agregar(actual);
    }
    return opciones;
  }

  for (const nombre of legacyNombres) {
    agregar(nombre);
  }
  if (actual) {
    agregar(actual);
  }

  if (opciones.length === 0) {
    for (const nombre of SECCIONES_GENERICAS) {
      agregar(nombre);
    }
  }

  return opciones;
}

export async function cargarOpcionesSeccionAula({ nivel, grado, gradoFormato = 'curricular' }) {
  if (!nivel || !grado) {
    return [];
  }

  const gradoConsulta = gradoFormato === 'estudiante'
    ? gradoEstudianteACurricular(nivel, grado)
    : grado;

  if (!gradoConsulta) {
    return [];
  }

  try {
    const data = await getSeccionesAulas({ nivel, grado: gradoConsulta, activo: 1 });
    const lista = Array.isArray(data) ? data : (data?.data ?? []);
    return opcionesDesdeNombresSeccion(lista.map((item) => item.nombre));
  } catch {
    return [];
  }
}

export function useOpcionesSeccionAula({
  nivel,
  grado,
  gradoFormato = 'curricular',
  /** Valores reales ya existentes (asignaciones, aulas, contextos). No incluir A/B/C genéricos. */
  fallback = [],
  legacy = undefined,
  valorActual = '',
}) {
  const legacyOpciones = legacy ?? fallback;
  const [catalogo, setCatalogo] = useState([]);

  useEffect(() => {
    let cancelado = false;

    if (!nivel || !grado) {
      setCatalogo([]);
      return undefined;
    }

    void cargarOpcionesSeccionAula({ nivel, grado, gradoFormato }).then((opciones) => {
      if (!cancelado) {
        setCatalogo(opciones);
      }
    });

    return () => {
      cancelado = true;
    };
  }, [nivel, grado, gradoFormato]);

  const opciones = useMemo(
    () => combinarOpcionesSeccion(catalogo, legacyOpciones, valorActual),
    [catalogo, legacyOpciones, valorActual],
  );

  return opciones;
}
