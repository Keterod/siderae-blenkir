import { useCallback, useEffect, useState } from 'react';
import { getConfiguracionPesos, patchDesactivarConfiguracionPeso, postConfiguracionPeso } from '../../lib/api';
import AlertMessage from '../ui/AlertMessage';
import Button from '../ui/Button';
import Card from '../ui/Card';
import EmptyState from '../ui/EmptyState';
import LoadingState from '../ui/LoadingState';

const DEFAULT = { peso_cuaderno: 33.33, peso_libro: 33.33, peso_tarea: 33.34 };

export default function PesosEvaluacionPanel() {
  const [items, setItems] = useState([]);
  const [form, setForm] = useState(DEFAULT);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);

  const cargar = useCallback(async () => {
    setCargando(true);
    try {
      setItems(await getConfiguracionPesos({ activo: true }));
    } catch {
      setError('No se pudieron cargar los pesos.');
    } finally {
      setCargando(false);
    }
  }, []);

  useEffect(() => {
    void cargar();
  }, [cargar]);

  async function guardar(e) {
    e.preventDefault();
    setError(null);
    try {
      await postConfiguracionPeso(form);
      setForm(DEFAULT);
      await cargar();
    } catch (err) {
      setError(err.payload?.message ?? 'Suma de pesos debe ser 100.');
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <Card className="p-6">
        <h2 className="text-lg font-semibold">Pesos de evaluación (C/L/T)</h2>
        <p className="mt-1 text-sm text-muted">Default 33.33 / 33.33 / 33.34. La suma debe ser 100.</p>
      </Card>
      {error ? <AlertMessage variant="error">{error}</AlertMessage> : null}
      <Card className="p-6">
        <form className="grid gap-3 sm:grid-cols-4" onSubmit={guardar}>
          {['peso_cuaderno', 'peso_libro', 'peso_tarea'].map((k) => (
            <label key={k} className="text-sm capitalize">
              {k.replace('peso_', '')}
              <input type="number" step="0.01" className="mt-1 w-full rounded border px-2 py-1.5" value={form[k]} onChange={(e) => setForm({ ...form, [k]: e.target.value })} required />
            </label>
          ))}
          <div className="flex items-end"><Button type="submit">Guardar configuración global</Button></div>
        </form>
      </Card>
      <Card className="p-6">
        {cargando ? <LoadingState /> : null}
        {!cargando && items.length === 0 ? <EmptyState title="Sin configuraciones activas" /> : null}
        <ul className="divide-y text-sm">
          {items.map((p) => (
            <li key={p.id} className="flex justify-between py-2">
              <span>C:{p.peso_cuaderno} L:{p.peso_libro} T:{p.peso_tarea}</span>
              <Button type="button" size="sm" variant="ghost" onClick={() => void patchDesactivarConfiguracionPeso(p.id).then(cargar)}>Desactivar</Button>
            </li>
          ))}
        </ul>
      </Card>
    </div>
  );
}
