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

