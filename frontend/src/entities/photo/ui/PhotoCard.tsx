import type { Photo } from '../model/types';
import '../../../app/styles/PhotoCard.css';
import type { Folder } from '../../folder/model/types';

type PhotoCardProps = {
  photo: Photo;
  folders: Folder[];
  onClick: () => void;
  onDelete: (photo: Photo) => void;
  onRename: (photo: Photo) => void;
  onMove: (photo: Photo) => void;
};

export function PhotoCard({ photo, onClick, onDelete, onRename, onMove }: PhotoCardProps) {
  return (
    <div className="photo-card" onClick={onClick}>
      <img src={photo.url} alt={photo.title} />

      <div className="photo-card__info">
        <p>{photo.title}</p>
        <p>Размер: {photo.size} байт</p>
        <p>Формат: {photo.format}</p>
        <p>Описание: {photo.description}</p>
        <p>Дата добавления: {photo.createdAt}</p>
        <p>Папка: {photo.folder ?? 'Нет'}</p>
      </div>

      <div className="photo-card__actions">
        <button
          onClick={(e) => {
            e.stopPropagation();
            onDelete(photo);
          }}
        >
          Удалить
        </button>

        <button
          onClick={(e) => {
            e.stopPropagation();
            onRename(photo);
          }}
        >
          Переименовать
        </button>

        <button
          onClick={(e) => {
            e.stopPropagation();
            onMove(photo);
          }}
        >
          Переместить
        </button>
      </div>
    </div>
  );
}
