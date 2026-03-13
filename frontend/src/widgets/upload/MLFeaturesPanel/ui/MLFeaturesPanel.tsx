import { Check } from 'lucide-react';
import styles from '../../../../app/styles/MLFeaturesPanel.module.css';
import { Loader, Error } from '../../../../shared';

type Props = {
  MLFormat: string;
  MLQuality: number;
  loading: boolean;
  error: string | null;
};

export function MLFeaturesPanel({ MLFormat, MLQuality, loading, error }: Props) {
  return (
    <div className={styles.uploadFeatures}>
      <h3 className={styles.uploadFeaturesTitle}>Интеллектуальные функции</h3>

      {loading ? (
        <Loader />
      ) : error ? (
        <Error error={error} />
      ) : MLFormat || MLQuality ? (
        <div className={styles.uploadFeaturesCards}>
          {MLFormat && (
            <div className={styles.uploadFeaturesCard}>
              <h4>Рекомендованный формат</h4>
              <p>{MLFormat.toUpperCase()}</p>
              <Check className={styles.uploadFeaturesIcon} />
            </div>
          )}
          {MLQuality && (
            <div className={styles.uploadFeaturesCard}>
              <h4>Рекомендованное качество</h4>
              <p>{MLQuality}%</p>
              <Check className={styles.uploadFeaturesIcon} />
            </div>
          )}
        </div>
      ) : (
        <p className={styles.uploadFeaturesNote}>ИИ пока не дал рекомендаций для загруженного фото.</p>
      )}
    </div>
  );
}
