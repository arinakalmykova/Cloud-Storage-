import { API_STORAGE_URL } from '../../../shared';

export async function getStorageStats(token: string | null) {
  const res = await fetch(`${API_STORAGE_URL}/`, {
    headers: {
      Authorization: token ? `Bearer ${token}` : '',
    },
  });
  if (!res.ok) throw new Error(`Ошибка: ${res.status}`);
  return res.json();
}