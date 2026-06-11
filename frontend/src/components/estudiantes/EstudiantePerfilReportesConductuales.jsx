import { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  getReportesConductuales,
  patchAnularReporteConductual,
  postReporteConductual,
} from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const NIVELES_GRAVEDAD = ['leve', 'moderado', 'grave'];

function formularioVacio() {
  return {
    fecha: new Date().toISOString().slice(0, 10),
    tipo_conducta: '',
    nivel_gravedad: 'leve',
    descripcion: '',
    accion_inmediata: '',
  };
}

function fechaLegible(valor) {
  if (!valor) {
    return '—';
  }
  const d = new Date(valor);
  return Number.isNaN(d.getTime()) ? String(valor) : d.toLocaleDateString('es-PE');
}

function badgeVariantPorGravedad(nivel) {
  if (nivel === 'grave') {
    return 'danger';
  }
  if (nivel === 'moderado') {
    return 'warning';
  }
  return 'success';
}

function mensajeErrorApi(error, fallback) {
  if (error.status === 403) {
    return 'Sin permiso para esta acción.';
  }
  if (error.status === 422 && error.payload?.message) {
    return error.payload.message;
  }
  if (error.status === 422 && error.payload?.errors) {
    const partes = Object.values(error.payload.errors).flat();
    return partes.length ? partes.join(' ') : fallback;
  }
  return fallback;
}

function validarFormulario(form) {
  const errores = {};
  if (!form.fecha?.trim()) {
    errores.fecha = 'La fecha es obligatoria.';
  }
  if (!form.tipo_conducta?.trim()) {
    errores.tipo_conducta = 'El tipo de conducta es obligatorio.';
  }
  if (!form.nivel_gravedad?.trim()) {
    errores.nivel_gravedad = 'La gravedad es obligatoria.';
  } else if (!NIVELES_GRAVEDAD.includes(form.nivel_gravedad)) {
    errores.nivel_gravedad = 'Seleccione una gravedad válida.';
  }
  if (!form.descripcion?.trim()) {
    errores.descripcion = 'La descripción es obligatoria.';
  }
  return errores;
}

