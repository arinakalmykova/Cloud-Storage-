import type { ReactNode } from 'react';
import { Button } from '../../shared';
import '../../app/styles/ModalWindow.css';

type ModalWindowProps = {
  onClose: () => void;
  children: ReactNode;
  title?: string;
};

export function ModalWindow({ onClose, children, title }: ModalWindowProps) {
  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div className="modal-content" onClick={(e) => e.stopPropagation()}>
        {title && <h2 className="modal-title">{title}</h2>}
        <div className="modal-body">{children}</div>
        <Button className="modal-close" onClick={onClose}>
          Закрыть
        </Button>
      </div>
    </div>
  );
}
