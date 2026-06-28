import { useCallback, useEffect, useMemo, useState } from 'react';
import { getVariablesSocio, postVariablesSocio } from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import ResumenAcademicoEstudiante from '../curricular/ResumenAcademicoEstudiante';
import ResumenAsistenciaCurricular from './ResumenAsistenciaCurricular';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';

function parseNumero(valor) {
  if (valor === null || valor === undefined || valor === '') {
    return Number.NaN;
  }
  return Number(valor);
}

export default function EstudiantePerfilDatos({
  estudianteId,
  anioEscolarPorDefecto,
  mostrarResumenCurricular = false,
  mostrarAsistenciaCurricular = false,
  mostrarVariablesSocio = false,
}) {
  const pestanas = useMemo(() => {
    const items = [];
    if (mostrarResumenCurricular) {
      items.push({ key: 'notas', label: 'Notas' });
    }
    if (mostrarAsistenciaCurricular) {
      items.push({ key: 'asistencia', label: 'Asistencia curricular' });
    }
    if (mostrarVariablesSocio) {
      items.push({ key: 'variables', label: 'Variables socioeconómicas' });
    }
    return items;
  }, [mostrarResumenCurricular, mostrarAsistenciaCurricular, mostrarVariablesSocio]);

  const [pestaña, setPestaña] = useState(() => pestanas[0]?.key ?? 'notas');

  useEffect(() => {
    if (!pestanas.some((t) => t.key === pestaña)) {
      setPestaña(pestanas[0]?.key ?? 'notas');
    }
  }, [pestanas, pestaña]);

  const [variablesSocio, setVariablesSocio] = useState([]);

  const [fmVar, setFmVar] = useState({
    composicion_familiar: 'nuclear',
    nivel_socioeconomico: 'medio',
    acceso_internet: false,
    distancia_colegio_km: '',
    anio_escolar: anioEscolarPorDefecto || anioEscolarActual(),
  });

  const [cargando, setCargando] = useState(true);
  const [errorCarga, setErrorCarga] = useState(null);
  const [errVar, setErrVar] = useState({});

  const cargarVariables = useCallback(async () => {
    if (!mostrarVariablesSocio) {
      setCargando(false);
      return;
    }
    setCargando(true);
    setErrorCarga(null);
    try {
      const v = await getVariablesSocio(estudianteId);
      setVariablesSocio(Array.isArray(v) ? v : []);
    } catch (error) {
      setErrorCarga(
        error.status === 403
          ? 'Sin permiso para registrar o ver variables socioeconómicas.'
          : 'No se pudieron cargar las variables socioeconómicas.',
      );
    } finally {
      setCargando(false);
    }
  }, [estudianteId, mostrarVariablesSocio]);

  useEffect(() => {
    setFmVar((prev) => ({
      ...prev,
      anio_escolar: anioEscolarPorDefecto || prev.anio_escolar || anioEscolarActual(),
    }));
  }, [anioEscolarPorDefecto]);

  useEffect(() => {
    void cargarVariables();
  }, [cargarVariables]);

  async function guardarSocioeconomicas(event) {
    event.preventDefault();
    setErrVar({});
    try {
      await postVariablesSocio(estudianteId, {
        ...fmVar,
        distancia_colegio_km: fmVar.distancia_colegio_km === '' ? null : parseNumero(fmVar.distancia_colegio_km),
      });
      await cargarVariables();
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setErrVar(error.payload.errors);
      }
    }
  }

  if (pestanas.length === 0) {
    return null;
  }

  if (mostrarVariablesSocio && errorCarga) {
    return <AlertMessage variant="warning">{errorCarga}</AlertMessage>;
  }

  return (
    <Card className="space-y-5 border-[var(--border)] shadow-card" data-testid="perfil-datos-academicos">
      <div>
        <h3 className="text-lg font-semibold tracking-tight text-[var(--text)]">Datos académicos y familiares</h3>
        <p className="mt-1.5 text-sm leading-relaxed text-muted">
          Calificaciones y asistencia curricular del estudiante. El registro operativo se realiza desde los módulos
          curriculares correspondientes.
        </p>
      </div>

      <div
        className="flex flex-wrap gap-2 rounded-lg border border-[var(--border)]/80 bg-[var(--background)]/50 p-2"
        role="tablist"
        aria-label="Datos académicos"
      >
        {pestanas.map((t) => (
          <Button
            key={t.key}
            type="button"
            size="sm"
            variant={pestaña === t.key ? 'primary' : 'ghost'}
            className={pestaña === t.key ? '' : 'text-muted hover:text-[var(--text)]'}
            onClick={() => setPestaña(t.key)}
            data-testid={`perfil-tab-${t.key}`}
          >
            {t.label}
          </Button>
        ))}
      </div>

      {pestaña === 'notas' ? (
        <div className="space-y-4" data-testid="perfil-tab-notas-curricular">
          <AlertMessage variant="info">
            Las calificaciones se registran desde el módulo <strong>Notas semanales</strong>.
          </AlertMessage>
          {mostrarResumenCurricular ? (
            <ResumenAcademicoEstudiante estudianteId={estudianteId} anioEscolar={anioEscolarPorDefecto} />
          ) : (
            <p className="text-sm text-muted">
              No tiene permiso para consultar el resumen académico curricular de este estudiante.
            </p>
          )}
        </div>
      ) : null}

      {pestaña === 'asistencia' ? (
        <ResumenAsistenciaCurricular estudianteId={estudianteId} anioEscolarPorDefecto={anioEscolarPorDefecto} />
      ) : null}

      {pestaña === 'variables' ? (
        <Card className="space-y-5 border-[var(--border)] shadow-sm">
          <div>
            <h4 className="text-base font-semibold text-[var(--text)]">Variables socioeconómicas</h4>
            <p className="mt-1 text-sm text-muted">Información de contexto del estudiante.</p>
          </div>

          {!cargando ? (
            <>
              {variablesSocio.length === 0 ? (
                <EmptyState title="Sin registros anuales" description="Guarde variables para el año escolar seleccionado." />
              ) : (
                <ul className="overflow-hidden rounded-lg border border-[var(--border)] text-sm">
                  {variablesSocio.map((row, i) => (
                    <li
                      key={row.id}
                      className={`border-b border-[var(--border)]/70 px-3 py-3 last:border-b-0 ${
                        i % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/30'
                      }`}
                    >
                      <p className="font-medium text-[var(--text)]">Año {row.anio_escolar}</p>
                      <p className="mt-0.5 text-xs text-muted">
                        {row.composicion_familiar} · NSE {row.nivel_socioeconomico} · Internet{' '}
                        {row.acceso_internet ? 'sí' : 'no'}
                        {row.distancia_colegio_km !== null && row.distancia_colegio_km !== undefined ? (
                          <> · Dist. {String(row.distancia_colegio_km)} km</>
                        ) : null}
                      </p>
                    </li>
                  ))}
                </ul>
              )}

              <div className="border-t border-[var(--border)] pt-5">
                <p className="mb-4 text-sm font-medium text-[var(--text)]">Registrar o actualizar</p>
                <form onSubmit={(event) => void guardarSocioeconomicas(event)} className="grid gap-4 sm:grid-cols-2">
                  <div className="flex flex-col gap-1 sm:col-span-2">
                    <label className="text-sm font-medium text-[var(--text)]" htmlFor="var-composicion">Composición familiar</label>
                    <select
                      id="var-composicion"
                      className="sb-field min-w-0"
                      value={fmVar.composicion_familiar}
                      onChange={(event) =>
                        setFmVar((valor) => ({ ...valor, composicion_familiar: event.target.value }))
                      }
                    >
                      <option value="nuclear">nuclear</option>
                      <option value="monoparental">monoparental</option>
                      <option value="extendida">extendida</option>
                      <option value="otros">otros</option>
                    </select>
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]" htmlFor="var-nivel-socio">Nivel socioeconómico</label>
                    <select
                      id="var-nivel-socio"
                      className="sb-field min-w-0"
                      value={fmVar.nivel_socioeconomico}
                      onChange={(event) =>
                        setFmVar((valor) => ({ ...valor, nivel_socioeconomico: event.target.value }))
                      }
                    >
                      <option value="bajo">bajo</option>
                      <option value="medio">medio</option>
                      <option value="alto">alto</option>
                    </select>
                  </div>

                  <div className="flex flex-col justify-end gap-1">
                    <label className="flex cursor-pointer items-center gap-3 text-sm font-medium text-[var(--text)]">
                      <input
                        type="checkbox"
                        checked={fmVar.acceso_internet}
                        onChange={(event) =>
                          setFmVar((valor) => ({ ...valor, acceso_internet: event.target.checked }))
                        }
                      />
                      Acceso a internet
                    </label>
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]" htmlFor="var-distancia">Distancia al colegio (km)</label>
                    <input
                      id="var-distancia"
                      type="number"
                      step="0.01"
                      min="0"
                      className="sb-field min-w-0"
                      value={fmVar.distancia_colegio_km}
                      onChange={(event) =>
                        setFmVar((valor) => ({ ...valor, distancia_colegio_km: event.target.value }))
                      }
                    />
                    {errVar.distancia_colegio_km?.[0] ? (
                      <p className="text-xs text-red-600">{errVar.distancia_colegio_km[0]}</p>
                    ) : null}
                  </div>

                  <div className="flex flex-col gap-1 sm:col-span-2">
                    <label className="text-sm font-medium text-[var(--text)]" htmlFor="var-anio">Año escolar (clave de fila)</label>
                    <input
                      id="var-anio"
                      required
                      className="sb-field min-w-0"
                      value={fmVar.anio_escolar}
                      onChange={(event) =>
                        setFmVar((valor) => ({ ...valor, anio_escolar: event.target.value }))
                      }
                    />
                  </div>

                  <div className="sm:col-span-2">
                    <Button type="submit" variant="primary" size="sm" data-testid="variables-guardar">
                      Guardar variables socioeconómicas
                    </Button>
                  </div>
                </form>
              </div>
            </>
          ) : (
            <p className="text-sm text-muted">Cargando…</p>
          )}
        </Card>
      ) : null}
    </Card>
  );
}
