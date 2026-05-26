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

const CURRICULAR = '/api/curricular';

export function getFormularioEvaluacionBimestral(params) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/evaluacion-bimestral/formulario?${qs}`);
}

export function postEvaluacionBimestralBulk(datos) {
  return request(`${CURRICULAR}/evaluacion-bimestral/bulk`, { method: 'POST', body: datos });
}

export function getEvaluacionBimestralConfig(params) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/evaluacion-bimestral/config?${qs}`);
}

export function getEvaluacionBimestralResultados(params) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/evaluacion-bimestral/resultados?${qs}`);
}

export function postEvalBimComponente(datos) {
  return request(`${CURRICULAR}/evaluacion-bimestral/componentes`, { method: 'POST', body: datos });
}

export function patchEvalBimComponente(id, datos) {
  return request(`${CURRICULAR}/evaluacion-bimestral/componentes/${id}`, { method: 'PATCH', body: datos });
}

export function postEvalBimEta(datos) {
  return request(`${CURRICULAR}/evaluacion-bimestral/etas`, { method: 'POST', body: datos });
}

export function patchEvalBimEta(id, datos) {
  return request(`${CURRICULAR}/evaluacion-bimestral/etas/${id}`, { method: 'PATCH', body: datos });
}

export const ADVERTENCIA_NO_ELIMINAR_NOTA_EVAL_BIM =
  'Para eliminar una nota registrada se requiere una acción específica.';
