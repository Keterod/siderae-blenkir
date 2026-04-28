import { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  createEstudiante,
  getEstudiante,
  getEstudiantes,
  updateEstudiante,
} from '../../lib/api';
import EstudiantePerfilDatos from './EstudiantePerfilDatos';

function formularioVacio() {
  return {
    codigo: '',
    nombres: '',
    apellidos: '',
    fecha_nacimiento: '',
    sexo: '',
    grado: '',
    seccion: '',
    nivel: 'primaria',
    sede: 'chilca',
    anio_escolar: '',
    activo: true,
  };
}

function llenarFormulario(desdeServidor) {
  const sexoValor = desdeServidor.sexo ?? '';
  const sexoValidado = sexoValor === 'M' || sexoValor === 'F' ? sexoValor : '';

  return {
    codigo: desdeServidor.codigo ?? '',
    nombres: desdeServidor.nombres ?? '',
    apellidos: desdeServidor.apellidos ?? '',
    fecha_nacimiento: desdeServidor.fecha_nacimiento
      ? String(desdeServidor.fecha_nacimiento).substring(0, 10)
      : '',
    sexo: sexoValidado,
    grado: desdeServidor.grado ?? '',
    seccion: desdeServidor.seccion ?? '',
    nivel: desdeServidor.nivel ?? 'primaria',
    sede: desdeServidor.sede ?? 'chilca',
    anio_escolar: desdeServidor.anio_escolar ?? '',
    activo: desdeServidor.activo !== false,
  };
}

