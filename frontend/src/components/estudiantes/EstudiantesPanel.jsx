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
import Badge from '../ui/Badge';
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

export default function EstudiantesPanel({ onClose = null }) {
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

  function cancelarFormulario() {
    setCampoErrores({});
    setErrorGeneral(null);
    if (vista === 'editar') {
      setVista('perfil');
    } else {
      setVista('lista');
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
    <Card className="space-y-5 border-[var(--border)] shadow-card" data-testid="estudiantes-panel">
      <div className="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border)] pb-4">
        <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">{tituloActual()}</h2>

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
              data-testid="estudiantes-volver-listado"
            >
              Volver al listado
            </Button>
          ) : null}

          {vista === 'lista' ? (
            <>
              <Button type="button" variant="outline" size="sm" onClick={() => cargarLista()} data-testid="estudiantes-actualizar">
                Actualizar
              </Button>
              <Button type="button" variant="primary" size="sm" onClick={() => abrirCreacion()} data-testid="estudiantes-nuevo">
                Nuevo estudiante
              </Button>
            </>
          ) : null}

          {typeof onClose === 'function' ? (
            <Button type="button" variant="danger" size="sm" onClick={onClose}>
              Cerrar módulo
            </Button>
          ) : null}
        </div>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}

      {vista === 'lista' && cargando ? <LoadingState label="Cargando listado…" /> : null}

      {vista === 'lista' && !cargando ? (
        lista.length === 0 ? (
          <EmptyState
            title="Aún no hay estudiantes"
            description="Cuando registre el primero aparecerán en esta tabla completa institucional. Use «Nuevo estudiante»."
          />
        ) : (
          <div className="space-y-3">
            <p className="text-sm leading-relaxed text-muted">Listado completo institucional; use «Ver perfil» para datos académicos por alumno.</p>
            <div className="overflow-x-auto rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm" data-testid="estudiantes-tabla">
              <table className="min-w-full text-left text-sm text-[var(--text)]">
                <thead className="border-b border-[var(--border)] bg-[var(--background)] text-[11px] font-semibold uppercase tracking-wide text-muted">
                  <tr>
                    <th className="px-4 py-3">Estudiante</th>
                    <th className="hidden px-4 py-3 sm:table-cell">Código</th>
                    <th className="px-4 py-3">Grado / Sección</th>
                    <th className="px-4 py-3">Estado</th>
                    <th className="px-4 py-3 text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {lista.map((item, index) => (
                    <tr
                      key={item.id}
                      data-testid={`estudiante-fila-${item.id}`}
                      className={`border-b border-[var(--border)]/70 last:border-0 ${
                        index % 2 === 0 ? 'bg-[var(--surface)]' : 'bg-[var(--background)]/35'
                      }`}
                    >
                      <td className="px-4 py-3 font-medium">{item.apellidos}, {item.nombres}</td>
                      <td className="hidden px-4 py-3 font-mono text-xs text-muted sm:table-cell">{item.codigo}</td>
                      <td className="px-4 py-3 text-muted">
                        {item.grado} · {item.seccion}
                      </td>
                      <td className="px-4 py-3">
                        {item.activo ? (
                          <Badge variant="success" className="normal-case">
                            Activo
                          </Badge>
                        ) : (
                          <Badge variant="neutral" className="normal-case">
                            Inactivo
                          </Badge>
                        )}
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex flex-wrap justify-end gap-2">
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => abrirDetalle(item.id)}
                            data-testid={`estudiante-perfil-${item.id}`}
                          >
                            Ver perfil
                          </Button>
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="text-[var(--secondary)]"
                            onClick={() => abrirEdicion(item.id)}
                          >
                            Editar
                          </Button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )
      ) : null}

      {(vista === 'crear' || vista === 'editar') && cargando ? <LoadingState label="Cargando formulario…" /> : null}

      {(vista === 'crear' || vista === 'editar') && !cargando ? (
        <form
          className="space-y-4"
          onSubmit={(event) => {
            event.preventDefault();
            void enviarFormulario(vista === 'editar');
          }}
        >
          <Card className="space-y-4 shadow-sm">
            <div>
              <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Información básica</h3>
              <p className="mt-1 text-sm text-muted">Datos personales del estudiante.</p>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Código</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.codigo}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, codigo: event.target.value }))}
                />
                {campoErrores.codigo ? <p className="text-xs text-red-600">{campoErrores.codigo.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Fecha de nacimiento</label>
                <input
                  type="date"
                  className="sb-field w-full min-w-0"
                  value={formulario.fecha_nacimiento}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, fecha_nacimiento: event.target.value }))}
                />
                {campoErrores.fecha_nacimiento ? <p className="text-xs text-red-600">{campoErrores.fecha_nacimiento.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1 sm:col-span-2">
                <label className="text-sm font-medium text-[var(--text)]">Nombres</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.nombres}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, nombres: event.target.value }))}
                />
                {campoErrores.nombres ? <p className="text-xs text-red-600">{campoErrores.nombres.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1 sm:col-span-2">
                <label className="text-sm font-medium text-[var(--text)]">Apellidos</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.apellidos}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, apellidos: event.target.value }))}
                />
                {campoErrores.apellidos ? <p className="text-xs text-red-600">{campoErrores.apellidos.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Sexo</label>
                <select
                  className="sb-field w-full min-w-0"
                  value={formulario.sexo}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, sexo: event.target.value }))}
                >
                  <option value="">Sin especificar</option>
                  <option value="M">M</option>
                  <option value="F">F</option>
                </select>
                {campoErrores.sexo ? <p className="text-xs text-red-600">{campoErrores.sexo.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col justify-end gap-1 sm:col-span-2">
                <label className="flex cursor-pointer items-center gap-3 text-sm font-medium text-[var(--text)]">
                  <input
                    type="checkbox"
                    checked={Boolean(formulario.activo)}
                    onChange={(event) => setFormulario((valor) => ({ ...valor, activo: event.target.checked }))}
                  />
                  Estudiante activo
                </label>
              </div>
            </div>
          </Card>

          <Card className="space-y-4 shadow-sm">
            <div>
              <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Información académica</h3>
              <p className="mt-1 text-sm text-muted">Ubicación y año escolar del estudiante.</p>
            </div>

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Año escolar</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.anio_escolar}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, anio_escolar: event.target.value }))}
                />
                {campoErrores.anio_escolar ? <p className="text-xs text-red-600">{campoErrores.anio_escolar.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Grado</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.grado}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, grado: event.target.value }))}
                />
                {campoErrores.grado ? <p className="text-xs text-red-600">{campoErrores.grado.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Sección</label>
                <input
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.seccion}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, seccion: event.target.value }))}
                />
                {campoErrores.seccion ? <p className="text-xs text-red-600">{campoErrores.seccion.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Nivel</label>
                <select
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.nivel}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, nivel: event.target.value }))}
                >
                  <option value="primaria">Primaria</option>
                  <option value="secundaria">Secundaria</option>
                </select>
                {campoErrores.nivel ? <p className="text-xs text-red-600">{campoErrores.nivel.join(' ')}</p> : null}
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-[var(--text)]">Sede</label>
                <select
                  required
                  className="sb-field w-full min-w-0"
                  value={formulario.sede}
                  onChange={(event) => setFormulario((valor) => ({ ...valor, sede: event.target.value }))}
                >
                  <option value="chilca">Chilca</option>
                  <option value="auquimarca">Auquimarca</option>
                </select>
                {campoErrores.sede ? <p className="text-xs text-red-600">{campoErrores.sede.join(' ')}</p> : null}
              </div>
            </div>
          </Card>

          <div className="flex flex-wrap justify-end gap-2 border-t border-[var(--border)] pt-4">
            <Button type="button" variant="outline" size="sm" disabled={guardando} onClick={cancelarFormulario}>
              Cancelar
            </Button>
            <Button disabled={guardando} type="submit" variant="primary" size="sm" data-testid="estudiante-guardar">
              {guardando ? 'Guardando…' : vista === 'editar' ? 'Guardar cambios' : 'Guardar estudiante'}
            </Button>
          </div>
        </form>
      ) : null}

      {vista === 'perfil' && cargando ? <LoadingState label="Cargando perfil…" /> : null}

      {vista === 'perfil' && !cargando && detalle ? (
        <div className="space-y-6 text-sm text-[var(--text)]">
          <Card className="border-[var(--border)] bg-[var(--surface)] shadow-sm ring-1 ring-[var(--border)]/60">
            <h3 className="text-[13px] font-semibold uppercase tracking-wide text-muted">Datos generales</h3>
            <dl className="mt-3 grid gap-x-6 gap-y-4 sm:grid-cols-2">
              <div>
                <dt className="text-muted">Código</dt>
                <dd className="mt-0.5 font-medium">{detalle.codigo}</dd>
              </div>
              <div>
                <dt className="text-muted">Año escolar</dt>
                <dd className="mt-0.5 font-medium">{detalle.anio_escolar}</dd>
              </div>
              <div>
                <dt className="text-muted">Nombres</dt>
                <dd className="mt-0.5">{detalle.nombres}</dd>
              </div>
              <div>
                <dt className="text-muted">Apellidos</dt>
                <dd className="mt-0.5">{detalle.apellidos}</dd>
              </div>
              <div>
                <dt className="text-muted">Grado</dt>
                <dd className="mt-0.5">{detalle.grado}</dd>
              </div>
              <div>
                <dt className="text-muted">Sección</dt>
                <dd className="mt-0.5">{detalle.seccion}</dd>
              </div>
              <div>
                <dt className="text-muted">Nivel</dt>
                <dd className="mt-0.5">{detalle.nivel}</dd>
              </div>
              <div>
                <dt className="text-muted">Sede</dt>
                <dd className="mt-0.5">{detalle.sede}</dd>
              </div>
              <div>
                <dt className="text-muted">Activo</dt>
                <dd className="mt-0.5">{detalle.activo ? 'Sí' : 'No'}</dd>
              </div>
              <div>
                <dt className="text-muted">Fecha de nacimiento</dt>
                <dd className="mt-0.5">{detalle.fecha_nacimiento ?? '—'}</dd>
              </div>
              <div>
                <dt className="text-muted">Sexo</dt>
                <dd className="mt-0.5">{detalle.sexo ?? '—'}</dd>
              </div>
            </dl>
          </Card>

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
              ubicacionEstudiante={{
                nivel: detalle.nivel,
                grado: detalle.grado,
                sede: detalle.sede,
                anio_escolar: detalle.anio_escolar,
              }}
              puedeUsarCatalogoMaterias={permissions.includes('gestionar_materias')}
            />
          ) : null}

          <div className="flex flex-wrap gap-2">
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={() => {
                void abrirEdicion(detalle.id);
              }}
              data-testid="perfil-editar"
            >
              Editar estudiante
            </Button>
          </div>
        </div>
      ) : null}
    </Card>
  );
}
