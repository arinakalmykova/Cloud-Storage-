import { API_STORAGE_URL } from '../../../shared';
import { apiClient } from '../../../shared';

export async function getStorageStats(token: string) {
  return apiClient(`${API_STORAGE_URL}/`, token);
}