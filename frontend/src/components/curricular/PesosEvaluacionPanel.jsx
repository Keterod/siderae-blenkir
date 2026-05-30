import { useCallback, useEffect, useState } from 'react';
import {
  getConfiguracionPesos,
  getCurricularAreas,
  getMallaCurricularPorGrado,
  getPesosEvaluacionResolver,
  patchConfiguracionPeso,
  patchDesactivarConfiguracionPeso,
  postConfiguracionPeso,
} from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import { resolverCalendarioActivoParaFiltros } from '../../lib/calendarioAcademico';
import {
  etiquetaNivelCurricular,
  gradosCurricularesPorNivel,
  NIVELES_CURRICULARES,
} from '../../lib/academicoCurricular';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const DEFAULT_PESOS = { peso_cuaderno: 33.33, peso_libro: 33.33, peso_tarea: 33.34 };

const SCOPES = [
  { value: 'global', label: 'Global' },
  { value: 'nivel_grado', label: 'Nivel / grado' },
  { value: 'area', label: 'Área' },
  { value: 'curso', label: 'Curso' },
];

const SCOPE_LABELS = {
  global: 'Global',
  nivel_grado: 'Nivel / grado',
  area: 'Área',
  curso: 'Curso',
  por_defecto: 'Valores por defecto del sistema',
};

function detectarScopeItem(item) {
  if (item.curso_catalogo_id) return 'curso';
  if (item.area_id) return 'area';
  if (item.nivel && item.grado) return 'nivel_grado';
  return 'global';
}

function etiquetaScopeItem(item) {
  const tipo = detectarScopeItem(item);
  if (tipo === 'curso') {
    const area = item.area?.nombre ?? `Área #${item.area_id}`;
    const curso = item.curso_catalogo?.nombre ?? `Curso #${item.curso_catalogo_id}`;
    return `Curso: ${area} — ${curso}`;
  }
  if (tipo === 'area') {
    return `Área: ${item.area?.nombre ?? item.area_id}`;
  }
  if (tipo === 'nivel_grado') {
    return `${etiquetaNivelCurricular(item.nivel)} / ${item.grado}`;
  }
  return 'Global';
}

function formularioVacio(scope = 'global') {
  return {
    scope,
    nivel: 'primaria',
    grado: '2do',
    area_id: '',
    curso_catalogo_id: '',
    malla_curso_id: '',
    ...DEFAULT_PESOS,
  };
}

function itemAFormulario(item) {
  return {
    scope: detectarScopeItem(item),
    nivel: item.nivel ?? 'primaria',
    grado: item.grado ?? '2do',
    area_id: item.area_id ? String(item.area_id) : '',
    curso_catalogo_id: item.curso_catalogo_id ? String(item.curso_catalogo_id) : '',
    malla_curso_id: '',
    peso_cuaderno: Number(item.peso_cuaderno),
    peso_libro: Number(item.peso_libro),
    peso_tarea: Number(item.peso_tarea),
  };
}

function construirPayload(form) {
  const payload = {
    peso_cuaderno: Number(form.peso_cuaderno),
    peso_libro: Number(form.peso_libro),
    peso_tarea: Number(form.peso_tarea),
  };

  if (form.scope === 'global') {
    return payload;
  }

  if (form.scope === 'nivel_grado') {
    return { ...payload, nivel: form.nivel, grado: form.grado };
  }

  if (form.scope === 'area') {
    return { ...payload, area_id: Number(form.area_id) };
  }

  return {
    ...payload,
    area_id: Number(form.area_id),
    curso_catalogo_id: Number(form.curso_catalogo_id),
  };
}

function obtenerMensajeError(err) {
  const payload = err?.payload;
  if (!payload) return 'No se pudo completar la operación.';
  if (payload.errors) {
    const first = Object.values(payload.errors).flat()[0];
    if (first) return first;
  }
  return payload.message ?? 'No se pudo completar la operación.';
}

