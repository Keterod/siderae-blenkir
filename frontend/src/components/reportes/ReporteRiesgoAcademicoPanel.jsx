import { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { getReportesRiesgoAcademico } from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const NIVELES_RIESGO = [
  { value: '', label: 'Todos' },
  { value: 'Alto', label: 'Alto' },
  { value: 'Medio', label: 'Medio' },
  { value: 'Bajo', label: 'Bajo' },
];

const BIMESTRES = [
  { value: '', label: 'Todos' },
  { value: '1', label: 'I' },
  { value: '2', label: 'II' },
  { value: '3', label: 'III' },
  { value: '4', label: 'IV' },
];

function variantBadgePorNivel(nivel) {
  if (nivel === 'Alto') return 'danger';
  if (nivel === 'Medio') return 'warning';
  if (nivel === 'Bajo') return 'success';
  return 'neutral';
}

function filtrosVacios() {
  return {
    anio_escolar: '',
    bimestre: '',
    grado: '',
    seccion: '',
    nivel: '',
  };
}

export default function ReporteRiesgoAcademicoPanel() {
  const { permissions } = useAuth();
  const puedeVer = permissions.includes('ver_reportes_riesgo');

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

    getReportesRiesgoAcademico({
      ...filtrosAplicados,
      page,
      per_page: perPage,
    })
      .then((respuesta) => {
        if (!active) return;
        setLista(Array.isArray(respuesta?.data) ? respuesta.data : []);
        setPaginacion({
          current_page: respuesta?.current_page ?? 1,
          last_page: respuesta?.last_page ?? 1,
          total: respuesta?.total ?? 0,
        });
        setErrorGeneral(null);
      })
      .catch((error) => {
        if (!active) return;
        if (error.status === 403) {
          setErrorGeneral('Sin permiso para ver reportes de riesgo.');
        } else {
          setErrorGeneral('No se pudo cargar el reporte de riesgo académico.');
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
      <Card className="space-y-3 border-[var(--border)] shadow-card" data-testid="reportes-riesgo-sin-permiso">
        <p className="text-sm text-muted">No tienes permiso para ver reportes de riesgo académico.</p>
      </Card>
    );
  }

  return (
    <Card className="space-y-5 border-[var(--border)] shadow-card" data-testid="reportes-riesgo-panel">
      <div className="border-b border-[var(--border)] pb-4">
        <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">Reportes de riesgo académico</h2>
        <p className="mt-2 max-w-3xl text-sm leading-relaxed text-muted">
          Consulta el listado de estudiantes con riesgo académico calculado. Los resultados se filtran por año escolar,
          grado, sección, bimestre y nivel de riesgo. No se recalcula el riesgo ni se requiere información
          socioeconómica.
        </p>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}

      <Card className="space-y-4 shadow-sm">
        <h3 className="text-sm font-semibold text-[var(--text)]">Filtros</h3>
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="rra-anio">Año escolar</label>
            <input
              id="rra-anio"
              type="text"
              inputMode="numeric"
              className="sb-field min-w-0"
              placeholder="Ej: 2026"
              value={filtros.anio_escolar}
              onChange={(e) => actualizarFiltro('anio_escolar', e.target.value)}
            />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="rra-bimestre">Bimestre</label>
            <select
              id="rra-bimestre"
              className="sb-field"
              value={filtros.bimestre}
              onChange={(e) => actualizarFiltro('bimestre', e.target.value)}
            >
              {BIMESTRES.map((opcion) => (
                <option key={opcion.value || 'todos-bimestre'} value={opcion.value}>
                  {opcion.label}
                </option>
              ))}
            </select>
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="rra-grado">Grado</label>
            <input
              id="rra-grado"
              type="text"
              className="sb-field min-w-0"
              placeholder="Ej: 5°"
              value={filtros.grado}
              onChange={(e) => actualizarFiltro('grado', e.target.value)}
            />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="rra-seccion">Sección</label>
            <input
              id="rra-seccion"
              type="text"
              className="sb-field min-w-0"
              placeholder="Ej: A"
              value={filtros.seccion}
              onChange={(e) => actualizarFiltro('seccion', e.target.value)}
            />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted" htmlFor="rra-riesgo">Nivel de riesgo</label>
            <select
              id="rra-riesgo"
              className="sb-field"
              value={filtros.nivel}
              onChange={(e) => actualizarFiltro('nivel', e.target.value)}
            >
              {NIVELES_RIESGO.map((opcion) => (
                <option key={opcion.value || 'todos-nivel'} value={opcion.value}>
                  {opcion.label}
                </option>
              ))}
            </select>
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

      {cargando ? <LoadingState label="Cargando reporte de riesgo…" /> : null}

      {!cargando && lista.length === 0 ? (
        <EmptyState
          title="Sin resultados"
          description="No hay registros de riesgo académico con los filtros seleccionados. Verifique que existan datos procesados."
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
                  <th className="px-4 py-3">Grado</th>
                  <th className="px-4 py-3">Sección</th>
                  <th className="px-4 py-3">Año escolar</th>
                  <th className="px-4 py-3">Bimestre</th>
                  <th className="px-4 py-3">Índice</th>
                  <th className="px-4 py-3">Nivel</th>
                  <th className="px-4 py-3">Fecha</th>
                </tr>
              </thead>
              <tbody>
                {lista.map((item, index) => (
                  <tr
                    key={item.id}
                    className={`border-b border-[var(--border)]/70 last:border-0 ${
                      index % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/35'
                    }`}
                  >
                    <td className="px-4 py-3 font-medium">{item.estudiante ?? '—'}</td>
                    <td className="px-4 py-3 text-muted">{item.grado ?? '—'}</td>
                    <td className="px-4 py-3 text-muted">{item.seccion ?? '—'}</td>
                    <td className="px-4 py-3 text-muted">{item.anio_escolar ?? '—'}</td>
                    <td className="px-4 py-3 text-muted">{item.bimestre ?? '—'}</td>
                    <td className="px-4 py-3 font-mono text-xs tabular-nums">{item.indice?.toFixed(4) ?? '—'}</td>
                    <td className="px-4 py-3">
                      <Badge variant={variantBadgePorNivel(item.nivel)} className="normal-case">
                        {item.nivel ?? '—'}
                      </Badge>
                    </td>
                    <td className="px-4 py-3 text-muted">{item.fecha ?? '—'}</td>
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
