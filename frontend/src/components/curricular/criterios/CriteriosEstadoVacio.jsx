import EmptyState from '../../ui/EmptyState';

export default function CriteriosEstadoVacio({ title, description, className = 'mt-4' }) {
  return <EmptyState className={className} title={title} description={description} />;
}
