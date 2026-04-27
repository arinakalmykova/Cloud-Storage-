import { useEffect, useState, useCallback } from 'react';
import { getFolders, createFolder, deleteFolder, renameFolder } from '../../entities';
import type { Folder } from '../../entities';

export function useFolders(token: string) {
  const [folders, setFolders] = useState<Folder[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  const fetchFolders = useCallback(async () => {
    if (!token) {
      setFolders([]);
      setLoading(false);
      return;
    }

    try {
      setLoading(true);
      setError(null);
      const data = await getFolders(token);
      setFolders(data);
    } catch (err) {
      console.error(err);
      setError('РћС€РёР±РєР° РїСЂРё Р·Р°РіСЂСѓР·РєРµ РїР°РїРѕРє');
    } finally {
      setLoading(false);
    }
  }, [token]);

  useEffect(() => {
    fetchFolders();
  }, [fetchFolders]);

  const createFolderHook = async (name: string) => {
    if (!token) return;

    try {
      setError(null);
      await createFolder(token, name);
      await fetchFolders();

      return {
        success: true,
        message: 'РџР°РїРєР° СѓСЃРїРµС€РЅРѕ СЃРѕР·РґР°РЅР°',
      };
    } catch (err) {
      console.error(err);
      setError('РћС€РёР±РєР° РїСЂРё СЃРѕР·РґР°РЅРёРё РїР°РїРєРё');
    }
  };

  const deleteFolderHook = async (folderId: string) => {
    if (!token) return;

    try {
      setError(null);
      await deleteFolder(token, folderId);
      await fetchFolders();

      return {
        success: true,
        message: 'РџР°РїРєР° СѓСЃРїРµС€РЅРѕ СѓРґР°Р»РµРЅР°',
      };
    } catch (err) {
      console.error(err);
      setError('РћС€РёР±РєР° РїСЂРё СѓРґР°Р»РµРЅРёРё РїР°РїРєРё');
    }
  };

  const renameFolderHook = async (folderId: string, newName: string) => {
    if (!token) return;

    try {
      setError(null);
      await renameFolder(token, folderId, newName);
      await fetchFolders();

      return {
        success: true,
        message: 'РџР°РїРєР° СѓСЃРїРµС€РЅРѕ РїРµСЂРµРёРјРµРЅРѕРІР°РЅР°',
      };
    } catch (err) {
      console.error(err);
      setError('РћС€РёР±РєР° РїСЂРё РїРµСЂРµРёРјРµРЅРѕРІР°РЅРёРё РїР°РїРєРё');
    }
  };

  return {
    folders,
    loading,
    error,
    createFolderHook,
    deleteFolderHook,
    renameFolderHook,
    fetchFolders,
  };
}
