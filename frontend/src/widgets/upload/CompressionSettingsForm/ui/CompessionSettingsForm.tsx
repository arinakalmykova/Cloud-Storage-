import { Settings } from 'lucide-react';
import { Input } from '../../../../shared';
import type { Folder } from '../../../../entities/folder/model/types';
import styles from '../../../../app/styles/CompressionSettingsForm.module.css';

type Props = {
  title: string;
  setTitle: (v: string) => void;
  description: string;
  setDescription: (v: string) => void;
  format: string;
  setFormat: (v: string) => void;
  quality: number;
  setQuality: (v: number) => void;
  setTags: (v: string) => void;
  setTagsList: (v: string[]) => void;
  tags: string;
  tagList: string[];
  file: File | null;
  originalSizeMB: number;
  folders: Folder[];
  folderId: string | null;
  setFolderId: (v: string | null) => void;
};

export function CompressionSettingsForm({
  title,
  setTitle,
  description,
  setDescription,
  format,
  setFormat,
  quality,
  setQuality,
  setTags,
  setTagsList,
  tags,
  tagList,
  originalSizeMB,
  folders,
  file,
  folderId,
  setFolderId,
}: Props) {
  let compressionRatio = 1;
  if (file) {
    switch (format) {
      case 'webp':
        compressionRatio = 1 - (0.8 * (100 - quality)) / 100;
        break;
      case 'jpeg':
        compressionRatio = 1 - (0.75 * (100 - quality)) / 100;
        break;
      case 'avif':
        compressionRatio = 1 - (0.85 * (100 - quality)) / 100;
        break;
      case 'png':
        compressionRatio = 1 - (0.3 * (100 - quality)) / 100;
        break;
      default:
        compressionRatio = 1 - (0.7 * (100 - quality)) / 100;
    }
  }
  const compressedSizeMB = file ? originalSizeMB * compressionRatio : 0;
  const savedPercent = file ? Math.round((1 - compressedSizeMB / originalSizeMB) * 100) : 0;

  const handleTagsChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setTags(e.target.value);
  };

  const handleTagKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      const newTag = tags.trim();
      if (newTag && !tagList.includes(newTag)) {
        setTagsList([...tagList, newTag]);
      }
      setTags('');
    }
  };

  const removeTag = (tag: string) => {
    setTagsList(tagList.filter((t) => t !== tag));
  };
  return (
    <div className={styles.uploadLoaded}>
      <div className={styles.uploadContent}>
        <div className={styles.uploadLoadedHeader}>
          <div className={styles.uploadLoadedTitle}>
            <Settings className={styles.uploadLoadedIcon} />
            <h2>Настройки сжатия</h2>
          </div>
          <p>Здесь вы можете настроить параметры сжатия</p>
        </div>

        <form className={styles.uploadLoadedForm}>
          <div className={styles.formGroup}>
            <Input
              label="Название:"
              id="title"
              type="text"
              name="title"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
            />
          </div>

          <div className={styles.formGroup}>
            <Input
              label="Описание:"
              type="text"
              name="description"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>

          <div className={styles.formGroup}>
            <div className={styles.formGroupText}>
              <label>Формат сжатия:</label>
              <select value={format} onChange={(e) => setFormat(e.target.value)}>
                <option value="webp">WebP</option>
                <option value="jpeg">JPEG</option>
                <option value="avif">AVIF</option>
                <option value="png">PNG</option>
              </select>
            </div>
          </div>

          <div className={styles.formGroup}>
            <div className={styles.formGroupText}>
              <label htmlFor="quality">Качество</label>
              <span>{quality}%</span>
            </div>
            <Input
              type="range"
              min={0}
              max={100}
              value={quality}
              onChange={(e) => setQuality(Number(e.target.value))}
              style={{
                width: '100%',
                borderRadius: '100px',
                background: `linear-gradient(
                  to right,
                  white 0%,
                  white ${quality}%,
                  #334155 ${quality}%,
                  #334155 100%
                )`,
              }}
            />
            <div className={styles.formGroupText}>
              <span>Маленький размер</span>
              <span>Высокое качество</span>
            </div>
          </div>

          <div className={styles.formGroup}>
            <Input
              label="Теги:"
              type="text"
              id="tags"
              value={tags}
              placeholder="Введите теги и нажмите Enter"
              onChange={handleTagsChange}
              onKeyDown={handleTagKeyDown}
            />

            {tagList.length > 0 && (
              <div className={styles.tagsList}>
                {tagList.map((tag) => (
                  <span key={tag} className={styles.tag}>
                    {tag}{' '}
                    <button type="button" onClick={() => removeTag(tag)}>
                      ×
                    </button>
                  </span>
                ))}
              </div>
            )}
          </div>

          <div className={styles.uploadFolder}>
            <label>Выберите папку для сохранения:</label>
            <select
              value={folderId ?? ''}
              onChange={(e) => setFolderId(e.target.value || null)}
            >
              <option value="">Без папки</option>
              {folders.map((folder) => (
                <option key={folder.id} value={folder.id}>
                  {folder.name}
                </option>
              ))}
            </select>
          </div>

          <div className={styles.uploadSizes}>
            <div className={styles.uploadSizesText}>
              <p>Оригинальный размер:</p>
              <span>{originalSizeMB.toFixed(2)} MB</span>
            </div>
            <div className={styles.uploadSizesText}>
              <p>Примерный размер после сжатия:</p>
              <span>{compressedSizeMB.toFixed(2)} MB</span>
            </div>
            <div className={styles.uploadSizesText}>
              <p>Вы экономите:</p>
              <span>{savedPercent}%</span>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}