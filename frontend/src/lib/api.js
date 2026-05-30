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

/** Normaliza respuesta paginada o array legacy de GET /api/estudiantes. */
export function estudiantesDesdeRespuesta(respuesta) {
  if (Array.isArray(respuesta)) {
    return respuesta;
  }
  if (respuesta && Array.isArray(respuesta.data)) {
    return respuesta.data;
  }
  return [];
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

export function getUsuarios(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `/api/usuarios?${qs}` : '/api/usuarios');
}

export function usuariosDesdeRespuesta(respuesta) {
  if (respuesta && Array.isArray(respuesta.data)) {
    return respuesta.data;
  }
  return [];
}

export function getUsuario(id) {
  return request(`/api/usuarios/${id}`);
}

export function createUsuario(datos) {
  return request('/api/usuarios', {
    method: 'POST',
    body: datos,
  });
}

export function updateUsuario(id, datos) {
  return request(`/api/usuarios/${id}`, {
    method: 'PATCH',
    body: datos,
  });
}

export function patchActivarUsuario(id) {
  return request(`/api/usuarios/${id}/activar`, {
    method: 'PATCH',
  });
}

export function patchDesactivarUsuario(id) {
  return request(`/api/usuarios/${id}/desactivar`, {
    method: 'PATCH',
  });
}

