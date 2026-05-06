const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

function getCookie(name) {
  const cookie = document.cookie
    .split('; ')
    .find((item) => item.startsWith(`${name}=`));

  if (!cookie) {
    return null;
  }

  return decodeURIComponent(cookie.split('=').slice(1).join('='));
}

async function request(path, options = {}) {
  const headers = {
    Accept: 'application/json',
    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
    ...(options.headers || {}),
  };

  const xsrfToken = getCookie('XSRF-TOKEN');
  if (xsrfToken) {
    headers['X-XSRF-TOKEN'] = xsrfToken;
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    credentials: 'include',
    ...options,
    headers,
    body: options.body ? JSON.stringify(options.body) : undefined,
  });

  const contentType = response.headers.get('content-type') || '';
  const isJson = contentType.includes('application/json');
  const payload = isJson ? await response.json() : null;

  if (!response.ok) {
    const error = new Error('Request failed');
    error.status = response.status;
    error.payload = payload;
    throw error;
  }

  return payload;
}

async function getCsrfCookie() {
  await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
  });
}

export async function login(credentials) {
  await getCsrfCookie();
  await request('/login', {
    method: 'POST',
    body: credentials,
  });
}

export async function logout() {
  await request('/logout', {
    method: 'POST',
  });
}

export async function getMe() {
  return request('/api/me', {
    method: 'GET',
  });
}

function buildQueryString(params) {
  const q = new URLSearchParams();
  if (!params || typeof params !== 'object') {
    return '';
  }
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '') {
      return;
    }
    q.set(key, String(value));
  });
  return q.toString();
}

export function getDashboard(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `/api/dashboard?${qs}` : '/api/dashboard');
}

/**
 * Descarga PDF del dashboard con los mismos filtros que GET /api/dashboard (Sprint 6B).
 * Devuelve Blob; el caller puede revocar URL al terminar.
 */
export async function exportDashboardPdf(filters = {}) {
  const qs = buildQueryString(filters);
  const path = qs ? `/api/dashboard/export?${qs}` : '/api/dashboard/export';

  const headers = {
    Accept: 'application/pdf',
  };

  const xsrfToken = getCookie('XSRF-TOKEN');
  if (xsrfToken) {
    headers['X-XSRF-TOKEN'] = xsrfToken;
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: 'GET',
    credentials: 'include',
    headers,
  });

  if (!response.ok) {
    const contentType = response.headers.get('content-type') || '';
    const error = new Error('Export failed');
    error.status = response.status;
    if (contentType.includes('application/json')) {
      error.payload = await response.json();
    }
    throw error;
  }

  return response.blob();
}

export function listarMaterias(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `/api/materias?${qs}` : '/api/materias');
}

export function crearMateria(datos) {
  return request('/api/materias', {
    method: 'POST',
    body: datos,
  });
}

export function actualizarMateria(id, datos) {
  return request(`/api/materias/${id}`, {
    method: 'PATCH',
    body: datos,
  });
}

export function desactivarMateria(id) {
  return request(`/api/materias/${id}/desactivar`, {
    method: 'PATCH',
  });
}

export function activarMateria(id) {
  return request(`/api/materias/${id}/activar`, {
    method: 'PATCH',
  });
}

export function getEstudiantes() {
  return request('/api/estudiantes');
}

export function getEstudiante(id) {
  return request(`/api/estudiantes/${id}`);
}

export function createEstudiante(datos) {
  return request('/api/estudiantes', {
    method: 'POST',
    body: datos,
  });
}

export function updateEstudiante(id, datos) {
  return request(`/api/estudiantes/${id}`, {
    method: 'PUT',
    body: datos,
  });
}

export function getNotas(estudianteId) {
  return request(`/api/estudiantes/${estudianteId}/notas`);
}

export function postNota(estudianteId, datos) {
  return request(`/api/estudiantes/${estudianteId}/notas`, {
    method: 'POST',
    body: datos,
  });
}

export function getAsistencias(estudianteId) {
  return request(`/api/estudiantes/${estudianteId}/asistencias`);
}

export function postAsistencia(estudianteId, datos) {
  return request(`/api/estudiantes/${estudianteId}/asistencias`, {
    method: 'POST',
    body: datos,
  });
}

export function getVariablesSocio(estudianteId) {
  return request(`/api/estudiantes/${estudianteId}/variables-socioeconomicas`);
}

export function postVariablesSocio(estudianteId, datos) {
  return request(`/api/estudiantes/${estudianteId}/variables-socioeconomicas`, {
    method: 'POST',
    body: datos,
  });
}

export function postProcesarRiesgo(estudianteId, datos = {}) {
  return request(`/api/estudiantes/${estudianteId}/procesar-riesgo`, {
    method: 'POST',
    body: datos,
  });
}

export function getAlertas() {
  return request('/api/alertas');
}

export function getAlerta(alertaId) {
  return request(`/api/alertas/${alertaId}`);
}

export function postIntervencion(alertaId, datos) {
  return request(`/api/alertas/${alertaId}/intervenciones`, {
    method: 'POST',
    body: datos,
  });
}

export function postCerrarAlerta(alertaId, datos) {
  return request(`/api/alertas/${alertaId}/cerrar`, {
    method: 'POST',
    body: datos,
  });
}

