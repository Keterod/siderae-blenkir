import { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  createEstudiante,
  getEstudiante,
  getEstudiantes,
  updateEstudiante,
} from '../../lib/api';
import EstudiantePerfilDatos from './EstudiantePerfilDatos';
import EstudiantePerfilRiesgo from './EstudiantePerfilRiesgo';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

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
    <Card className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--border)] pb-3">
        <h2 className="text-xl font-semibold text-[var(--text)]">{tituloActual()}</h2>

        <div className="flex flex-wrap gap-2">
          {vista !== 'lista' ? (
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={() => {
                setCampoErrores({});
                setErrorGeneral(null);
                setVista('lista');
              }}
            >
              Volver al listado
            </Button>
          ) : null}

          {vista === 'lista' ? (
            <>
              <Button type="button" variant="outline" size="sm" onClick={() => cargarLista()}>
                Actualizar
              </Button>
              <Button type="button" variant="primary" size="sm" onClick={() => abrirCreacion()}>
                Nuevo
              </Button>
            </>
          ) : null}

          <Button type="button" variant="danger" size="sm" onClick={onClose}>
            Cerrar módulo
          </Button>
        </div>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}

      {vista === 'lista' && cargando ? <LoadingState label="Cargando listado…" /> : null}

      {vista === 'lista' && !cargando ? (
        lista.length === 0 ? (
          <EmptyState title="Sin estudiantes registrados" description="Puedes registrar el primero con el botón Nuevo." />
        ) : (
          <ul className="divide-y divide-[var(--border)]">
            {lista.map((item) => (
              <li key={item.id} className="flex flex-wrap items-center justify-between gap-2 py-3">
                <button
                  type="button"
                  className="text-left font-medium text-[var(--text)] hover:text-[var(--primary-dark)] hover:underline"
                  onClick={() => abrirDetalle(item.id)}
                >
                  {item.apellidos}, {item.nombres} ({item.codigo})
                </button>

                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  className="text-[var(--secondary)]"
                  onClick={() => abrirEdicion(item.id)}
                >
                  Editar
                </Button>
              </li>
            ))}
          </ul>
        )
      ) : null}

      {(vista === 'crear' || vista === 'editar') && cargando ? <LoadingState label="Cargando formulario…" /> : null}

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
              <label className="text-sm font-medium text-[var(--text)]">Código</label>

              <input
                required
                className="sb-field"
                value={formulario.codigo}
                onChange={(event) => setFormulario((valor) => ({ ...valor, codigo: event.target.value }))}
              />

              {campoErrores.codigo ? <p className="text-xs text-red-600">{campoErrores.codigo.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Año escolar</label>

              <input
                required

                className="sb-field"
                value={formulario.anio_escolar}
                onChange={(event) => setFormulario((valor) => ({ ...valor, anio_escolar: event.target.value }))}
              />

              {campoErrores.anio_escolar ? <p className="text-xs text-red-600">{campoErrores.anio_escolar.join(' ')}</p> : null}
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-2">
            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Nombres</label>

              <input
                required
                className="sb-field"
                value={formulario.nombres}
                onChange={(event) => setFormulario((valor) => ({ ...valor, nombres: event.target.value }))}
              />

              {campoErrores.nombres ? <p className="text-xs text-red-600">{campoErrores.nombres.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Apellidos</label>

              <input
                required
                className="sb-field"
                value={formulario.apellidos}
                onChange={(event) => setFormulario((valor) => ({ ...valor, apellidos: event.target.value }))}
              />

              {campoErrores.apellidos ? <p className="text-xs text-red-600">{campoErrores.apellidos.join(' ')}</p> : null}
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3">
            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Grado</label>

              <input
                required

                className="sb-field"

                value={formulario.grado}
                onChange={(event) => setFormulario((valor) => ({ ...valor, grado: event.target.value }))}
              />

              {campoErrores.grado ? <p className="text-xs text-red-600">{campoErrores.grado.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Sección</label>

              <input
                required

                className="sb-field"

                value={formulario.seccion}
                onChange={(event) => setFormulario((valor) => ({ ...valor, seccion: event.target.value }))}
              />

              {campoErrores.seccion ? <p className="text-xs text-red-600">{campoErrores.seccion.join(' ')}</p> : null}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Fecha nac.</label>

              <input
                type="date"
                className="sb-field"
                value={formulario.fecha_nacimiento}
                onChange={(event) => setFormulario((valor) => ({ ...valor, fecha_nacimiento: event.target.value }))}
              />

              {campoErrores.fecha_nacimiento ? <p className="text-xs text-red-600">{campoErrores.fecha_nacimiento.join(' ')}</p> : null}
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3">
            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Sexo</label>

              <select
                className="sb-field"

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
              <label className="text-sm font-medium text-[var(--text)]">Nivel</label>

              <select
                required
                className="sb-field"

                value={formulario.nivel}
                onChange={(event) => setFormulario((valor) => ({ ...valor, nivel: event.target.value }))}

              >
                <option value="primaria">Primaria</option>
                <option value="secundaria">Secundaria</option>
              </select>

              {campoErrores.nivel ? <p className="text-xs text-red-600">{campoErrores.nivel.join(' ')}</p> : null}

            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-[var(--text)]">Sede</label>

              <select
                required
                className="sb-field"

                value={formulario.sede}
                onChange={(event) => setFormulario((valor) => ({ ...valor, sede: event.target.value }))}

              >
                <option value="chilca">Chilca</option>
                <option value="auquimarca">Auquimarca</option>

              </select>

              {campoErrores.sede ? <p className="text-xs text-red-600">{campoErrores.sede.join(' ')}</p> : null}

            </div>
          </div>

          <label className="flex items-center gap-2 text-sm font-medium text-[var(--text)]">

            <input
              type="checkbox"
              checked={Boolean(formulario.activo)}
              onChange={(event) => setFormulario((valor) => ({ ...valor, activo: event.target.checked }))}

            />

            Activo
          </label>

          <Button disabled={guardando} type="submit" variant="primary">
            {guardando ? 'Guardando...' : 'Guardar'}
          </Button>
        </form>

      ) : null}

      {vista === 'perfil' && cargando ? <LoadingState label="Cargando perfil…" /> : null}

      {vista === 'perfil' && !cargando && detalle ? (

        <div className="space-y-4 text-sm text-[var(--text)]">

          <dl className="grid gap-3 sm:grid-cols-2">

            <div>

              <dt className="text-muted">Código</dt>

              <dd className="font-medium">{detalle.codigo}</dd>

            </div>

            <div>

              <dt className="text-muted">Año escolar</dt>

              <dd className="font-medium">{detalle.anio_escolar}</dd>

            </div>

            <div>

              <dt className="text-muted">Nombres</dt>

              <dd>{detalle.nombres}</dd>

            </div>

            <div>

              <dt className="text-muted">Apellidos</dt>

              <dd>{detalle.apellidos}</dd>

            </div>

            <div>

              <dt className="text-muted">Grado</dt>

              <dd>{detalle.grado}</dd>

            </div>

            <div>

              <dt className="text-muted">Sección</dt>

              <dd>{detalle.seccion}</dd>

            </div>

            <div>

              <dt className="text-muted">Nivel</dt>

              <dd>{detalle.nivel}</dd>

            </div>

            <div>

              <dt className="text-muted">Sede</dt>

              <dd>{detalle.sede}</dd>

            </div>

            <div>

              <dt className="text-muted">Activo</dt>

              <dd>{detalle.activo ? 'Sí' : 'No'}</dd>

            </div>

            <div>

              <dt className="text-muted">Fecha de nacimiento</dt>

              <dd>{detalle.fecha_nacimiento ?? '—'}</dd>

            </div>

            <div>

              <dt className="text-muted">Sexo</dt>

              <dd>{detalle.sexo ?? '—'}</dd>

            </div>

          </dl>

          <EstudiantePerfilRiesgo
            estudianteId={detalle.id}
            ultimoIndice={detalle.ultimo_indice_riesgo}
            puedeProcesar={permissions.includes('procesar_riesgo')}
            onDetalleRefrescado={setDetalle}
          />

          {permissions.includes('registrar_datos_academicos') ? (
            <EstudiantePerfilDatos
              estudianteId={detalle.id}
              anioEscolarPorDefecto={detalle.anio_escolar}
            />
          ) : null}

          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => {
              void abrirEdicion(detalle.id);
            }}
          >
            Editar
          </Button>

        </div>

      ) : null}

    </Card>

  );

}
