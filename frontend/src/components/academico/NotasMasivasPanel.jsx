import { useCallback, useMemo, useState } from 'react';
import {
  getEstudiantes,
  listarMaterias,
  postNotasLote,
} from '../../lib/api';
import { anioEscolarActual, gradoEsValidoParaNivel, gradosPorNivel } from '../../lib/academico';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const F_CTX = {
  sede: 'chilca',
  nivel: 'primaria',
  grado: '',
  seccion: '',
  anio_escolar: anioEscolarActual(),
  bimestre: '1',
};

function mensajeErrorApi(error) {
  const p = error?.payload;
  if (p?.message && typeof p.message === 'string') {
    return p.message;
  }
  if (p?.errors && typeof p.errors === 'object') {
    return Object.entries(p.errors)
      .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(', ') : String(v)}`)
      .join(' ');
  }
  return 'No se pudo completar la operación.';
}

export default function NotasMasivasPanel() {
  const [ctx, setCtx] = useState(F_CTX);
  const [estudiantes, setEstudiantes] = useState([]);
  const [notasPorId, setNotasPorId] = useState({});
  const [materias, setMaterias] = useState([]);
  const [materiaId, setMateriaId] = useState('');

  const [cargandoLista, setCargandoLista] = useState(false);
  const [listaSolicitada, setListaSolicitada] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState(null);
  const [exito, setExito] = useState(null);

  const puedeCargar = useMemo(
    () =>
      Boolean(
        ctx.grado?.trim() &&
          ctx.seccion?.trim() &&
          ctx.anio_escolar?.trim() &&
          ctx.bimestre,
      ),
    [ctx],
  );

  const cargarLista = useCallback(async () => {
    if (!puedeCargar) {
      return;
    }
    setCargandoLista(true);
    setListaSolicitada(true);
    setError(null);
    setExito(null);
    setEstudiantes([]);
    setNotasPorId({});
    setMaterias([]);
    setMateriaId('');
    try {
      const params = {
        sede: ctx.sede,
        nivel: ctx.nivel,
        grado: ctx.grado.trim(),
        seccion: ctx.seccion.trim(),
        anio_escolar: ctx.anio_escolar.trim(),
        all: 1,
      };
      const [alumnos, mats] = await Promise.all([
        getEstudiantes(params),
        listarMaterias({
          ...params,
          activo: true,
        }),
      ]);
      setEstudiantes(Array.isArray(alumnos) ? alumnos : []);
      setMaterias(Array.isArray(mats) ? mats : []);
      const inicial = {};
      (Array.isArray(alumnos) ? alumnos : []).forEach((e) => {
        inicial[e.id] = '';
      });
      setNotasPorId(inicial);
    } catch (e) {
      setError(
        e.status === 403
          ? 'Sin permiso para listar estudiantes o materias en este contexto.'
          : 'No se pudo cargar el listado.',
      );
      setEstudiantes([]);
      setMaterias([]);
    } finally {
      setCargandoLista(false);
    }
  }, [ctx, puedeCargar]);

  function actualizarNota(estudianteId, valor) {
    setNotasPorId((prev) => ({ ...prev, [estudianteId]: valor }));
  }

  async function guardarLote() {
    setError(null);
    setExito(null);
    const mid = materiaId ? Number(materiaId) : null;
    if (!mid) {
      setError('Seleccione una materia del catálogo.');
      return;
    }
    if (!puedeCargar || estudiantes.length === 0) {
      setError('No hay alumnos cargados para guardar.');
      return;
    }

    const filas = [];
    for (const est of estudiantes) {
      const raw = notasPorId[est.id];
      if (raw === undefined || raw === null || String(raw).trim() === '') {
        continue;
      }
      const num = Number(String(raw).replace(',', '.'));
      if (Number.isNaN(num)) {
        setError(`Nota inválida para el código ${est.codigo}.`);
        return;
      }
      if (num < 0 || num > 20) {
        setError(`La nota debe estar entre 0 y 20 (código ${est.codigo}).`);
        return;
      }
      filas.push({ estudiante_id: est.id, nota: num });
    }

    if (filas.length === 0) {
      setError('Ingrese al menos una nota numérica antes de guardar.');
      return;
    }

    setGuardando(true);
    try {
      const resultado = await postNotasLote({
        materia_id: mid,
        anio_escolar: ctx.anio_escolar.trim(),
        bimestre: ctx.bimestre,
        sede: ctx.sede,
        nivel: ctx.nivel,
        grado: ctx.grado.trim(),
        seccion: ctx.seccion.trim(),
        filas,
      });
      const riesgo = resultado?.riesgo;
      const procesados = Number(riesgo?.procesados || 0);
      const omitidos = Array.isArray(riesgo?.omitidos) ? riesgo.omitidos.length : 0;
      const fallidos = Array.isArray(riesgo?.fallidos) ? riesgo.fallidos.length : 0;
      setExito(
        `Se registraron ${filas.length} nota(s). Riesgo: ${procesados} procesado(s), ${omitidos} omitido(s), ${fallidos} fallido(s).`,
      );
    } catch (e) {
      setError(mensajeErrorApi(e));
    } finally {
      setGuardando(false);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h2 className="text-xl font-semibold text-[var(--text)]">Registro masivo de notas</h2>
        <p className="mt-1 text-sm text-muted">
          Seleccione el contexto académico, cargue la lista de alumnos y registre las notas por materia del catálogo.
        </p>
      </div>

      <Card className="border-[var(--border)] bg-[var(--surface)] p-6 shadow-card">
        <h3 className="text-sm font-semibold text-[var(--text)]">Contexto</h3>
        <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Sede</span>
            <select
              className="rounded-md border border-[var(--border)] bg-white px-3 py-2 text-[var(--text)]"
              value={ctx.sede}
              onChange={(ev) => setCtx((c) => ({ ...c, sede: ev.target.value }))}
            >
              <option value="chilca">Chilca</option>
              <option value="auquimarca">Auquimarca</option>
            </select>
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Nivel</span>
            <select
              className="rounded-md border border-[var(--border)] bg-white px-3 py-2 text-[var(--text)]"
              value={ctx.nivel}
              onChange={(ev) =>
                setCtx((c) => {
                  const nivel = ev.target.value;
                  const grado = gradoEsValidoParaNivel(nivel, c.grado) ? c.grado : '';
                  return { ...c, nivel, grado };
                })
              }
            >
              <option value="primaria">Primaria</option>
              <option value="secundaria">Secundaria</option>
            </select>
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Grado</span>
            <select
              className="rounded-md border border-[var(--border)] px-3 py-2 text-[var(--text)]"
              value={ctx.grado}
              onChange={(ev) => setCtx((c) => ({ ...c, grado: ev.target.value }))}
            >
              <option value="">Seleccione…</option>
              {gradosPorNivel(ctx.nivel).map((grado) => (
                <option key={grado} value={grado}>
                  {grado}
                </option>
              ))}
            </select>
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Sección</span>
            <input
              type="text"
              className="rounded-md border border-[var(--border)] px-3 py-2 text-[var(--text)]"
              placeholder="p. ej. A"
              value={ctx.seccion}
              onChange={(ev) => setCtx((c) => ({ ...c, seccion: ev.target.value }))}
            />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Año escolar</span>
            <input
              type="text"
              className="rounded-md border border-[var(--border)] px-3 py-2 text-[var(--text)]"
              value={ctx.anio_escolar}
              onChange={(ev) => setCtx((c) => ({ ...c, anio_escolar: ev.target.value }))}
            />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Bimestre</span>
            <select
              className="rounded-md border border-[var(--border)] bg-white px-3 py-2 text-[var(--text)]"
              value={ctx.bimestre}
              onChange={(ev) => setCtx((c) => ({ ...c, bimestre: ev.target.value }))}
            >
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </label>
        </div>
        <div className="mt-6 flex flex-wrap gap-3">
          <Button type="button" onClick={() => void cargarLista()} disabled={!puedeCargar || cargandoLista}>
            {cargandoLista ? 'Cargando…' : 'Cargar alumnos y materias'}
          </Button>
          {!puedeCargar ? (
            <p className="self-center text-xs text-muted">Complete grado, sección y año escolar para cargar.</p>
          ) : null}
        </div>
      </Card>

      {cargandoLista ? <LoadingState label="Cargando datos…" /> : null}

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? (
        <AlertMessage variant="success" className="border-success/40">
          {exito}
        </AlertMessage>
      ) : null}

      {listaSolicitada && !cargandoLista && materias.length === 0 && (
        <AlertMessage variant="warning">
          No hay materias activas en el catálogo para el nivel, grado, año y sede seleccionados. Cree o active materias
          en el módulo Materias (administración).
        </AlertMessage>
      )}

      {estudiantes.length > 0 ? (
        <Card className="border-[var(--border)] bg-[var(--surface)] p-6 shadow-card">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <label className="flex min-w-[240px] flex-col gap-1 text-sm">
              <span className="font-medium text-[var(--text)]">Materia (catálogo)</span>
              <select
                className="rounded-md border border-[var(--border)] bg-white px-3 py-2 text-[var(--text)]"
                value={materiaId}
                onChange={(ev) => setMateriaId(ev.target.value)}
                disabled={materias.length === 0}
              >
                <option value="">{materias.length === 0 ? 'Sin materias disponibles' : 'Seleccione…'}</option>
                {materias.map((m) => (
                  <option key={m.id} value={m.id}>
                    {m.nombre}
                  </option>
                ))}
              </select>
            </label>
            <Button type="button" variant="primary" disabled={guardando || !materiaId} onClick={() => void guardarLote()}>
              {guardando ? 'Guardando…' : 'Guardar notas del lote'}
            </Button>
          </div>

          <div className="mt-6 overflow-x-auto">
            <table className="w-full min-w-[520px] border-collapse text-left text-sm">
              <thead>
                <tr className="border-b border-[var(--border)] text-muted">
                  <th className="py-2 pr-4 font-medium">Código</th>
                  <th className="py-2 pr-4 font-medium">Estudiante</th>
                  <th className="py-2 font-medium">Nota (0–20)</th>
                </tr>
              </thead>
              <tbody>
                {estudiantes.map((e) => (
                  <tr key={e.id} className="border-b border-[var(--border)]/60">
                    <td className="py-2 pr-4 align-middle font-mono text-xs">{e.codigo}</td>
                    <td className="py-2 pr-4 align-middle">
                      {e.apellidos}, {e.nombres}
                    </td>
                    <td className="py-2 align-middle">
                      <input
                        type="text"
                        inputMode="decimal"
                        className="w-28 rounded-md border border-[var(--border)] px-2 py-1.5"
                        value={notasPorId[e.id] ?? ''}
                        onChange={(ev) => actualizarNota(e.id, ev.target.value)}
                        aria-label={`Nota para ${e.codigo}`}
                      />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>
      ) : null}

      {listaSolicitada && !cargandoLista && estudiantes.length === 0 && error === null && !exito ? (
        <EmptyState
          title="Sin alumnos en este contexto"
          description="Ajuste sede, nivel, grado, sección o año escolar, o bien registre estudiantes para este aula."
        />
      ) : null}
    </div>
  );
}
