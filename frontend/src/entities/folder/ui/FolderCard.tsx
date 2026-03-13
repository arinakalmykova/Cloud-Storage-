import type { Folder } from '../../../entities';
import { Button } from '../../../shared';
import styles from '../../../app/styles/FolderCard.module.css';

type Props = {
  folder: Folder;
  onClick: () => void;
  onDelete: (folder: Folder) => Promise<{ success: boolean; message?: string }>;
  onRename: (folder: Folder) => void;
};

export function FolderCard({ folder, onClick, onDelete, onRename }: Props) {
  console.log(styles);
  return (
    <div className={styles.folderCard} onClick={onClick}>
      <h3>{folder.name}</h3>
      <p>{folder.photos?.length ?? 0} фото</p>

      <div className={styles.actions}>
        <Button
          onClick={async(e) => {
            e.stopPropagation();
            try {
              const result = await onDelete(folder); 
              if (result.success) {
                alert('Папка успешно удалена');
              } else {
                alert(result.message || 'Ошибка при удалении папки');
              }
            } catch (error) {
              alert('Произошла ошибка при удалении папки');
              console.error(error);
            }
          }}
        >
          Удалить
        </Button>

         <Button
          onClick={(e) => {
            e.stopPropagation();
            onRename(folder); 
          }}
        >
          Переименовать
        </Button>
      </div>
    </div>
  );
}
