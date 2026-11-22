import { useCallback, useEffect, useState, type DependencyList } from 'react';
import { ApiError } from '../lib/api';

export type ApiState<T> =
  | { status: 'loading' }
  | { status: 'success'; data: T }
  | { status: 'error'; error: ApiError };

/**
 * Runs an API call when `deps` change, cancelling the previous request.
 * Returns a discriminated state plus a `retry` callback for error states.
 */
export function useApi<T>(load: (signal: AbortSignal) => Promise<T>, deps: DependencyList): ApiState<T> & { retry: () => void } {
  const [state, setState] = useState<ApiState<T>>({ status: 'loading' });
  const [attempt, setAttempt] = useState(0);

  useEffect(() => {
    const controller = new AbortController();
    setState({ status: 'loading' });

    load(controller.signal)
      .then((data) => {
        if (!controller.signal.aborted) setState({ status: 'success', data });
      })
      .catch((error: unknown) => {
        if (controller.signal.aborted) return;
        setState({
          status: 'error',
          error: error instanceof ApiError ? error : new ApiError('Something went wrong. Please try again.', 0),
        });
      });

    return () => controller.abort();
    // `load` is intentionally excluded: callers pass inline closures and list their real inputs in `deps`.
  }, [...deps, attempt]);

  const retry = useCallback(() => setAttempt((n) => n + 1), []);

  return { ...state, retry };
}
