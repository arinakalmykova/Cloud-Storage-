import { useState } from 'react';
import { motion } from 'framer-motion';
import { useFolders, useFolderPhoto } from '../../features';
import { useAppSelector } from '../../app';
import { PhotoCard, FolderCard } from '../../entities';
import type { Photo } from '../../entities';
import { ModalWindow, ModalWindowPhoto } from '../../widgets';
import { Button, Input } from '../../shared';
import '../../app/styles/Archive.css';

export function ArchivePage() {
  const token = useAppSelector((state) => state.auth.token);

  const { folders, loading, error, createFolderHook, deleteFolderHook, renameFolderHook } =
    useFolders(token ?? '');

  const {
    recentPhotos,
    deletePhotoHook,
    renamePhotoHook,
    movePhotoToFolderHook,
    downloadPhotoHook,
  } = useFolderPhoto(token ?? '');

  const [currentFolder, setCurrentFolder] = useState<string | null>(null);
  const [selectedPhoto, setSelectedPhoto] = useState<Photo | null>(null);

  const [renamePhoto, setRenamePhoto] = useState<Photo | null>(null);
  const [movePhoto, setMovePhoto] = useState<Photo | null>(null);

  const [newTitle, setNewTitle] = useState('');
  const [targetFolder, setTargetFolder] = useState<string | null>(null);

  const [createFolderOpen, setCreateFolderOpen] = useState(false);
  const [renameFolderData, setRenameFolderData] = useState<{
    id: string;
    name: string;
  } | null>(null);

  const [newFolderName, setNewFolderName] = useState('');

  if (loading) return <p className="loading-message">Загрузка папок...</p>;
  if (error) return <p className="error-message">{error}</p>;

  return (
    <>
      <div className="archive-page">
        <div className="archive-page__content">
          <div className="archive-page__topbar">
            <motion.div
              className="archive-page__welcome"
              initial={{ opacity: 0, y: -20 }}
              animate={{ opacity: 1, y: 0 }}
            >
              <h1>Архив фото</h1>
            </motion.div>
          </div>

          <div className="archive-section">
            <div className="archive-section__header">
              <h2>Папки</h2>
              <button onClick={() => setCreateFolderOpen(true)}>Новая папка</button>
            </div>

            {!currentFolder && (
              <div>
                <div className="archive-grid">
                  {folders.map((folder) => (
                    <FolderCard
                      key={folder.id}
                      folder={folder}
                      onClick={() => setCurrentFolder(folder.id)}
                      onDelete={(folder) => deleteFolderHook(folder.id)}
                      onRename={(folder) =>
                        setRenameFolderData({ id: folder.id, name: folder.name })
                      }
                    />
                  ))}
                </div>
                <div className="archive-recent-photos">
                  <h2>Недавно добавленные фото</h2>

                  <div className="recent-photos-grid">
                    {recentPhotos.map((photo) => (
                      <PhotoCard
                        key={photo.id}
                        photo={photo}
                        onClick={() => setSelectedPhoto(photo)}
                        onDelete={(photo) => deletePhotoHook(photo.id)}
                        onRename={(photo) => {
                          setRenamePhoto(photo);
                          setNewTitle(photo.title);
                        }}
                        onMove={(photo) => {
                          setMovePhoto(photo);
                        }}
                        onDownload={(photo) => {
                          downloadPhotoHook(photo.url, photo.title);
                        }}
                      />
                    ))}
                  </div>
                </div>
              </div>
            )}
            {currentFolder && (
              <div className="archive-grid">
                <div className="folder-page-header">
                  <h3>{folders.find((f) => f.id === currentFolder)?.name}</h3>
                  <p>{folders.find((f) => f.id === currentFolder)?.photos.length} фото</p>
                </div>
                <button className="back-button" onClick={() => setCurrentFolder(null)}>
                  Назад к папкам
                </button>
                {folders
                  .find((f) => f.id === currentFolder)
                  ?.photos.map((photo) => (
                    <PhotoCard
                      key={photo.id}
                      photo={photo}
                      onClick={() => setSelectedPhoto(photo)}
                      onDelete={(photo) => deletePhotoHook(photo.id)}
                      onRename={(photo) => {
                        setRenamePhoto(photo);
                        setNewTitle(photo.title);
                      }}
                      onMove={(photo) => {
                        setMovePhoto(photo);
                      }}
                      onDownload={(photo) => {
                        downloadPhotoHook(photo.url, photo.title);
                      }}
                    />
                  ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {selectedPhoto && (
        <ModalWindowPhoto selectedPhoto={selectedPhoto} onClose={() => setSelectedPhoto(null)} />
      )}

      {renamePhoto && (
        <ModalWindow onClose={() => setRenamePhoto(null)}>
          <h3>Переименовать фото</h3>
          <Input label="Название:" value={newTitle} onChange={(e) => setNewTitle(e.target.value)} />
          <Button
            className="modal-button"
            onClick={() => {
              renamePhotoHook(renamePhoto.id, newTitle);
              setRenamePhoto(null);
            }}
          >
            Сохранить
          </Button>
        </ModalWindow>
      )}

      {movePhoto && (
        <ModalWindow onClose={() => setMovePhoto(null)}>
          <h3>Переместить фото</h3>
          <select value={targetFolder ?? ''} onChange={(e) => setTargetFolder(e.target.value)}>
            <option value="">Без папки</option>
            {folders.map((folder) => (
              <option key={folder.id} value={folder.id}>
                {folder.name}
              </option>
            ))}
          </select>
          <Button
            className="modal-button"
            onClick={() => {
              movePhotoToFolderHook(movePhoto.id, targetFolder);
              setMovePhoto(null);
            }}
          >
            Переместить
          </Button>
        </ModalWindow>
      )}

      {createFolderOpen && (
        <ModalWindow onClose={() => setCreateFolderOpen(false)}>
          <h3>Создать новую папку</h3>

          <Input
            label="Название:"
            value={newFolderName}
            onChange={(e) => setNewFolderName(e.target.value)}
          />

          <Button
            className="modal-button"
            onClick={() => {
              createFolderHook(newFolderName);
              setNewFolderName('');
              setCreateFolderOpen(false);
            }}
          >
            Создать
          </Button>
        </ModalWindow>
      )}

      {renameFolderData && (
        <ModalWindow onClose={() => setRenameFolderData(null)}>
          <h3>Переименовать папку</h3>

          <Input
            label="Название:"
            value={renameFolderData.name}
            onChange={(e) =>
              setRenameFolderData({
                ...renameFolderData,
                name: e.target.value,
              })
            }
          />

          <Button
            className="modal-button"
            onClick={() => {
              renameFolderHook(renameFolderData.id, renameFolderData.name);
              setRenameFolderData(null);
            }}
          >
            Сохранить
          </Button>
        </ModalWindow>
      )}
    </>
  );
}
