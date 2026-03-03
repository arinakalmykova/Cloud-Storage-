import { API_UPLOAD_URL } from '../../../shared';

export async function getUploadUrl(token: string, file: File, title: string, description: string) {
  const res = await fetch(`${API_UPLOAD_URL}/upload-url`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
      file: file,
      mimeType: file.type || 'image/jpeg',
      fileName: title,
      description: description,
    }),
  });

  if (!res.ok) throw new Error('Не удалось получить ссылку');
  return res.json();
}

export async function markUploaded(
  token: string,
  photoId: string,
  url: string,
  size: number,
  quality: number,
  format: string,
  folderId: string | null
) {
  await fetch(`${API_UPLOAD_URL}/mark-uploaded`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({ photo_id: photoId, url, size, quality, format, folder_id: folderId }),
  });
}

export async function checkPhotoStatus(token: string, id: string) {
  const res = await fetch(`${API_UPLOAD_URL}/${id}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.json();
}

export async function updateTags(token: string, photoId: string, tags: string[]) {
  const res = await fetch(`${API_UPLOAD_URL}/${photoId}/tags`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
    body: JSON.stringify({ tags }),
  });

  if (!res.ok) {
    throw new Error('Ошибка обновления тегов');
  }
  return res.json();
}

export async function recommendML(token: string, file: File) {
  const formData = new FormData();
  formData.append('file', file);

  const fileName = file.name.toLowerCase();
  const supportedFormats = ['.jpg', '.jpeg', '.png', '.webp', '.avif'];
  const isValidFormat = supportedFormats.some((format) => fileName.endsWith(format));

  if (!isValidFormat) {
    throw new Error(
      `Неподдерживаемый формат файла: ${file.name}. Поддерживаются: ${supportedFormats.join(', ')}`
    );
  }

  try {
    const res = await fetch(`${API_UPLOAD_URL}/recommend`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
      },
      body: formData,
      credentials: 'include',
    });

    if (res.status === 302) {
      const redirectUrl = res.headers.get('Location');
      throw new Error(`Редирект на: ${redirectUrl}`);
    }

    if (!res.ok) {
      const errorText = await res.text();
      throw new Error(`Ошибка ML-рекомендации: ${res.status} - ${errorText}`);
    }

    return res.json();
  } catch (error) {
    console.error('Ошибка запроса:', error);
    throw error;
  }
}

export async function deletePhoto(token: string, photoId: string) {
  const res = await fetch(`${API_UPLOAD_URL}/${photoId}`, {
    method: 'DELETE',
    headers: { Authorization: `Bearer ${token}` },
  });

  if (!res.ok) {
    throw new Error('Ошибка удаления фото');
  }
}

export async function renamePhoto(token: string, photoId: string, newTitle: string) {
  const res = await fetch(`${API_UPLOAD_URL}/${photoId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
    body: JSON.stringify({ title: newTitle }),
  });

  if (!res.ok) {
    throw new Error('Ошибка переименования фото');
  }
}

export async function recentAddPhotos(token: string) {
  const res = await fetch(`${API_UPLOAD_URL}/recent`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return res.json();
}

export async function searchPhotos(token: string, params: string) {
  const urlParams = new URLSearchParams(params);
  const res = await fetch(`${API_UPLOAD_URL}/search?${urlParams.toString()}`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return res.json();
}

export async function getFilters(token: string) {
  const res = await fetch(`${API_UPLOAD_URL}/filters`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return res.json();
}
