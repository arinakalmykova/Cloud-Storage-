import { Slider } from '../../../../widgets';
import '../../../../app/styles/UploadResultPreview.css';

type Props = {
  title: string;
  description: string;
  format: string;
  quality: number;
  originalSizeMB: number;
  compressed_size: number;
  tagList: string[];
  previewUrl: string | null;
  finalUrl: string | null;
  file: File | null;
};

export function UploadResultPreview({
  file,
  title,
  description,
  format,
  originalSizeMB,
  compressed_size,
  tagList,
  previewUrl,
  finalUrl,
}: Props) {
  const compressedSizeResultMB = compressed_size / (1024 * 1024);
  const savedPercentResult = file
    ? Math.round((1 - compressedSizeResultMB / originalSizeMB) * 100)
    : 0;

  return (
    <div className="upload__preview">
      {file && finalUrl && (
        <>
          <Slider originalSrc={previewUrl!} compressedSrc={finalUrl} />
          <div className="upload__preview__info">
            <h4>Ваше фото успешно сохранено в архиве</h4>
            <p>
              <strong>Название:</strong> {title}
            </p>
            <p>
              <strong>Описание:</strong> {description}
            </p>
            <p>
              <strong>Формат:</strong> {format.toUpperCase()}
            </p>
            <p>
              <strong>Оригинальный размер:</strong> {originalSizeMB.toFixed(2)} MB
            </p>
            <p>
              <strong>Размер после сжатия:</strong> {compressedSizeResultMB.toFixed(2)} MB
            </p>
            <p>
              <strong>Экономия:</strong> {savedPercentResult}%
            </p>
            {tagList.length > 0 && (
              <p>
                <strong>Теги:</strong> {tagList.join(', ')}
              </p>
            )}
          </div>
        </>
      )}
    </div>
  );
}
