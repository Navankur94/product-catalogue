import { Link, useParams } from 'react-router-dom';
import { RatingBadge } from '../components/RatingBadge';
import { EmptyState, ErrorState, LoadingBlock } from '../components/States';
import { EnquiryForm } from '../features/enquiry/EnquiryForm';
import { useApi } from '../hooks/useApi';
import { usePageTitle } from '../hooks/usePageTitle';
import { ApiError, api } from '../lib/api';
import { categoryLabel, categoryPath, formatPrice } from '../lib/format';

export function ProductPage() {
  const { id = '' } = useParams();
  const productId = /^\d+$/.test(id) ? Number(id) : null;

  const product = useApi(
    (signal) => (productId === null ? Promise.reject(new ApiError('Product not found.', 404)) : api.product(productId, signal)),
    [productId],
  );
  usePageTitle(product.status === 'success' ? product.data.title : undefined);

  if (product.status === 'loading') {
    return (
      <div className="container">
        <LoadingBlock label="Loading product" />
      </div>
    );
  }

  if (product.status === 'error') {
    return (
      <div className="container">
        {product.error.isNotFound ? (
          <EmptyState
            title="Product not found"
            message="This product may have been removed or the link is incorrect."
            action={<Link to="/" className="button button-primary">Back to home</Link>}
          />
        ) : (
          <ErrorState message={product.error.message} onRetry={product.retry} />
        )}
      </div>
    );
  }

  const item = product.data;

  return (
    <div className="container">
      <nav aria-label="Breadcrumb" className="breadcrumb">
        <Link to="/">Home</Link> / <Link to={categoryPath(item.category)}>{categoryLabel(item.category)}</Link> /{' '}
        <span aria-current="page">{item.title}</span>
      </nav>

      <div className="product-detail">
        <div className="product-detail-media">
          <img src={item.image} alt={item.title} />
        </div>

        <div className="product-detail-info">
          <Link to={categoryPath(item.category)} className="product-detail-category">
            {categoryLabel(item.category)}
          </Link>
          <h1>{item.title}</h1>
          <RatingBadge rating={item.rating} />
          <p className="product-detail-price">{formatPrice(item.price)}</p>
          <p className="product-detail-description">{item.description}</p>

          <div className="product-detail-enquiry">
            {/* key resets the form when navigating between products */}
            <EnquiryForm key={item.id} product={item} />
          </div>
        </div>
      </div>
    </div>
  );
}
