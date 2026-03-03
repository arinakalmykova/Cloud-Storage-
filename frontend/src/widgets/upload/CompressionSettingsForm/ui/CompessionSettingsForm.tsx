import { Settings } from 'lucide-react';
import { Input } from '../../../../shared';
import type { Folder } from '../../../../entities/folder/model/types';
import '../../../../app/styles/CompressionSettingsForm.css';

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
    <div className="upload__loaded">
      <div className="upload__content">
        <div className="upload__loaded__header">
          <div className="upload__loaded__title">
            <Settings className="upload__loaded__icon" />
            <h2>Настройки сжатия</h2>
          </div>
          <p>Здесь вы можете настроить параметры сжатия</p>
        </div>
        <form className="upload__loaded-form" method="post" action="#">
          <div className="form-group">
            <label htmlFor="title">Название</label>
            <Input
              id="title"
              type="text"
              name="title"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
            />
          </div>
          <div className="form-group">
            <label htmlFor="Description">Описание</label>
            <Input
              type="text"
              name="description"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>

          <div className="form-group">
            <div className="form-group__text">
              <label>Формат сжатия:</label>
              <select value={format} onChange={(e) => setFormat(e.target.value)}>
                <option value="webp">WebP</option>
                <option value="jpeg">JPEG</option>
                <option value="avif">AVIF</option>
                <option value="png">PNG</option>
              </select>
            </div>
          </div>
          <div className="form-group range">
            <div className="form-group__text">
              <label htmlFor="Quality">Качество</label>
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
                          white calc(${quality}%),
                          #334155 calc(${quality}%),
                          #334155 100%
                        )`,
              }}
            />
            <div className="form-group__text">
              <span>Маленький размер</span>
              <span>Высокое качество</span>
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="Tags">Теги</label>
            <Input
              type="text"
              id="tags"
              value={tags}
              placeholder="Введите теги и нажмите Enter"
              onChange={handleTagsChange}
              onKeyDown={handleTagKeyDown}
            />

            {tagList.length > 0 && (
              <div className="tags-list">
                {tagList.map((tag) => (
                  <span key={tag} className="tag">
                    {tag}{' '}
                    <button type="button" onClick={() => removeTag(tag)}>
                      ×
                    </button>
                  </span>
                ))}
              </div>
            )}
          </div>
          <div className="upload__folder">
            <label>Выберите папку для сохранения:</label>
            <select value={folderId ?? ''} onChange={(e) => setFolderId(e.target.value || null)}>
              <option value="">Без папки</option>
              {folders.map((folder) => (
                <option key={folder.id} value={folder.id}>
                  {folder.name}
                </option>
              ))}
            </select>
          </div>
          <div className="upload__sizes">
            <div className="upload__sizes__text">
              {' '}
              <p>Оригинальный размер:</p> <span> {originalSizeMB.toFixed(2)} MB</span>
            </div>
            <div className="upload__sizes__text">
              {' '}
              <p>Примерный размер после сжатия:</p> <span>-{compressedSizeMB.toFixed(2)} MB</span>
            </div>
            <div className="upload__sizes__text">
              {' '}
              <p>Вы экономите:</p>
              <span> {savedPercent}%</span>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}
