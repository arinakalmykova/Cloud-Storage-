import { useEffect, useState, useCallback } from 'react';
import { movePhotoToFolder, renamePhoto, deletePhoto, recentAddPhotos } from '../../entities';

export function useFolderPhoto(token: string) {
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [recentPhotos, setRecentPhotos] = useState<any[]>([]);

  const fetchFoldersPhoto = useCallback(() => {
    if (!token) return;

    setLoading(true);
    recentAddPhotos(token)
      .then((data) => {
        console.log('recent photos from API:', data);
        setRecentPhotos(data);
      })
      .catch((err) => {
        console.error(err);
        setError('Ошибка при загрузке последних добавленных фото');
      });
  }, [token]);

  useEffect(() => {
    fetchFoldersPhoto();
  }, [fetchFoldersPhoto]);

  const movePhotoToFolderHook = async (photoId: string, folderId: string | null) => {
    if (!token) return;
    try {
      await movePhotoToFolder(token, photoId, folderId);
    } catch (err) {
      console.error(err);
      setError('Ошибка при перемещении фото в папку');
    }
  };

  const renamePhotoHook = async (photoId: string, newTitle: string) => {
    if (!token) return;
    try {
      await renamePhoto(token, photoId, newTitle);
    } catch (err) {
      console.error(err);
      setError('Ошибка при переименовании фото');
    }
  };

  const deletePhotoHook = async (photoId: string) => {
    if (!token) return;
    try {
      await deletePhoto(token, photoId);
    } catch (err) {
      console.error(err);
      setError('Ошибка при удалении фото');
    }
  };

  const downloadPhotoHook = (url: string, title?: string) => {
    const link = document.createElement('a');
    link.href = url;
    link.download = title || 'photo';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return {
    loading,
    error,
    movePhotoToFolderHook,
    recentPhotos,
    renamePhotoHook,
    deletePhotoHook,
    downloadPhotoHook,
  };
}
