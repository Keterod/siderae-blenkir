import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getAsistencias,
  getNotas,
  getVariablesSocio,
  listarMaterias,
  postAsistencia,
  postNota,
  postVariablesSocio,
} from '../../lib/api';
import { anioEscolarActual } from '../../lib/academico';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';

function parseNumero(valor) {
  if (valor === null || valor === undefined || valor === '') {
    return NaN;
  }
  return Number(valor);
}

const PESTANAS = [
  { key: 'notas', label: 'Notas' },
  { key: 'asistencia', label: 'Asistencia' },
  { key: 'variables', label: 'Variables socioeconómicas' },
];

export default function EstudiantePerfilDatos({
  estudianteId,
  anioEscolarPorDefecto,
  ubicacionEstudiante = null,
  puedeUsarCatalogoMaterias = false,
}) {
  const [pestaña, setPestaña] = useState('notas');

  const [notas, setNotas] = useState([]);
  const [asistencias, setAsistencias] = useState([]);
  const [variablesSocio, setVariablesSocio] = useState([]);

  const [fmNota, setFmNota] = useState({
    anio_escolar: anioEscolarPorDefecto || anioEscolarActual(),
    bimestre: '1',
    materia_id: '',
    curso: '',
    nota: '',
    nota_conducta: '',
  });

  const [catalogoMaterias, setCatalogoMaterias] = useState([]);

  const [fmAsis, setFmAsis] = useState({
    semana_inicio: '',
    estado: 'presente',
    anio_escolar: anioEscolarPorDefecto || anioEscolarActual(),
    bimestre: '1',
  });

  const [fmVar, setFmVar] = useState({
    composicion_familiar: 'nuclear',
    nivel_socioeconomico: 'medio',
    acceso_internet: false,
    distancia_colegio_km: '',
    anio_escolar: anioEscolarPorDefecto || anioEscolarActual(),
  });

  const [cargando, setCargando] = useState(true);
  const [errorCarga, setErrorCarga] = useState(null);
  const [errNota, setErrNota] = useState({});
  const [errAsis, setErrAsis] = useState({});
  const [errVar, setErrVar] = useState({});

  const cargarTodo = useCallback(async () => {
    setCargando(true);
    setErrorCarga(null);
    try {
      const [n, a, v] = await Promise.all([
        getNotas(estudianteId),
        getAsistencias(estudianteId),
        getVariablesSocio(estudianteId),
      ]);
      setNotas(Array.isArray(n) ? n : []);
      setAsistencias(Array.isArray(a) ? a : []);
      setVariablesSocio(Array.isArray(v) ? v : []);
    } catch (error) {
      setErrorCarga(
        error.status === 403 ? 'Sin permiso para registrar o ver datos académicos.' : 'No se pudieron cargar los datos académicos.',
      );
    } finally {
      setCargando(false);
    }
  }, [estudianteId]);

  useEffect(() => {
    setFmNota((prev) => ({ ...prev, anio_escolar: anioEscolarPorDefecto || prev.anio_escolar || anioEscolarActual() }));
    setFmAsis((prev) => ({ ...prev, anio_escolar: anioEscolarPorDefecto || prev.anio_escolar || anioEscolarActual() }));
    setFmVar((prev) => ({ ...prev, anio_escolar: anioEscolarPorDefecto || prev.anio_escolar || anioEscolarActual() }));
  }, [anioEscolarPorDefecto]);

  const cargarCatalogoMaterias = useCallback(async () => {
    if (!puedeUsarCatalogoMaterias || !ubicacionEstudiante?.nivel) {
      setCatalogoMaterias([]);
      return;
    }
    try {
      const anio = fmNota.anio_escolar || ubicacionEstudiante.anio_escolar || '';
      const datos = await listarMaterias({
        nivel: ubicacionEstudiante.nivel,
        grado: ubicacionEstudiante.grado,
        anio_escolar: anio,
        sede: ubicacionEstudiante.sede,
        activo: true,
      });
      setCatalogoMaterias(Array.isArray(datos) ? datos : []);
    } catch {
      setCatalogoMaterias([]);
    }
  }, [
    puedeUsarCatalogoMaterias,
    ubicacionEstudiante,
    fmNota.anio_escolar,
  ]);

  useEffect(() => {
    void cargarCatalogoMaterias();
  }, [cargarCatalogoMaterias]);

  useEffect(() => {
    void cargarTodo();
  }, [cargarTodo]);

  async function guardarNota(event) {
    event.preventDefault();
    setErrNota({});
    try {
      const usarMateria = Boolean(fmNota.materia_id);
      const payload = {
        anio_escolar: fmNota.anio_escolar,
        bimestre: fmNota.bimestre,
        nota: parseNumero(fmNota.nota),
        nota_conducta: fmNota.nota_conducta === '' ? null : parseNumero(fmNota.nota_conducta),
      };
      if (usarMateria) {
        payload.materia_id = Number(fmNota.materia_id);
      } else {
        payload.curso = fmNota.curso.trim();
      }
      await postNota(estudianteId, payload);
      await cargarTodo();
      setFmNota((valor) => ({
        ...valor,
        materia_id: '',
        curso: '',
        nota: '',
        nota_conducta: '',
      }));
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setErrNota(error.payload.errors);
      }
    }
  }

  async function guardarAsistencia(event) {
    event.preventDefault();
    setErrAsis({});
    try {
      await postAsistencia(estudianteId, fmAsis);
      await cargarTodo();
      setFmAsis((valor) => ({
        ...valor,
        semana_inicio: '',
      }));
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setErrAsis(error.payload.errors);
      }
    }
  }

  async function guardarSocioeconomicas(event) {
    event.preventDefault();
    setErrVar({});
    try {
      await postVariablesSocio(estudianteId, {
        ...fmVar,
        distancia_colegio_km: fmVar.distancia_colegio_km === '' ? null : parseNumero(fmVar.distancia_colegio_km),
      });
      await cargarTodo();
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setErrVar(error.payload.errors);
      }
    }
  }

  const resumen = useMemo(() => {
    const vals = notas.map((row) => parseNumero(row.nota)).filter((x) => !Number.isNaN(x));
    const media = vals.length > 0 ? vals.reduce((soma, atual) => soma + atual, 0) / vals.length : null;
    const ausencias = asistencias.filter((row) => row.estado === 'falta').length;
    const totalAis = asistencias.length;
    const porcentajePresenciaApprox = totalAis === 0 ? null : (((totalAis - ausencias) / totalAis) * 100).toFixed(1);
    return {
      promedioNotas: media === null ? '—' : media.toFixed(2),
      porcentajeAsistenciaApprox: porcentajePresenciaApprox === null ? '—' : `${porcentajePresenciaApprox}%`,
    };
  }, [notas, asistencias]);

  if (errorCarga) {
    return <AlertMessage variant="warning">{errorCarga}</AlertMessage>;
  }

  return (
    <Card className="space-y-5 border-[var(--border)] shadow-card" data-testid="perfil-datos-academicos">
      <div>
        <h3 className="text-lg font-semibold tracking-tight text-[var(--text)]">Datos académicos y familiares</h3>
        <p className="mt-1.5 text-sm leading-relaxed text-muted">
          Notas y asistencia se registran estudiante por estudiante desde esta sección del perfil. Los valores mostrados
          provienen del sistema institucional.
        </p>
      </div>

      <Card padding className="space-y-2 border-[var(--border)]/90 bg-orange-50/25 ring-1 ring-[var(--primary)]/15">
        <h4 className="text-[11px] font-semibold uppercase tracking-wide text-muted">Resumen de apoyo</h4>
        <p className="text-sm leading-relaxed text-muted">
          Promedio de notas: <span className="font-semibold text-[var(--text)]">{resumen.promedioNotas}</span>
          {' · '}
          Asistencia aproximada (semanas ya registradas):{' '}
          <span className="font-semibold text-[var(--text)]">{resumen.porcentajeAsistenciaApprox}</span>
          {' · '}orientación rápida, no equivale un reporte oficial.
        </p>
      </Card>

      <div
        className="flex flex-wrap gap-2 rounded-lg border border-[var(--border)]/80 bg-[var(--background)]/50 p-2"
        role="tablist"
        aria-label="Datos académicos"
      >
        {PESTANAS.map((t) => (
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
        <Card className="space-y-5 border-[var(--border)] shadow-sm">
          <h4 className="text-base font-semibold text-[var(--text)]">Registro de notas</h4>
          {cargando ? (
            <p className="text-sm text-muted">Cargando…</p>
          ) : (
            <>
              {notas.length === 0 ? (
                <EmptyState title="Sin notas registradas" description="Agregue la primera calificación con el formulario inferior." />
              ) : (
                <ul className="overflow-hidden rounded-lg border border-[var(--border)] text-sm">
                  {notas.map((row, i) => (
                    <li
                      key={row.id}
                      className={`border-b border-[var(--border)]/70 px-3 py-3 last:border-b-0 ${
                        i % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/30'
                      }`}
                    >
                      <p className="font-medium text-[var(--text)]">{row.curso}</p>
                      <p className="mt-0.5 text-xs text-muted">
                        {row.anio_escolar || '—'} · Bimestre {row.bimestre}: nota <span className="font-semibold">{row.nota ?? '—'}</span>
                        {row.nota_conducta !== null && row.nota_conducta !== undefined ? (
                          <span> · conducta {row.nota_conducta}</span>
                        ) : null}
                      </p>
                    </li>
                  ))}
                </ul>
              )}

              <div className="border-t border-[var(--border)] pt-5">
                <p className="mb-4 text-sm font-medium text-[var(--text)]">Registrar nota</p>
                <form onSubmit={(event) => void guardarNota(event)} className="grid gap-4 sm:grid-cols-2">
                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Año escolar</label>
                    <input
                      required
                      value={fmNota.anio_escolar}
                      className="sb-field min-w-0"
                      onChange={(event) => setFmNota((valor) => ({ ...valor, anio_escolar: event.target.value }))}
                    />
                    {errNota.anio_escolar?.[0] ? <p className="text-xs text-red-600">{errNota.anio_escolar[0]}</p> : null}
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Bimestre</label>
                    <select
                      className="sb-field min-w-0"
                      value={fmNota.bimestre}
                      onChange={(event) => setFmNota((valor) => ({ ...valor, bimestre: event.target.value }))}
                    >
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                    </select>
                  </div>

                  {puedeUsarCatalogoMaterias && ubicacionEstudiante ? (
                    <div className="flex flex-col gap-1 sm:col-span-2">
                      <label className="text-sm font-medium text-[var(--text)]">Curso desde catálogo (opcional)</label>
                      <select
                        className="sb-field min-w-0"
                        value={fmNota.materia_id}
                        onChange={(event) =>
                          setFmNota((valor) => ({
                            ...valor,
                            materia_id: event.target.value,
                            curso: event.target.value ? '' : valor.curso,
                          }))
                        }
                      >
                        <option value="">Registrar con nombre manual (árbol siguiente)</option>
                        {catalogoMaterias.map((m) => (
                          <option key={m.id} value={String(m.id)}>
                            {m.nombre}
                          </option>
                        ))}
                      </select>
                      <p className="text-xs text-muted">
                        Lista filtrada por sede/nivel/grado/año ({fmNota.anio_escolar || ubicacionEstudiante.anio_escolar}).
                        Si elige materia del catálogo, el servidor completa también el texto de curso.
                      </p>
                      {errNota.materia_id?.[0] ? (
                        <p className="text-xs text-red-600">{errNota.materia_id[0]}</p>
                      ) : null}
                    </div>
                  ) : null}

                  <div className="flex flex-col gap-1 sm:col-span-2">
                    <label className="text-sm font-medium text-[var(--text)]">
                      {fmNota.materia_id ? 'Nombre de curso (rellenado desde catálogo al guardar)' : 'Nombre del curso o área'}
                    </label>
                    <input
                      required={!fmNota.materia_id}
                      disabled={Boolean(fmNota.materia_id)}
                      value={fmNota.curso}
                      className="sb-field min-w-0 disabled:cursor-not-allowed disabled:opacity-60"
                      placeholder={fmNota.materia_id ? 'Se usará el nombre institucional al guardar' : ''}
                      onChange={(event) => setFmNota((valor) => ({ ...valor, curso: event.target.value }))}
                    />
                    {!fmNota.materia_id ? (
                      errNota.curso?.[0] ? <p className="text-xs text-red-600">{errNota.curso[0]}</p> : null
                    ) : null}
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Nota (0–20)</label>
                    <input
                      required
                      type="number"
                      step="0.01"
                      min="0"
                      max="20"
                      value={fmNota.nota}
                      className="sb-field min-w-0"
                      onChange={(event) => setFmNota((valor) => ({ ...valor, nota: event.target.value }))}
                    />
                    {errNota.nota?.[0] ? <p className="text-xs text-red-600">{errNota.nota[0]}</p> : null}
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Conducta (opcional)</label>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      max="20"
                      value={fmNota.nota_conducta}
                      className="sb-field min-w-0"
                      onChange={(event) => setFmNota((valor) => ({ ...valor, nota_conducta: event.target.value }))}
                    />
                  </div>

                  <div className="sm:col-span-2">
                    <Button type="submit" variant="primary" size="sm" data-testid="nota-agregar">
                      Agregar nota
                    </Button>
                  </div>
                </form>
              </div>
            </>
          )}
        </Card>
      ) : null}

      {pestaña === 'asistencia' ? (
        <Card className="space-y-5 border-[var(--border)] shadow-sm">
          <h4 className="text-base font-semibold text-[var(--text)]">Registro de asistencia</h4>

          {!cargando ? (
            <>
              {asistencias.length === 0 ? (
                <EmptyState title="Sin asistencias" description="Registre semanas con el formulario inferior." />
              ) : (
                <ul className="overflow-hidden rounded-lg border border-[var(--border)] text-sm">
                  {asistencias.map((row, i) => (
                    <li
                      key={row.id}
                      className={`border-b border-[var(--border)]/70 px-3 py-3 last:border-b-0 ${
                        i % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/30'
                      }`}
                    >
                      <p className="font-medium capitalize text-[var(--text)]">{row.estado}</p>
                      <p className="mt-0.5 text-xs text-muted">
                        Semana desde {String(row.semana_inicio).substring(0, 10)} · Año {row.anio_escolar ?? '—'} · B{row.bimestre}
                      </p>
                    </li>
                  ))}
                </ul>
              )}

              <div className="border-t border-[var(--border)] pt-5">
                <p className="mb-4 text-sm font-medium text-[var(--text)]">Registrar asistencia</p>
                <form onSubmit={(event) => void guardarAsistencia(event)} className="grid gap-4 sm:grid-cols-2">
                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Inicio de semana lectiva</label>
                    <input
                      required
                      type="date"
                      value={fmAsis.semana_inicio}
                      className="sb-field min-w-0"
                      onChange={(event) => setFmAsis((valor) => ({ ...valor, semana_inicio: event.target.value }))}
                    />
                    {errAsis.semana_inicio?.[0] ? <p className="text-xs text-red-600">{errAsis.semana_inicio[0]}</p> : null}
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Estado</label>
                    <select
                      className="sb-field min-w-0"
                      value={fmAsis.estado}
                      onChange={(event) => setFmAsis((valor) => ({ ...valor, estado: event.target.value }))}
                    >
                      <option value="presente">Presente</option>
                      <option value="tardanza">Tardanza</option>
                      <option value="falta">Falta</option>
                    </select>
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Año escolar</label>
                    <input
                      required
                      value={fmAsis.anio_escolar}
                      className="sb-field min-w-0"
                      onChange={(event) => setFmAsis((valor) => ({ ...valor, anio_escolar: event.target.value }))}
                    />
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Bimestre</label>
                    <select
                      className="sb-field min-w-0"
                      value={fmAsis.bimestre}
                      onChange={(event) => setFmAsis((valor) => ({ ...valor, bimestre: event.target.value }))}
                    >
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                    </select>
                  </div>

                  <div className="sm:col-span-2">
                    <Button type="submit" variant="primary" size="sm" data-testid="asistencia-registrar">
                      Registrar asistencia
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

      {pestaña === 'variables' ? (
        <Card className="space-y-5 border-[var(--border)] shadow-sm">
          <div>
            <h4 className="text-base font-semibold text-[var(--text)]">Variables socioeconómicas</h4>
            <p className="mt-1 text-sm text-muted">Factores ambientales disponibles para el análisis de riesgo.</p>
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
                        {row.composicion_familiar} · NSE {row.nivel_socioeconomico} · Internet {row.acceso_internet ? 'sí' : 'no'}
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
                    <label className="text-sm font-medium text-[var(--text)]">Composición familiar</label>
                    <select
                      className="sb-field min-w-0"
                      value={fmVar.composicion_familiar}
                      onChange={(event) => setFmVar((valor) => ({ ...valor, composicion_familiar: event.target.value }))}
                    >
                      <option value="nuclear">nuclear</option>
                      <option value="monoparental">monoparental</option>
                      <option value="extendida">extendida</option>
                      <option value="otros">otros</option>
                    </select>
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Nivel socioeconómico</label>
                    <select
                      className="sb-field min-w-0"
                      value={fmVar.nivel_socioeconomico}
                      onChange={(event) => setFmVar((valor) => ({ ...valor, nivel_socioeconomico: event.target.value }))}
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
                        onChange={(event) => setFmVar((valor) => ({ ...valor, acceso_internet: event.target.checked }))}
                      />
                      Acceso a internet
                    </label>
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="text-sm font-medium text-[var(--text)]">Distancia al colegio (km)</label>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      className="sb-field min-w-0"
                      value={fmVar.distancia_colegio_km}
                      onChange={(event) => setFmVar((valor) => ({ ...valor, distancia_colegio_km: event.target.value }))}
                    />
                    {errVar.distancia_colegio_km?.[0] ? (
                      <p className="text-xs text-red-600">{errVar.distancia_colegio_km[0]}</p>
                    ) : null}
                  </div>

                  <div className="flex flex-col gap-1 sm:col-span-2">
                    <label className="text-sm font-medium text-[var(--text)]">Año escolar (clave de fila)</label>
                    <input
                      required
                      className="sb-field min-w-0"
                      value={fmVar.anio_escolar}
                      onChange={(event) => setFmVar((valor) => ({ ...valor, anio_escolar: event.target.value }))}
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
