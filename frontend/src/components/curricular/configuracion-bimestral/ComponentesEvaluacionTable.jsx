import { useState } from 'react';
import Button from '../../ui/Button';
import Card from '../../ui/Card';
import ComponenteEvaluacionRow from './ComponenteEvaluacionRow';
import { FIELD, separarActivosInactivos } from './configuracionBimestralUtils';
import PesosResumen from './PesosResumen';

function AgregarComponenteForm({ disabled, procesando, onAgregar }) {
  const [nombre, setNombre] = useState('');
  const [errorLocal, setErrorLocal] = useState(null);

  async function enviar(e) {
    e.preventDefault();
    const valor = nombre.trim();
    if (!valor) {
      setErrorLocal('El nombre del componente es obligatorio.');
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
        Nuevo componente personalizado
        <input
          className={FIELD}
          value={nombre}
          onChange={(e) => setNombre(e.target.value)}
          placeholder="Ej. Proyecto, Exposición…"
          disabled={disabled || procesando}
        />
      </label>
      <Button type="submit" disabled={disabled || procesando}>
        Agregar
      </Button>
      {errorLocal ? <p className="text-xs text-red-700 sm:basis-full">{errorLocal}</p> : null}
    </form>
  );
}

function TablaComponentes({
  titulo,
  items,
  activos,
  procesando,
  onToggleActivo,
  onGuardarPeso,
  onGuardarNombre,
  esPlantillaGrado = false,
}) {
  if (!items.length) return null;

  return (
    <div className="mt-4">
      <h4 className="text-xs font-semibold uppercase tracking-wide text-muted">{titulo}</h4>
      <div className="mt-2 overflow-x-auto">
        <table className="w-full min-w-[32rem] text-sm">
          <thead>
            <tr className="border-b bg-[var(--surface-muted)]/60 text-left text-xs text-muted">
              <th className="px-3 py-2 font-medium">Componente</th>
              <th className="px-3 py-2 font-medium">Código / tipo</th>
              <th className="px-3 py-2 text-center font-medium">Peso</th>
              <th className="px-3 py-2 text-center font-medium">Estado</th>
              <th className="px-3 py-2 text-right font-medium">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {items.map((c) => (
              <ComponenteEvaluacionRow
                key={c.id}
                componente={c}
                activos={activos}
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

export default function ComponentesEvaluacionTable({
  componentes,
  procesando,
  onToggleActivo,
  onGuardarPeso,
  onGuardarNombre,
  onAgregar,
  esPlantillaGrado = false,
}) {
  const { activos, inactivos } = separarActivosInactivos(componentes);

  return (
    <Card className="p-5 sm:p-6">
      <h3 className="text-base font-semibold text-[var(--text)]">Componentes de evaluación</h3>
      <p className="mt-1 text-xs text-muted">
        Los componentes activos participan en el cálculo del nivel de logro.
      </p>

      <div className="mt-3">
        <PesosResumen titulo="Pesos activos" activos={activos} campo="peso" />
      </div>

      <TablaComponentes
        titulo="Activos"
        items={activos}
        activos={activos}
        procesando={procesando}
        onToggleActivo={onToggleActivo}
        onGuardarPeso={onGuardarPeso}
        onGuardarNombre={onGuardarNombre}
        esPlantillaGrado={esPlantillaGrado}
      />

      <TablaComponentes
        titulo="Inactivos"
        items={inactivos}
        activos={activos}
        procesando={procesando}
        onToggleActivo={onToggleActivo}
        onGuardarPeso={onGuardarPeso}
        onGuardarNombre={onGuardarNombre}
        esPlantillaGrado={esPlantillaGrado}
      />

      <AgregarComponenteForm
        disabled={!componentes.length}
        procesando={Boolean(procesando)}
        onAgregar={onAgregar}
      />
    </Card>
  );
}
