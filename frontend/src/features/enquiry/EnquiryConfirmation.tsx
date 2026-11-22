import { CheckCircle2 } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import type { Product } from '../../types/catalogue';

interface Props {
  product: Product;
  message: string;
  email: string;
  onReset: () => void;
}

/** Thank-you confirmation shown after the API accepts an enquiry. */
export function EnquiryConfirmation({ product, message, email, onReset }: Props) {
  const headingRef = useRef<HTMLHeadingElement>(null);

  // Move focus so screen-reader and keyboard users land on the confirmation.
  useEffect(() => headingRef.current?.focus(), []);

  return (
    <div className="confirmation" role="status">
      <CheckCircle2 size={40} className="confirmation-icon" aria-hidden="true" />
      <h2 ref={headingRef} tabIndex={-1}>
        Thank you!
      </h2>
      <p>{message}</p>
      <p className="confirmation-detail">
        Your enquiry about <strong>{product.title}</strong> has been sent. We’ll reply to <strong>{email}</strong>.
      </p>
      <div className="confirmation-actions">
        <Link to="/" className="button button-primary">
          Continue browsing
        </Link>
        <button type="button" className="button button-secondary" onClick={onReset}>
          Send another enquiry
        </button>
      </div>
    </div>
  );
}