export default function EstudiantesPanel({ onClose }) {
  const { permissions } = useAuth();

  const [vista, setVista] = useState('lista');



  const [lista, setLista] = useState([]);

  const [detalle, setDetalle] = useState(null);

  const [editandoId, setEditandoId] = useState(null);

  const [formulario, setFormulario] = useState(formularioVacio());

  const [cargando, setCargando] = useState(true);

  const [guardando, setGuardando] = useState(false);

  const [errorGeneral, setErrorGeneral] = useState(null);

  const [campoErrores, setCampoErrores] = useState({});

  const cargarLista = useCallback(async () => {
    setErrorGeneral(null);
    setCampoErrores({});

    try {
      const resultado = await getEstudiantes();
      setLista(Array.isArray(resultado) ? resultado : []);

    } catch (error) {

      if (error.status === 403) {

        setErrorGeneral('Sin permiso para gestionar estudiantes.');

      } else {

        setErrorGeneral('No se pudo cargar el listado de estudiantes.');
      }

      setLista([]);
    }
  }, []);

  useEffect(() => {

    if (vista !== 'lista') {

      setCargando(false);

      return undefined;
    }

    let omitir = false;

    (async () => {

      setCargando(true);

      await cargarLista();

      if (!omitir) {

        setCargando(false);

      }

    })();

    return () => {

      omitir = true;

    };

  }, [vista, cargarLista]);

  async function abrirDetalle(estudianteId) {
    setCargando(true);
    setErrorGeneral(null);
    setCampoErrores({});

    try {

      const item = await getEstudiante(estudianteId);

      setDetalle(item);

      setEditandoId(estudianteId);

      setVista('perfil');

    } catch {

      setErrorGeneral('No se pudo cargar el estudiante.');

    } finally {

      setCargando(false);

    }
  }

  function abrirCreacion() {

    setDetalle(null);

    setEditandoId(null);

    setFormulario(formularioVacio());


    setCampoErrores({});

    setErrorGeneral(null);

    setVista('crear');

  }

  async function abrirEdicion(estudianteId) {
    setCargando(true);
    setCampoErrores({});
    setErrorGeneral(null);

    try {

      const item = await getEstudiante(estudianteId);

      setDetalle(item);

      setEditandoId(estudianteId);

      setFormulario(llenarFormulario(item));

      setVista('editar');

    } catch {

      setErrorGeneral('No se pudo cargar el estudiante para editar.');

    } finally {

      setCargando(false);

    }
  }

  async function enviarFormulario(esEdicion) {

    const cuerpo = {
      codigo: formulario.codigo.trim(),
      nombres: formulario.nombres.trim(),
      apellidos: formulario.apellidos.trim(),
      grado: formulario.grado.trim(),
      seccion: formulario.seccion.trim(),
      nivel: formulario.nivel,
      sede: formulario.sede,
      anio_escolar: formulario.anio_escolar.trim(),
      fecha_nacimiento: formulario.fecha_nacimiento || null,
      sexo: formulario.sexo || null,

      activo: Boolean(formulario.activo),

    };

    setGuardando(true);
    setCampoErrores({});
    setErrorGeneral(null);

    try {

      if (esEdicion && editandoId) {

        const actualizado = await updateEstudiante(editandoId, cuerpo);

        setDetalle(actualizado ?? null);

        setVista('perfil');

      } else {

        const nuevo = await createEstudiante(cuerpo);

        if (nuevo?.id) {

          setEditandoId(nuevo.id);

          setDetalle(nuevo);

          setCampoErrores({});

          setVista('perfil');

        } else {

          setVista('lista');

        }

      }

      await cargarLista();

    } catch (error) {

      if (error.status === 422 && error.payload?.errors) {

        setCampoErrores(error.payload.errors);

      } else if (error.status === 403) {

        setErrorGeneral('Sin permiso para guardar estudiantes.');

      } else {

        setErrorGeneral('No se pudo guardar el estudiante.');

      }

    } finally {

      setGuardando(false);

    }

  }

  function tituloActual() {

    if (vista === 'crear') {

      return 'Registrar estudiante';

    }

    if (vista === 'editar') {

      return 'Editar estudiante';

    }

    if (vista === 'perfil') {

      return 'Perfil de estudiante';

    }

    return 'Estudiantes';

  }

  return (
    <section className="space-y-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div className="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
        <h2 className="text-xl font-semibold text-slate-900">{tituloActual()}</h2>

        <div className="flex flex-wrap gap-2">
          {vista !== 'lista' ? (

            <button
              type="button"

              className="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700"
              onClick={() => {

                setCampoErrores({});

                setErrorGeneral(null);

                setVista('lista');

              }}

            >
              Volver al listado
            </button>
          ) : null}

          {vista === 'lista' ? (

            <>
              <button type="button" onClick={() => cargarLista()} className="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700">
                Actualizar
              </button>

              <button type="button" onClick={() => abrirCreacion()} className="rounded bg-slate-900 px-3 py-2 text-sm text-white">

                Nuevo
              </button>
            </>

          ) : null}

          <button type="button" onClick={onClose} className="rounded border border-red-100 px-3 py-2 text-sm text-red-700">

            Cerrar módulo
          </button>
        </div>
      </div>

      {errorGeneral ? <p className="text-sm text-red-600">{errorGeneral}</p> : null}

      {vista === 'lista' && cargando ? <p className="text-sm text-slate-500">Cargando listado...</p> : null}

      {vista === 'lista' && !cargando ? (

        <ul className="divide-y divide-slate-100">
          {lista.length === 0 ? (

            <li className="py-3 text-sm text-slate-500">No hay estudiantes registrados.</li>

          ) : (
            lista.map((item) => (
              <li key={item.id} className="flex flex-wrap items-center justify-between gap-2 py-3">
                <button
                  type="button"
                  className="text-left font-medium text-slate-900 hover:underline"
                  onClick={() => abrirDetalle(item.id)}
                >
                  {item.apellidos}, {item.nombres} ({item.codigo})
                </button>

                <button type="button" className="text-sm text-blue-700" onClick={() => abrirEdicion(item.id)}>
                  Editar
                </button>
              </li>
            ))

          )}
        </ul>

      ) : null}

      {(vista === 'crear' || vista === 'editar') && cargando ? <p className="text-sm text-slate-500">Cargando formulario...</p> : null}

      {(vista === 'crear' || vista === 'editar') && !cargando ? (

        <form
          className="space-y-3"
            onSubmit={(event) => {

            event.preventDefault();

            void enviarFormulario(vista === 'editar');

          }}


        >
          <div className="grid gap-3 sm:grid-cols-2">
            <div className="space-y-1">
              <label className="text-sm text-slate-700">Código</label>

              <input
                required
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                value={formulario.codigo}
                onChange={(event) => setFormulario((valor) => ({ ...valor, codigo: event.target.value }))}
              />

              {campoErrores.codigo ? <p className="text-xs text-red-600">{campoErrores.codigo.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm text-slate-700">Año escolar</label>

              <input
                required

                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                value={formulario.anio_escolar}
                onChange={(event) => setFormulario((valor) => ({ ...valor, anio_escolar: event.target.value }))}
              />

              {campoErrores.anio_escolar ? <p className="text-xs text-red-600">{campoErrores.anio_escolar.join(' ')}</p> : null}
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-2">
            <div className="space-y-1">
              <label className="text-sm text-slate-700">Nombres</label>

              <input
                required
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                value={formulario.nombres}
                onChange={(event) => setFormulario((valor) => ({ ...valor, nombres: event.target.value }))}
              />

              {campoErrores.nombres ? <p className="text-xs text-red-600">{campoErrores.nombres.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm text-slate-700">Apellidos</label>

              <input
                required
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                value={formulario.apellidos}
                onChange={(event) => setFormulario((valor) => ({ ...valor, apellidos: event.target.value }))}
              />

              {campoErrores.apellidos ? <p className="text-xs text-red-600">{campoErrores.apellidos.join(' ')}</p> : null}
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3">
            <div className="space-y-1">
              <label className="text-sm text-slate-700">Grado</label>

              <input
                required

                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"

                value={formulario.grado}
                onChange={(event) => setFormulario((valor) => ({ ...valor, grado: event.target.value }))}
              />

              {campoErrores.grado ? <p className="text-xs text-red-600">{campoErrores.grado.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm text-slate-700">Sección</label>

              <input
                required

                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"

                value={formulario.seccion}
                onChange={(event) => setFormulario((valor) => ({ ...valor, seccion: event.target.value }))}
              />

              {campoErrores.seccion ? <p className="text-xs text-red-600">{campoErrores.seccion.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm text-slate-700">Fecha nac.</label>

              <input
                type="date"
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                value={formulario.fecha_nacimiento}
                onChange={(event) => setFormulario((valor) => ({ ...valor, fecha_nacimiento: event.target.value }))}
              />

              {campoErrores.fecha_nacimiento ? <p className="text-xs text-red-600">{campoErrores.fecha_nacimiento.join(' ')}</p> : null}
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3">
            <div className="space-y-1">
              <label className="text-sm text-slate-700">Sexo</label>

              <select
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"

                value={formulario.sexo}
                onChange={(event) => setFormulario((valor) => ({ ...valor, sexo: event.target.value }))}

              >
                <option value="">Sin especificar</option>
                <option value="M">M</option>
                <option value="F">F</option>
              </select>

              {campoErrores.sexo ? <p className="text-xs text-red-600">{campoErrores.sexo.join(' ')}</p> : null}

            </div>

            <div className="space-y-1">
              <label className="text-sm text-slate-700">Nivel</label>

              <select
                required
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"

                value={formulario.nivel}
                onChange={(event) => setFormulario((valor) => ({ ...valor, nivel: event.target.value }))}

              >
                <option value="primaria">Primaria</option>
                <option value="secundaria">Secundaria</option>
              </select>

              {campoErrores.nivel ? <p className="text-xs text-red-600">{campoErrores.nivel.join(' ')}</p> : null}

            </div>

            <div className="space-y-1">
              <label className="text-sm text-slate-700">Sede</label>

              <select
                required
                className="w-full rounded border border-slate-300 px-3 py-2 text-sm"

                value={formulario.sede}
                onChange={(event) => setFormulario((valor) => ({ ...valor, sede: event.target.value }))}

              >
                <option value="chilca">Chilca</option>
                <option value="auquimarca">Auquimarca</option>

              </select>

              {campoErrores.sede ? <p className="text-xs text-red-600">{campoErrores.sede.join(' ')}</p> : null}

            </div>
          </div>

          <label className="flex items-center gap-2 text-sm text-slate-700">

            <input
              type="checkbox"
              checked={Boolean(formulario.activo)}
              onChange={(event) => setFormulario((valor) => ({ ...valor, activo: event.target.checked }))}

            />

            Activo
          </label>

          <button
            disabled={guardando}

            type="submit"
            className="rounded bg-slate-900 px-4 py-2 text-sm text-white disabled:opacity-70"

          >
            {guardando ? 'Guardando...' : 'Guardar'}
          </button>
        </form>

      ) : null}

      {vista === 'perfil' && cargando ? <p className="text-sm text-slate-500">Cargando perfil...</p> : null}

      {vista === 'perfil' && !cargando && detalle ? (

        <div className="space-y-4 text-sm text-slate-800">

          <dl className="grid gap-3 sm:grid-cols-2">

            <div>

              <dt className="text-slate-500">Código</dt>

              <dd className="font-medium">{detalle.codigo}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Año escolar</dt>

              <dd className="font-medium">{detalle.anio_escolar}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Nombres</dt>

              <dd>{detalle.nombres}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Apellidos</dt>

              <dd>{detalle.apellidos}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Grado</dt>

              <dd>{detalle.grado}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Sección</dt>

              <dd>{detalle.seccion}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Nivel</dt>

              <dd>{detalle.nivel}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Sede</dt>

              <dd>{detalle.sede}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Activo</dt>

              <dd>{detalle.activo ? 'Sí' : 'No'}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Fecha de nacimiento</dt>

              <dd>{detalle.fecha_nacimiento ?? '—'}</dd>

            </div>

            <div>

              <dt className="text-slate-500">Sexo</dt>

              <dd>{detalle.sexo ?? '—'}</dd>

            </div>

          </dl>

          {permissions.includes('registrar_datos_academicos') ? (
            <EstudiantePerfilDatos
              estudianteId={detalle.id}
              anioEscolarPorDefecto={detalle.anio_escolar}
            />
          ) : null}

          <button
            type="button"
            className="rounded border border-slate-300 px-3 py-2 text-sm text-slate-800"
            onClick={() => {

              void abrirEdicion(detalle.id);

            }}

          >
            Editar
          </button>

        </div>

      ) : null}

    </section>

  );

}
