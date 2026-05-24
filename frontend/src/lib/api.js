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

export function getEstudiantes(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `/api/estudiantes?${qs}` : '/api/estudiantes');
}

export function postNotasLote(datos) {
  return request('/api/notas/lote', {
    method: 'POST',
    body: datos,
  });
}

export function postAsistenciasLote(datos) {
  return request('/api/asistencias/lote', {
    method: 'POST',
    body: datos,
  });
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

const CURRICULAR = '/api/curricular';

export function getCatalogoNivelesGrados() {
  return request(`${CURRICULAR}/catalogo/niveles-grados`);
}

export function getCurricularAreas(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/areas?${qs}` : `${CURRICULAR}/areas`);
}

export function getCompetenciasPorArea(areaId) {
  return request(`${CURRICULAR}/areas/${areaId}/competencias`);
}

export function getCapacidadesPorCompetencia(competenciaId) {
  return request(`${CURRICULAR}/competencias/${competenciaId}/capacidades`);
}

export function getCurricularPeriodos(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/periodos?${qs}` : `${CURRICULAR}/periodos`);
}

export function getCurricularSemanas(periodoId) {
  return request(`${CURRICULAR}/periodos/${periodoId}/semanas`);
}

export function getMallasCurriculares(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/mallas?${qs}` : `${CURRICULAR}/mallas`);
}

/** Obtiene o prepara automáticamente la malla del año/nivel/grado. */
export function getMallaCurricularPorGrado(params) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/mallas/grado?${qs}`);
}

export function getMallaCurricular(id) {
  return request(`${CURRICULAR}/mallas/${id}`);
}

export function postCargarPlantillaMalla(datos) {
  return request(`${CURRICULAR}/mallas/cargar-plantilla`, { method: 'POST', body: datos });
}

export function postMallaCurso(mallaId, datos) {
  return request(`${CURRICULAR}/mallas/${mallaId}/cursos`, { method: 'POST', body: datos });
}

export function patchMallaCurso(mallaId, mallaCursoId, datos) {
  return request(`${CURRICULAR}/mallas/${mallaId}/cursos/${mallaCursoId}`, { method: 'PATCH', body: datos });
}

export function patchDesactivarMallaCurso(mallaId, mallaCursoId) {
  return request(`${CURRICULAR}/mallas/${mallaId}/cursos/${mallaCursoId}/desactivar`, { method: 'PATCH' });
}

export function patchReactivarMallaCurso(mallaId, mallaCursoId) {
  return request(`${CURRICULAR}/mallas/${mallaId}/cursos/${mallaCursoId}/reactivar`, { method: 'PATCH' });
}

export function getTemasSemanales(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/temas?${qs}` : `${CURRICULAR}/temas`);
}

export function postTemaSemanal(datos) {
  return request(`${CURRICULAR}/temas`, { method: 'POST', body: datos });
}

export function patchTemaSemanal(temaId, datos) {
  return request(`${CURRICULAR}/temas/${temaId}`, { method: 'PATCH', body: datos });
}

export function patchDesactivarTemaSemanal(temaId) {
  return request(`${CURRICULAR}/temas/${temaId}/desactivar`, { method: 'PATCH' });
}

export function getConfiguracionPesos(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/pesos?${qs}` : `${CURRICULAR}/pesos`);
}

export function postConfiguracionPeso(datos) {
  return request(`${CURRICULAR}/pesos`, { method: 'POST', body: datos });
}

export function patchDesactivarConfiguracionPeso(id) {
  return request(`${CURRICULAR}/pesos/${id}/desactivar`, { method: 'PATCH' });
}

export function getAsignacionesDocente(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/asignaciones-docente?${qs}` : `${CURRICULAR}/asignaciones-docente`);
}

export function getCurricularDocentes(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/docentes?${qs}` : `${CURRICULAR}/docentes`);
}

export function getAsignacionesDocentePorDocente(docenteId, params = {}) {
  const qs = buildQueryString(params);
  return request(
    qs
      ? `${CURRICULAR}/asignaciones-docente/docente/${docenteId}?${qs}`
      : `${CURRICULAR}/asignaciones-docente/docente/${docenteId}`,
  );
}

export function postAsignacionDocente(datos) {
  return request(`${CURRICULAR}/asignaciones-docente`, { method: 'POST', body: datos });
}

export function postAsignacionDocenteBulk(datos) {
  return request(`${CURRICULAR}/asignaciones-docente/bulk`, { method: 'POST', body: datos });
}

export function patchDesactivarAsignacionDocente(id) {
  return request(`${CURRICULAR}/asignaciones-docente/${id}/desactivar`, { method: 'PATCH' });
}

export function getDocenteAulasCursos(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/docente/aulas-cursos?${qs}` : `${CURRICULAR}/docente/aulas-cursos`);
}

export function getFormularioNotasSemanales(params) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/notas-semanales/formulario?${qs}`);
}

export function postNotasSemanalesBulk(datos) {
  return request(`${CURRICULAR}/notas-semanales/bulk`, { method: 'POST', body: datos });
}

export function getResumenAcademico(estudianteId, params = {}) {
  const qs = buildQueryString(params);
  return request(
    qs
      ? `${CURRICULAR}/estudiantes/${estudianteId}/resumen-academico?${qs}`
      : `${CURRICULAR}/estudiantes/${estudianteId}/resumen-academico`,
  );
}

