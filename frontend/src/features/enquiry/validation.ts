import type { EnquiryField } from '../../types/catalogue';

export type EnquiryValues = Record<EnquiryField, string>;
export type EnquiryErrors = Partial<Record<EnquiryField, string>>;

export const EMPTY_ENQUIRY: EnquiryValues = { name: '', email: '', phone: '', message: '' };

export const LIMITS = { name: 100, email: 254, phone: 30, message: 2000 } as const;

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_PATTERN = /^[0-9+()\-.\s]+$/;

/**
 * Client-side checks mirror the Laravel rules for fast feedback.
 * The server remains the source of truth and its 422 errors are shown too.
 */
export function validateEnquiry(values: EnquiryValues): EnquiryErrors {
  const errors: EnquiryErrors = {};
  const name = values.name.trim();
  const email = values.email.trim();
  const phone = values.phone.trim();

  if (!name) errors.name = 'Please enter your name.';
  else if (name.length < 2) errors.name = 'Name must be at least 2 characters.';
  else if (name.length > LIMITS.name) errors.name = `Name must be ${LIMITS.name} characters or fewer.`;

  if (!email) errors.email = 'Please enter your email address.';
  else if (email.length > LIMITS.email || !EMAIL_PATTERN.test(email)) errors.email = 'Please enter a valid email address.';

  if (phone && (phone.length > LIMITS.phone || !PHONE_PATTERN.test(phone))) {
    errors.phone = 'Use digits, spaces and + ( ) - . only (max 30 characters).';
  }

  if (values.message.length > LIMITS.message) errors.message = `Message must be ${LIMITS.message} characters or fewer.`;

  return errors;
}
