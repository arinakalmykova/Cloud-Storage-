import { Upload } from 'lucide-react';
import { Input } from '../../../../shared';
import '../../../../app/styles/UploadFileBlock.css';

type Props = {
  uploading: boolean;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
};

export function UploadFileBlock({ uploading, onChange }: Props) {
  return (
    <div className="upload__form">
      <div className="upload__file">
        <label className="upload__label">
          <Upload className="upload__icon" />
          <h2>Загрузите фото сюда</h2>
          <Input
            className="upload__input"
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
