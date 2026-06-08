import { useState } from 'react';
import Button from '../../ui/Button';
import Card from '../../ui/Card';
import EtaConfigRow from './EtaConfigRow';
import { FIELD, separarActivosInactivos } from './configuracionBimestralUtils';
import PesosResumen from './PesosResumen';

function AgregarEtaForm({ disabled, procesando, onAgregar }) {
  const [nombre, setNombre] = useState('');
  const [errorLocal, setErrorLocal] = useState(null);

  async function enviar(e) {
    e.preventDefault();
    const valor = nombre.trim();
    if (!valor) {
      setErrorLocal('El nombre de la ETA es obligatorio.');
      return;
    }
    setErrorLocal(null);
    try {
      await onAgregar(valor);
      setNombre('');
    } catch {
      /* panel */
    }
  }

  return (
    <form className="mt-4 flex flex-col gap-2 border-t border-[var(--border)] pt-4 sm:flex-row sm:items-end" onSubmit={enviar}>
      <label className="flex-1 text-sm font-medium text-[var(--text)]">
        Nueva ETA
        <input
          className={FIELD}
          value={nombre}
          onChange={(e) => setNombre(e.target.value)}
          placeholder="Ej. ETA 4"
          disabled={disabled || procesando}
        />
      </label>
      <Button type="submit" disabled={disabled || procesando}>
        Agregar ETA
      </Button>
      {errorLocal ? <p className="text-xs text-red-700 sm:basis-full">{errorLocal}</p> : null}
    </form>
  );
}

function TablaEtas({
  titulo,
  items,
  activas,
  procesando,
  onToggleActivo,
  onGuardarPeso,
  onGuardarNombre,
  esPlantillaGrado,
}) {
  if (!items.length) return null;

  return (
    <div className="mt-4">
      <h4 className="text-xs font-semibold uppercase tracking-wide text-muted">{titulo}</h4>
      <div className="mt-2 overflow-x-auto">
        <table className="w-full min-w-[28rem] text-sm">
          <thead>
            <tr className="border-b bg-[var(--surface-muted)]/60 text-left text-xs text-muted">
              <th className="px-3 py-2 font-medium">ETA</th>
              <th className="px-3 py-2 text-center font-medium">Peso interno</th>
              <th className="px-3 py-2 text-center font-medium">Estado</th>
              <th className="px-3 py-2 text-right font-medium">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {items.map((eta) => (
              <EtaConfigRow
                key={eta.id}
                eta={eta}
                activas={activas}
                procesando={procesando}
                onToggleActivo={onToggleActivo}
                onGuardarPeso={onGuardarPeso}
                onGuardarNombre={onGuardarNombre}
                esPlantillaGrado={esPlantillaGrado}
              />
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default function EtasConfigTable({
  etas,
  procesando,
  onToggleActivo,
  onGuardarPeso,
  onGuardarNombre,
  onAgregar,
  esPlantillaGrado = false,
}) {
  const { activos: activas, inactivos: inactivas } = separarActivosInactivos(etas);

  return (
    <Card className="p-5 sm:p-6">
      <h3 className="text-base font-semibold text-[var(--text)]">ETAs (Evaluaciones de aprendizaje)</h3>
      <p className="mt-1 text-xs text-muted">
        Define las ETAs disponibles. La participación real en cada aula depende de las notas registradas.
      </p>

      <div className="mt-3">
        <PesosResumen titulo="Pesos internos activos" activos={activas} campo="peso_interno" />
      </div>

      <TablaEtas
        titulo="Activas"
        items={activas}
        activas={activas}
        procesando={procesando}
        onToggleActivo={onToggleActivo}
        onGuardarPeso={onGuardarPeso}
        onGuardarNombre={onGuardarNombre}
        esPlantillaGrado={esPlantillaGrado}
      />

      <TablaEtas
        titulo="Inactivas"
        items={inactivas}
        activas={activas}
        procesando={procesando}
        onToggleActivo={onToggleActivo}
        onGuardarPeso={onGuardarPeso}
        onGuardarNombre={onGuardarNombre}
        esPlantillaGrado={esPlantillaGrado}
      />

      <AgregarEtaForm
        disabled={!etas.length}
        procesando={Boolean(procesando)}
        onAgregar={onAgregar}
      />
    </Card>
  );
}
