import { API_UPLOAD_URL } from '../../../shared/api/api';

export async function getUploadUrl(token: string, file: File, title: string, description: string) {
  const res = await fetch(`${API_UPLOAD_URL}/upload-url`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
      mimeType: file.type || 'image/jpeg',
      fileName: title,
      description: description,
    }),
  });

  if (!res.ok) throw new Error('Не удалось получить ссылку');
  return res.json();
}

export async function markUploaded(token: string, photoId: string, url: string, size: number) {
  await fetch(`${API_UPLOAD_URL}/mark-uploaded`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({ photo_id: photoId, url, size }),
  });
}

export async function checkPhotoStatus(token: string, id: string) {
  const res = await fetch(`${API_UPLOAD_URL}/${id}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.json();
}

export async function updateTags(token: string, tags: string[]): Promise<void> {
  await fetch(`${API_UPLOAD_URL}/tags`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
    body: JSON.stringify({ tags }),
  });
}
