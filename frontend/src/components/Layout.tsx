import { useEffect } from 'react';
import { Link, Outlet, useLocation } from 'react-router-dom';
import { CategoryNav } from './CategoryNav';
import { SearchForm } from './SearchForm';

export function Layout() {
  const { pathname, search } = useLocation();

  useEffect(() => {
    window.scrollTo({ top: 0 });
  }, [pathname, search]);

  return (
    <div className="app">
      <a className="skip-link" href="#main">
        Skip to content
      </a>
      <header className="site-header">
        <div className="container header-bar">
          <Link to="/" className="brand" aria-label="Northstar home">
            <span className="brand-mark" aria-hidden="true">N</span>
            Northstar
          </Link>
          <SearchForm />
        </div>
        <CategoryNav />
      </header>

      <main id="main" className="site-main">
        <Outlet />
      </main>

      <footer className="site-footer">
        <div className="container footer-inner">
          <span>© {new Date().getFullYear()} Northstar</span>
          <span>Product catalogue proof of concept</span>
        </div>
      </footer>
    </div>
  );
}
