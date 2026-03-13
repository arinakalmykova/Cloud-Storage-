import { Upload } from 'lucide-react';
import { Input } from '../../../../shared';
import styles from '../../../../app/styles/UploadFileBlock.module.css';

type Props = {
  uploading: boolean;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
};

export function UploadFileBlock({ uploading, onChange }: Props) {
  return (
    <div className={styles.uploadForm}>
      <div className={styles.uploadFile}>
        <label className={styles.uploadLabel}>
          <Upload className={styles.uploadIcon} />
          <h2>Загрузите фото сюда</h2>
          <Input
            className={styles.uploadInput}
            type="file"
            accept="image/*"
            disabled={uploading}
            onChange={onChange}
          />
        </label>
      </div>
    </div>
  );
}
