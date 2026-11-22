import type {
  CategoryListing,
  DataResponse,
  EnquiryPayload,
  FieldErrors,
  MessageResponse,
  Product,
} from '../types/catalogue';

/**
 * Centralised client for the Laravel API. The browser never talks to the
 * upstream product provider; every request goes through these functions.
 */
const BASE_URL = (import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export class ApiError extends Error {
  readonly status: number;
  readonly fieldErrors: FieldErrors;

  constructor(message: string, status: number, fieldErrors: FieldErrors = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.fieldErrors = fieldErrors;
  }

  get isNotFound(): boolean {
    return this.status === 404;
  }
}

interface RequestOptions {
  method?: 'GET' | 'POST';
  body?: unknown;
  signal?: AbortSignal;
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

function readFieldErrors(body: unknown): FieldErrors {
  if (!isRecord(body) || !isRecord(body.errors)) return {};
  const result: FieldErrors = {};
  for (const [field, messages] of Object.entries(body.errors)) {
    if (Array.isArray(messages) && typeof messages[0] === 'string') result[field] = messages[0];
  }
  return result;
}

function fallbackMessage(status: number): string {
  if (status === 429) return 'Too many requests. Please wait a minute and try again.';
  if (status === 404) return 'We couldn’t find what you were looking for.';
  if (status >= 500) return 'The catalogue is temporarily unavailable. Please try again shortly.';
  return 'Something went wrong. Please try again.';
}

async function request<T>(path: string, { method = 'GET', body, signal }: RequestOptions = {}): Promise<T> {
  let response: Response;
  try {
    response = await fetch(`${BASE_URL}${path}`, {
      method,
      signal,
      headers: {
        Accept: 'application/json',
        ...(body === undefined ? {} : { 'Content-Type': 'application/json' }),
      },
      body: body === undefined ? undefined : JSON.stringify(body),
    });
  } catch (error) {
    if (error instanceof DOMException && error.name === 'AbortError') throw error;
    throw new ApiError('We couldn’t reach the catalogue. Check your connection and try again.', 0);
  }

  const payload: unknown = await response.json().catch(() => null);

  if (!response.ok) {
    const message =
      response.status !== 429 && isRecord(payload) && typeof payload.message === 'string'
        ? payload.message
        : fallbackMessage(response.status);
    throw new ApiError(message, response.status, readFieldErrors(payload));
  }

  return payload as T;
}

const unwrap = <T,>(response: DataResponse<T>): T => response.data;

export const api = {
  featuredProducts: (signal?: AbortSignal): Promise<Product[]> =>
    request<DataResponse<Product[]>>('/products/featured', { signal }).then(unwrap),

  searchProducts: (query: string, signal?: AbortSignal): Promise<Product[]> =>
    request<DataResponse<Product[]>>(`/products?q=${encodeURIComponent(query)}`, { signal }).then(unwrap),

  product: (id: number, signal?: AbortSignal): Promise<Product> =>
    request<DataResponse<Product>>(`/products/${id}`, { signal }).then(unwrap),

  categories: (signal?: AbortSignal): Promise<string[]> =>
    request<DataResponse<string[]>>('/categories', { signal }).then(unwrap),

  category: (name: string, signal?: AbortSignal): Promise<CategoryListing> =>
    request<DataResponse<CategoryListing>>(`/categories/${encodeURIComponent(name)}/products`, { signal }).then(unwrap),

  sendEnquiry: (payload: EnquiryPayload): Promise<MessageResponse> =>
    request<MessageResponse>('/enquiries', { method: 'POST', body: payload }),
};
