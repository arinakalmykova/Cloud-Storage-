
import {  Check } from 'lucide-react';
import '../../../../app/styles/MLFeaturesPanel.css';

type Props={
    MLFormat: string,
    MLQuality: number
}


export function MLFeaturesPanel( {MLFormat, MLQuality}: Props) {
    return ( <div className="upload__features">
          <h3 className="upload__features__title">Интеллектуальные функции</h3>

          {MLFormat || MLQuality ? (
            <div className="upload__features__cards">
              {MLFormat && (
                <div className="upload__features__card">
                  <h4>Рекомендованный формат</h4>
                  <p>{MLFormat.toUpperCase()}</p>
                  <Check className="upload__features__icon" />
                </div>
              )}
              {MLQuality && (
                <div className="upload__features__card">
                  <h4>Рекомендованное качество</h4>
                  <p>{MLQuality}%</p>
                  <Check className="upload__features__icon" />
                </div>
              )}
            </div>
          ) : (
            <p className="upload__features__note">ИИ пока не дал рекомендаций для загруженного фото.</p>
          )}
        </div>);
}