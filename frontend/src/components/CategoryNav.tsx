import { NavLink } from 'react-router-dom';
import { useApi } from '../hooks/useApi';
import { api } from '../lib/api';
import { categoryLabel, categoryPath } from '../lib/format';

/** Horizontal category links under the header; scrolls sideways on small screens. */
export function CategoryNav() {
  const categories = useApi((signal) => api.categories(signal), []);

  // Navigation is secondary: hide quietly while loading or if it fails (pages show their own errors).
  if (categories.status !== 'success' || categories.data.length === 0) {
    return <nav className="category-nav category-nav-placeholder" aria-hidden="true" />;
  }

  return (
    <nav className="category-nav" aria-label="Categories">
      <ul className="container category-nav-list">
        <li>
          <NavLink to="/" end className="category-pill">
            All
          </NavLink>
        </li>
        {categories.data.map((category) => (
          <li key={category}>
            <NavLink to={categoryPath(category)} className="category-pill">
              {categoryLabel(category)}
            </NavLink>
          </li>
        ))}
      </ul>
    </nav>
  );
}
