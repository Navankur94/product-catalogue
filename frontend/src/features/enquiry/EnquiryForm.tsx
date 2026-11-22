import { useRef, useState, type ChangeEvent, type FormEvent } from 'react';
import { ApiError, api } from '../../lib/api';
import type { EnquiryField, Product } from '../../types/catalogue';
import { EnquiryConfirmation } from './EnquiryConfirmation';
import { FormField } from './FormField';
import { EMPTY_ENQUIRY, LIMITS, validateEnquiry, type EnquiryErrors, type EnquiryValues } from './validation';

const FIELDS: EnquiryField[] = ['name', 'email', 'phone', 'message'];

function isEnquiryField(field: string): field is EnquiryField {
  return (FIELDS as string[]).includes(field);
}

export function EnquiryForm({ product }: { product: Product }) {
  const [values, setValues] = useState<EnquiryValues>(EMPTY_ENQUIRY);
  const [errors, setErrors] = useState<EnquiryErrors>({});
  const [formError, setFormError] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [confirmation, setConfirmation] = useState<{ message: string; email: string } | null>(null);
  const formRef = useRef<HTMLFormElement>(null);

  function update(field: EnquiryField) {
    return (event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
      const next = { ...values, [field]: event.target.value };
      setValues(next);
      // Once a field shows an error, re-validate it as the user types.
      if (errors[field]) setErrors((current) => ({ ...current, [field]: validateEnquiry(next)[field] }));
    };
  }

  function focusFirstInvalid(fieldErrors: EnquiryErrors) {
    const first = FIELDS.find((field) => fieldErrors[field]);
    if (first) formRef.current?.querySelector<HTMLElement>(`[name="${first}"]`)?.focus();
  }

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setFormError('');

    const clientErrors = validateEnquiry(values);
    setErrors(clientErrors);
    if (Object.keys(clientErrors).length > 0) {
      focusFirstInvalid(clientErrors);
      return;
    }

    setSubmitting(true);
    try {
      const email = values.email.trim();
      const response = await api.sendEnquiry({
        product_id: product.id,
        name: values.name.trim(),
        email,
        phone: values.phone.trim() || undefined,
        message: values.message.trim() || undefined,
      });
      setConfirmation({ message: response.message, email });
    } catch (error) {
      if (error instanceof ApiError && error.status === 422) {
        const serverErrors: EnquiryErrors = {};
        for (const [field, message] of Object.entries(error.fieldErrors)) {
          if (isEnquiryField(field)) serverErrors[field] = message;
        }
        setErrors(serverErrors);
        focusFirstInvalid(serverErrors);
        // Errors not tied to a visible field (e.g. product no longer exists).
        if (Object.keys(serverErrors).length === 0) setFormError(error.message);
      } else {
        setFormError(error instanceof ApiError ? error.message : 'We couldn’t send your enquiry. Please try again.');
      }
    } finally {
      setSubmitting(false);
    }
  }

  if (confirmation) {
    return (
      <EnquiryConfirmation
        product={product}
        message={confirmation.message}
        email={confirmation.email}
        onReset={() => {
          setValues(EMPTY_ENQUIRY);
          setErrors({});
          setConfirmation(null);
        }}
      />
    );
  }

  return (
    <form ref={formRef} className="enquiry-form" onSubmit={submit} noValidate aria-labelledby="enquiry-heading">
      <h2 id="enquiry-heading">Enquire about this product</h2>
      <p className="enquiry-intro">Send our sales team a question and we’ll get back to you by email.</p>

      <FormField id="enquiry-name" label="Name" error={errors.name}>
        {(describedBy) => (
          <input
            id="enquiry-name"
            name="name"
            autoComplete="name"
            value={values.name}
            onChange={update('name')}
            maxLength={LIMITS.name}
            aria-invalid={Boolean(errors.name)}
            aria-describedby={describedBy}
            required
          />
        )}
      </FormField>

      <FormField id="enquiry-email" label="Email" error={errors.email}>
        {(describedBy) => (
          <input
            id="enquiry-email"
            name="email"
            type="email"
            autoComplete="email"
            value={values.email}
            onChange={update('email')}
            maxLength={LIMITS.email}
            aria-invalid={Boolean(errors.email)}
            aria-describedby={describedBy}
            required
          />
        )}
      </FormField>

      <FormField id="enquiry-phone" label="Phone" optional error={errors.phone}>
        {(describedBy) => (
          <input
            id="enquiry-phone"
            name="phone"
            type="tel"
            autoComplete="tel"
            value={values.phone}
            onChange={update('phone')}
            maxLength={LIMITS.phone}
            aria-invalid={Boolean(errors.phone)}
            aria-describedby={describedBy}
          />
        )}
      </FormField>

      <FormField
        id="enquiry-message"
        label="Message"
        optional
        error={errors.message}
        hint={`${values.message.length}/${LIMITS.message}`}
      >
        {(describedBy) => (
          <textarea
            id="enquiry-message"
            name="message"
            rows={4}
            value={values.message}
            onChange={update('message')}
            maxLength={LIMITS.message}
            aria-invalid={Boolean(errors.message)}
            aria-describedby={describedBy}
          />
        )}
      </FormField>

      {formError && (
        <p className="form-error" role="alert">
          {formError}
        </p>
      )}

      <button type="submit" className="button button-primary button-block" disabled={submitting}>
        {submitting ? 'Sending…' : 'Send enquiry'}
      </button>
    </form>
  );
}
