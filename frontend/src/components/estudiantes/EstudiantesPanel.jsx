import { useCallback, useEffect, useMemo, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  createEstudiante,
  getEstudiante,
  getEstudiantes,
  updateEstudiante,
} from '../../lib/api';
import {
  anioEscolarActual,
  etiquetaNivelEstudiante,
  gradoEsValidoParaNivel,
  gradosPorNivel,
  NIVELES_ESTUDIANTE,
} from '../../lib/academico';
import EstudiantePerfilDatos from './EstudiantePerfilDatos';
import EstudiantePerfilRiesgo from './EstudiantePerfilRiesgo';
import Badge from '../ui/Badge';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

function formularioVacio() {
  return {
    codigo: '',
    nombres: '',
    apellidos: '',
    fecha_nacimiento: '',
    sexo: '',
    grado: '',
    seccion: '',
    nivel: 'primaria',
    sede: 'chilca',
    anio_escolar: anioEscolarActual(),
    activo: true,
  };
}

function filtrosVacios() {
  return {
    q: '',
    sede: '',
    nivel: '',
    grado: '',
    seccion: '',
    anio_escolar: anioEscolarActual(),
  };
}

function llenarFormulario(desdeServidor) {
  const sexoValor = desdeServidor.sexo ?? '';
  const sexoValidado = sexoValor === 'M' || sexoValor === 'F' ? sexoValor : '';

  return {
    codigo: desdeServidor.codigo ?? '',
    nombres: desdeServidor.nombres ?? '',
    apellidos: desdeServidor.apellidos ?? '',
    fecha_nacimiento: desdeServidor.fecha_nacimiento
      ? String(desdeServidor.fecha_nacimiento).substring(0, 10)
      : '',
    sexo: sexoValidado,
    grado: desdeServidor.grado ?? '',
    seccion: desdeServidor.seccion ?? '',
    nivel: desdeServidor.nivel ?? 'primaria',
    sede: desdeServidor.sede ?? 'chilca',
    anio_escolar: desdeServidor.anio_escolar ?? '',
    activo: desdeServidor.activo !== false,
  };
}

