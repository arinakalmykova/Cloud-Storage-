import '../../app/styles/ModalWindowPhoto.css';

export function ModalWindowPhoto({
  selectedPhoto,
  onClose,
}: {
  selectedPhoto: any;
  onClose: () => void;
}) {
  if (!selectedPhoto) return null;
  return (
    <div className="photo-modal" onClick={onClose}>
      <div className="photo-modal__content">
        <img src={selectedPhoto.url} />
        <h3>Название: {selectedPhoto.title}</h3>
        <p>Описание: {selectedPhoto.description}</p>
        <p>Формат: {selectedPhoto.format}</p>
        <p>Размер: {selectedPhoto.size} байт</p>
        <p>Дата добавления: {selectedPhoto.createdAt}</p>
        <p>Папка: {selectedPhoto.folder ? selectedPhoto.folder : 'Нет'}</p>
      </div>
    </div>
  );
}
