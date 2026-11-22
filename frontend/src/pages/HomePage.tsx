import { ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';
import { ProductGrid } from '../components/ProductGrid';
import { Section } from '../components/Section';
import { EmptyState, ErrorState, LoadingGrid } from '../components/States';
import { useApi } from '../hooks/useApi';
import { usePageTitle } from '../hooks/usePageTitle';
import { api } from '../lib/api';
import { categoryLabel, categoryPath } from '../lib/format';

export function HomePage() {
  usePageTitle();
  const featured = useApi((signal) => api.featuredProducts(signal), []);
  const categories = useApi((signal) => api.categories(signal), []);

  return (
    <div className="container">
      <section className="hero">
        <h1>Discover products worth keeping</h1>
        <p>Browse our top-rated picks, explore by category, or search the whole catalogue.</p>
      </section>

      <Section title="Featured products" subtitle="Our five top-rated products right now.">
        {featured.status === 'loading' && <LoadingGrid label="Loading featured products" />}
        {featured.status === 'error' && <ErrorState message={featured.error.message} onRetry={featured.retry} />}
        {featured.status === 'success' &&
          (featured.data.length > 0 ? (
            <ProductGrid products={featured.data} label="Featured products" />
          ) : (
            <EmptyState title="No featured products yet" message="Please check back soon." />
          ))}
      </Section>

      {categories.status === 'success' && categories.data.length > 0 && (
        <Section title="Shop by category">
          <ul className="category-tiles">
            {categories.data.map((category) => (
              <li key={category}>
                <Link to={categoryPath(category)} className="category-tile">
                  <span>{categoryLabel(category)}</span>
                  <ArrowRight size={18} aria-hidden="true" />
                </Link>
              </li>
            ))}
          </ul>
        </Section>
      )}
    </div>
  );
}
