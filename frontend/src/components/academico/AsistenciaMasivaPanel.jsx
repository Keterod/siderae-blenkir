import { useCallback, useMemo, useState } from 'react';
import { getEstudiantes, postAsistenciasLote } from '../../lib/api';
import { anioEscolarActual, gradoEsValidoParaNivel, gradosPorNivel } from '../../lib/academico';
import { ETIQUETA_SEDE_OPERATIVA, SEDE_OPERATIVA } from '../../lib/sedeOperativa';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const F_CTX = {
  sede: SEDE_OPERATIVA,
  nivel: 'primaria',
  grado: '',
  seccion: '',
  anio_escolar: anioEscolarActual(),
  bimestre: '1',
  semana_inicio: '',
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

const ESTADOS = [
  { value: 'presente', label: 'Presente' },
  { value: 'tardanza', label: 'Tardanza' },
  { value: 'falta', label: 'Falta' },
];

export default function AsistenciaMasivaPanel() {
  const [ctx, setCtx] = useState(F_CTX);
  const [estudiantes, setEstudiantes] = useState([]);
  const [estadoPorId, setEstadoPorId] = useState({});

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
          ctx.bimestre &&
          ctx.semana_inicio?.trim(),
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
    setEstadoPorId({});
    try {
      const params = {
        sede: ctx.sede,
        nivel: ctx.nivel,
        grado: ctx.grado.trim(),
        seccion: ctx.seccion.trim(),
        anio_escolar: ctx.anio_escolar.trim(),
        all: 1,
      };
      const alumnos = await getEstudiantes(params);
      setEstudiantes(Array.isArray(alumnos) ? alumnos : []);
      const inicial = {};
      (Array.isArray(alumnos) ? alumnos : []).forEach((e) => {
        inicial[e.id] = 'presente';
      });
      setEstadoPorId(inicial);
    } catch (e) {
      setError(e.status === 403 ? 'Sin permiso para listar estudiantes.' : 'No se pudo cargar el listado.');
      setEstudiantes([]);
    } finally {
      setCargandoLista(false);
    }
  }, [ctx, puedeCargar]);

  function actualizarEstado(estudianteId, valor) {
    setEstadoPorId((prev) => ({ ...prev, [estudianteId]: valor }));
  }

  async function guardarLote() {
    setError(null);
    setExito(null);
    if (!puedeCargar || estudiantes.length === 0) {
      setError('Cargue alumnos y complete la fecha de inicio de semana antes de guardar.');
      return;
    }

    const filas = estudiantes.map((est) => ({
      estudiante_id: est.id,
      estado: estadoPorId[est.id] || 'presente',
    }));

    setGuardando(true);
    try {
      const resultado = await postAsistenciasLote({
        semana_inicio: ctx.semana_inicio.trim(),
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
        `Se registró asistencia para ${filas.length} alumno(s). Riesgo: ${procesados} procesado(s), ${omitidos} omitido(s), ${fallidos} fallido(s).`,
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
        <h2 className="text-xl font-semibold text-[var(--text)]">Registro masivo de asistencia</h2>
        <p className="mt-1 text-sm text-muted">
          La asistencia se registra por semana escolar usando la fecha de inicio de la semana (campo{' '}
          <strong>semana_inicio</strong> del sistema). Seleccione el contexto y marque el estado por alumno.
        </p>
      </div>

      <Card className="border-[var(--border)] bg-[var(--surface)] p-6 shadow-card">
        <h3 className="text-sm font-semibold text-[var(--text)]">Contexto</h3>
        <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <p className="flex flex-col gap-1 text-sm">
            <span className="text-muted">Sede</span>
            <span className="rounded-md border border-[var(--border)] bg-[var(--background)] px-3 py-2 font-medium text-[var(--text)]">
              {ETIQUETA_SEDE_OPERATIVA}
            </span>
          </p>
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
          <label className="flex flex-col gap-1 text-sm sm:col-span-2">
            <span className="text-muted">Semana (fecha de inicio)</span>
            <input
              type="date"
              className="rounded-md border border-[var(--border)] px-3 py-2 text-[var(--text)]"
              value={ctx.semana_inicio}
              onChange={(ev) => setCtx((c) => ({ ...c, semana_inicio: ev.target.value }))}
            />
          </label>
        </div>
        <div className="mt-6 flex flex-wrap gap-3">
          <Button type="button" onClick={() => void cargarLista()} disabled={!puedeCargar || cargandoLista}>
            {cargandoLista ? 'Cargando…' : 'Cargar alumnos'}
          </Button>
          {!puedeCargar ? (
            <p className="self-center text-xs text-muted">
              Complete grado, sección, año escolar, bimestre y fecha de inicio de semana.
            </p>
          ) : null}
        </div>
      </Card>

      {cargandoLista ? <LoadingState label="Cargando alumnos…" /> : null}

      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      {exito ? (
        <AlertMessage variant="success" className="border-success/40">
          {exito}
        </AlertMessage>
      ) : null}

      {estudiantes.length > 0 ? (
        <Card className="border-[var(--border)] bg-[var(--surface)] p-6 shadow-card">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <p className="text-sm text-muted">
              Marque la asistencia para la semana que inicia el {ctx.semana_inicio || '—'}.
            </p>
            <Button type="button" disabled={guardando || !ctx.semana_inicio} onClick={() => void guardarLote()}>
              {guardando ? 'Guardando…' : 'Guardar asistencia del lote'}
            </Button>
          </div>

          <div className="mt-6 overflow-x-auto">
            <table className="w-full min-w-[560px] border-collapse text-left text-sm">
              <thead>
                <tr className="border-b border-[var(--border)] text-muted">
                  <th className="py-2 pr-4 font-medium">Código</th>
                  <th className="py-2 pr-4 font-medium">Estudiante</th>
                  <th className="py-2 font-medium">Estado</th>
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
                      <select
                        className="rounded-md border border-[var(--border)] bg-white px-2 py-1.5 text-[var(--text)]"
                        value={estadoPorId[e.id] ?? 'presente'}
                        onChange={(ev) => actualizarEstado(e.id, ev.target.value)}
                        aria-label={`Asistencia ${e.codigo}`}
                      >
                        {ESTADOS.map((opt) => (
                          <option key={opt.value} value={opt.value}>
                            {opt.label}
                          </option>
                        ))}
                      </select>
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
          description="Verifique grado, sección y año escolar o registre estudiantes para este aula."
        />
      ) : null}
    </div>
  );
}
