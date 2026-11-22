import { AlertCircle, SearchX } from 'lucide-react';
import type { ReactNode } from 'react';

export function LoadingGrid({ count = 5, label = 'Loading products' }: { count?: number; label?: string }) {
  return (
    <div className="product-grid" role="status" aria-live="polite">
      <span className="visually-hidden">{label}…</span>
      {Array.from({ length: count }, (_, index) => (
        <div key={index} className="skeleton-card" aria-hidden="true">
          <div className="skeleton skeleton-media" />
          <div className="skeleton skeleton-line" />
          <div className="skeleton skeleton-line short" />
        </div>
      ))}
    </div>
  );
}

export function LoadingBlock({ label = 'Loading' }: { label?: string }) {
  return (
    <div className="state" role="status" aria-live="polite">
      <span className="spinner" aria-hidden="true" />
      <p>{label}…</p>
    </div>
  );
}

export function ErrorState({ title = 'Something went wrong', message, onRetry }: { title?: string; message: string; onRetry?: () => void }) {
  return (
    <div className="state state-error" role="alert">
      <AlertCircle size={32} aria-hidden="true" />
      <h2>{title}</h2>
      <p>{message}</p>
      {onRetry && (
        <button type="button" className="button button-secondary" onClick={onRetry}>
          Try again
        </button>
      )}
    </div>
  );
}

export function EmptyState({ title, message, action }: { title: string; message?: string; action?: ReactNode }) {
  return (
    <div className="state">
      <SearchX size={32} aria-hidden="true" />
      <h2>{title}</h2>
      {message && <p>{message}</p>}
      {action}
    </div>
  );
}
