const priceFormatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

/** Prices from the catalogue are treated as USD (see DECISIONS.md). */
export function formatPrice(value: number): string {
  return priceFormatter.format(value);
}

/** "men's clothing" → "Men's Clothing" (display only; URLs keep the raw value). */
export function categoryLabel(category: string): string {
  return category.replace(/(^|\s)\p{L}/gu, (match) => match.toUpperCase());
}

export function categoryPath(category: string): string {
  return `/categories/${encodeURIComponent(category)}`;
}

export function productPath(id: number): string {
  return `/products/${id}`;
}
