import { useState } from 'react';
import type { Photo } from '../../entities';
import { searchPhotos } from '../../entities';

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

  const performSearch = async (params: { query?: string; filters?: SearchFilters }) => {
    try {
      const queryParams = new URLSearchParams();

      if (params.query) queryParams.append('query', params.query);
      if (params.filters) {
        Object.entries(params.filters).forEach(([key, value]) => {
          if (value && (Array.isArray(value) ? value.length > 0 : true)) {
            queryParams.append(key, Array.isArray(value) ? value.join(',') : value.toString());
          }
        });
      }

      const token = localStorage.getItem('token') || '';
      const data = await searchPhotos(token, queryParams.toString());
      setPhotos(data);
    } catch (err) {
      console.error('Ошибка поиска фото', err);
    }
  };

  return { photos, searchPhotos: performSearch };
}
