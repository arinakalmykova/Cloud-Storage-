import { useRef, useState, useEffect } from 'react';
import { getUploadUrl, markUploaded, updateTags } from '../../../entities';
import { startDotsAnimation } from '../../../features';

export function usePhotoUpload(
  token: string | null,
  title: string,
  description: string,
  tagList: string[] = [],
  folderId: string | null
) {
  const [uploading, setUploading] = useState(false);
  const [status, setStatus] = useState('');
  const [finalUrl, setFinalUrl] = useState<string | null>(null);
  const [compressed_size, setCompressedSize] = useState(0);
  const stopAnimationRef = useRef<(() => void) | null>(null);
  const [photoId, setPhotoId] = useState<string | null>(null);
  const isMountedRef = useRef(true);

  useEffect(() => {
    return () => {
      isMountedRef.current = false;
      if (stopAnimationRef.current) {
        stopAnimationRef.current();
      }
    };
  }, []);

  const stopAnimation = () => {
    if (stopAnimationRef.current) {
      stopAnimationRef.current();
      stopAnimationRef.current = null;
    }
  };

  const upload = async (file: File, quality: number, format: string) => {
    console.log('upload вызван с:', { file, quality, format });
    if (!token) return;

    setUploading(true);
    setStatus('Получаем безопасную ссылку...');
    setFinalUrl(null);
    stopAnimation();

    try {
      const { photo_id, upload_url } = await getUploadUrl(token, file, title, description);
      setPhotoId(photo_id);

      await fetch(upload_url, {
        method: 'PUT',
        body: file,
        headers: { 'Content-Type': file.type },
      });

      setStatus('Сжимаем...');
      stopAnimationRef.current = startDotsAnimation(setStatus);

      await markUploaded(
        token,
        photo_id,
        upload_url.split('?')[0],
        file.size,
        quality,
        format,
        folderId
      );

      setStatus('Фото отправлено на сжатие. Ожидаем подтверждения...');
    } catch (e: any) {
      stopAnimation();
      setStatus('Ошибка: ' + (e.message || 'Неизвестная ошибка'));
      setUploading(false);
    }
  };

  const handleCompressionDone = (compressedUrl: string, compressedSize: number) => {
    if (!isMountedRef.current) return;

    stopAnimation();
    setStatus('Фото успешно сжато и загружено!');
    setFinalUrl(compressedUrl);

    if (tagList.length > 0 && photoId) {
      updateTags(token!, photoId, tagList)
        .then(() => console.log('Теги успешно обновлены'))
        .catch((err) => console.error('Ошибка при обновлении тегов', err));
    }

    setCompressedSize(compressedSize);
    setUploading(false);
  };

  return {
    uploading,
    status,
    finalUrl,
    upload,
    photoId,
    compressed_size,
    setStatus,
    setFinalUrl,
    setCompressedSize,
    setUploading,
    onCompressionDone: handleCompressionDone,
  };
}
