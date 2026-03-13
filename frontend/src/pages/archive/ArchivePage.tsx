import { useState, useReducer } from 'react';
import { motion } from 'framer-motion';
import { useFolders, useFolderPhoto } from '../../features';
import { useAppSelector } from '../../app';
import { PhotoCard, FolderCard } from '../../entities';
import type { Photo } from '../../entities';
import { ModalWindow, ModalWindowPhoto } from '../../widgets';
import { Button, Input, Loader, Error } from '../../shared';
import styles from '../../app/styles/Archive.module.css';

type ModalAction =
  | { type: 'OPEN_RENAME_PHOTO'; photo: Photo }
  | { type: 'OPEN_MOVE_PHOTO'; photo: Photo }
  | { type: 'OPEN_CREATE_FOLDER' }
  | { type: 'OPEN_RENAME_FOLDER'; folderId: string; folderName: string }
  | { type: 'CLOSE_MODAL' }
  | { type: 'SET_MODAL_FIELD'; field: string; value: any };

type ModalState =
  | { type: 'renamePhoto'; photo: Photo; newTitle: string }
  | { type: 'movePhoto'; photo: Photo; targetFolder: string | null }
  | { type: 'createFolder'; newFolderName: string }
  | { type: 'renameFolder'; folderId: string; folderName: string }
  | null;

function modalReducer(state: ModalState, action: ModalAction): ModalState {
  switch (action.type) {
    case 'OPEN_RENAME_PHOTO':
      return { type: 'renamePhoto', photo: action.photo, newTitle: action.photo.title };
    case 'OPEN_MOVE_PHOTO':
      return { type: 'movePhoto', photo: action.photo, targetFolder: null };
    case 'OPEN_CREATE_FOLDER':
      return { type: 'createFolder', newFolderName: '' };
    case 'OPEN_RENAME_FOLDER':
      return { type: 'renameFolder', folderId: action.folderId, folderName: action.folderName };
    case 'CLOSE_MODAL':
      return null;
    case 'SET_MODAL_FIELD':
      if (!state) return state;
      return { ...state, [action.field]: action.value };
    default:
      return state;
  }
}

