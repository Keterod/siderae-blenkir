import Card from '../../ui/Card';
import EmptyState from '../../ui/EmptyState';

export default function MallaEstadoVacio({ title, description }) {
  return (
    <Card className="p-8">
      <EmptyState title={title} description={description} />
    </Card>
  );
}
