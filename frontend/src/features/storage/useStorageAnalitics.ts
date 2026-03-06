import { useState, useEffect } from 'react';
import { getStorageStats } from '../../entities';
import { useAppSelector } from '../../app';

export interface StorageAnalytics {
  photoCount: number;
  usedBytes: number;
  totalBytes: number;
  percent: number;
  uploadSpeed: string;
  timeline: {
    month: string;
    storage: number;
  }[];
}

export function useStorageAnalytics() {
  const [data, setData] = useState<StorageAnalytics | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const { token } = useAppSelector((state) => state.auth);

  useEffect(() => {
    async function fetchStorage() {
      try {
        setLoading(true);
        const response: StorageAnalytics = await getStorageStats(token);
        setData(response);
      } catch (err: any) {
        console.error('Ошибка получения статистики:', err);
        setError(err.message || 'Не удалось загрузить статистику');
      } finally {
        setLoading(false);
      }
    }

    if (token) fetchStorage();
  }, [token]);

  return { data, loading, error };
}