export function ArchivePage() {
  const token = useAppSelector((state) => state.auth.token);
  const [modal, dispatchModal] = useReducer(modalReducer, null);
  const [selectedPhoto, setSelectedPhoto] = useState<Photo | null>(null);
  const [currentFolder, setCurrentFolder] = useState<string | null>(null);

  const { folders, loading, error, createFolderHook, deleteFolderHook, renameFolderHook } =
    useFolders(token ?? '');

  const {
    recentPhotos,
    deletePhotoHook,
    renamePhotoHook,
    movePhotoToFolderHook,
    downloadPhotoHook,
  } = useFolderPhoto(token ?? '');

  if (loading) return <Loader />;
  if (error) return <Error error={error} />;

  return (
    <>
      <div className={styles.archivePage}>
        <div className={styles.archivePageContent}>
          <div className={styles.archivePageToolbar}>
            <motion.div
              className={styles.archivePageWelcome}
              initial={{ opacity: 0, y: -20 }}
              animate={{ opacity: 1, y: 0 }}
            >
              <h1>Архив фото</h1>
            </motion.div>
          </div>

          <div className={styles.archiveSection}>
            <div className={styles.archiveSectionHeader}>
              <h2>Папки</h2>
              <button onClick={() => dispatchModal({ type: 'OPEN_CREATE_FOLDER' })}>
                Новая папка
              </button>
            </div>

            {!currentFolder && (
              <>
                <div className={styles.archiveFolderGrid}>
                  {folders.map((folder) => (
                    <FolderCard
                      key={folder.id}
                      folder={folder}
                      onClick={() => setCurrentFolder(folder.id)}
                      onDelete={async (folder) => {
                        const result = await deleteFolderHook(folder.id);
                        return result ?? { success: false, message: 'Ошибка удаления' };
                      }}
                      onRename={async (folder) => {
                        dispatchModal({
                          type: 'OPEN_RENAME_FOLDER',
                          folderId: folder.id,
                          folderName: folder.name,
                        });
                      }}
                    />
                  ))}
                </div>

                <div className={styles.archiveRecentPhotos}>
                  <h2>Недавно добавленные фото</h2>
                  <div className={styles.recentPhotosGrid}>
                    {recentPhotos.map((photo) => (
                      <PhotoCard
                        key={photo.id}
                        photo={photo}
                        onClick={() => setSelectedPhoto(photo)}
                        onDelete={async () => {
                          const result = await deletePhotoHook(photo.id);
                          return result ?? { success: false, message: 'Ошибка удаления' };
                        }}
                        onRename={() => dispatchModal({ type: 'OPEN_RENAME_PHOTO', photo })}
                        onMove={() => dispatchModal({ type: 'OPEN_MOVE_PHOTO', photo })}
                        onDownload={() => downloadPhotoHook(photo.url, photo.title)}
                      />
                    ))}
                  </div>
                </div>
              </>
            )}

            {currentFolder && (
              <div className={styles.archiveGrid}>
                <div className={styles.folderPageHeader}>
                  <h3>{folders.find((f) => f.id === currentFolder)?.name}</h3>
                  <p>{folders.find((f) => f.id === currentFolder)?.photos.length} фото</p>
                </div>
                <button className={styles.backButton} onClick={() => setCurrentFolder(null)}>
                  Назад к папкам
                </button>
                {folders
                  .find((f) => f.id === currentFolder)
                  ?.photos.map((photo) => (
                    <div className={styles.photoGrid}>
                      <PhotoCard
                        key={photo.id}
                        photo={photo}
                        onClick={() => setSelectedPhoto(photo)}
                        onDelete={async () => {
                          const result = await deletePhotoHook(photo.id);
                          return result ?? { success: false, message: 'Ошибка удаления' };
                        }}
                        onRename={() => dispatchModal({ type: 'OPEN_RENAME_PHOTO', photo })}
                        onMove={() => dispatchModal({ type: 'OPEN_MOVE_PHOTO', photo })}
                        onDownload={() => downloadPhotoHook(photo.url, photo.title)}
                      />
                    </div>
                  ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {selectedPhoto && (
        <ModalWindowPhoto selectedPhoto={selectedPhoto} onClose={() => setSelectedPhoto(null)} />
      )}

      {modal?.type === 'renamePhoto' && (
        <ModalWindow onClose={() => dispatchModal({ type: 'CLOSE_MODAL' })}>
          <h3>Переименовать фото</h3>
          <Input
            value={modal.newTitle}
            onChange={(e) =>
              dispatchModal({ type: 'SET_MODAL_FIELD', field: 'newTitle', value: e.target.value })
            }
          />
          <Button
            className={styles.modalButton}
            onClick={async () => {
              try {
                const result = await renamePhotoHook(modal.photo.id, modal.newTitle);
                if (result?.success) {
                  alert('Фото успешно переименовано');
                  dispatchModal({ type: 'CLOSE_MODAL' });
                } else {
                  alert(result?.message || 'Ошибка при переименовании фото');
                }
              } catch (e) {
                alert('Произошла ошибка при переименовании фото');
                console.error(e);
              }
            }}
          >
            Сохранить
          </Button>
        </ModalWindow>
      )}

      {modal?.type === 'movePhoto' && (
        <ModalWindow onClose={() => dispatchModal({ type: 'CLOSE_MODAL' })}>
          <h3>Переместить фото</h3>
          <select
            value={modal.targetFolder ?? ''}
            onChange={(e) =>
              dispatchModal({
                type: 'SET_MODAL_FIELD',
                field: 'targetFolder',
                value: e.target.value,
              })
            }
          >
            <option value="">Без папки</option>
            {folders.map((folder) => (
              <option key={folder.id} value={folder.id}>
                {folder.name}
              </option>
            ))}
          </select>
          <Button
            className={styles.modalButton}
            onClick={async () => {
              try {
                const result = await movePhotoToFolderHook(modal.photo.id, modal.targetFolder);
                if (result?.success) {
                  alert('Фото успешно перемещено');
                  dispatchModal({ type: 'CLOSE_MODAL' });
                } else {
                  alert(result?.message || 'Ошибка при перемещении фото');
                }
              } catch (e) {
                alert('Ошибка при перемещении фото');
                console.error(e);
              }
            }}
          >
            Переместить
          </Button>
        </ModalWindow>
      )}

      {modal?.type === 'createFolder' && (
        <ModalWindow onClose={() => dispatchModal({ type: 'CLOSE_MODAL' })}>
          <h3>Создать новую папку</h3>
          <Input
            value={modal.newFolderName}
            onChange={(e) =>
              dispatchModal({
                type: 'SET_MODAL_FIELD',
                field: 'newFolderName',
                value: e.target.value,
              })
            }
          />
          <Button
            className={styles.modalButton}
            onClick={async () => {
              try {
                const result = await createFolderHook(modal.newFolderName);
                if (result?.success) {
                  alert('Папка успешно создана');
                  dispatchModal({ type: 'CLOSE_MODAL' });
                } else {
                  alert(result?.message || 'Ошибка при создании папки');
                }
              } catch (e) {
                alert('Ошибка при создании папки');
                console.error(e);
              }
            }}
          >
            Создать
          </Button>
        </ModalWindow>
      )}

      {modal?.type === 'renameFolder' && (
        <ModalWindow onClose={() => dispatchModal({ type: 'CLOSE_MODAL' })}>
          <h3>Переименовать папку</h3>
          <Input
            value={modal.folderName}
            onChange={(e) =>
              dispatchModal({
                type: 'SET_MODAL_FIELD',
                field: 'folderName',
                value: e.target.value,
              })
            }
          />
          <Button
            className={styles.modalButton}
            onClick={async () => {
              try {
                const result = await renameFolderHook(modal.folderId, modal.folderName);
                if (result?.success) {
                  alert('Папка успешно переименована');
                  dispatchModal({ type: 'CLOSE_MODAL' });
                } else {
                  alert(result?.message || 'Ошибка при переименовании папки');
                }
              } catch (e) {
                alert('Ошибка при переименовании папки');
                console.error(e);
              }
            }}
          >
            Сохранить
          </Button>
        </ModalWindow>
      )}
    </>
  );
}
