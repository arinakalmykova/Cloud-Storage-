import { API_UPLOAD_URL} from '../../../shared';
import {apiClient} from '../../../shared';

export async function getUploadUrl(token: string, file: File, title: string, description: string) {
  return apiClient(`${API_UPLOAD_URL}/upload-url`, token, { method: 'POST', body: JSON.stringify({
      file: file,
      mimeType: file.type || 'image/jpeg',
      fileName: title,
      description: description,
    }),
  });
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
  return apiClient(`${API_UPLOAD_URL}/mark-uploaded`, token, { method: 'POST', body: JSON.stringify({ photo_id: photoId, url, size, quality, format, folder_id: folderId }) });
}

export async function checkPhotoStatus(token: string, id: string) {
  return apiClient(`${API_UPLOAD_URL}/${id}`, token);
}

export async function updateTags(token: string, photoId: string, tags: string[]) {
  return apiClient(`${API_UPLOAD_URL}/${photoId}/tags`, token, { method: 'POST', body: JSON.stringify({ tags }) });
}

export async function recommendML(token: string, file: File) {
  const formData = new FormData();
  formData.append("file", file);

  const fileName = file.name.toLowerCase();
  const supportedFormats = [".jpg", ".jpeg", ".png", ".webp", ".avif"];

  const isValidFormat = supportedFormats.some((format) =>
    fileName.endsWith(format)
  );

  if (!isValidFormat) {
    throw new Error(
      `Неподдерживаемый формат файла: ${file.name}. Поддерживаются: ${supportedFormats.join(", ")}`
    );
  }

  try {
    const res = await fetch(`${API_UPLOAD_URL}/recommend`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
      },
      body: formData,
      credentials: "include",
    });

    if (res.status === 302) {
      const redirectUrl = res.headers.get("Location");
      throw new Error(`Редирект на: ${redirectUrl}`);
    }

    if (!res.ok) {
      const errorText = await res.text();
      throw new Error(`Ошибка ML-рекомендации: ${res.status} - ${errorText}`);
    }

    return res.json();
  } catch (error) {
    console.error("Ошибка запроса:", error);
    throw error;
  }
}

export async function deletePhoto(token: string, photoId: string) {
  return apiClient(`${API_UPLOAD_URL}/${photoId}`, token, { method: 'DELETE' });
}

export async function renamePhoto(token: string, photoId: string, newTitle: string) {
  return apiClient(`${API_UPLOAD_URL}/${photoId}`, token, { method: 'PUT', body: JSON.stringify({ title: newTitle }) });
}

export async function recentAddPhotos(token: string) {
  return apiClient(`${API_UPLOAD_URL}/recent`, token);
}

export async function searchPhotos(token: string, params: string) {
  const urlParams = new URLSearchParams(params);
  return apiClient(`${API_UPLOAD_URL}/search?${urlParams.toString()}`, token);
}

export async function getFilters(token: string) {
  return apiClient(`${API_UPLOAD_URL}/filters`, token);
}
