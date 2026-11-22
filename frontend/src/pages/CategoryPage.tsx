import { Link, useParams } from 'react-router-dom';
import { ProductGrid } from '../components/ProductGrid';
import { Section } from '../components/Section';
import { EmptyState, ErrorState, LoadingGrid } from '../components/States';
import { useApi } from '../hooks/useApi';
import { usePageTitle } from '../hooks/usePageTitle';
import { api } from '../lib/api';
import { categoryLabel } from '../lib/format';

export function CategoryPage() {
  const { category = '' } = useParams();
  const listing = useApi((signal) => api.category(category, signal), [category]);
  const label = categoryLabel(listing.status === 'success' ? listing.data.category : category);
  usePageTitle(label);

  if (listing.status === 'error' && listing.error.isNotFound) {
    return (
      <div className="container">
        <EmptyState
          title="Category not found"
          message={`We couldn’t find a category called “${category}”.`}
          action={<Link to="/" className="button button-primary">Back to home</Link>}
        />
      </div>
    );
  }

  return (
    <div className="container">
      <header className="page-header">
        <nav aria-label="Breadcrumb" className="breadcrumb">
          <Link to="/">Home</Link> / <span aria-current="page">{label}</span>
        </nav>
        <h1>{label}</h1>
      </header>

      {listing.status === 'loading' && <LoadingGrid label={`Loading ${label}`} />}
      {listing.status === 'error' && <ErrorState message={listing.error.message} onRetry={listing.retry} />}
      {listing.status === 'success' && (
        <>
          <Section title="Top rated" subtitle={`The highest-rated products in ${label}.`}>
            <ProductGrid products={listing.data.featured} label={`Top rated in ${label}`} />
          </Section>
          {listing.data.others.length > 0 && (
            <Section title={`More in ${label}`}>
              <ProductGrid products={listing.data.others} label={`More in ${label}`} />
            </Section>
          )}
        </>
      )}
    </div>
  );
}
