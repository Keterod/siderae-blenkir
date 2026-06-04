import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  activarMateria,
  actualizarMateria,
  crearMateria,
  desactivarMateria,
  listarMaterias,
} from '../../lib/api';
import { anioEscolarActual, gradoEsValidoParaNivel, gradosPorNivel } from '../../lib/academico';
import { ETIQUETA_SEDE_OPERATIVA, SEDE_OPERATIVA, conSedeOperativa } from '../../lib/sedeOperativa';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const F_INICIAL = {
  nombre: '',
  nivel: 'primaria',
  grado: '',
  anio_escolar: anioEscolarActual(),
  sede: SEDE_OPERATIVA,
};

const F_FILTROS = {
  nivel: '',
  grado: '',
  anio_escolar: anioEscolarActual(),
  activo: '',
};

function filtroActivoParam(valor) {
  if (valor === '' || valor === 'todos') {
    return {};
  }
  if (valor === 'si') {
    return { activo: true };
  }
  if (valor === 'no') {
    return { activo: false };
  }
  return {};
}

export default function MateriasPanel() {
  const [filtros, setFiltros] = useState(F_FILTROS);
  const [aplicados, setAplicados] = useState(F_FILTROS);
  const [items, setItems] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [errorGeneral, setErrorGeneral] = useState(null);
  const [formulario, setFormulario] = useState(F_INICIAL);
  const [formErr, setFormErr] = useState({});
  const [guardando, setGuardando] = useState(false);

  const [editId, setEditId] = useState(null);
  const [formEdicion, setFormEdicion] = useState(null);
  const [errEdicion, setErrEdicion] = useState({});

  const queryParams = useMemo(() => {
    const p = {};
    if (aplicados.nivel) {
      p.nivel = aplicados.nivel;
    }
    if (aplicados.grado) {
      p.grado = aplicados.grado.trim();
    }
    if (aplicados.anio_escolar) {
      p.anio_escolar = aplicados.anio_escolar.trim();
    }
    return conSedeOperativa({ ...p, ...filtroActivoParam(aplicados.activo) });
  }, [aplicados]);

  const cargar = useCallback(async () => {
    setCargando(true);
    setErrorGeneral(null);
    try {
      const data = await listarMaterias(queryParams);
      setItems(Array.isArray(data) ? data : []);
    } catch (error) {
      if (error.status === 403) {
        setErrorGeneral('No tiene permiso para gestionar el catálogo de materias.');
      } else {
        setErrorGeneral('No se pudo cargar el catálogo de materias.');
      }
      setItems([]);
    } finally {
      setCargando(false);
    }
  }, [queryParams]);

  useEffect(() => {
    void cargar();
  }, [cargar]);

  function aplicarFiltros(event) {
    event.preventDefault();
    const next = { ...filtros };
    setAplicados(next);
    setFormulario((prev) => ({
      ...prev,
      sede: SEDE_OPERATIVA,
      nivel: next.nivel || prev.nivel,
      grado: next.grado || prev.grado,
      anio_escolar: next.anio_escolar || prev.anio_escolar,
    }));
  }

  async function crear(event) {
    event.preventDefault();
    setFormErr({});
    setGuardando(true);
    try {
      await crearMateria({
        nombre: formulario.nombre.trim(),
        nivel: formulario.nivel,
        grado: formulario.grado.trim(),
        anio_escolar: formulario.anio_escolar.trim(),
        sede: SEDE_OPERATIVA,
        activo: true,
      });
      setFormulario((prev) => ({
        ...F_INICIAL,
        sede: SEDE_OPERATIVA,
        nivel: aplicados.nivel || prev.nivel || F_INICIAL.nivel,
        grado: aplicados.grado || '',
        anio_escolar: aplicados.anio_escolar || prev.anio_escolar || F_INICIAL.anio_escolar,
      }));
      await cargar();
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setFormErr(error.payload.errors);
      } else if (error.payload?.message) {
        setFormErr({ nombre: [error.payload.message] });
      } else {
        setFormErr({ nombre: ['Error al guardar.'] });
      }
    } finally {
      setGuardando(false);
    }
  }

  function abrirEditar(row) {
    setEditId(row.id);
    setFormEdicion({
      nombre: row.nombre,
      nivel: row.nivel,
      grado: row.grado,
      anio_escolar: row.anio_escolar,
      sede: row.sede,
    });
    setErrEdicion({});
  }

  function cerrarEditar() {
    setEditId(null);
    setFormEdicion(null);
    setErrEdicion({});
  }

  async function guardarEdicion(event) {
    event.preventDefault();
    if (!editId || !formEdicion) {
      return;
    }
    setErrEdicion({});
    setGuardando(true);
    try {
      await actualizarMateria(editId, {
        nombre: formEdicion.nombre.trim(),
        nivel: formEdicion.nivel,
        grado: formEdicion.grado.trim(),
        anio_escolar: formEdicion.anio_escolar.trim(),
        sede: SEDE_OPERATIVA,
      });
      cerrarEditar();
      await cargar();
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setErrEdicion(error.payload.errors);
      } else if (error.payload?.message) {
        setErrEdicion({ nombre: [error.payload.message] });
      } else {
        setErrEdicion({ nombre: ['Error al actualizar.'] });
      }
    } finally {
      setGuardando(false);
    }
  }

  async function alternarActivo(row) {
    setErrorGeneral(null);
    try {
      if (row.activo) {
        await desactivarMateria(row.id);
      } else {
        await activarMateria(row.id);
      }
      await cargar();
    } catch {
      setErrorGeneral('No se pudo cambiar el estado de la materia.');
    }
  }

  const gradosFiltro = useMemo(
    () => (filtros.nivel ? gradosPorNivel(filtros.nivel) : []),
    [filtros.nivel],
  );
  const gradosCrear = useMemo(
    () => gradosPorNivel(formulario.nivel),
    [formulario.nivel],
  );
  const gradosEditar = useMemo(
    () => (formEdicion?.nivel ? gradosPorNivel(formEdicion.nivel) : []),
    [formEdicion?.nivel],
  );

  return (
    <div className="space-y-6" data-testid="panel-materias">
      <Card className="border-[var(--border)] bg-[var(--surface)] shadow-card">
        <h2 className="text-lg font-semibold tracking-tight text-[var(--text)]">Gestión de materias</h2>
        <p className="mt-2 text-sm leading-relaxed text-muted">
          Catálogo de la sede {ETIQUETA_SEDE_OPERATIVA} por nivel, grado y año escolar. Las materias desactivadas no pueden asignarse a nuevas notas con
          vínculo oficial. No se eliminan filas usadas en el historial.
        </p>
      </Card>

      {errorGeneral ? (
        <AlertMessage variant="warning">{errorGeneral}</AlertMessage>
      ) : null}

      <Card padding className="border-[var(--border)] shadow-sm">
        <h3 className="text-sm font-semibold text-[var(--text)]">Filtrar catálogo</h3>
        <p className="mt-2 text-xs text-muted">Sede: {ETIQUETA_SEDE_OPERATIVA}</p>
        <form className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" onSubmit={(e) => aplicarFiltros(e)}>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted">Nivel</label>
            <select
              className="sb-field min-w-0"
              value={filtros.nivel}
              onChange={(e) =>
                setFiltros((f) => {
                  const nivel = e.target.value;
                  const grado = gradoEsValidoParaNivel(nivel, f.grado) ? f.grado : '';
                  return { ...f, nivel, grado };
                })
              }
            >
              <option value="">Todos</option>
              <option value="primaria">Primaria</option>
              <option value="secundaria">Secundaria</option>
            </select>
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted">Grado</label>
            <select
              className="sb-field min-w-0"
              value={filtros.grado}
              onChange={(e) => setFiltros((f) => ({ ...f, grado: e.target.value }))}
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
            <label className="text-xs font-medium text-muted">Año escolar</label>
            <input
              className="sb-field min-w-0"
              value={filtros.anio_escolar}
              onChange={(e) => setFiltros((f) => ({ ...f, anio_escolar: e.target.value }))}
              placeholder="2026"
            />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted">Activo</label>
            <select
              className="sb-field min-w-0"
              value={filtros.activo}
              onChange={(e) => setFiltros((f) => ({ ...f, activo: e.target.value }))}
            >
              <option value="">Todos</option>
              <option value="si">Activas</option>
              <option value="no">Inactivas</option>
            </select>
          </div>
          <div className="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-4">
            <Button type="submit" variant="primary" size="sm" data-testid="materias-filtrar">
              Aplicar filtros
            </Button>
          </div>
        </form>
      </Card>

      <Card padding className="border-[var(--border)] shadow-sm">
        <h3 className="text-sm font-semibold text-[var(--text)]">Registrar materia nueva</h3>
        <p className="mt-1 text-xs text-muted">Sede: {ETIQUETA_SEDE_OPERATIVA}</p>
        <form className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" onSubmit={(e) => void crear(e)}>
          <div className="flex flex-col gap-1 sm:col-span-2">
            <label className="text-xs font-medium text-muted">Nombre</label>
            <input
              required
              className="sb-field min-w-0"
              value={formulario.nombre}
              onChange={(e) => setFormulario((v) => ({ ...v, nombre: e.target.value }))}
            />
            {formErr.nombre?.[0] ? <p className="text-xs text-red-600">{formErr.nombre[0]}</p> : null}
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted">Nivel</label>
            <select
              required
              className="sb-field min-w-0"
              value={formulario.nivel}
              onChange={(e) =>
                setFormulario((v) => {
                  const nivel = e.target.value;
                  const grado = gradoEsValidoParaNivel(nivel, v.grado) ? v.grado : '';
                  return { ...v, nivel, grado };
                })
              }
            >
              <option value="primaria">Primaria</option>
              <option value="secundaria">Secundaria</option>
            </select>
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted">Grado</label>
            <select
              required
              className="sb-field min-w-0"
              value={formulario.grado}
              onChange={(e) => setFormulario((v) => ({ ...v, grado: e.target.value }))}
            >
              <option value="">Seleccione…</option>
              {gradosCrear.map((grado) => (
                <option key={grado} value={grado}>
                  {grado}
                </option>
              ))}
            </select>
            {formErr.grado?.[0] ? <p className="text-xs text-red-600">{formErr.grado[0]}</p> : null}
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-muted">Año escolar</label>
            <input
              required
              className="sb-field min-w-0"
              value={formulario.anio_escolar}
              onChange={(e) => setFormulario((v) => ({ ...v, anio_escolar: e.target.value }))}
            />
            {formErr.anio_escolar?.[0] ? <p className="text-xs text-red-600">{formErr.anio_escolar[0]}</p> : null}
          </div>
          <div className="sm:col-span-2 lg:col-span-4">
            <Button type="submit" variant="primary" size="sm" disabled={guardando} data-testid="materia-guardar-nueva">
              {guardando ? 'Guardando…' : 'Registrar materia'}
            </Button>
          </div>
        </form>
      </Card>

      {cargando ? (
        <LoadingState label="Cargando materias…" />
      ) : items.length === 0 ? (
        <EmptyState title="Sin materias con los filtros aplicados" description="Ajuste los filtros o registre una nueva." />
      ) : (
        <Card padding={false} className="overflow-hidden border-[var(--border)] shadow-sm">
          <div className="overflow-x-auto">
            <table className="min-w-full text-left text-sm">
              <thead className="border-b border-[var(--border)] bg-[var(--background)]/70 text-xs uppercase text-muted">
                <tr>
                  <th className="px-4 py-3 font-semibold tracking-wide">Nombre</th>
                  <th className="px-4 py-3 font-semibold tracking-wide">Sede</th>
                  <th className="px-4 py-3 font-semibold tracking-wide">Nivel</th>
                  <th className="px-4 py-3 font-semibold tracking-wide">Grado</th>
                  <th className="px-4 py-3 font-semibold tracking-wide">Año</th>
                  <th className="px-4 py-3 font-semibold tracking-wide">Estado</th>
                  <th className="px-4 py-3 font-semibold tracking-wide">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {items.map((row) => (
                  <tr key={row.id} className="border-b border-[var(--border)]/70 last:border-b-0">
                    <td className="px-4 py-3 font-medium text-[var(--text)]">{row.nombre}</td>
                    <td className="px-4 py-3 text-muted">{ETIQUETA_SEDE_OPERATIVA}</td>
                    <td className="px-4 py-3 text-muted">{row.nivel}</td>
                    <td className="px-4 py-3 text-muted">{row.grado}</td>
                    <td className="px-4 py-3 text-muted">{row.anio_escolar}</td>
                    <td className="px-4 py-3">
                      <span
                        className={`rounded px-2 py-0.5 text-xs font-medium ${
                          row.activo ? 'bg-emerald-50 text-emerald-800' : 'bg-neutral-100 text-neutral-600'
                        }`}
                      >
                        {row.activo ? 'Activa' : 'Inactiva'}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex flex-wrap gap-2">
                        <Button type="button" variant="outline" size="sm" onClick={() => abrirEditar(row)}>
                          Editar
                        </Button>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          className="text-[var(--secondary)]"
                          onClick={() => void alternarActivo(row)}
                        >
                          {row.activo ? 'Desactivar' : 'Reactivar'}
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>
      )}

      {editId !== null && formEdicion ? (
        <Card padding className="border-[var(--border)] shadow-card ring-1 ring-[var(--primary)]/20">
          <h3 className="text-sm font-semibold text-[var(--text)]">Editar materia #{editId}</h3>
          <form className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5" onSubmit={(e) => void guardarEdicion(e)}>
            <div className="flex flex-col gap-1 sm:col-span-2">
              <label className="text-xs font-medium text-muted">Nombre</label>
              <input
                required
                className="sb-field min-w-0"
                value={formEdicion.nombre}
                onChange={(e) => setFormEdicion((v) => ({ ...v, nombre: e.target.value }))}
              />
              {errEdicion.nombre?.[0] ? <p className="text-xs text-red-600">{errEdicion.nombre[0]}</p> : null}
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Nivel</label>
              <select
                required
                className="sb-field min-w-0"
                value={formEdicion.nivel}
              onChange={(e) =>
                setFormEdicion((v) => {
                  const nivel = e.target.value;
                  const grado = gradoEsValidoParaNivel(nivel, v.grado) ? v.grado : '';
                  return { ...v, nivel, grado };
                })
              }
              >
                <option value="primaria">Primaria</option>
                <option value="secundaria">Secundaria</option>
              </select>
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Grado</label>
              <select
                required
                className="sb-field min-w-0"
                value={formEdicion.grado}
                onChange={(e) => setFormEdicion((v) => ({ ...v, grado: e.target.value }))}
              >
                <option value="">Seleccione…</option>
                {gradosEditar.map((grado) => (
                  <option key={grado} value={grado}>
                    {grado}
                  </option>
                ))}
              </select>
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Año escolar</label>
              <input
                required
                className="sb-field min-w-0"
                value={formEdicion.anio_escolar}
                onChange={(e) => setFormEdicion((v) => ({ ...v, anio_escolar: e.target.value }))}
              />
            </div>
            <div className="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-5">
              <Button type="submit" variant="primary" size="sm" disabled={guardando}>
                Guardar cambios
              </Button>
              <Button type="button" variant="outline" size="sm" disabled={guardando} onClick={cerrarEditar}>
                Cancelar
              </Button>
            </div>
          </form>
        </Card>
      ) : null}
    </div>
  );
}
