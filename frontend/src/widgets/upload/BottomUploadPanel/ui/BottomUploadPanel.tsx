import '../../../../app/styles/BottomUploadPanel.css';
import {Button} from '../../../../widgets';
type Props={
    file: File,
    uploading: boolean,
    status: string,
    title: string,
    quality: number,
    format: string,
    setFile: (file: File | null) => void,
    upload: (file: File, quality: number, format: string) => void,
    previewUrl: string | null

}
export function BottomUploadPanel( {file, uploading, status, title, quality, format, setFile, upload, previewUrl}: Props) {
    return ( file && (<div className="upload__bottom-panel">
                <img src={previewUrl!} alt={title} className="upload__bottom-panel-img" />
                <div className="upload__bottom-panel-info">
                  <p className="upload__bottom-panel-title">{title}.{file.type.split('/')[1]}</p>
                  <div className="upload__bottom-panel-actions">
                   <Button
                      className="upload__bottom-panel-remove"
                      onClick={() => setFile(null)}
                    >
                      ×
                    </Button>
                    <Button
                      className="upload__bottom-panel-upload"
                      onClick={() => file && upload(file, quality, format)}
                      disabled={uploading}
                    >
                      {uploading ? 'Загрузка...' : 'Загрузить и сжать'}
                    </Button>
                  
                  </div>
                   {status && (
                      <p
                        className="upload__bottom-panel-status"
                      >
                        {status}
                      </p>
                    )}
                  {uploading && (
                    <div className="upload__bottom-panel-progress">
                   
                    </div>
                  )}
                </div>
              </div>
    ));
}