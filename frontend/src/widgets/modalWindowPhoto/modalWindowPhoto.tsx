import styles from '../../app/styles/ModalWindowPhoto.module.css';
import { useState } from 'react';

export function ModalWindowPhoto({
  selectedPhoto,
  onClose,
}: {
  selectedPhoto: any;
  onClose: () => void;
}) {
  const [orientation, setOrientation] = useState<'horizontal' | 'vertical' | null>(null);

  if (!selectedPhoto) return null;

  return (
    <div className={styles.photoModal} onClick={onClose}>
      <div
        className={`${styles.photoModalContent} ${orientation === 'vertical' ? styles.vertical : styles.horizontal}`}
        onClick={(e) => e.stopPropagation()}
      >
        <img
          src={selectedPhoto.url}
          onLoad={(e) => {
            const img = e.target as HTMLImageElement;
            setOrientation(img.naturalWidth > img.naturalHeight ? 'horizontal' : 'vertical');
          }}
          alt={selectedPhoto.title || 'Photo'}
        />
        <div className={styles.photoModalInfo}>
          <h3>Название: {selectedPhoto.title || selectedPhoto.fileName}</h3>
          <p>Описание: {selectedPhoto.description || 'Нет описания'}</p>
          <p>Формат: {selectedPhoto.format || 'Не указан'}</p>
          <p>Размер: {selectedPhoto.size ? `${selectedPhoto.size} байт` : 'Не указан'}</p>
          <p>Дата добавления: {selectedPhoto.createdAt || 'Не указана'}</p>
          <p>Папка: {selectedPhoto.folder || 'Нет'}</p>
          <p>Теги: {selectedPhoto.tags?.length ? selectedPhoto.tags.join(', ') : 'Нет'}</p>
        </div>
      </div>
    </div>
  );
}
