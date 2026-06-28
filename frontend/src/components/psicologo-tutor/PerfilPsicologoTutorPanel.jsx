import { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { getSeguimientoPsicologoTutor } from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const NIVELES_ESTUDIANTE = [
  { value: '', label: 'Todos' },
  { value: 'inicial', label: 'Inicial' },
  { value: 'primaria', label: 'Primaria' },
  { value: 'secundaria', label: 'Secundaria' },
];

const NIVELES_RIESGO = [
  { value: '', label: 'Todos' },
  { value: 'Bajo', label: 'Bajo' },
  { value: 'Medio', label: 'Medio' },
  { value: 'Alto', label: 'Alto' },
];

function variantBadgePorNivel(nivel) {
  if (nivel === 'Alto') return 'danger';
  if (nivel === 'Medio') return 'warning';
  if (nivel === 'Bajo') return 'success';
  return 'neutral';
}

function variantBadgePorSemaforo(color) {
  if (color === 'verde') return 'success';
  if (color === 'amarillo') return 'warning';
  if (color === 'rojo') return 'danger';
  return 'neutral';
}

function etiquetaSemaforo(color) {
  if (color === 'verde') return 'Verde';
  if (color === 'amarillo') return 'Amarillo';
  if (color === 'rojo') return 'Rojo';
  return '—';
}

function filtrosVacios() {
  return {
    anio_escolar: '',
    nivel: '',
    grado: '',
    seccion: '',
    nivel_riesgo: '',
    con_reportes_activos: false,
    con_alertas_activas: false,
  };
}

export default function PerfilPsicologoTutorPanel() {
  const { permissions } = useAuth();
  const puedeVer = permissions.includes('ver_perfil_psicologo_tutor');

  const [filtros, setFiltros] = useState(filtrosVacios());
  const [filtrosAplicados, setFiltrosAplicados] = useState(filtrosVacios());
  const [lista, setLista] = useState([]);
  const [paginacion, setPaginacion] = useState({
    current_page: 1,
    last_page: 1,
    total: 0,
  });
  const [page, setPage] = useState(1);
  const [perPage] = useState(15);
  const [cargando, setCargando] = useState(true);
  const [errorGeneral, setErrorGeneral] = useState(null);

  useEffect(() => {
    if (!puedeVer) {
      return;
    }

    let active = true;

    getSeguimientoPsicologoTutor({
      ...filtrosAplicados,
      page,
      per_page: perPage,
    })
      .then((respuesta) => {
        if (!active) return;
        setLista(Array.isArray(respuesta?.data) ? respuesta.data : []);
        const meta = respuesta?.meta || {};
        setPaginacion({
          current_page: meta.current_page ?? 1,
          last_page: meta.last_page ?? 1,
          total: meta.total ?? 0,
        });
        setErrorGeneral(null);
      })
      .catch((error) => {
        if (!active) return;
        if (error.status === 403) {
          setErrorGeneral('Sin permiso para ver el seguimiento psicólogo/tutor.');
        } else {
          setErrorGeneral('No se pudo cargar el listado de seguimiento.');
        }
        setLista([]);
        setPaginacion({ current_page: 1, last_page: 1, total: 0 });
      })
      .finally(() => {
        if (active) {
          setCargando(false);
        }
      });

    return () => {
      active = false;
    };
  }, [puedeVer, filtrosAplicados, page, perPage]);

  function actualizarFiltro(campo, valor) {
    setFiltros((prev) => ({ ...prev, [campo]: valor }));
  }

  function aplicarFiltros() {
    setCargando(true);
    setFiltrosAplicados(filtros);
    setPage(1);
  }

  function limpiarFiltros() {
    const vacios = filtrosVacios();
    setCargando(true);
    setFiltros(vacios);
    setFiltrosAplicados(vacios);
    setPage(1);
  }

  function cambiarPagina(nuevaPagina) {
    setCargando(true);
    setPage(nuevaPagina);
  }

  if (!puedeVer) {
    return (
      <Card className="space-y-3 border-[var(--border)] shadow-card" data-testid="psicologo-tutor-sin-permiso">
        <p className="text-sm text-muted">No tienes permiso para ver el seguimiento psicólogo/tutor.</p>
      </Card>
    );
  }

  return (
    <Card className="space-y-5 border-[var(--border)] shadow-card" data-testid="psicologo-tutor-panel">
      <div className="border-b border-[var(--border)] pb-4">
        <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">Seguimiento psicólogo/tutor</h2>
        <p className="mt-2 max-w-3xl text-sm leading-relaxed text-muted">
          Listado de estudiantes de la sede Chilca que presentan señales de seguimiento: riesgo académico registrado,
          reportes conductuales activos o alertas activas. Esta vista es de apoyo institucional y tutorial;
          <strong>no es un diagnóstico clínico ni médico</strong>.
        </p>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}

      <Card className="space-y-4 shadow-sm">
        <h3 className="text-sm font-semibold text-[var(--text)]">Filtros</h3>
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="ppt-anio">Año escolar</label>
            <input
              id="ppt-anio"
              type="text"
              inputMode="numeric"
              className="sb-field min-w-0"
              placeholder="Ej: 2026"
              value={filtros.anio_escolar}
              onChange={(e) => actualizarFiltro('anio_escolar', e.target.value)}
            />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="ppt-nivel">Nivel</label>
            <select
              id="ppt-nivel"
              className="sb-field"
              value={filtros.nivel}
              onChange={(e) => actualizarFiltro('nivel', e.target.value)}
            >
              {NIVELES_ESTUDIANTE.map((opcion) => (
                <option key={opcion.value || 'todos-nivel'} value={opcion.value}>
                  {opcion.label}
                </option>
              ))}
            </select>
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="ppt-grado">Grado</label>
            <input
              id="ppt-grado"
              type="text"
              className="sb-field min-w-0"
              placeholder="Ej: 5°"
              value={filtros.grado}
              onChange={(e) => actualizarFiltro('grado', e.target.value)}
            />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="ppt-seccion">Sección</label>
            <input
              id="ppt-seccion"
              type="text"
              className="sb-field min-w-0"
              placeholder="Ej: A"
              value={filtros.seccion}
              onChange={(e) => actualizarFiltro('seccion', e.target.value)}
            />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="ppt-riesgo">Nivel de riesgo</label>
            <select
              id="ppt-riesgo"
              className="sb-field"
              value={filtros.nivel_riesgo}
              onChange={(e) => actualizarFiltro('nivel_riesgo', e.target.value)}
            >
              {NIVELES_RIESGO.map((opcion) => (
                <option key={opcion.value || 'todos-riesgo'} value={opcion.value}>
                  {opcion.label}
                </option>
              ))}
            </select>
          </div>
          <div className="flex items-center gap-2 sm:col-span-2 lg:col-span-2">
            <label className="flex items-center gap-2 text-sm text-[var(--text)]">
              <input
                type="checkbox"
                className="h-4 w-4 rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--primary)]"
                checked={filtros.con_reportes_activos}
                onChange={(e) => actualizarFiltro('con_reportes_activos', e.target.checked)}
              />
              Solo con reportes activos
            </label>
            <label className="flex items-center gap-2 text-sm text-[var(--text)]">
              <input
                type="checkbox"
                className="h-4 w-4 rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--primary)]"
                checked={filtros.con_alertas_activas}
                onChange={(e) => actualizarFiltro('con_alertas_activas', e.target.checked)}
              />
              Solo con alertas activas
            </label>
          </div>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button type="button" variant="primary" size="sm" onClick={aplicarFiltros} disabled={cargando}>
            Buscar
          </Button>
          <Button type="button" variant="outline" size="sm" onClick={limpiarFiltros} disabled={cargando}>
            Limpiar filtros
          </Button>
        </div>
      </Card>

      {cargando ? <LoadingState label="Cargando seguimiento…" /> : null}

      {!cargando && lista.length === 0 ? (
        <EmptyState
          title="Sin casos de seguimiento"
          description="No hay estudiantes con señales de seguimiento para los filtros seleccionados. Verifique que existan índices de riesgo, reportes conductuales o alertas activas."
        />
      ) : null}

      {!cargando && lista.length > 0 ? (
        <div className="space-y-3">
          <p className="text-sm text-muted">
            {paginacion.total === 1 ? '1 registro encontrado.' : `${paginacion.total} registros encontrados.`}
          </p>
          <div className="overflow-x-auto rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm">
            <table className="min-w-full text-left text-sm text-[var(--text)]">
              <thead className="border-b border-[var(--border)] bg-[var(--background)] text-[11px] font-semibold uppercase tracking-wide text-muted">
                <tr>
                  <th className="px-4 py-3">Estudiante</th>
                  <th className="px-4 py-3">Grado / Sección</th>
                  <th className="px-4 py-3">Último riesgo</th>
                  <th className="px-4 py-3">Fecha riesgo</th>
                  <th className="px-4 py-3">Reportes activos</th>
                  <th className="px-4 py-3">Alertas activas</th>
                  <th className="px-4 py-3">Completitud</th>
                </tr>
              </thead>
              <tbody>
                {lista.map((item, index) => (
                  <tr
                    key={item.estudiante_id ?? index}
                    className={`border-b border-[var(--border)]/70 last:border-0 ${
                      index % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/35'
                    }`}
                  >
                    <td className="px-4 py-3 font-medium">{item.estudiante ?? '—'}</td>
                    <td className="px-4 py-3 text-muted">
                      {[item.grado, item.seccion].filter(Boolean).join(' / ') || '—'}
                    </td>
                    <td className="px-4 py-3">
                      {item.ultimo_nivel ? (
                        <div className="flex flex-col gap-0.5">
                          <Badge variant={variantBadgePorNivel(item.ultimo_nivel)} className="w-fit normal-case">
                            {item.ultimo_nivel}
                          </Badge>
                          {item.ultimo_indice !== null && item.ultimo_indice !== undefined ? (
                            <span className="font-mono text-xs tabular-nums text-muted">
                              índice {item.ultimo_indice.toFixed(4)}
                            </span>
                          ) : null}
                        </div>
                      ) : (
                        '—'
                      )}
                    </td>
                    <td className="px-4 py-3 text-muted">{item.fecha_ultimo_riesgo ?? '—'}</td>
                    <td className="px-4 py-3 text-muted">{item.reportes_conductuales_activos ?? '—'}</td>
                    <td className="px-4 py-3 text-muted">{item.alertas_activas ?? '—'}</td>
                    <td className="px-4 py-3">
                      <Badge variant={variantBadgePorSemaforo(item.semaforo_completitud)} className="normal-case">
                        {etiquetaSemaforo(item.semaforo_completitud)}
                      </Badge>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {paginacion.last_page > 1 ? (
            <div className="flex items-center justify-between gap-2 text-sm text-muted">
              <span>
                Página {paginacion.current_page} de {paginacion.last_page} ({paginacion.total} registros)
              </span>
              <div className="flex gap-2">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={page <= 1}
                  onClick={() => cambiarPagina(page - 1)}
                >
                  Anterior
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={page >= paginacion.last_page}
                  onClick={() => cambiarPagina(page + 1)}
                >
                  Siguiente
                </Button>
              </div>
            </div>
          ) : null}
        </div>
      ) : null}
    </Card>
  );
}
