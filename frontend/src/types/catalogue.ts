/** Shapes returned by the Laravel catalogue API (see README "API contract"). */

export interface Rating {
  rate: number;
  count: number;
}

export interface Product {
  id: number;
  title: string;
  description: string;
  category: string;
  price: number;
  image: string;
  rating: Rating | null;
}

export interface CategoryListing {
  category: string;
  featured: Product[];
  others: Product[];
}

export interface DataResponse<T> {
  data: T;
}

export interface MessageResponse {
  message: string;
}

export interface EnquiryPayload {
  product_id: number;
  name: string;
  email: string;
  phone?: string;
  message?: string;
}

export type EnquiryField = 'name' | 'email' | 'phone' | 'message';

export type FieldErrors = Partial<Record<string, string>>;
