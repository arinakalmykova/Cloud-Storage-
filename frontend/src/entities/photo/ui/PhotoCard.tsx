import type { Photo } from '../../../entities';
import { Button } from '../../../shared';
import styles from '../../../app/styles/PhotoCard.module.css';

type PhotoCardProps = {
  photo: Photo;
  onClick?: () => void;
  onDelete?: (photo: Photo) => Promise<{ success: boolean; message?: string }>;
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
    <div className={styles.photoCard} onClick={onClick}>
      <img src={photo.url} alt={photo.title} />

      <div className={styles.photoCardInfo}>
        <p>{photo.title}</p>
        <p>Размер: {photo.size} байт</p>
        <p>Формат: {photo.format}</p>
        <p>Дата добавления: {photo.createdAt}</p>
      </div>

      <div className={styles.photoCardActions}>
        {onDelete && (
          <Button
          onClick={async(e) => {
            e.stopPropagation();
            try {
              const result = await onDelete(photo); 
              if (result.success) {
                alert('Фото успешно удалено');
                window.location.reload();
              } else {
                alert(result.message || 'Ошибка при удалении фото');
              }
            } catch (error) {
              alert('Произошла ошибка при удалении фото');
              console.error(error);
            }
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
