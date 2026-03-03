import { useEffect, useState, useCallback } from 'react';
import { getFolders, createFolder, deleteFolder, renameFolder } from '../../entities';
import type { Folder } from '../../entities';

export function useFolders(token: string) {
  const [folders, setFolders] = useState<Folder[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  const fetchFolders = useCallback(() => {
    if (!token) return;

    setLoading(true);
    getFolders(token)
      .then((data) => {
        console.log('folders from API:', data);
        setFolders(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error(err);
        setError('Ошибка при загрузке папок');
        setLoading(false);
      });
  }, [token]);

  useEffect(() => {
    fetchFolders();
  }, [fetchFolders]);

  const createFolderHook = async (name: string) => {
    if (!token) return;
    try {
      const newFolder = await createFolder(token, name);
      setFolders((prev) => [...prev, newFolder]);
    } catch (err) {
      console.error(err);
      setError('Ошибка при создании папки');
    }
  };

  const deleteFolderHook = async (folderId: string) => {
    if (!token) return;
    try {
      await deleteFolder(token, folderId);
      setFolders((prev) => prev.filter((folder) => folder.id !== folderId));
    } catch (err) {
      console.error(err);
      setError('Ошибка при удалении папки');
    }
  };

  const renameFolderHook = async (folderId: string, newName: string) => {
    if (!token) return;
    try {
      await renameFolder(token, folderId, newName);
      setFolders((prev) =>
        prev.map((folder) => (folder.id === folderId ? { ...folder, name: newName } : folder))
      );
    } catch (err) {
      console.error(err);
      setError('Ошибка при переименовании папки');
    }
  };

  return { folders, loading, error, createFolderHook, deleteFolderHook, renameFolderHook };
}
