import { Button } from '../../../../shared';
import styles from '../../../../app/styles/BottomUploadPanel.module.css';

type Props = {
  file: File;
  uploading: boolean;
  status: string;
  title: string;
  quality: number;
  format: string;
  setFile: (file: File | null) => void;
  upload: (file: File, quality: number, format: string) => void;
  previewUrl: string | null;
};
export function BottomUploadPanel({
  file,
  uploading,
  status,
  title,
  quality,
  format,
  setFile,
  upload,
  previewUrl,
}: Props) {
  return (
    file && (
      <div className={styles.uploadBottomPanel}>
        <img src={previewUrl!} alt={title} className={styles.uploadBottomPanelImage} />
        <div className={styles.uploadBottomPanelInfo}>
          <p className={styles.uploadBottomPanelTitle}>
            {title}.{file.type.split('/')[1]}
          </p>
          <div className={styles.uploadBottomPanelActions}>
            <Button className={styles.uploadBottomPanelRemove} onClick={() => setFile(null)}>
              ×
            </Button>
            <Button
              className={styles.uploadBottomPanelUpload}
              onClick={() => file && upload(file, quality, format)}
              disabled={uploading}
            >
              {uploading ? 'Загрузка...' : 'Загрузить и сжать'}
            </Button>
          </div>
          {status && <p className={styles.uploadBottomPanelStatus}>{status}</p>}
          {uploading && <div className={styles.uploadBottomPanelProgress}></div>}
        </div>
      </div>
    )
  );
}
