import { Check } from 'lucide-react';
import styles from '../../../../app/styles/MLFeaturesPanel.module.css';
import { Loader, Error } from '../../../../shared';

type Props = {
  MLFormat: string;
  MLQuality: number;
  MLContentType: string;
  loading: boolean;
  error: string | null;
};

const contentTypeLabels: Record<string, string> = {
  photo: 'Фотографическое изображение',
  text_graphics: 'Текст/графика',
  illustration: 'Иллюстрация',
  ui_screenshot: 'Изображение интерфейса',
  mixed: 'Комбинированное изображение',
};

export function MLFeaturesPanel({
  MLFormat,
  MLQuality,
  MLContentType,
  loading,
  error,
}: Props) {
  return (
    <div className={styles.uploadFeatures}>
      <h3 className={styles.uploadFeaturesTitle}>Рекомендуемые параметры сжатия</h3>

      {loading ? (
        <Loader />
      ) : error ? (
        <Error error={error} />
      ) : MLFormat || MLQuality || MLContentType ? (
        <div className={styles.uploadFeaturesCards}>
          {MLContentType && (
            <div className={styles.uploadFeaturesCard}>
              <h4>Тип изображения</h4>
              <p>{contentTypeLabels[MLContentType] ?? MLContentType}</p>
              <Check className={styles.uploadFeaturesIcon} />
            </div>
          )}
          {MLFormat && (
            <div className={styles.uploadFeaturesCard}>
              <h4>Рекомендуемый формат</h4>
              <p>{MLFormat.toUpperCase()}</p>
              <Check className={styles.uploadFeaturesIcon} />
            </div>
          )}
          {MLQuality ? (
            <div className={styles.uploadFeaturesCard}>
              <h4>Рекомендуемое качество</h4>
              <p>{MLQuality}%</p>
              <Check className={styles.uploadFeaturesIcon} />
            </div>
          ) : null}
        </div>
      ) : (
        <p className={styles.uploadFeaturesNote}>
          Для загруженного изображения пока не удалось подобрать параметров сжатия.
        </p>
      )}
    </div>
  );
}
