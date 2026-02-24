import { useState } from 'react';
import { usePhotoUpload, usePhotoCompressionEcho, useMLRecommendation } from '../../features';
import '../../app/styles/Upload.css';
import { motion } from 'framer-motion';
import {
  UploadFileBlock,
  CompressionSettingsForm,
  UploadResultPreview,
  MLFeaturesPanel,
  BottomUploadPanel,
} from '../../widgets';
import { useAuth } from '../../features';
import { useFolders } from '../../features';

export function UploadPage() {
  const [file, setFile] = useState<File | null>(null);
  const { token, user } = useAuth();
  const userId = String(user?.id);
  const [title, setTitle] = useState<string>('');
  const [description, setDescription] = useState<string>('');
  const [tags, setTags] = useState<string>('');
  const [tagList, setTagsList] = useState<string[]>([]);
  const [quality, setQuality] = useState<number>(0);
  const [format, setFormat] = useState<string>('');
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const originalSizeMB = file ? file.size / (1024 * 1024) : 0;
  const { folders } = useFolders(token ?? '');
  const [folderId, setFolderId] = useState<string | null>(null);
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
  } = usePhotoUpload(token, title, description, tagList, folderId);

  usePhotoCompressionEcho({
    userId,
    token,
    photoId,
    onDone: onCompressionDone,
  });

  const { MLQuality, MLFormat, run: getMLRecommendation } = useMLRecommendation(token);

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0] || null;
    if (!selectedFile) return;

    if (previewUrl) URL.revokeObjectURL(previewUrl);
    setPreviewUrl(URL.createObjectURL(selectedFile));
    setFile(selectedFile);
    setTitle(selectedFile.name.replace(/\.[^/.]+$/, ''));
    setFinalUrl(null);
    setCompressedSize(0);
    setUploading(false);
    setStatus('');

    const result = await getMLRecommendation(selectedFile);
    if (result?.quality) setQuality(result.quality);
    if (result?.format) setFormat(result.format);
  };

  return (
    <div className="upload-page ">
      <div className="upload-page__content">
        <motion.div
          className="upload__welcome"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <h1>Загрузка фото</h1>
          <p>Загрузите ваши фото и оптимизируйте их с использованием умного сжатия</p>
        </motion.div>
        <div className="upload__info">
          <UploadFileBlock uploading={uploading} onChange={handleFileChange} />
          <CompressionSettingsForm
            {...{
              title,
              file,
              setTitle,
              description,
              setDescription,
              format,
              setFormat,
              quality,
              setQuality,
              tags,
              tagList,
              setTags,
              setTagsList,
              originalSizeMB,
              folders,
              folderId,
              setFolderId,
            }}
          />
          {file && previewUrl && (
            <BottomUploadPanel
              file={file}
              uploading={uploading}
              upload={upload}
              status={status}
              title={title}
              quality={quality}
              format={format}
              setFile={setFile}
              previewUrl={previewUrl}
            />
          )}
          <UploadResultPreview
            file={file}
            finalUrl={finalUrl}
            title={title}
            description={description}
            format={format}
            quality={quality}
            originalSizeMB={originalSizeMB}
            compressed_size={compressed_size}
            tagList={tagList}
            previewUrl={previewUrl}
          />
          <MLFeaturesPanel MLFormat={MLFormat} MLQuality={MLQuality} />
        </div>
      </div>
    </div>
  );
}
