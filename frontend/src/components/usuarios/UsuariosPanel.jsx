import { useCallback, useEffect, useState } from 'react';
import {
  createUsuario,
  getUsuarios,
  patchActivarUsuario,
  patchDesactivarUsuario,
  postRestablecerContrasenaUsuario,
  updateUsuario,
  usuariosDesdeRespuesta,
} from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Badge from '../ui/Badge';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const ROLES = [
  { value: '', label: 'Todos los roles' },
  { value: 'administrador', label: 'Administrador' },
  { value: 'docente', label: 'Docente' },
  { value: 'coordinador_academico', label: 'Coordinador académico' },
  { value: 'psicologo_tutor', label: 'Psicólogo / tutor' },
  { value: 'directivo', label: 'Directivo' },
];

const ETIQUETAS_ROL = {
  administrador: 'Administrador',
  docente: 'Docente',
  coordinador_academico: 'Coordinador académico',
  psicologo_tutor: 'Psicólogo / tutor',
  directivo: 'Directivo',
};

function formularioVacio() {
  return {
    name: '',
    email: '',
    rol: 'docente',
    password: '',
    password_confirmation: '',
  };
}

function etiquetaRol(rol) {
  return ETIQUETAS_ROL[rol] ?? rol?.replace(/_/g, ' ') ?? '—';
}

