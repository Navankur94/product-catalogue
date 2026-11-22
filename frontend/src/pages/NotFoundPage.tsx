import { Link } from 'react-router-dom';
import { EmptyState } from '../components/States';
import { usePageTitle } from '../hooks/usePageTitle';

export function NotFoundPage() {
  usePageTitle('Page not found');

  return (
    <div className="container">
      <EmptyState
        title="Page not found"
        message="The page you’re looking for doesn’t exist."
        action={<Link to="/" className="button button-primary">Back to home</Link>}
      />
    </div>
  );
}
