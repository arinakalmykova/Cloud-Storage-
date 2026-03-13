import type { ReactNode } from 'react';
import { Button } from '../../shared';
import styles from '../../app/styles/ModalWindow.module.css';

type ModalWindowProps = {
  onClose: () => void;
  children: ReactNode;
  title?: string;
};

export function ModalWindow({ onClose, children, title }: ModalWindowProps) {
  return (
    <div className={styles.modalBackdrop} onClick={onClose}>
      <div className={styles.modalContent} onClick={(e) => e.stopPropagation()}>
        {title && <h2 className={styles.modalTitle}>{title}</h2>}
        <div className={styles.modalBody}>{children}</div>
        <Button className={styles.modalClose} type="button" onClick={onClose}>
          ✕
        </Button>
      </div>
    </div>
  );
}
