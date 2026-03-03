import type { Folder } from '../../../entities';
import { Button } from '../../../shared';
import '../../../app/styles/FolderCard.css';

type Props = {
  folder: Folder;
  onClick: () => void;
  onDelete: (folder: Folder) => void;
  onRename: (folder: Folder) => void;
};

export function FolderCard({ folder, onClick, onDelete, onRename }: Props) {
  return (
    <div className="folder-card" onClick={onClick}>
      <h3>{folder.name}</h3>
      <p>{folder.photos?.length ?? 0} фото</p>

      <div className="folder-card__actions">
        <Button
          onClick={(e) => {
            e.stopPropagation();
            onDelete(folder);
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
