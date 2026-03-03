import type { Photo } from '../../../entities';
import { Button } from '../../../shared';
import '../../../app/styles/PhotoCard.css';

type PhotoCardProps = {
  photo: Photo;
  onClick?: () => void;
  onDelete?: (photo: Photo) => void;
  onRename?: (photo: Photo) => void;
  onMove?: (photo: Photo) => void;
  onDownload?: (photo: Photo) => void;
};

export function PhotoCard({
  photo,
  onClick,
  onDelete,
  onRename,
  onMove,
  onDownload,
}: PhotoCardProps) {
  return (
    <div className="photo-card" onClick={onClick}>
      <img src={photo.url} alt={photo.title} />

      <div className="photo-card__info">
        <p>{photo.title}</p>
        <p>Размер: {photo.size} байт</p>
        <p>Формат: {photo.format}</p>
        <p>Дата добавления: {photo.createdAt}</p>
      </div>

      <div className="photo-card__actions">
        {onDelete && (
          <Button
            onClick={(e: React.MouseEvent) => {
              e.stopPropagation();
              onDelete?.(photo);
            }}
          >
            Удалить
          </Button>
        )}
        {onRename && (
          <Button
            onClick={(e: React.MouseEvent) => {
              e.stopPropagation();
              onRename?.(photo);
            }}
          >
            Переименовать
          </Button>
        )}
        {onMove && (
          <Button
            onClick={(e: React.MouseEvent) => {
              e.stopPropagation();
              onMove?.(photo);
            }}
          >
            Переместить
          </Button>
        )}
        <Button
          onClick={(e: React.MouseEvent) => {
            e.stopPropagation();
            onDownload?.(photo);
          }}
        >
          Скачать
        </Button>
      </div>
    </div>
  );
}
