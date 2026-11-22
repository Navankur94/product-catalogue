import { Link } from 'react-router-dom';
import { categoryLabel, formatPrice, productPath } from '../lib/format';
import type { Product } from '../types/catalogue';
import { RatingBadge } from './RatingBadge';

export function ProductCard({ product }: { product: Product }) {
  return (
    <article className="product-card">
      <Link to={productPath(product.id)} className="product-card-link">
        <div className="product-card-media">
          <img src={product.image} alt="" loading="lazy" />
        </div>
        <div className="product-card-body">
          <span className="product-card-category">{categoryLabel(product.category)}</span>
          <h3 className="product-card-title">{product.title}</h3>
          <div className="product-card-footer">
            <span className="price">{formatPrice(product.price)}</span>
            <RatingBadge rating={product.rating} compact />
          </div>
        </div>
      </Link>
    </article>
  );
}
