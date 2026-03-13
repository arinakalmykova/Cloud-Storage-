import { useReducer } from 'react';
import { motion } from 'framer-motion';
import {
  usePhotoUpload,
  usePhotoCompressionEcho,
  useMLRecommendation,
  useAuth,
  useFolders,
} from '../../features';
import {
  UploadFileBlock,
  CompressionSettingsForm,
  UploadResultPreview,
  MLFeaturesPanel,
  BottomUploadPanel,
} from '../../widgets';
import styles from '../../app/styles/Upload.module.css';

type UploadState = {
  file: File | null;
  previewUrl: string | null;
  title: string;
  description: string;
  tags: string;
  tagList: string[];
  quality: number;
  format: string;
  folderId: string | null;
};

type UploadAction =
  | { type: 'SET_FILE'; file: File | null; previewUrl: string | null }
  | { type: 'SET_TITLE'; title: string }
  | { type: 'SET_DESCRIPTION'; description: string }
  | { type: 'SET_TAGS'; tags: string }
  | { type: 'SET_TAG_LIST'; tagList: string[] }
  | { type: 'SET_QUALITY'; quality: number }
  | { type: 'SET_FORMAT'; format: string }
  | { type: 'SET_FOLDER_ID'; folderId: string | null }
  | { type: 'RESET_UPLOAD' };

function uploadReducer(state: UploadState, action: UploadAction): UploadState {
  switch (action.type) {
    case 'SET_FILE':
      return { ...state, file: action.file, previewUrl: action.previewUrl };
    case 'SET_TITLE':
      return { ...state, title: action.title };
    case 'SET_DESCRIPTION':
      return { ...state, description: action.description };
    case 'SET_TAGS':
      return { ...state, tags: action.tags };
    case 'SET_TAG_LIST':
      return { ...state, tagList: action.tagList };
    case 'SET_QUALITY':
      return { ...state, quality: action.quality };
    case 'SET_FORMAT':
      return { ...state, format: action.format };
    case 'SET_FOLDER_ID':
      return { ...state, folderId: action.folderId };
    case 'RESET_UPLOAD':
      return { ...state, file: null, previewUrl: null, title: '', description: '', tags: '', tagList: [], quality: 0, format: '', folderId: null };
    default:
      return state;
  }
}

export function UploadPage() {
  const { token, user } = useAuth();
  const userId = String(user?.id);
  const { folders } = useFolders(token ?? '');

  const [state, dispatch] = useReducer(uploadReducer, {
    file: null,
    previewUrl: null,
    title: '',
    description: '',
    tags: '',
    tagList: [],
    quality: 0,
    format: '',
    folderId: null,
  });

  const originalSizeMB = state.file ? state.file.size / (1024 * 1024) : 0;

  const {
    uploading,
    status,
    finalUrl,
    upload,
    photoId,
    compressed_size,
    onCompressionDone,
    setUploading,
    setStatus,
    setFinalUrl,
    setCompressedSize,
  } = usePhotoUpload(token, state.title, state.description, state.tagList, state.folderId);

  usePhotoCompressionEcho({ userId, token, photoId, onDone: onCompressionDone });

  const { MLQuality, MLFormat, run: getMLRecommendation, loading, error } = useMLRecommendation(token);

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0] || null;
    if (!selectedFile) return;

    if (state.previewUrl) URL.revokeObjectURL(state.previewUrl);

    const preview = URL.createObjectURL(selectedFile);
    dispatch({ type: 'SET_FILE', file: selectedFile, previewUrl: preview });
    dispatch({ type: 'SET_TITLE', title: selectedFile.name.replace(/\.[^/.]+$/, '') });

    // Сбрасываем состояние загрузки
    setFinalUrl(null);
    setCompressedSize(0);
    setUploading(false);
    setStatus('');

    const result = await getMLRecommendation(selectedFile);
    if (result?.quality) dispatch({ type: 'SET_QUALITY', quality: result.quality });
    if (result?.format) dispatch({ type: 'SET_FORMAT', format: result.format });
  };

  return (
    <div className={styles.uploadPage}>
      <div className={styles.uploadPageContent}>
        <motion.div
          className={styles.uploadWelcome}
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <h1>Загрузка фото</h1>
          <p>Загрузите ваши фото и оптимизируйте их с использованием умного сжатия</p>
        </motion.div>

        <div className={styles.uploadInfo}>
          <UploadFileBlock uploading={uploading} onChange={handleFileChange} />

          <CompressionSettingsForm
            {...{
              title: state.title,
              setTitle: (title: string) => dispatch({ type: 'SET_TITLE', title }),
              description: state.description,
              setDescription: (desc: string) => dispatch({ type: 'SET_DESCRIPTION', description: desc }),
              format: state.format,
              setFormat: (fmt: string) => dispatch({ type: 'SET_FORMAT', format: fmt }),
              quality: state.quality,
              setQuality: (q: number) => dispatch({ type: 'SET_QUALITY', quality: q }),
              tags: state.tags,
              tagList: state.tagList,
              setTags: (tags: string) => dispatch({ type: 'SET_TAGS', tags }),
              setTagsList: (list: string[]) => dispatch({ type: 'SET_TAG_LIST', tagList: list }),
              originalSizeMB,
              folders,
              folderId: state.folderId,
              setFolderId: (id: string | null) => dispatch({ type: 'SET_FOLDER_ID', folderId: id }),
              file: state.file,
            }}
          />

          {state.file && state.previewUrl && (
            <BottomUploadPanel
              file={state.file}
              uploading={uploading}
              upload={upload}
              status={status}
              title={state.title}
              quality={state.quality}
              format={state.format}
              setFile={(f: File | null) => dispatch({ type: 'SET_FILE', file: f, previewUrl: null })}
              previewUrl={state.previewUrl}
            />
          )}

          <UploadResultPreview
            file={state.file}
            finalUrl={finalUrl}
            title={state.title}
            description={state.description}
            format={state.format}
            quality={state.quality}
            originalSizeMB={originalSizeMB}
            compressed_size={compressed_size}
            tagList={state.tagList}
            previewUrl={state.previewUrl}
          />

          <MLFeaturesPanel MLFormat={MLFormat} MLQuality={MLQuality} loading={loading} error={error}  />
        </div>
      </div>
    </div>
  );
}