import { useRef, useState, useEffect } from 'react';
import {
  getUploadUrl,
  markUploaded,
  checkPhotoStatus,
  updateTags
} from '../../../entities/photo/api/photos.api.ts';
import { startDotsAnimation } from './processingAnimation.tsx';

export function usePhotoUpload(token: string | null, title: string, description: string, tagList: string[] = []) {
  const [uploading, setUploading] = useState(false);
  const [status, setStatus] = useState('');
  const [finalUrl, setFinalUrl] = useState<string | null>(null);

  const stopAnimationRef = useRef<(() => void) | null>(null);
  const photoIdRef = useRef<string | null>(null);

  useEffect(() => {
    return () => {
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

  const upload = async (file: File) => {
    if (!token) return;

    setUploading(true);
    setStatus('Получаем безопасную ссылку...');
    setFinalUrl(null);
    stopAnimation();

    try {
      const { photo_id, upload_url } = await getUploadUrl(token, file, title, description);
      photoIdRef.current = photo_id;

      await fetch(upload_url, {
        method: 'PUT',
        body: file,
        headers: { 'Content-Type': file.type },
      });

      setStatus('Сжимаем в WebP');

      stopAnimationRef.current = startDotsAnimation(setStatus);

      await markUploaded(token, photo_id, upload_url.split('?')[0], file.size);

      setTimeout(async () => {
        try {
          const data = await checkPhotoStatus(token, photo_id);

          if (data.status === 'compressed') {
            stopAnimation();
            setStatus('Фото успешно сжато и загружено!');
            setFinalUrl(data.url);

            if (tagList.length > 0) {
              try {
                await updateTags(token, tagList);
                console.log('Теги успешно обновлены');
              } catch (err) {
                console.error('Ошибка при обновлении тегов', err);
              }
            }
            
            setUploading(false);
          } else {
            setStatus('Обработка всё ещё идёт...');
          }
        } catch (err) {
          stopAnimation();
          setStatus('Ошибка при проверке статуса');
          setUploading(false);
        }
      }, 20000);
    } catch (e: any) {
      stopAnimation();
      setStatus('Ошибка: ' + (e.message || 'Неизвестная ошибка'));
      setUploading(false);
    }
  };

  return {
    uploading,
    status,
    finalUrl,
    upload,
    photoIdRef,
    setFinalUrl,
    setUploading,
  };
}
