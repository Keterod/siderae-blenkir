import { useState } from 'react';
import Button from '../../ui/Button';
import {
  etiquetaTipoComponente,
  FIELD,
  formatoPeso,
  validarPesoEdicionPlantillaGrado,
  validarSumaManual,
} from './configuracionBimestralUtils';

export default function ComponenteEvaluacionRow({
  componente,
  activos,
  procesando,
  onToggleActivo,
  onGuardarPeso,
  onGuardarNombre,
  esPlantillaGrado = false,
}) {
  const esPersonalizado = componente.tipo === 'personalizado';
  const [editandoPeso, setEditandoPeso] = useState(false);
  const [pesoDraft, setPesoDraft] = useState('');
  const [editandoNombre, setEditandoNombre] = useState(false);
  const [nombreDraft, setNombreDraft] = useState('');
  const [errorLocal, setErrorLocal] = useState(null);

  const bloqueado = procesando === componente.id;

  function iniciarEditarPeso() {
    setPesoDraft(String(componente.peso ?? ''));
    setErrorLocal(null);
    setEditandoPeso(true);
  }

  async function confirmarPeso() {
    const error = esPlantillaGrado
      ? validarPesoEdicionPlantillaGrado(pesoDraft)
      : validarSumaManual(activos, componente.id, pesoDraft, 'peso');
    if (error) {
      setErrorLocal(error);
      return;
    }
    setErrorLocal(null);
    try {
      await onGuardarPeso(componente.id, Number(pesoDraft));
      setEditandoPeso(false);
    } catch {
      /* error manejado en panel */
    }
  }

  async function confirmarNombre() {
    const nombre = nombreDraft.trim();
    if (!nombre) {
      setErrorLocal('El nombre es obligatorio.');
      return;
    }
    setErrorLocal(null);
    try {
      await onGuardarNombre(componente.id, nombre);
      setEditandoNombre(false);
    } catch {
      /* error manejado en panel */
    }
  }

  return (
    <tr className={`border-b last:border-b-0 ${componente.activo ? '' : 'opacity-70'}`}>
      <td className="px-3 py-2">
        {editandoNombre && esPersonalizado ? (
          <div className="flex flex-col gap-1">
            <input
              className={FIELD}
              value={nombreDraft}
              onChange={(e) => setNombreDraft(e.target.value)}
              disabled={bloqueado}
            />
            <div className="flex gap-1">
              <Button size="sm" disabled={bloqueado} onClick={() => void confirmarNombre()}>
                Guardar
              </Button>
              <Button size="sm" variant="ghost" disabled={bloqueado} onClick={() => setEditandoNombre(false)}>
                Cancelar
              </Button>
            </div>
          </div>
        ) : (
          <div className="flex flex-col">
            <span className="font-medium text-[var(--text)]">{componente.nombre}</span>
            {esPersonalizado ? (
              <button
                type="button"
                className="mt-0.5 w-fit text-xs text-[var(--primary)] hover:underline disabled:opacity-50"
                disabled={bloqueado || !componente.activo}
                onClick={() => {
                  setNombreDraft(componente.nombre ?? '');
                  setEditandoNombre(true);
                }}
              >
                Editar nombre
              </button>
            ) : null}
          </div>
        )}
      </td>
      <td className="px-3 py-2 text-xs text-muted">
        <span className="rounded bg-[var(--surface-muted)] px-1.5 py-0.5">
          {componente.codigo ?? componente.tipo}
        </span>
        <div className="mt-0.5">{etiquetaTipoComponente(componente)}</div>
      </td>
      <td className="px-3 py-2 text-center">
        {editandoPeso && componente.activo ? (
          <div className="flex flex-col items-center gap-1">
            <input
              type="number"
              step="0.01"
              min="0"
              max="100"
              className={`${FIELD} max-w-[5.5rem] text-center`}
              value={pesoDraft}
              onChange={(e) => setPesoDraft(e.target.value)}
              disabled={bloqueado}
            />
            <div className="flex gap-1">
              <Button size="sm" disabled={bloqueado} onClick={() => void confirmarPeso()}>
                OK
              </Button>
              <Button size="sm" variant="ghost" disabled={bloqueado} onClick={() => setEditandoPeso(false)}>
                ✕
              </Button>
            </div>
          </div>
        ) : (
          <span>{formatoPeso(componente.peso)}</span>
        )}
        {errorLocal ? <p className="mt-1 text-[10px] text-red-700">{errorLocal}</p> : null}
      </td>
      <td className="px-3 py-2 text-center">
        <span
          className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${
            componente.activo
              ? 'bg-emerald-100 text-emerald-900'
              : 'bg-slate-200 text-slate-700'
          }`}
        >
          {componente.activo ? 'Activo' : 'Inactivo'}
        </span>
      </td>
      <td className="px-3 py-2">
        <div className="flex flex-wrap justify-end gap-1">
          {componente.activo ? (
            <Button
              size="sm"
              variant="outline"
              disabled={bloqueado}
              onClick={iniciarEditarPeso}
            >
              Peso
            </Button>
          ) : null}
          <Button
            size="sm"
            variant={componente.activo ? 'ghost' : 'secondary'}
            disabled={bloqueado}
            onClick={() => void onToggleActivo(componente)}
          >
            {componente.activo ? 'Desactivar' : 'Activar'}
          </Button>
        </div>
      </td>
    </tr>
  );
}
