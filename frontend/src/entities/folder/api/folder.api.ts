import { API_FOLDER_URL } from '../../../shared';

export async function getFolders(token: string | null) {
  const res = await fetch(`${API_FOLDER_URL}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.json();
}

export async function createFolder(token: string, name: string) {
  const res = await fetch(`${API_FOLDER_URL}/`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ name }),
  });

  if (!res.ok) {
    throw new Error('Не удалось создать папку');
  }

  return res.json();
}

export async function deleteFolder(token: string, folderId: string) {
  const res = await fetch(`${API_FOLDER_URL}/${folderId}`, {
    method: 'DELETE',
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!res.ok) {
    throw new Error('Не удалось удалить папку');
  }
}

export async function renameFolder(token: string, folderId: string, newName: string) {
  const res = await fetch(`${API_FOLDER_URL}/${folderId}`, {
    method: 'PUT',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ name: newName }),
  });

  if (!res.ok) {
    throw new Error('Не удалось переименовать папку');
  }
}

export async function movePhotoToFolder(token: string, photoId: string, folderId: string | null) {
  const res = await fetch(`${API_FOLDER_URL}/move-photo`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ photo_id: photoId, folder_id: folderId }),
  });

  if (!res.ok) {
    throw new Error('Не удалось переместить фото');
  }
}
