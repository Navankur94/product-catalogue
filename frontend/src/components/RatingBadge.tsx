import { Star } from 'lucide-react';
import type { Rating } from '../types/catalogue';

export function RatingBadge({ rating, compact = false }: { rating: Rating | null; compact?: boolean }) {
  if (!rating) return <span className="rating rating-empty">No ratings yet</span>;

  const label = `Rated ${rating.rate.toFixed(1)} out of 5 from ${rating.count} reviews`;

  return (
    <span className="rating" aria-label={label} title={label}>
      <Star size={compact ? 14 : 16} fill="currentColor" aria-hidden="true" />
      <span aria-hidden="true">
        {rating.rate.toFixed(1)}
        {!compact && <span className="rating-count"> ({rating.count} reviews)</span>}
      </span>
    </span>
  );
}
