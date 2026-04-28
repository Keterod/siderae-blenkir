import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  getAsistencias,
  getNotas,
  getVariablesSocio,
  postAsistencia,
  postNota,
  postVariablesSocio,
} from '../../lib/api';

function parseNumero(valor) {
  if (valor === null || valor === undefined || valor === '') {
    return NaN;
  }

  return Number(valor);

}

export default function EstudiantePerfilDatos({
  estudianteId,
  anioEscolarPorDefecto,
}) {

  const [notas, setNotas] = useState([]);
  const [asistencias, setAsistencias] = useState([]);
  const [variablesSocio, setVariablesSocio] = useState([]);

  const [fmNota, setFmNota] = useState({
    anio_escolar: anioEscolarPorDefecto || '',
    bimestre: '1',
    curso: '',
    nota: '',
    nota_conducta: '',
  });

  const [fmAsis, setFmAsis] = useState({
    semana_inicio: '',
    estado: 'presente',
    anio_escolar: anioEscolarPorDefecto || '',
    bimestre: '1',
  });

  const [fmVar, setFmVar] = useState({
    composicion_familiar: 'nuclear',
    nivel_socioeconomico: 'medio',
    acceso_internet: false,
    distancia_colegio_km: '',
    anio_escolar: anioEscolarPorDefecto || '',
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
      setErrorCarga(error.status === 403 ? 'Sin permiso para registrar o ver datos académicos.' : 'No se pudieron cargar los datos académicos.');

    } finally {

      setCargando(false);

    }

  }, [estudianteId]);

  useEffect(() => {

    setFmNota((prev) => ({ ...prev, anio_escolar: anioEscolarPorDefecto || prev.anio_escolar }));

    setFmAsis((prev) => ({ ...prev, anio_escolar: anioEscolarPorDefecto || prev.anio_escolar }));

    setFmVar((prev) => ({ ...prev, anio_escolar: anioEscolarPorDefecto || prev.anio_escolar }));

  }, [anioEscolarPorDefecto]);

  useEffect(() => {

    void cargarTodo();

  }, [cargarTodo]);

  async function guardarNota(event) {
    event.preventDefault();

    setErrNota({});

    try {

      await postNota(estudianteId, {
        anio_escolar: fmNota.anio_escolar,
        bimestre: fmNota.bimestre,

        curso: fmNota.curso.trim(),

        nota: parseNumero(fmNota.nota),

        nota_conducta:

          fmNota.nota_conducta === ''

            ? null

            : parseNumero(fmNota.nota_conducta),

      });

      await cargarTodo();

      setFmNota((valor) => ({

        ...valor,

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

        distancia_colegio_km:

          fmVar.distancia_colegio_km === ''

            ? null

            : parseNumero(fmVar.distancia_colegio_km),

      });

      await cargarTodo();

    } catch (error) {

      if (error.status === 422 && error.payload?.errors) {

        setErrVar(error.payload.errors);

      }

    }

  }

  const resumen = useMemo(() => {

    const vals = notas

      .map((row) => parseNumero(row.nota))

      .filter((x) => !Number.isNaN(x));

    const media =

      vals.length > 0

        ? vals.reduce((soma, atual) => soma + atual, 0) / vals.length

        : null;

    const ausencias = asistencias.filter((row) => row.estado === 'falta').length;

    const totalAis = asistencias.length;

    const porcentajePresenciaApprox = totalAis === 0

      ? null

      : (((totalAis - ausencias) / totalAis) * 100).toFixed(1);

    return {

      promedioNotas: media === null ? '—' : media.toFixed(2),

      porcentajeAsistenciaApprox: porcentajePresenciaApprox === null ? '—' : `${porcentajePresenciaApprox}%`,
    };

  }, [notas, asistencias]);

  if (errorCarga) {

    return <p className="rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">{errorCarga}</p>;

  }

  return (

    <div className="space-y-8 border-t border-slate-200 pt-6">

      <div>

        <h3 className="text-base font-semibold text-slate-900">Resumen base (orientativo)</h3>

        <p className="text-xs text-slate-600">

          Promedio de notas en pantalla ({resumen.promedioNotas}) • Registro aprox. de presencia sobre filas registradas ({resumen.porcentajeAsistenciaApprox}). No sustituye al motor ML del Sprint 4.
        </p>

      </div>

      <section>

        <h4 className="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Notas</h4>

        {cargando ? <p className="text-sm text-slate-500">…</p> : null}

        {!cargando ? (

          <>

            <ul className="mb-4 divide-y divide-slate-100 text-sm">

              {notas.length === 0 ? (

                <li className="py-2 text-slate-500">Sin registros.</li>

              ) : (
                notas.map((row) => (
                  <li key={row.id} className="py-2">
                    <span className="font-medium">{row.curso}</span>
                    {' '}
· {row.anio_escolar || '—'}

                    · B {row.bimestre}: nota{' '}

                    <span>{row.nota ?? '—'}</span>

                    {row.nota_conducta !== null && row.nota_conducta !== undefined ? (

                      <span>{' '}· conducta {row.nota_conducta}</span>

                    ) : null}

                  </li>

                ))

              )}
            </ul>

            <form onSubmit={(event) => void guardarNota(event)} className="grid gap-3 sm:grid-cols-2">

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Año escolar</label>

                <input

                  required
                  value={fmNota.anio_escolar}
                  className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm"
                  onChange={(event) => setFmNota((valor) => ({ ...valor, anio_escolar: event.target.value }))}

                />

                {errNota.anio_escolar?.[0] ? <p className="text-xs text-red-600">{errNota.anio_escolar[0]}</p> : null}

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Bimestre</label>

                <select

                  className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm"

                  value={fmNota.bimestre}

                  onChange={(event) => setFmNota((valor) => ({ ...valor, bimestre: event.target.value }))}

                >

                  <option value="1">1</option>

                  <option value="2">2</option>

                  <option value="3">3</option>

                  <option value="4">4</option>

                </select>

              </div>

              <div className="space-y-1 sm:col-span-2">

                <label className="text-xs text-slate-600">Curso</label>

                <input

                  required
                  value={fmNota.curso}
                  className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm"

                  onChange={(event) => setFmNota((valor) => ({ ...valor, curso: event.target.value }))}

                />

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Nota (0–20)</label>

                <input required type="number" step="0.01" min="0" max="20" value={fmNota.nota} className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" onChange={(event) => setFmNota((valor) => ({ ...valor, nota: event.target.value }))} />

                {errNota.nota?.[0] ? <p className="text-xs text-red-600">{errNota.nota[0]}</p> : null}

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Conducta (opc.)</label>

                <input type="number" step="0.01" min="0" max="20" value={fmNota.nota_conducta} className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" onChange={(event) => setFmNota((valor) => ({ ...valor, nota_conducta: event.target.value }))} />

              </div>

              <button type="submit" className="sm:col-span-2 rounded bg-slate-800 px-3 py-2 text-sm text-white">

                Agregar nota
              </button>

            </form>

          </>

        ) : null}

      </section>

      <section>

        <h4 className="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Asistencia</h4>

        {!cargando ? (
          <>
            <ul className="mb-4 divide-y divide-slate-100 text-sm">
              {asistencias.length === 0 ? (

                <li className="py-2 text-slate-500">Sin registros.</li>

              ) : (
                asistencias.map((row) => (
                  <li key={row.id} className="py-2">
                    Semana desde {String(row.semana_inicio).substring(0, 10)}

· {row.estado}

                    · año {row.anio_escolar ?? '—'}

                    · B{row.bimestre}

                  </li>

                ))

              )}
            </ul>

            <form onSubmit={(event) => void guardarAsistencia(event)} className="grid gap-3 sm:grid-cols-2">

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Semana inicio</label>

                <input required type="date" value={fmAsis.semana_inicio} className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" onChange={(event) => setFmAsis((valor) => ({ ...valor, semana_inicio: event.target.value }))} />

                {errAsis.semana_inicio?.[0] ? <p className="text-xs text-red-600">{errAsis.semana_inicio[0]}</p> : null}

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Estado</label>

                <select className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" value={fmAsis.estado} onChange={(event) => setFmAsis((valor) => ({ ...valor, estado: event.target.value }))}>

                  <option value="presente">presente</option>

                  <option value="tardanza">tardanza</option>

                  <option value="falta">falta</option>

                </select>

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Año escolar</label>

                <input required value={fmAsis.anio_escolar} className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" onChange={(event) => setFmAsis((valor) => ({ ...valor, anio_escolar: event.target.value }))} />

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Bimestre</label>

                <select className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" value={fmAsis.bimestre} onChange={(event) => setFmAsis((valor) => ({ ...valor, bimestre: event.target.value }))}>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                </select>

              </div>

              <button type="submit" className="sm:col-span-2 rounded bg-slate-800 px-3 py-2 text-sm text-white">
                Registrar asistencia

              </button>

            </form>

          </>
        ) : null}

      </section>

      <section>

        <h4 className="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Variables socioeconómicas</h4>

        {!cargando ? (
          <>
            <ul className="mb-4 divide-y divide-slate-100 text-sm">
              {variablesSocio.length === 0 ? (

                <li className="py-2 text-slate-500">Sin registros.</li>

              ) : (
                variablesSocio.map((row) => (

                  <li key={row.id} className="py-2">

                    <span className="font-medium">{row.anio_escolar}</span>: {row.composicion_familiar} · {row.nivel_socioeconomico} · Internet {row.acceso_internet ? 'sí' : 'no'}

                    {row.distancia_colegio_km !== null && row.distancia_colegio_km !== undefined

                      ? (
                        <>
                          {' '}
                          · Dist. {String(row.distancia_colegio_km)} km
                        </>
                        )

                      : null}

                  </li>

                ))

              )}
            </ul>

            <form onSubmit={(event) => void guardarSocioeconomicas(event)} className="grid gap-3 sm:grid-cols-2">

              <div className="space-y-1 sm:col-span-2">

                <label className="text-xs text-slate-600">Composición familiar</label>

                <select className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" value={fmVar.composicion_familiar} onChange={(event) => setFmVar((valor) => ({ ...valor, composicion_familiar: event.target.value }))}>
                  <option value="nuclear">nuclear</option>
                  <option value="monoparental">monoparental</option>
                  <option value="extendida">extendida</option>

                  <option value="otros">otros</option>

                </select>

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Nivel socioeconómico</label>

                <select className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" value={fmVar.nivel_socioeconomico} onChange={(event) => setFmVar((valor) => ({ ...valor, nivel_socioeconomico: event.target.value }))}>
                  <option value="bajo">bajo</option>
                  <option value="medio">medio</option>

                  <option value="alto">alto</option>

                </select>

              </div>

              <div className="space-y-1">

                <label className="flex items-center gap-2 text-xs text-slate-600">

                  <input type="checkbox" checked={fmVar.acceso_internet} onChange={(event) => setFmVar((valor) => ({ ...valor, acceso_internet: event.target.checked }))} />
                  {' '}

                  Acceso a internet

                </label>

              </div>

              <div className="space-y-1">

                <label className="text-xs text-slate-600">Distancia colegio (km)</label>

                <input type="number" step="0.01" min="0" className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" value={fmVar.distancia_colegio_km} onChange={(event) => setFmVar((valor) => ({ ...valor, distancia_colegio_km: event.target.value }))} />

                {errVar.distancia_colegio_km?.[0]
                  ? (
                    <p className="text-xs text-red-600">{errVar.distancia_colegio_km[0]}</p>

                    )

                  : null}

              </div>

              <div className="space-y-1 sm:col-span-2">

                <label className="text-xs text-slate-600">Año escolar (clave para crear/actualizar fila única por año)</label>

                <input required className="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" value={fmVar.anio_escolar} onChange={(event) => setFmVar((valor) => ({ ...valor, anio_escolar: event.target.value }))} />

              </div>

              <button type="submit" className="sm:col-span-2 rounded bg-slate-800 px-3 py-2 text-sm text-white">

                Guardar variables socioeconómicas

              </button>

            </form>

          </>

        ) : null}

      </section>

    </div>

  );

}