export default function EstudiantesPanel({ onClose = null }) {
  const { permissions } = useAuth();
  const puedeGestionarEstudiantes = permissions.includes('gestionar_estudiantes');

  const [vista, setVista] = useState('lista');
  const [lista, setLista] = useState([]);
  const [detalle, setDetalle] = useState(null);
  const [editandoId, setEditandoId] = useState(null);
  const [formulario, setFormulario] = useState(formularioVacio());
  const [cargando, setCargando] = useState(true);
  const [guardando, setGuardando] = useState(false);
  const [errorGeneral, setErrorGeneral] = useState(null);
  const [campoErrores, setCampoErrores] = useState({});
  const [filtros, setFiltros] = useState(filtrosVacios());
  const [filtrosAplicados, setFiltrosAplicados] = useState(filtrosVacios());
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(25);
  const [paginacion, setPaginacion] = useState({
    total: 0,
    lastPage: 1,
    currentPage: 1,
  });

  const cargarLista = useCallback(async (opts = {}) => {
    const filtrosConsulta = opts.filtros ?? filtrosAplicados;
    const pageConsulta = opts.page ?? page;
    const perPageConsulta = opts.perPage ?? perPage;

    setErrorGeneral(null);
    setCampoErrores({});
    try {
      const resultado = await getEstudiantes({
        ...filtrosConsulta,
        page: pageConsulta,
        per_page: perPageConsulta,
      });
      const filas = Array.isArray(resultado?.data) ? resultado.data : [];
      setLista(filas);
      setPaginacion({
        total: resultado?.total ?? filas.length,
        lastPage: resultado?.last_page ?? 1,
        currentPage: resultado?.current_page ?? pageConsulta,
      });
      if (opts.syncPage !== false) {
        setPage(resultado?.current_page ?? pageConsulta);
      }
      if (opts.syncPerPage !== false && resultado?.per_page) {
        setPerPage(resultado.per_page);
      }
    } catch (error) {
      if (error.status === 403) {
        setErrorGeneral('Sin permiso para gestionar estudiantes.');
      } else {
        setErrorGeneral('No se pudo cargar el listado de estudiantes.');
      }
      setLista([]);
      setPaginacion({ total: 0, lastPage: 1, currentPage: 1 });
    }
  }, [filtrosAplicados, page, perPage]);

  useEffect(() => {
    if (vista !== 'lista') {
      setCargando(false);
      return undefined;
    }

    let omitir = false;
    (async () => {
      setCargando(true);
      await cargarLista();
      if (!omitir) {
        setCargando(false);
      }
    })();

    return () => {
      omitir = true;
    };
  }, [vista, cargarLista, page, perPage, filtrosAplicados]);

  async function abrirDetalle(estudianteId) {
    setCargando(true);
    setErrorGeneral(null);
    setCampoErrores({});
    try {
      const item = await getEstudiante(estudianteId);
      setDetalle(item);
      setEditandoId(estudianteId);
      setVista('perfil');
    } catch {
      setErrorGeneral('No se pudo cargar el estudiante.');
    } finally {
      setCargando(false);
    }
  }

  function abrirCreacion() {
    setDetalle(null);
    setEditandoId(null);
    setFormulario(formularioVacio());
    setCampoErrores({});
    setErrorGeneral(null);
    setVista('crear');
  }

  const gradosFormulario = useMemo(
    () => gradosPorNivel(formulario.nivel),
    [formulario.nivel],
  );

  const gradosFiltro = useMemo(
    () => (filtros.nivel ? gradosPorNivel(filtros.nivel) : []),
    [filtros.nivel],
  );

  async function abrirEdicion(estudianteId) {
    setCargando(true);
    setCampoErrores({});
    setErrorGeneral(null);
    try {
      const item = await getEstudiante(estudianteId);
      setDetalle(item);
      setEditandoId(estudianteId);
      setFormulario(llenarFormulario(item));
      setVista('editar');
    } catch {
      setErrorGeneral('No se pudo cargar el estudiante para editar.');
    } finally {
      setCargando(false);
    }
  }

  async function enviarFormulario(esEdicion) {
    if (!gradoEsValidoParaNivel(formulario.nivel, formulario.grado)) {
      setCampoErrores({
        grado: ['El grado no es válido para el nivel indicado.'],
      });
      return;
    }

    const cuerpo = {
      codigo: formulario.codigo.trim(),
      nombres: formulario.nombres.trim(),
      apellidos: formulario.apellidos.trim(),
      grado: formulario.grado.trim(),
      seccion: formulario.seccion.trim(),
      nivel: formulario.nivel,
      sede: formulario.sede,
      anio_escolar: formulario.anio_escolar.trim(),
      fecha_nacimiento: formulario.fecha_nacimiento || null,
      sexo: formulario.sexo || null,
      activo: Boolean(formulario.activo),
    };

    setGuardando(true);
    setCampoErrores({});
    setErrorGeneral(null);

    try {
      if (esEdicion && editandoId) {
        const actualizado = await updateEstudiante(editandoId, cuerpo);
        setDetalle(actualizado ?? null);
        setVista('perfil');
      } else {
        const nuevo = await createEstudiante(cuerpo);
        if (nuevo?.id) {
          setEditandoId(nuevo.id);
          setDetalle(nuevo);
          setCampoErrores({});
          setVista('perfil');
        } else {
          setVista('lista');
        }
      }

      await cargarLista();
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setCampoErrores(error.payload.errors);
      } else if (error.status === 403) {
        setErrorGeneral('Sin permiso para guardar estudiantes.');
      } else {
        setErrorGeneral('No se pudo guardar el estudiante.');
      }
    } finally {
      setGuardando(false);
    }
  }

  function cancelarFormulario() {
    setCampoErrores({});
    setErrorGeneral(null);
    if (vista === 'editar') {
      setVista('perfil');
    } else {
      setVista('lista');
    }
  }

  function tituloActual() {
    if ((vista === 'crear' || vista === 'editar') && !puedeGestionarEstudiantes) {
      return 'Estudiantes';
    }
    if (vista === 'crear') {
      return 'Registrar estudiante';
    }
    if (vista === 'editar') {
      return 'Editar estudiante';
    }
    if (vista === 'perfil') {
      return 'Perfil de estudiante';
    }
    return 'Estudiantes';
  }

  return (
    <Card className="space-y-5 border-[var(--border)] shadow-card" data-testid="estudiantes-panel">
      <div className="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border)] pb-4">
        <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">{tituloActual()}</h2>

        <div className="flex flex-wrap gap-2">
          {vista !== 'lista' ? (
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={() => {
                setCampoErrores({});
                setErrorGeneral(null);
                setVista('lista');
              }}
              data-testid="estudiantes-volver-listado"
            >
              Volver al listado
            </Button>
          ) : null}

          {vista === 'lista' ? (
            <>
              <Button type="button" variant="outline" size="sm" onClick={() => cargarLista()} data-testid="estudiantes-actualizar">
                Actualizar
              </Button>
              {puedeGestionarEstudiantes ? (
                <Button type="button" variant="primary" size="sm" onClick={() => abrirCreacion()} data-testid="estudiantes-nuevo">
                  Nuevo estudiante
                </Button>
              ) : null}
            </>
          ) : null}

          {typeof onClose === 'function' ? (
            <Button type="button" variant="danger" size="sm" onClick={onClose}>
              Cerrar módulo
            </Button>
          ) : null}
        </div>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}

      {vista === 'lista' && cargando ? <LoadingState label="Cargando listado…" /> : null}

      {vista === 'lista' && !cargando ? (
        <>
          <Card className="space-y-4 shadow-sm">
            <h3 className="text-sm font-semibold text-[var(--text)]">Búsqueda y filtros</h3>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              <div className="flex flex-col gap-1 sm:col-span-2">
                <label className="text-xs font-medium text-muted">Buscar (código, nombres o apellidos)</label>
                <input
                  className="sb-field min-w-0"
                  value={filtros.q}
                  onChange={(event) => setFiltros((prev) => ({ ...prev, q: event.target.value }))}
                />
              </div>
              <div className="flex flex-col gap-1">
                <label className="text-xs font-medium text-muted">Sede</label>
                <select
                  className="sb-field min-w-0"
                  value={filtros.sede}
                  onChange={(event) => setFiltros((prev) => ({ ...prev, sede: event.target.value }))}
                >
                  <option value="">Todas</option>
                  <option value="chilca">Chilca</option>
                  <option value="auquimarca">Auquimarca</option>
                </select>
              </div>
              <div className="flex flex-col gap-1">
                <label className="text-xs font-medium text-muted">Nivel</label>
                <select
                  className="sb-field min-w-0"
                  value={filtros.nivel}
                  onChange={(event) =>
                    setFiltros((prev) => {
                      const nivel = event.target.value;
                      const grado = gradoEsValidoParaNivel(nivel, prev.grado) ? prev.grado : '';
                      return { ...prev, nivel, grado };
                    })
                  }
                >
                  <option value="">Todos</option>
                  {NIVELES_ESTUDIANTE.map((nivel) => (
                    <option key={nivel.value} value={nivel.value}>
                      {nivel.label}
                    </option>
                  ))}
                </select>
              </div>
              <div className="flex flex-col gap-1">
                <label className="text-xs font-medium text-muted">Grado</label>
                <select
                  className="sb-field min-w-0"
                  value={filtros.grado}
                  onChange={(event) => setFiltros((prev) => ({ ...prev, grado: event.target.value }))}
                  disabled={!filtros.nivel}
                >
                  <option value="">{filtros.nivel ? 'Todos' : 'Seleccione nivel'}</option>
                  {gradosFiltro.map((grado) => (
                    <option key={grado} value={grado}>
                      {grado}
                    </option>
                  ))}
                </select>
              </div>
              <div className="flex flex-col gap-1">
                <label className="text-xs font-medium text-muted">Sección</label>
                <input
                  className="sb-field min-w-0"
                  value={filtros.seccion}
                  onChange={(event) => setFiltros((prev) => ({ ...prev, seccion: event.target.value }))}
                />
              </div>
              <div className="flex flex-col gap-1">
                <label className="text-xs font-medium text-muted">Año escolar</label>
                <input
                  className="sb-field min-w-0"
                  value={filtros.anio_escolar}
                  onChange={(event) => setFiltros((prev) => ({ ...prev, anio_escolar: event.target.value }))}
                />
              </div>
              <div className="flex items-end gap-2 sm:col-span-2">
                <Button
                  type="button"
                  variant="primary"
                  size="sm"
                  onClick={() => {
                    setPage(1);
                    setFiltrosAplicados({ ...filtros });
                  }}
                >
                  Aplicar filtros
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => {
                    const base = filtrosVacios();
                    setFiltros(base);
                    setFiltrosAplicados(base);
                    setPage(1);
                  }}
                >
                  Limpiar filtros
                </Button>
              </div>
            </div>
          </Card>

          {lista.length === 0 ? (
            <EmptyState
              title="Sin resultados"
              description="No se encontraron estudiantes con los filtros aplicados. Ajuste criterios o limpie filtros."
            />
          ) : (
          <div className="space-y-3">
            <p className="text-sm leading-relaxed text-muted">
              Mostrando {lista.length} de {paginacion.total} estudiantes. Use «Ver perfil» para datos académicos por alumno.
            </p>
            <div className="overflow-x-auto rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm" data-testid="estudiantes-tabla">
              <table className="min-w-full text-left text-sm text-[var(--text)]">
                <thead className="border-b border-[var(--border)] bg-[var(--background)] text-[11px] font-semibold uppercase tracking-wide text-muted">
                  <tr>
                    <th className="px-4 py-3">Estudiante</th>
                    <th className="hidden px-4 py-3 sm:table-cell">Código</th>
                    <th className="px-4 py-3">Grado / Sección</th>
                    <th className="px-4 py-3">Estado</th>
                    <th className="px-4 py-3 text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {lista.map((item, index) => (
                    <tr
                      key={item.id}
                      data-testid={`estudiante-fila-${item.id}`}
                      className={`border-b border-[var(--border)]/70 last:border-0 ${
                        index % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/35'
                      }`}
                    >
                      <td className="px-4 py-3 font-medium">{item.apellidos}, {item.nombres}</td>
                      <td className="hidden px-4 py-3 font-mono text-xs text-muted sm:table-cell">{item.codigo}</td>
                      <td className="px-4 py-3 text-muted">
                        {etiquetaNivelEstudiante(item.nivel)} · {item.grado} · {item.seccion}
                      </td>
                      <td className="px-4 py-3">
                        {item.activo ? (
                          <Badge variant="success" className="normal-case">
                            Activo
                          </Badge>
                        ) : (
                          <Badge variant="neutral" className="normal-case">
                            Inactivo
                          </Badge>
                        )}
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex flex-wrap justify-end gap-2">
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => abrirDetalle(item.id)}
                            data-testid={`estudiante-perfil-${item.id}`}
                          >
                            Ver perfil
                          </Button>
                          {puedeGestionarEstudiantes ? (
                            <Button
                              type="button"
                              variant="ghost"
                              size="sm"
                              className="text-[var(--secondary)]"
                              onClick={() => abrirEdicion(item.id)}
                            >
                              Editar
                            </Button>
                          ) : null}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div
              className="flex flex-col gap-3 border-t border-[var(--border)] pt-4 sm:flex-row sm:items-center sm:justify-between"
              data-testid="estudiantes-paginacion"
            >
              <p className="text-sm text-muted">
                Página {paginacion.currentPage} de {paginacion.lastPage}
                {' · '}
                {paginacion.total} registro{paginacion.total === 1 ? '' : 's'}
              </p>

              <div className="flex flex-wrap items-center gap-2">
                <label className="flex items-center gap-2 text-sm text-muted">
                  Por página
                  <select
                    className="sb-field min-w-0 py-1"
                    value={perPage}
                    disabled={cargando}
                    onChange={(event) => {
                      const valor = Number(event.target.value);
                      setPerPage(valor);
                      setPage(1);
                    }}
                    data-testid="estudiantes-per-page"
                  >
                    <option value={25}>25</option>
                    <option value={50}>50</option>
                    <option value={100}>100</option>
                  </select>
                </label>

                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={cargando || page <= 1}
                  onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                  data-testid="estudiantes-pagina-anterior"
                >
                  Anterior
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={cargando || page >= paginacion.lastPage}
                  onClick={() => setPage((prev) => Math.min(paginacion.lastPage, prev + 1))}
                  data-testid="estudiantes-pagina-siguiente"
                >
                  Siguiente
                </Button>
              </div>
            </div>
          </div>
          )}
        </>
      ) : null}

      {(vista === 'crear' || vista === 'editar') && puedeGestionarEstudiantes && cargando ? (
        <LoadingState label="Cargando formulario…" />
      ) : null}

      {(vista === 'crear' || vista === 'editar') && !puedeGestionarEstudiantes ? (
        <AlertMessage>Su perfil no incluye permiso para crear o editar estudiantes. Use «Volver al listado».</AlertMessage>
      ) : null}

      {(vista === 'crear' || vista === 'editar') && puedeGestionarEstudiantes && !cargando ? (
        <form
          className="space-y-4"
          onSubmit={(event) => {
            event.preventDefault();
            void enviarFormulario(vista === 'editar');
          }}
        >
          <Card className="space-y-4 shadow-sm">
            <div>
              <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Información básica</h3>
              <p className="mt-1 text-sm text-muted">Datos personales del estudiante.</p>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Código</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.codigo}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, codigo: event.target.value }))}
                />
                {campoErrores.codigo ? <p className="text-xs text-red-600">{campoErrores.codigo.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Fecha de nacimiento</label>
                <input
                  type="date"
                  className="sb-field w-full min-w-0"
                  value={formulario.fecha_nacimiento}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, fecha_nacimiento: event.target.value }))}
                />
                {campoErrores.fecha_nacimiento ? <p className="text-xs text-red-600">{campoErrores.fecha_nacimiento.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1 sm:col-span-2">
                <label className="text-sm font-medium text-[var(--text)]">Nombres</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.nombres}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, nombres: event.target.value }))}
                />
                {campoErrores.nombres ? <p className="text-xs text-red-600">{campoErrores.nombres.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1 sm:col-span-2">
                <label className="text-sm font-medium text-[var(--text)]">Apellidos</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.apellidos}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, apellidos: event.target.value }))}
                />
                {campoErrores.apellidos ? <p className="text-xs text-red-600">{campoErrores.apellidos.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Sexo</label>
                <select
                  className="sb-field w-full min-w-0"
                  value={formulario.sexo}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, sexo: event.target.value }))}
                >
                  <option value="">Sin especificar</option>
                  <option value="M">M</option>
                  <option value="F">F</option>
                </select>
                {campoErrores.sexo ? <p className="text-xs text-red-600">{campoErrores.sexo.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col justify-end gap-1 sm:col-span-2">
                <label className="flex cursor-pointer items-center gap-3 text-sm font-medium text-[var(--text)]">
                  <input
                    type="checkbox"
                    checked={Boolean(formulario.activo)}
                    onChange={(event) => setFormulario((valor) => ({ ...valor, activo: event.target.checked }))}
                  />
                  Estudiante activo
                </label>
              </div>
            </div>
          </Card>

          <Card className="space-y-4 shadow-sm">
            <div>
              <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Información académica</h3>
              <p className="mt-1 text-sm text-muted">Ubicación y año escolar del estudiante.</p>
            </div>

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Año escolar</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.anio_escolar}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, anio_escolar: event.target.value }))}
                />
                {campoErrores.anio_escolar ? <p className="text-xs text-red-600">{campoErrores.anio_escolar.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Nivel</label>
                <select
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.nivel}
                  onChange={(event) =>
                    setFormulario((valor) => {
                      const nivel = event.target.value;
                      const grado = gradoEsValidoParaNivel(nivel, valor.grado) ? valor.grado : '';
                      return { ...valor, nivel, grado };
                    })
                  }
                >
                  {NIVELES_ESTUDIANTE.map((nivel) => (
                    <option key={nivel.value} value={nivel.value}>
                      {nivel.label}
                    </option>
                  ))}
                </select>
                {campoErrores.nivel ? <p className="text-xs text-red-600">{campoErrores.nivel.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Grado</label>
                <select
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.grado}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, grado: event.target.value }))}
                  disabled={gradosFormulario.length === 0}
                >
                  <option value="">Seleccione…</option>
                  {gradosFormulario.map((grado) => (
                    <option key={grado} value={grado}>
                      {grado}
                    </option>
                  ))}
                </select>
                {campoErrores.grado ? <p className="text-xs text-red-600">{campoErrores.grado.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Sección</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.seccion}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, seccion: event.target.value }))}
                />
                {campoErrores.seccion ? <p className="text-xs text-red-600">{campoErrores.seccion.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Sede</label>
                <select
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.sede}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, sede: event.target.value }))}
                >
                  <option value="chilca">Chilca</option>
                  <option value="auquimarca">Auquimarca</option>
                </select>
                {campoErrores.sede ? <p className="text-xs text-red-600">{campoErrores.sede.join(' ')}</p> : null}
              </div>
            </div>
          </Card>

          <div className="flex flex-wrap justify-end gap-2 border-t border-[var(--border)] pt-4">
            <Button type="button" variant="outline" size="sm" disabled={guardando} onClick={cancelarFormulario}>
              Cancelar
            </Button>
            <Button disabled={guardando} type="submit" variant="primary" size="sm" data-testid="estudiante-guardar">
              {guardando ? 'Guardando…' : vista === 'editar' ? 'Guardar cambios' : 'Guardar estudiante'}
            </Button>
          </div>
        </form>
      ) : null}

      {vista === 'perfil' && cargando ? <LoadingState label="Cargando perfil…" /> : null}

      {vista === 'perfil' && !cargando && detalle ? (
        <div className="space-y-6 text-sm text-[var(--text)]">
          <Card className="border-[var(--border)] bg-[var(--surface)] shadow-sm ring-1 ring-[var(--border)]/60">
            <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Datos generales</h3>
            <dl className="mt-3 grid gap-x-6 gap-y-4 sm:grid-cols-2">
              <div>
                <dt className="text-muted">Código</dt>
                <dd className="mt-0.5 font-medium">{detalle.codigo}</dd>
              </div>
              <div>
                <dt className="text-muted">Año escolar</dt>
                <dd className="mt-0.5 font-medium">{detalle.anio_escolar}</dd>
              </div>
              <div>
                <dt className="text-muted">Nombres</dt>
                <dd className="mt-0.5">{detalle.nombres}</dd>
              </div>
              <div>
                <dt className="text-muted">Apellidos</dt>
                <dd className="mt-0.5">{detalle.apellidos}</dd>
              </div>
              <div>
                <dt className="text-muted">Grado</dt>
                <dd className="mt-0.5">{detalle.grado}</dd>
              </div>
              <div>
                <dt className="text-muted">Sección</dt>
                <dd className="mt-0.5">{detalle.seccion}</dd>
              </div>
              <div>
                <dt className="text-muted">Nivel</dt>
                <dd className="mt-0.5">{etiquetaNivelEstudiante(detalle.nivel)}</dd>
              </div>
              <div>
                <dt className="text-muted">Sede</dt>
                <dd className="mt-0.5">
                  {detalle.sede === 'chilca' ? 'Chilca' : detalle.sede === 'auquimarca' ? 'Auquimarca' : detalle.sede}
                </dd>
              </div>
              <div>
                <dt className="text-muted">Activo</dt>
                <dd className="mt-0.5">{detalle.activo ? 'Sí' : 'No'}</dd>
              </div>
              <div>
                <dt className="text-muted">Fecha de nacimiento</dt>
                <dd className="mt-0.5">{detalle.fecha_nacimiento ?? '—'}</dd>
              </div>
              <div>
                <dt className="text-muted">Sexo</dt>
                <dd className="mt-0.5">{detalle.sexo ?? '—'}</dd>
              </div>
            </dl>
          </Card>

          <EstudiantePerfilRiesgo />

          {permissions.includes('registrar_datos_academicos') ||
          permissions.includes('ver_notas_academicas') ||
          permissions.includes('ver_asistencia_curricular') ||
          permissions.includes('registrar_asistencia_curricular') ? (
            <EstudiantePerfilDatos
              estudianteId={detalle.id}
              anioEscolarPorDefecto={detalle.anio_escolar}
              mostrarResumenCurricular={permissions.includes('ver_notas_academicas')}
              mostrarAsistenciaCurricular={
                permissions.includes('ver_asistencia_curricular')
                || permissions.includes('registrar_asistencia_curricular')
              }
            />
          ) : null}

          {puedeGestionarEstudiantes ? (
            <div className="flex flex-wrap gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => {
                  void abrirEdicion(detalle.id);
                }}
                data-testid="perfil-editar"
              >
                Editar estudiante
              </Button>
            </div>
          ) : null}
        </div>
      ) : null}
    </Card>
  );
}
