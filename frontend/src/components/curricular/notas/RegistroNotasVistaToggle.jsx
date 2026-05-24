import Button from '../../ui/Button';

export default function RegistroNotasVistaToggle({ vista, onChange }) {
  return (
    <div className="inline-flex w-full rounded border border-[var(--border)] bg-[var(--surface)] p-px" role="group" aria-label="Modo de vista">
      <Button
        type="button"
        variant={vista === 'aula' ? 'primary' : 'ghost'}
        size="sm"
        className="flex-1 rounded-sm px-2 py-0.5 text-[10px] leading-4"
        onClick={() => onChange('aula')}
        data-testid="registro-notas-vista-aula"
      >
        Por aula
      </Button>
      <Button
        type="button"
        variant={vista === 'estudiante' ? 'primary' : 'ghost'}
        size="sm"
        className="flex-1 rounded-sm px-2 py-0.5 text-[10px] leading-4"
        onClick={() => onChange('estudiante')}
        data-testid="registro-notas-vista-estudiante"
      >
        Por alumno
      </Button>
    </div>
  );
}
