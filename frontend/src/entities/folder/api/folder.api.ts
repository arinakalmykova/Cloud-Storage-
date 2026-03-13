import { API_FOLDER_URL } from '../../../shared';
import { apiClient } from '../../../shared';

export async function getFolders(token: string) {
  return apiClient(`${API_FOLDER_URL}`, token);
}

export async function createFolder(token: string, name: string) {
  return apiClient (`${API_FOLDER_URL}/`, token, { method: 'POST', body: JSON.stringify({ name }) });
}

export async function deleteFolder(token: string, folderId: string) {
  return apiClient(`${API_FOLDER_URL}/${folderId}`, token, { method: 'DELETE' });
}

export async function renameFolder(token: string, folderId: string, newName: string) {
  return apiClient(`${API_FOLDER_URL}/${folderId}`, token, { method: 'PUT', body: JSON.stringify({ name: newName }) });
}

export async function movePhotoToFolder(token: string, photoId: string, folderId: string | null) {
  return apiClient(`${API_FOLDER_URL}/move-photo`, token, { method: 'POST', body: JSON.stringify({ photo_id: photoId, folder_id: folderId }) });
}