export default function EstudiantePerfilReportesConductuales({ estudianteId }) {
  const { permissions } = useAuth();
  const puedeVer = permissions.includes('ver_reportes_conductuales');
  const puedeRegistrar = permissions.includes('registrar_reportes_conductuales');

  const [reportes, setReportes] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [errorGeneral, setErrorGeneral] = useState(null);
  const [mostrarFormulario, setMostrarFormulario] = useState(false);
  const [form, setForm] = useState(formularioVacio);
  const [errForm, setErrForm] = useState({});
  const [guardando, setGuardando] = useState(false);
  const [errorAccion, setErrorAccion] = useState(null);
  const [anulandoId, setAnulandoId] = useState(null);

  const cargarReportes = useCallback(async () => {
    if (!estudianteId || !puedeVer) {
      return;
    }

    setErrorGeneral(null);
    try {
      const data = await getReportesConductuales(estudianteId);
      setReportes(Array.isArray(data) ? data : []);
    } catch (error) {
      setReportes([]);
      setErrorGeneral(mensajeErrorApi(error, 'No se pudo cargar los reportes conductuales.'));
    }
  }, [estudianteId, puedeVer]);

  useEffect(() => {
    let omitir = false;
    (async () => {
      if (!puedeVer) {
        setCargando(false);
        return;
      }
      setCargando(true);
      await cargarReportes();
      if (!omitir) {
        setCargando(false);
      }
    })();
    return () => {
      omitir = true;
    };
  }, [cargarReportes, puedeVer]);

  async function enviarReporte(event) {
    event.preventDefault();
    const errores = validarFormulario(form);
    setErrForm(errores);
    if (Object.keys(errores).length) {
      return;
    }

    setGuardando(true);
    setErrorAccion(null);
    try {
      await postReporteConductual(estudianteId, {
        fecha: form.fecha,
        tipo_conducta: form.tipo_conducta.trim(),
        nivel_gravedad: form.nivel_gravedad,
        descripcion: form.descripcion.trim(),
        accion_inmediata: form.accion_inmediata.trim() || null,
      });
      setForm(formularioVacio());
      setMostrarFormulario(false);
      await cargarReportes();
    } catch (error) {
      setErrorAccion(mensajeErrorApi(error, 'No se pudo registrar el reporte conductual.'));
    } finally {
      setGuardando(false);
    }
  }

  async function anularReporte(reporte) {
    const confirmar = window.confirm(
      `¿Anular el reporte del ${fechaLegible(reporte.fecha)} (${reporte.tipo_conducta})? Esta acción no elimina el registro del sistema.`,
    );
    if (!confirmar) {
      return;
    }

    setAnulandoId(reporte.id);
    setErrorAccion(null);
    try {
      await patchAnularReporteConductual(reporte.id);
      await cargarReportes();
    } catch (error) {
      setErrorAccion(mensajeErrorApi(error, 'No se pudo anular el reporte conductual.'));
    } finally {
      setAnulandoId(null);
    }
  }

  if (!puedeVer && !puedeRegistrar) {
    return null;
  }

  return (
    <Card
      className="space-y-4 border-[var(--border)] ring-1 ring-[var(--border)]/70"
      data-testid="perfil-reportes-conductuales"
    >
      <div className="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border)]/80 pb-4">
        <div>
          <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">
            Reportes conductuales
          </h3>
          <p className="mt-1.5 text-sm leading-relaxed text-muted">
            Registro institucional de incidencias conductuales asociadas al estudiante.
          </p>
        </div>
        {puedeRegistrar ? (
          <Button
            type="button"
            variant="primary"
            size="sm"
            data-testid="reporte-conductual-nuevo"
            onClick={() => {
              setMostrarFormulario((v) => !v);
              setErrorAccion(null);
              setErrForm({});
            }}
          >
            {mostrarFormulario ? 'Cancelar' : 'Registrar reporte'}
          </Button>
        ) : null}
      </div>

      {errorGeneral ? (
        <AlertMessage variant="danger">{errorGeneral}</AlertMessage>
      ) : null}

      {errorAccion ? (
        <AlertMessage variant="danger">{errorAccion}</AlertMessage>
      ) : null}

      {mostrarFormulario && puedeRegistrar ? (
        <form
          className="grid gap-4 rounded-lg border border-[var(--border)] bg-[var(--background)]/30 p-4 sm:grid-cols-2"
          onSubmit={(event) => {
            void enviarReporte(event);
          }}
          data-testid="reporte-conductual-formulario"
        >
          <div className="flex flex-col gap-1">
            <label className="text-sm font-medium text-[var(--text)]" htmlFor="rc-fecha">
              Fecha
            </label>
            <input
              id="rc-fecha"
              type="date"
              required
              className="sb-field min-w-0"
              value={form.fecha}
              onChange={(event) => setForm((v) => ({ ...v, fecha: event.target.value }))}
            />
            {errForm.fecha ? <p className="text-xs text-red-600">{errForm.fecha}</p> : null}
          </div>

          <div className="flex flex-col gap-1">
            <label className="text-sm font-medium text-[var(--text)]" htmlFor="rc-tipo">
              Tipo de conducta
            </label>
            <input
              id="rc-tipo"
              required
              className="sb-field min-w-0"
              value={form.tipo_conducta}
              onChange={(event) => setForm((v) => ({ ...v, tipo_conducta: event.target.value }))}
            />
            {errForm.tipo_conducta ? (
              <p className="text-xs text-red-600">{errForm.tipo_conducta}</p>
            ) : null}
          </div>

          <div className="flex flex-col gap-1">
            <label className="text-sm font-medium text-[var(--text)]" htmlFor="rc-gravedad">
              Nivel de gravedad
            </label>
            <select
              id="rc-gravedad"
              required
              className="sb-field min-w-0"
              value={form.nivel_gravedad}
              onChange={(event) => setForm((v) => ({ ...v, nivel_gravedad: event.target.value }))}
            >
              {NIVELES_GRAVEDAD.map((nivel) => (
                <option key={nivel} value={nivel}>
                  {nivel}
                </option>
              ))}
            </select>
            {errForm.nivel_gravedad ? (
              <p className="text-xs text-red-600">{errForm.nivel_gravedad}</p>
            ) : null}
          </div>

          <div className="flex flex-col gap-1 sm:col-span-2">
            <label className="text-sm font-medium text-[var(--text)]" htmlFor="rc-descripcion">
              Descripción
            </label>
            <textarea
              id="rc-descripcion"
              required
              rows={3}
              className="sb-field min-w-0 resize-y"
              value={form.descripcion}
              onChange={(event) => setForm((v) => ({ ...v, descripcion: event.target.value }))}
            />
            {errForm.descripcion ? (
              <p className="text-xs text-red-600">{errForm.descripcion}</p>
            ) : null}
          </div>

          <div className="flex flex-col gap-1 sm:col-span-2">
            <label className="text-sm font-medium text-[var(--text)]" htmlFor="rc-accion">
              Acción inmediata (opcional)
            </label>
            <textarea
              id="rc-accion"
              rows={2}
              className="sb-field min-w-0 resize-y"
              value={form.accion_inmediata}
              onChange={(event) => setForm((v) => ({ ...v, accion_inmediata: event.target.value }))}
            />
          </div>

          <div className="sm:col-span-2">
            <Button
              type="submit"
              variant="primary"
              size="sm"
              disabled={guardando}
              data-testid="reporte-conductual-guardar"
            >
              {guardando ? 'Guardando…' : 'Guardar reporte'}
            </Button>
          </div>
        </form>
      ) : null}

      {cargando ? (
        <LoadingState message="Cargando reportes conductuales…" />
      ) : !puedeVer ? (
        <p className="text-sm text-muted">Sin permiso para consultar reportes conductuales.</p>
      ) : reportes.length === 0 ? (
        <EmptyState
          title="Sin reportes conductuales"
          description="No hay reportes activos registrados para este estudiante."
        />
      ) : (
        <div className="overflow-x-auto rounded-lg border border-[var(--border)]">
          <table className="min-w-full divide-y divide-[var(--border)] text-sm">
            <thead className="bg-[var(--background)]/50">
              <tr>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Fecha
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Tipo
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Gravedad
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Descripción
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Acción inmediata
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                  Registrado por
                </th>
                {puedeRegistrar ? (
                  <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-muted">
                    Acciones
                  </th>
                ) : null}
              </tr>
            </thead>
            <tbody className="divide-y divide-[var(--border)]">
              {reportes.map((item) => (
                <tr key={item.id} data-testid={`reporte-conductual-${item.id}`}>
                  <td className="whitespace-nowrap px-4 py-3 tabular-nums">{fechaLegible(item.fecha)}</td>
                  <td className="px-4 py-3">{item.tipo_conducta ?? '—'}</td>
                  <td className="px-4 py-3">
                    <Badge variant={badgeVariantPorGravedad(item.nivel_gravedad)} className="normal-case">
                      {item.nivel_gravedad ?? '—'}
                    </Badge>
                  </td>
                  <td className="max-w-xs px-4 py-3 whitespace-pre-wrap">{item.descripcion ?? '—'}</td>
                  <td className="max-w-xs px-4 py-3 whitespace-pre-wrap text-muted">
                    {item.accion_inmediata?.trim() ? item.accion_inmediata : '—'}
                  </td>
                  <td className="px-4 py-3 text-muted">{item.registrado_por?.email ?? '—'}</td>
                  {puedeRegistrar ? (
                    <td className="px-4 py-3 text-right">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className="text-red-700"
                        disabled={anulandoId === item.id}
                        data-testid={`reporte-conductual-anular-${item.id}`}
                        onClick={() => {
                          void anularReporte(item);
                        }}
                      >
                        {anulandoId === item.id ? 'Anulando…' : 'Anular'}
                      </Button>
                    </td>
                  ) : null}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </Card>
  );
}
