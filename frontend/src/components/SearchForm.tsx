import { Search } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { useLocation, useNavigate, useSearchParams } from 'react-router-dom';

export const MAX_SEARCH_LENGTH = 120;

export function SearchForm() {
  const navigate = useNavigate();
  const { pathname } = useLocation();
  const [params] = useSearchParams();
  const currentQuery = pathname === '/search' ? (params.get('q') ?? '') : '';
  const [term, setTerm] = useState(currentQuery);

  // Keep the input in sync with the URL (back/forward, leaving the search page).
  useEffect(() => setTerm(currentQuery), [currentQuery]);

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const query = term.trim();
    if (query) navigate(`/search?q=${encodeURIComponent(query)}`);
  }

  return (
    <form className="search-form" role="search" onSubmit={submit}>
      <label htmlFor="site-search" className="visually-hidden">
        Search products
      </label>
      <Search className="search-icon" size={18} aria-hidden="true" />
      <input
        id="site-search"
        type="search"
        placeholder="Search products"
        value={term}
        maxLength={MAX_SEARCH_LENGTH}
        onChange={(event) => setTerm(event.target.value)}
      />
      <button type="submit" className="button button-primary button-small">
        Search
      </button>
    </form>
  );
}
