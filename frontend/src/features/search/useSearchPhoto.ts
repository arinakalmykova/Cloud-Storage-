import { useState } from 'react';
import type { Photo } from '../../entities';
import { searchPhotos } from '../../entities';
import { useAppSelector } from '../../app';

interface SearchFilters {
  title?: string;
  tags?: string[];
  color?: string;
  description?: string;
  dateFrom?: string;
  dateTo?: string;
  format?: string;
}

export function useSearchPhotos() {
  const [photos, setPhotos] = useState<Photo[]>([]);
  const { token  } = useAppSelector((state) => state.auth);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const performSearch = async (params: { query?: string; filters?: SearchFilters }) => {
    if (!token) return;

    try {
      setLoading(true);
      setError(null);

      const queryParams = new URLSearchParams();

      if (params.query) queryParams.append('query', params.query);

      if (params.filters) {
        Object.entries(params.filters).forEach(([key, value]) => {
          if (value && (Array.isArray(value) ? value.length > 0 : true)) {
            queryParams.append(
              key,
              Array.isArray(value) ? value.join(',') : value.toString()
            );
          }
        });
      }

      const data = await searchPhotos(token, queryParams.toString());

      setPhotos(data);

    } catch (err: any) {
      console.error('Ошибка поиска фото', err);
      setError(err.message || 'Ошибка поиска');
    } finally {
      setLoading(false);
    }
  };

  return { photos, searchPhotos: performSearch, loading, error };
}
