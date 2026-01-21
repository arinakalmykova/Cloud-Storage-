import { useRef, useState, useEffect } from 'react';
import {
  getUploadUrl,
  markUploaded,
  updateTags
} from '../../../entities/photo/api/photos.api.ts';
import { startDotsAnimation } from './processingAnimation.tsx';

export function usePhotoUpload(token: string | null, title: string, description: string, tagList: string[] = []) {
  const [uploading, setUploading] = useState(false);
  const [status, setStatus] = useState('');
  const [finalUrl, setFinalUrl] = useState<string | null>(null);

  const stopAnimationRef = useRef<(() => void) | null>(null);
  const photoIdRef = useRef<string | null>(null);
  const isMountedRef = useRef(true); // чтобы не обновлять состояние после unmount

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

      setStatus('Сжимаем в WebP...');

      // Запускаем анимацию точек — она будет идти, пока не придёт событие
      stopAnimationRef.current = startDotsAnimation(setStatus);

      await markUploaded(token, photo_id, upload_url.split('?')[0], file.size, quality, format);

      setStatus('Фото отправлено на сжатие. Ожидаем подтверждения...');

      // Больше НЕ используем setTimeout и checkPhotoStatus!
      // Дальше за обновление отвечает usePhotoCompressionEcho → onDone

    } catch (e: any) {
      stopAnimation();
      setStatus('Ошибка: ' + (e.message || 'Неизвестная ошибка'));
      setUploading(false);
    }
  };

  // Функция, которую вызовет usePhotoCompressionEcho когда событие придёт
  const handleCompressionDone = (compressedUrl: string) => {
    if (!isMountedRef.current) return;

    stopAnimation();
    setStatus('Фото успешно сжато и загружено!');
    setFinalUrl(compressedUrl);

    // Обновляем теги, если они есть
    if (tagList.length > 0 && photoIdRef.current) {
      updateTags(token!, photoIdRef.current, tagList)
        .then(() => console.log('Теги успешно обновлены'))
        .catch(err => console.error('Ошибка при обновлении тегов', err));
    }

    setUploading(false);
  };

  return {
    uploading,
    status,
    finalUrl,
    upload,
    photoIdRef,
    setFinalUrl,
    setUploading,
    // ← Добавляем эту функцию, чтобы передать в usePhotoCompressionEcho как onDone
    onCompressionDone: handleCompressionDone,
  };
}