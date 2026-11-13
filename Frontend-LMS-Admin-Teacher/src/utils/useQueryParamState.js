import { useCallback } from 'react';
import { useSearchParams } from 'react-router-dom';

/**
 * Menyelaraskan state filter halaman dengan parameter query URL
 * agar navigasi sub-menu sidebar dan kontrol filter halaman selalu sinkron.
 */
export function useQueryParamState(key, defaultValue = 'all') {
  const [searchParams, setSearchParams] = useSearchParams();
  const value = searchParams.get(key) || defaultValue;

  const setValue = useCallback(
    (nextValue) => {
      setSearchParams(
        (prev) => {
          const next = new URLSearchParams(prev);
          if (!nextValue || nextValue === defaultValue) {
            next.delete(key);
          } else {
            next.set(key, nextValue);
          }
          return next;
        },
        { replace: true }
      );
    },
    [key, defaultValue, setSearchParams]
  );

  return [value, setValue];
}

