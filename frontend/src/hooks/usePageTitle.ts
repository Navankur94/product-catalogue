import { useEffect } from 'react';

const SITE_NAME = 'Northstar';

export function usePageTitle(title?: string): void {
  useEffect(() => {
    document.title = title ? `${title} · ${SITE_NAME}` : `${SITE_NAME} — Product Catalogue`;
  }, [title]);
}