export default function UsuariosPanel() {
  const [vista, setVista] = useState('lista');
  const [lista, setLista] = useState([]);
  const [paginacion, setPaginacion] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [filtros, setFiltros] = useState({ q: '', rol: '', incluir_inactivos: false });
  const [page, setPage] = useState(1);
  const [formulario, setFormulario] = useState(formularioVacio());
  const [editandoId, setEditandoId] = useState(null);
  const [cargando, setCargando] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [errorGeneral, setErrorGeneral] = useState(null);
  const [campoErrores, setCampoErrores] = useState({});
  const [modalPassword, setModalPassword] = useState(null);
  const [passwordForm, setPasswordForm] = useState({ password: '', password_confirmation: '' });

  const cargarLista = useCallback(async () => {
    setCargando(true);
    setErrorGeneral(null);
    try {
      const params = {
        page,
        per_page: 25,
        q: filtros.q.trim() || undefined,
        rol: filtros.rol || undefined,
        incluir_inactivos: filtros.incluir_inactivos ? 1 : undefined,
      };
      const respuesta = await getUsuarios(params);
      setLista(usuariosDesdeRespuesta(respuesta));
      setPaginacion({
        current_page: respuesta?.current_page ?? 1,
        last_page: respuesta?.last_page ?? 1,
        total: respuesta?.total ?? 0,
      });
    } catch (error) {
      if (error.status === 403) {
        setErrorGeneral('Sin permiso para gestionar usuarios.');
      } else {
        setErrorGeneral('No se pudo cargar el listado de usuarios.');
      }
      setLista([]);
    } finally {
      setCargando(false);
    }
  }, [filtros, page]);

  useEffect(() => {
    if (vista === 'lista') {
      cargarLista();
    }
  }, [vista, cargarLista]);

  function actualizarFiltros(updater) {
    setFiltros(updater);
    setPage(1);
  }

  function abrirCreacion() {
    setFormulario(formularioVacio());
    setEditandoId(null);
    setCampoErrores({});
    setErrorGeneral(null);
    setVista('crear');
  }

  function abrirEdicion(usuario) {
    setFormulario({
      name: usuario.name ?? '',
      email: usuario.email ?? '',
      rol: usuario.rol ?? 'docente',
      password: '',
      password_confirmation: '',
    });
    setEditandoId(usuario.id);
    setCampoErrores({});
    setErrorGeneral(null);
    setVista('editar');
  }

  function abrirModalPassword(usuario) {
    setModalPassword(usuario);
    setPasswordForm({ password: '', password_confirmation: '' });
    setCampoErrores({});
  }

  async function enviarFormulario(esEdicion) {
    setGuardando(true);
    setCampoErrores({});
    setErrorGeneral(null);

    try {
      if (esEdicion && editandoId) {
        await updateUsuario(editandoId, {
          name: formulario.name.trim(),
          email: formulario.email.trim(),
          rol: formulario.rol,
        });
      } else {
        await createUsuario({
          name: formulario.name.trim(),
          email: formulario.email.trim(),
          rol: formulario.rol,
          password: formulario.password,
          password_confirmation: formulario.password_confirmation,
        });
      }
      setVista('lista');
      await cargarLista();
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setCampoErrores(error.payload.errors);
      } else if (error.status === 403) {
        setErrorGeneral('Sin permiso para guardar el usuario.');
      } else {
        setErrorGeneral('No se pudo guardar el usuario.');
      }
    } finally {
      setGuardando(false);
    }
  }

  async function cambiarEstado(usuario, activar) {
    const accion = activar ? 'activar' : 'desactivar';
    const confirmar = window.confirm(
      activar
        ? `¿Activar la cuenta de ${usuario.name}?`
        : `¿Desactivar la cuenta de ${usuario.name}? No podrá iniciar sesión.`,
    );
    if (!confirmar) {
      return;
    }

    setErrorGeneral(null);
    try {
      if (activar) {
        await patchActivarUsuario(usuario.id);
      } else {
        await patchDesactivarUsuario(usuario.id);
      }
      await cargarLista();
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        const mensajes = Object.values(error.payload.errors).flat().join(' ');
        setErrorGeneral(mensajes || 'No se pudo cambiar el estado del usuario.');
      } else {
        setErrorGeneral(`No se pudo ${accion} el usuario.`);
      }
    }
  }

  async function enviarRestablecerPassword() {
    if (!modalPassword) {
      return;
    }
    setGuardando(true);
    setCampoErrores({});
    try {
      await postRestablecerContrasenaUsuario(modalPassword.id, passwordForm);
      setModalPassword(null);
      setPasswordForm({ password: '', password_confirmation: '' });
    } catch (error) {
      if (error.status === 422 && error.payload?.errors) {
        setCampoErrores(error.payload.errors);
      } else {
        setErrorGeneral('No se pudo restablecer la contraseña.');
      }
    } finally {
      setGuardando(false);
    }
  }

  function tituloActual() {
    if (vista === 'crear') {
      return 'Nuevo usuario';
    }
    if (vista === 'editar') {
      return 'Editar usuario';
    }
    return 'Usuarios';
  }

  return (
    <Card className="space-y-5 border-[var(--border)] shadow-card" data-testid="usuarios-panel">
      <div className="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border)] pb-4">
        <h2 className="text-xl font-semibold tracking-tight text-[var(--text)]">{tituloActual()}</h2>
        <div className="flex flex-wrap gap-2">
          {vista !== 'lista' ? (
            <Button type="button" variant="outline" size="sm" onClick={() => setVista('lista')}>
              Volver al listado
            </Button>
          ) : (
            <>
              <Button type="button" variant="outline" size="sm" onClick={() => cargarLista()}>
                Actualizar
              </Button>
              <Button type="button" variant="primary" size="sm" onClick={() => abrirCreacion()}>
                Nuevo usuario
              </Button>
            </>
          )}
        </div>
      </div>

      {errorGeneral ? <AlertMessage>{errorGeneral}</AlertMessage> : null}

      {vista === 'lista' && cargando ? <LoadingState label="Cargando usuarios…" /> : null}

      {vista === 'lista' && !cargando ? (
        <>
          <Card className="space-y-4 shadow-sm">
            <h3 className="text-sm font-semibold text-[var(--text)]">Búsqueda y filtros</h3>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              <div className="flex flex-col gap-1 sm:col-span-2">
                <label className="text-xs font-medium text-muted">Buscar (nombre o correo)</label>
                <input
                  className="sb-field min-w-0"
                  value={filtros.q}
                  onChange={(event) => actualizarFiltros((prev) => ({ ...prev, q: event.target.value }))}
                />
              </div>
              <div className="flex flex-col gap-1">
                <label className="text-xs font-medium text-muted">Rol</label>
                <select
                  className="sb-field"
                  value={filtros.rol}
                  onChange={(event) => actualizarFiltros((prev) => ({ ...prev, rol: event.target.value }))}
                >
                  {ROLES.map((opcion) => (
                    <option key={opcion.value || 'todos'} value={opcion.value}>
                      {opcion.label}
                    </option>
                  ))}
                </select>
              </div>
              <div className="flex items-end gap-2 pb-1">
                <label className="flex cursor-pointer items-center gap-2 text-sm text-[var(--text)]">
                  <input
                    type="checkbox"
                    checked={filtros.incluir_inactivos}
                    onChange={(event) => actualizarFiltros((prev) => ({
                      ...prev,
                      incluir_inactivos: event.target.checked,
                    }))}
                  />
                  Incluir inactivos
                </label>
              </div>
            </div>
          </Card>

          {lista.length === 0 ? (
            <EmptyState
              title="Sin usuarios"
              description="No hay usuarios que coincidan con los filtros actuales."
            />
          ) : (
            <div className="overflow-x-auto rounded-lg border border-[var(--border)]">
              <table className="min-w-full divide-y divide-[var(--border)] text-sm">
                <thead className="bg-[var(--surface-muted)]">
                  <tr>
                    <th className="px-3 py-2 text-left font-medium text-muted">Nombre</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">Correo</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">Rol</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">Estado</th>
                    <th className="px-3 py-2 text-right font-medium text-muted">Acciones</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[var(--border)]">
                  {lista.map((usuario) => (
                    <tr key={usuario.id}>
                      <td className="px-3 py-2 text-[var(--text)]">{usuario.name}</td>
                      <td className="px-3 py-2 text-muted">{usuario.email}</td>
                      <td className="px-3 py-2">
                        <Badge tone="neutral">{etiquetaRol(usuario.rol)}</Badge>
                      </td>
                      <td className="px-3 py-2">
                        <Badge tone={usuario.activo ? 'success' : 'warning'}>
                          {usuario.activo ? 'Activo' : 'Inactivo'}
                        </Badge>
                      </td>
                      <td className="px-3 py-2">
                        <div className="flex flex-wrap justify-end gap-1">
                          <Button type="button" variant="ghost" size="sm" onClick={() => abrirEdicion(usuario)}>
                            Editar
                          </Button>
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => abrirModalPassword(usuario)}
                          >
                            Contraseña
                          </Button>
                          {usuario.activo ? (
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              onClick={() => cambiarEstado(usuario, false)}
                            >
                              Desactivar
                            </Button>
                          ) : (
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              onClick={() => cambiarEstado(usuario, true)}
                            >
                              Activar
                            </Button>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {paginacion.last_page > 1 ? (
            <div className="flex items-center justify-between gap-2 text-sm text-muted">
              <span>
                Página {paginacion.current_page} de {paginacion.last_page} ({paginacion.total} usuarios)
              </span>
              <div className="flex gap-2">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={page <= 1}
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                >
                  Anterior
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={page >= paginacion.last_page}
                  onClick={() => setPage((p) => p + 1)}
                >
                  Siguiente
                </Button>
              </div>
            </div>
          ) : null}
        </>
      ) : null}

      {(vista === 'crear' || vista === 'editar') && (
        <Card className="space-y-4 shadow-sm">
          <div className="grid gap-3 sm:grid-cols-2">
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Nombre</label>
              <input
                className="sb-field"
                value={formulario.name}
                onChange={(event) => setFormulario((prev) => ({ ...prev, name: event.target.value }))}
              />
              {campoErrores.name ? (
                <p className="text-xs text-[var(--danger)]">{campoErrores.name.join(' ')}</p>
              ) : null}
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Correo</label>
              <input
                type="email"
                className="sb-field"
                value={formulario.email}
                onChange={(event) => setFormulario((prev) => ({ ...prev, email: event.target.value }))}
              />
              {campoErrores.email ? (
                <p className="text-xs text-[var(--danger)]">{campoErrores.email.join(' ')}</p>
              ) : null}
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Rol</label>
              <select
                className="sb-field"
                value={formulario.rol}
                onChange={(event) => setFormulario((prev) => ({ ...prev, rol: event.target.value }))}
              >
                {ROLES.filter((r) => r.value).map((opcion) => (
                  <option key={opcion.value} value={opcion.value}>
                    {opcion.label}
                  </option>
                ))}
              </select>
              {campoErrores.rol ? (
                <p className="text-xs text-[var(--danger)]">{campoErrores.rol.join(' ')}</p>
              ) : null}
            </div>
            {vista === 'crear' ? (
              <>
                <div className="flex flex-col gap-1">
                  <label className="text-xs font-medium text-muted">Contraseña</label>
                  <input
                    type="password"
                    className="sb-field"
                    value={formulario.password}
                    onChange={(event) => setFormulario((prev) => ({ ...prev, password: event.target.value }))}
                  />
                  {campoErrores.password ? (
                    <p className="text-xs text-[var(--danger)]">{campoErrores.password.join(' ')}</p>
                  ) : null}
                </div>
                <div className="flex flex-col gap-1">
                  <label className="text-xs font-medium text-muted">Confirmar contraseña</label>
                  <input
                    type="password"
                    className="sb-field"
                    value={formulario.password_confirmation}
                    onChange={(event) => setFormulario((prev) => ({
                      ...prev,
                      password_confirmation: event.target.value,
                    }))}
                  />
                </div>
              </>
            ) : null}
          </div>
          <div className="flex gap-2">
            <Button
              type="button"
              variant="primary"
              size="sm"
              disabled={guardando}
              onClick={() => enviarFormulario(vista === 'editar')}
            >
              {guardando ? 'Guardando…' : 'Guardar'}
            </Button>
            <Button type="button" variant="outline" size="sm" onClick={() => setVista('lista')}>
              Cancelar
            </Button>
          </div>
        </Card>
      )}

      {modalPassword ? (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4">
          <Card className="w-full max-w-md space-y-4 shadow-card">
            <h3 className="text-lg font-semibold text-[var(--text)]">
              Restablecer contraseña — {modalPassword.name}
            </h3>
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Nueva contraseña</label>
              <input
                type="password"
                className="sb-field"
                value={passwordForm.password}
                onChange={(event) => setPasswordForm((prev) => ({
                  ...prev,
                  password: event.target.value,
                }))}
              />
              {campoErrores.password ? (
                <p className="text-xs text-[var(--danger)]">{campoErrores.password.join(' ')}</p>
              ) : null}
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-xs font-medium text-muted">Confirmar contraseña</label>
              <input
                type="password"
                className="sb-field"
                value={passwordForm.password_confirmation}
                onChange={(event) => setPasswordForm((prev) => ({
                  ...prev,
                  password_confirmation: event.target.value,
                }))}
              />
            </div>
            <div className="flex justify-end gap-2">
              <Button type="button" variant="outline" size="sm" onClick={() => setModalPassword(null)}>
                Cancelar
              </Button>
              <Button
                type="button"
                variant="primary"
                size="sm"
                disabled={guardando}
                onClick={() => enviarRestablecerPassword()}
              >
                {guardando ? 'Guardando…' : 'Restablecer'}
              </Button>
            </div>
          </Card>
        </div>
      ) : null}
    </Card>
  );
}