export default function PesosEvaluacionPanel() {
  const [items, setItems] = useState([]);
  const [form, setForm] = useState(formularioVacio());
  const [editandoId, setEditandoId] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);

  const [areas, setAreas] = useState([]);
  const [mallaCursos, setMallaCursos] = useState([]);
  const [cargandoMalla, setCargandoMalla] = useState(false);

  const [filtrosResolver, setFiltrosResolver] = useState({
    anio_escolar: anioEscolarActual(),
    nivel: 'primaria',
    grado: '2do',
    malla_curso_id: '',
  });
  const [resolverResultado, setResolverResultado] = useState(null);
  const [cargandoResolver, setCargandoResolver] = useState(false);

  const cargar = useCallback(async () => {
    setCargando(true);
    try {
      setItems(await getConfiguracionPesos({ activo: true }));
    } catch {
      setError('No se pudieron cargar los pesos.');
    } finally {
      setCargando(false);
    }
  }, []);

  const cargarAreas = useCallback(async (nivel) => {
    if (!nivel) {
      setAreas([]);
      return;
    }
    const data = await getCurricularAreas({ nivel, incluir_cursos: true });
    setAreas(Array.isArray(data) ? data : []);
  }, []);

  const cargarMallaCursos = useCallback(async (filtros) => {
    if (!filtros.grado) {
      setMallaCursos([]);
      return;
    }
    setCargandoMalla(true);
    try {
      const malla = await getMallaCurricularPorGrado({
        anio_escolar: filtros.anio_escolar,
        nivel: filtros.nivel,
        grado: filtros.grado,
      });
      const cursos = (malla?.malla_cursos ?? malla?.mallaCursos ?? malla?.cursos ?? []).filter((c) => c.activo !== false);
      setMallaCursos(cursos);
    } catch {
      setMallaCursos([]);
    } finally {
      setCargandoMalla(false);
    }
  }, []);

  useEffect(() => {
    void cargar();
    void resolverCalendarioActivoParaFiltros().then((cal) => {
      if (!cal?.anio) return;
      setFiltrosResolver((prev) => ({ ...prev, anio_escolar: cal.anio }));
      setForm((prev) => ({ ...prev, nivel: prev.nivel, grado: prev.grado }));
    });
  }, [cargar]);

  useEffect(() => {
    const nivelArea =
      form.scope === 'nivel_grado' ? form.nivel : form.scope === 'area' ? form.nivel : filtrosResolver.nivel;
    if (form.scope === 'area' || form.scope === 'curso') {
      void cargarAreas(nivelArea);
    }
  }, [form.scope, form.nivel, filtrosResolver.nivel, cargarAreas]);

  useEffect(() => {
    if (form.scope === 'curso' || filtrosResolver.grado) {
      void cargarMallaCursos({
        anio_escolar: filtrosResolver.anio_escolar,
        nivel: filtrosResolver.nivel,
        grado: filtrosResolver.grado,
      });
    }
  }, [form.scope, filtrosResolver, cargarMallaCursos]);

  function validarFormularioLocal() {
    if (form.scope === 'nivel_grado' && (!form.nivel || !form.grado)) {
      return 'Seleccione nivel y grado.';
    }
    if (form.scope === 'area' && !form.area_id) {
      return 'Seleccione un área.';
    }
    if (form.scope === 'curso') {
      if (!form.malla_curso_id && (!form.area_id || !form.curso_catalogo_id)) {
        return 'Seleccione un curso de malla.';
      }
    }
    return null;
  }

  function sincronizarCursoDesdeMalla(mallaCursoId) {
    const curso = mallaCursos.find((c) => String(c.id) === String(mallaCursoId));
    if (!curso) return;
    setForm((prev) => ({
      ...prev,
      malla_curso_id: String(mallaCursoId),
      area_id: String(curso.area_id),
      curso_catalogo_id: String(curso.curso_catalogo_id),
    }));
  }

  async function guardar(e) {
    e.preventDefault();
    setError(null);
    setExito(null);

    const validacionLocal = validarFormularioLocal();
    if (validacionLocal) {
      setError(validacionLocal);
      return;
    }

    setGuardando(true);
    try {
      const payload = construirPayload(form);
      if (editandoId) {
        await patchConfiguracionPeso(editandoId, payload);
        setExito('Configuración actualizada.');
      } else {
        await postConfiguracionPeso(payload);
        setExito('Configuración creada.');
      }
      setEditandoId(null);
      setForm(formularioVacio());
      await cargar();
    } catch (err) {
      setError(obtenerMensajeError(err));
    } finally {
      setGuardando(false);
    }
  }

  function iniciarEdicion(item) {
    setEditandoId(item.id);
    setForm(itemAFormulario(item));
    setError(null);
    setExito(null);
  }

  function cancelarEdicion() {
    setEditandoId(null);
    setForm(formularioVacio());
    setError(null);
  }

  async function desactivar(id) {
    setError(null);
    setExito(null);
    try {
      await patchDesactivarConfiguracionPeso(id);
      if (editandoId === id) cancelarEdicion();
      setExito('Configuración desactivada.');
      await cargar();
    } catch (err) {
      setError(obtenerMensajeError(err));
    }
  }

  async function consultarPesoEfectivo() {
    if (!filtrosResolver.malla_curso_id) {
      setError('Seleccione un curso de malla para consultar el peso efectivo.');
      return;
    }
    setCargandoResolver(true);
    setError(null);
    try {
      const data = await getPesosEvaluacionResolver({
        malla_curso_id: filtrosResolver.malla_curso_id,
      });
      setResolverResultado(data);
    } catch (err) {
      setResolverResultado(null);
      setError(obtenerMensajeError(err));
    } finally {
      setCargandoResolver(false);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <Card className="p-6">
        <h2 className="text-lg font-semibold">Pesos de evaluación (C/L/T)</h2>
        <p className="mt-1 text-sm text-muted">
          Configure cuaderno, libro y tarea por alcance. Prioridad de resolución: curso → área → nivel/grado → global.
        </p>
      </Card>

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? <AlertMessage variant="success">{exito}</AlertMessage> : null}

      <Card className="p-6">
        <h3 className="text-sm font-semibold">{editandoId ? 'Editar configuración' : 'Nueva configuración'}</h3>
        <form className="mt-4 grid gap-4" onSubmit={guardar}>
          <label className="block text-sm">
            Alcance
            <select
              className="mt-1 w-full rounded border px-2 py-1.5"
              value={form.scope}
              onChange={(e) => setForm(formularioVacio(e.target.value))}
              disabled={Boolean(editandoId)}
            >
              {SCOPES.map((s) => (
                <option key={s.value} value={s.value}>{s.label}</option>
              ))}
            </select>
          </label>

          {form.scope === 'nivel_grado' ? (
            <div className="grid gap-3 sm:grid-cols-2">
              <label className="text-sm">
                Nivel
                <select className="mt-1 w-full rounded border px-2 py-1.5" value={form.nivel} onChange={(e) => setForm({ ...form, nivel: e.target.value, grado: '' })}>
                  {NIVELES_CURRICULARES.map((n) => (
                    <option key={n.value} value={n.value}>{n.label}</option>
                  ))}
                </select>
              </label>
              <label className="text-sm">
                Grado
                <select className="mt-1 w-full rounded border px-2 py-1.5" value={form.grado} onChange={(e) => setForm({ ...form, grado: e.target.value })} required>
                  <option value="">Seleccione</option>
                  {gradosCurricularesPorNivel(form.nivel).map((g) => (
                    <option key={g} value={g}>{g}</option>
                  ))}
                </select>
              </label>
            </div>
          ) : null}

          {form.scope === 'area' ? (
            <div className="grid gap-3 sm:grid-cols-2">
              <label className="text-sm">
                Nivel
                <select className="mt-1 w-full rounded border px-2 py-1.5" value={form.nivel} onChange={(e) => setForm({ ...form, nivel: e.target.value, area_id: '' })}>
                  {NIVELES_CURRICULARES.map((n) => (
                    <option key={n.value} value={n.value}>{n.label}</option>
                  ))}
                </select>
              </label>
              <label className="text-sm">
                Área
                <select className="mt-1 w-full rounded border px-2 py-1.5" value={form.area_id} onChange={(e) => setForm({ ...form, area_id: e.target.value })} required>
                  <option value="">Seleccione</option>
                  {areas.map((a) => (
                    <option key={a.id} value={a.id}>{a.nombre}</option>
                  ))}
                </select>
              </label>
            </div>
          ) : null}

          {form.scope === 'curso' ? (
            <div className="grid gap-3">
              <div className="grid gap-3 sm:grid-cols-3">
                <label className="text-sm">
                  Año escolar
                  <input className="mt-1 w-full rounded border px-2 py-1.5" value={filtrosResolver.anio_escolar} onChange={(e) => setFiltrosResolver({ ...filtrosResolver, anio_escolar: e.target.value, malla_curso_id: '' })} />
                </label>
                <label className="text-sm">
                  Nivel
                  <select className="mt-1 w-full rounded border px-2 py-1.5" value={filtrosResolver.nivel} onChange={(e) => setFiltrosResolver({ ...filtrosResolver, nivel: e.target.value, grado: '', malla_curso_id: '' })}>
                    {NIVELES_CURRICULARES.map((n) => (
                      <option key={n.value} value={n.value}>{n.label}</option>
                    ))}
                  </select>
                </label>
                <label className="text-sm">
                  Grado
                  <select className="mt-1 w-full rounded border px-2 py-1.5" value={filtrosResolver.grado} onChange={(e) => setFiltrosResolver({ ...filtrosResolver, grado: e.target.value, malla_curso_id: '' })}>
                    {gradosCurricularesPorNivel(filtrosResolver.nivel).map((g) => (
                      <option key={g} value={g}>{g}</option>
                    ))}
                  </select>
                </label>
              </div>
              <label className="text-sm">
                Curso de malla
                <select
                  className="mt-1 w-full rounded border px-2 py-1.5"
                  value={form.malla_curso_id}
                  onChange={(e) => sincronizarCursoDesdeMalla(e.target.value)}
                  required
                  disabled={cargandoMalla}
                >
                  <option value="">{cargandoMalla ? 'Cargando cursos…' : 'Seleccione'}</option>
                  {mallaCursos.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.area?.nombre ?? `Área ${c.area_id}`} — {c.curso_catalogo?.nombre ?? c.nombre ?? `Curso ${c.curso_catalogo_id}`}
                    </option>
                  ))}
                </select>
              </label>
              {mallaCursos.length === 0 && !cargandoMalla ? (
                <p className="text-xs text-muted">No hay cursos en la malla seleccionada. Revise año, nivel y grado.</p>
              ) : null}
            </div>
          ) : null}

          <div className="grid gap-3 sm:grid-cols-3">
            {[
              ['peso_cuaderno', 'Cuaderno'],
              ['peso_libro', 'Libro'],
              ['peso_tarea', 'Tarea'],
            ].map(([key, label]) => (
              <label key={key} className="text-sm">
                {label}
                <input
                  type="number"
                  step="0.01"
                  className="mt-1 w-full rounded border px-2 py-1.5"
                  value={form[key]}
                  onChange={(e) => setForm({ ...form, [key]: e.target.value })}
                  required
                />
              </label>
            ))}
          </div>

          <div className="flex flex-wrap gap-2">
            <Button type="submit" disabled={guardando}>
              {editandoId ? 'Guardar cambios' : 'Crear configuración'}
            </Button>
            {editandoId ? (
              <Button type="button" variant="ghost" onClick={cancelarEdicion}>Cancelar</Button>
            ) : null}
          </div>
        </form>
      </Card>

      <Card className="p-6">
        <h3 className="text-sm font-semibold">Configuraciones activas</h3>
        {cargando ? <LoadingState /> : null}
        {!cargando && items.length === 0 ? <EmptyState title="Sin configuraciones activas" /> : null}
        <ul className="mt-4 divide-y text-sm">
          {items.map((p) => (
            <li key={p.id} className="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="font-medium">{etiquetaScopeItem(p)}</p>
                <p className="text-muted">
                  C: {p.peso_cuaderno} · L: {p.peso_libro} · T: {p.peso_tarea}
                </p>
              </div>
              <div className="flex gap-2">
                <Button type="button" size="sm" variant="ghost" onClick={() => iniciarEdicion(p)}>Editar</Button>
                <Button type="button" size="sm" variant="ghost" onClick={() => void desactivar(p.id)}>Desactivar</Button>
              </div>
            </li>
          ))}
        </ul>
      </Card>

      <Card className="p-6">
        <h3 className="text-sm font-semibold">Peso efectivo por curso</h3>
        <p className="mt-1 text-xs text-muted">Consulta qué configuración se aplica a un curso de malla concreto.</p>
        <div className="mt-4 grid gap-3 sm:grid-cols-4">
          <label className="text-sm">
            Año escolar
            <input className="mt-1 w-full rounded border px-2 py-1.5" value={filtrosResolver.anio_escolar} onChange={(e) => setFiltrosResolver({ ...filtrosResolver, anio_escolar: e.target.value, malla_curso_id: '' })} />
          </label>
          <label className="text-sm">
            Nivel
            <select className="mt-1 w-full rounded border px-2 py-1.5" value={filtrosResolver.nivel} onChange={(e) => setFiltrosResolver({ ...filtrosResolver, nivel: e.target.value, grado: '', malla_curso_id: '' })}>
              {NIVELES_CURRICULARES.map((n) => (
                <option key={n.value} value={n.value}>{n.label}</option>
              ))}
            </select>
          </label>
          <label className="text-sm">
            Grado
            <select className="mt-1 w-full rounded border px-2 py-1.5" value={filtrosResolver.grado} onChange={(e) => setFiltrosResolver({ ...filtrosResolver, grado: e.target.value, malla_curso_id: '' })}>
              {gradosCurricularesPorNivel(filtrosResolver.nivel).map((g) => (
                <option key={g} value={g}>{g}</option>
              ))}
            </select>
          </label>
          <label className="text-sm">
            Curso de malla
            <select className="mt-1 w-full rounded border px-2 py-1.5" value={filtrosResolver.malla_curso_id} onChange={(e) => setFiltrosResolver({ ...filtrosResolver, malla_curso_id: e.target.value })}>
              <option value="">Seleccione</option>
              {mallaCursos.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.area?.nombre ?? `Área ${c.area_id}`} — {c.curso_catalogo?.nombre ?? c.nombre ?? `Curso ${c.curso_catalogo_id}`}
                </option>
              ))}
            </select>
          </label>
        </div>
        <div className="mt-4">
          <Button type="button" onClick={() => void consultarPesoEfectivo()} disabled={cargandoResolver}>
            {cargandoResolver ? 'Consultando…' : 'Consultar peso efectivo'}
          </Button>
        </div>
        {resolverResultado ? (
          <div className="mt-4 rounded border bg-[var(--surface-muted)] p-4 text-sm">
            <p><strong>Curso:</strong> {resolverResultado.curso?.area} — {resolverResultado.curso?.nombre}</p>
            <p><strong>Alcance aplicado:</strong> {SCOPE_LABELS[resolverResultado.scope_aplicado] ?? resolverResultado.scope_aplicado}</p>
            <p><strong>Pesos:</strong> C: {resolverResultado.pesos?.cuaderno} · L: {resolverResultado.pesos?.libro} · T: {resolverResultado.pesos?.tarea}</p>
            {resolverResultado.configuracion ? (
              <p className="text-muted">Configuración #{resolverResultado.configuracion.id}</p>
            ) : (
              <p className="text-muted">Sin fila en base de datos; se usan valores por defecto del sistema.</p>
            )}
          </div>
        ) : null}
      </Card>
    </div>
  );
}