export function postRestablecerContrasenaUsuario(id, datos) {
  return request(`/api/usuarios/${id}/restablecer-contrasena`, {
    method: 'POST',
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

export function getCompetenciasPorArea(areaId, params = {}) {
  const qs = buildQueryString(params);
  const base = `${CURRICULAR}/areas/${areaId}/competencias`;
  return request(qs ? `${base}?${qs}` : base);
}

export function getCapacidadesPorCompetencia(competenciaId, params = {}) {
  const qs = buildQueryString(params);
  const base = `${CURRICULAR}/competencias/${competenciaId}/capacidades`;
  return request(qs ? `${base}?${qs}` : base);
}

export function postCompetencia(areaId, datos) {
  return request(`${CURRICULAR}/areas/${areaId}/competencias`, { method: 'POST', body: datos });
}

export function patchCompetencia(competenciaId, datos) {
  return request(`${CURRICULAR}/competencias/${competenciaId}`, { method: 'PATCH', body: datos });
}

export function patchDesactivarCompetencia(competenciaId) {
  return request(`${CURRICULAR}/competencias/${competenciaId}/desactivar`, { method: 'PATCH' });
}

export function patchReactivarCompetencia(competenciaId) {
  return request(`${CURRICULAR}/competencias/${competenciaId}/reactivar`, { method: 'PATCH' });
}

export function postCapacidad(competenciaId, datos) {
  return request(`${CURRICULAR}/competencias/${competenciaId}/capacidades`, { method: 'POST', body: datos });
}

export function patchCapacidad(capacidadId, datos) {
  return request(`${CURRICULAR}/capacidades/${capacidadId}`, { method: 'PATCH', body: datos });
}

export function patchDesactivarCapacidad(capacidadId) {
  return request(`${CURRICULAR}/capacidades/${capacidadId}/desactivar`, { method: 'PATCH' });
}

export function patchReactivarCapacidad(capacidadId) {
  return request(`${CURRICULAR}/capacidades/${capacidadId}/reactivar`, { method: 'PATCH' });
}

export const COMPETENCIAS_PREFILL_KEY = 'siderae-competencias-prefill';

export function guardarPrefillCompetenciasCapacidades(datos) {
  try {
    sessionStorage.setItem(COMPETENCIAS_PREFILL_KEY, JSON.stringify(datos));
  } catch {
    /* ignore */
  }
}

export function leerPrefillCompetenciasCapacidades() {
  try {
    const raw = sessionStorage.getItem(COMPETENCIAS_PREFILL_KEY);
    if (!raw) return null;
    sessionStorage.removeItem(COMPETENCIAS_PREFILL_KEY);
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

export const EVENTO_ABRIR_COMPETENCIAS = 'siderae-open-competencias';

export function solicitarModuloCompetenciasCapacidades(prefill = null) {
  if (prefill) {
    guardarPrefillCompetenciasCapacidades(prefill);
  }
  window.dispatchEvent(new Event(EVENTO_ABRIR_COMPETENCIAS));
}

export function getCurricularPeriodos(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/periodos?${qs}` : `${CURRICULAR}/periodos`);
}

export function getAniosEscolares() {
  return request(`${CURRICULAR}/anios-escolares`);
}

export function getAnioEscolarActivo() {
  return request(`${CURRICULAR}/anios-escolares/activo`);
}

export function getAnioEscolar(id) {
  return request(`${CURRICULAR}/anios-escolares/${id}`);
}

export function postAnioEscolar(datos) {
  return request(`${CURRICULAR}/anios-escolares`, { method: 'POST', body: datos });
}

export function patchAnioEscolar(id, datos) {
  return request(`${CURRICULAR}/anios-escolares/${id}`, { method: 'PATCH', body: datos });
}

export function postActivarAnioEscolar(id) {
  return request(`${CURRICULAR}/anios-escolares/${id}/activar`, { method: 'POST' });
}

export function postCerrarAnioEscolar(id) {
  return request(`${CURRICULAR}/anios-escolares/${id}/cerrar`, { method: 'POST' });
}

export function postGenerarBimestresAnioEscolar(id, datos = {}) {
  return request(`${CURRICULAR}/anios-escolares/${id}/generar-bimestres`, { method: 'POST', body: datos });
}

export function patchPeriodoAcademico(id, datos) {
  return request(`${CURRICULAR}/periodos-academicos/${id}`, { method: 'PATCH', body: datos });
}

export function postMarcarPeriodoVigente(id) {
  return request(`${CURRICULAR}/periodos-academicos/${id}/marcar-vigente`, { method: 'POST' });
}

export function postCerrarPeriodoAcademico(id) {
  return request(`${CURRICULAR}/periodos-academicos/${id}/cerrar`, { method: 'POST' });
}

export function postGenerarSemanasPeriodo(id) {
  return request(`${CURRICULAR}/periodos-academicos/${id}/generar-semanas`, { method: 'POST' });
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

export function getPesosEvaluacionResolver(params = {}) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/pesos/resolver?${qs}`);
}

export function postConfiguracionPeso(datos) {
  return request(`${CURRICULAR}/pesos`, { method: 'POST', body: datos });
}

export function patchConfiguracionPeso(id, datos) {
  return request(`${CURRICULAR}/pesos/${id}`, { method: 'PATCH', body: datos });
}

export function patchDesactivarConfiguracionPeso(id) {
  return request(`${CURRICULAR}/pesos/${id}/desactivar`, { method: 'PATCH' });
}

export function getComponentesCalificacion(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/componentes-calificacion?${qs}` : `${CURRICULAR}/componentes-calificacion`);
}

export function getComponentesCalificacionPorNivel(nivel, params = {}) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/componentes-calificacion/por-nivel/${encodeURIComponent(nivel)}?${qs}`);
}

export function getValidacionSumaComponentesCalificacion(params = {}) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/componentes-calificacion/validar-suma?${qs}`);
}

export function postComponenteCalificacion(datos) {
  return request(`${CURRICULAR}/componentes-calificacion`, { method: 'POST', body: datos });
}

export function patchComponenteCalificacion(id, datos) {
  return request(`${CURRICULAR}/componentes-calificacion/${id}`, { method: 'PATCH', body: datos });
}

export function patchDesactivarComponenteCalificacion(id) {
  return request(`${CURRICULAR}/componentes-calificacion/${id}/desactivar`, { method: 'PATCH' });
}

export function patchReactivarComponenteCalificacion(id, datos) {
  return request(`${CURRICULAR}/componentes-calificacion/${id}/reactivar`, { method: 'PATCH', body: datos });
}

export function postReordenarComponentesCalificacion(datos) {
  return request(`${CURRICULAR}/componentes-calificacion/reordenar`, { method: 'POST', body: datos });
}

export function postAsegurarDefaultsComponentesCalificacion(datos) {
  return request(`${CURRICULAR}/componentes-calificacion/asegurar-defaults`, { method: 'POST', body: datos });
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

/** Contextos únicos (aula + curso) para filtros del modo consulta global (admin/coordinación/directivo). */
export function getContextosConsultaGlobales(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/notas-semanales/contextos-aula?${qs}` : `${CURRICULAR}/notas-semanales/contextos-aula`);
}

export function getFormularioNotasSemanales(params) {
  const qs = buildQueryString(params);
  return request(`${CURRICULAR}/notas-semanales/formulario?${qs}`);
}

/** Descarga plantilla Excel del registro auxiliar (.xlsx). Devuelve { blob, filename }. */
export async function descargarPlantillaRegistroAuxiliarExcel(params = {}) {
  const qs = buildQueryString(params);
  const path = `${CURRICULAR}/notas-semanales/plantilla-excel?${qs}`;

  const headers = {
    Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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

  const blob = await response.blob();
  const disposition = response.headers.get('content-disposition') ?? '';
  const match = disposition.match(/filename="?([^";\n]+)"?/i);
  const filename = match?.[1] ?? 'plantilla_registro_auxiliar.xlsx';

  return { blob, filename };
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

export function getAsistenciaDiariaFormulario(params = {}) {
  const qs = buildQueryString(params);
  return request(
    qs ? `${CURRICULAR}/asistencias-diarias/formulario?${qs}` : `${CURRICULAR}/asistencias-diarias/formulario`,
  );
}

export function postAsistenciaDiariaBulk(payload) {
  return request(`${CURRICULAR}/asistencias-diarias/bulk`, { method: 'POST', body: payload });
}

export function getAsistenciaDiariaResumen(params = {}) {
  const qs = buildQueryString(params);
  return request(qs ? `${CURRICULAR}/asistencias-diarias/resumen?${qs}` : `${CURRICULAR}/asistencias-diarias/resumen`);
}

/** Alias legible para consumo en perfil de estudiante. */
export function getResumenAsistenciaDiaria(params = {}) {
  return getAsistenciaDiariaResumen(params);
}

