import { useState, useEffect } from 'react';
import { getFilters } from '../../entities';
import { useAppSelector } from '../../app';

export function usePhotoFilters() {
  const [tags, setTags] = useState<string[]>([]);
  const [colors, setColors] = useState<string[]>([]);
  const token = useAppSelector((state) => state.auth.token);
  useEffect(() => {
    const fetchFilters = async () => {
      if (!token) return;
      const data = await getFilters(token);
      setTags(data.tags);
      setColors(data.colors);
    };

    fetchFilters();
  }, []);

  return { tags, colors };
}
