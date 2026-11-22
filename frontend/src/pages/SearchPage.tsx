import { Link, useSearchParams } from 'react-router-dom';
import { ProductGrid } from '../components/ProductGrid';
import { EmptyState, ErrorState, LoadingGrid } from '../components/States';
import { useApi } from '../hooks/useApi';
import { usePageTitle } from '../hooks/usePageTitle';
import { api } from '../lib/api';

export function SearchPage() {
  const [params] = useSearchParams();
  const query = (params.get('q') ?? '').trim();
  usePageTitle(query ? `Search: ${query}` : 'Search');

  if (!query) {
    return (
      <div className="container">
        <EmptyState title="Search the catalogue" message="Type a product name, description or category in the search box above." />
      </div>
    );
  }

  return <SearchResults query={query} />;
}

function SearchResults({ query }: { query: string }) {
  const results = useApi((signal) => api.searchProducts(query, signal), [query]);

  return (
    <div className="container">
      <header className="page-header">
        <h1>Results for “{query}”</h1>
        {results.status === 'success' && (
          <p className="page-subtitle" aria-live="polite">
            {results.data.length} {results.data.length === 1 ? 'product' : 'products'} found
          </p>
        )}
      </header>

      {results.status === 'loading' && <LoadingGrid label="Searching" count={4} />}
      {results.status === 'error' && <ErrorState message={results.error.message} onRetry={results.retry} />}
      {results.status === 'success' &&
        (results.data.length > 0 ? (
          <ProductGrid products={results.data} label={`Search results for ${query}`} />
        ) : (
          <EmptyState
            title="No products found"
            message="Try a different word, or browse a category instead."
            action={<Link to="/" className="button button-secondary">Back to home</Link>}
          />
        ))}
    </div>
  );
}
