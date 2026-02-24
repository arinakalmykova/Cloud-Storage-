// features/photo-upload/lib/usePhotoCompressionEcho.tsx
import { useEffect } from 'react';
import { initEcho } from '../../websocket/lib/echo'; // или откуда у тебя путь

type Props = {
  userId: string | null;
  token: string | null;
  photoId: string | null;
  onDone: (url: string, size: number) => void;
};

export function usePhotoCompressionEcho({ userId, token, photoId, onDone }: Props) {
  useEffect(() => {
    if (!userId || !token || !photoId) {
      console.warn('[usePhotoCompressionEcho] Нет userId или token → пропускаем подписку');
      return;
    }

    const echo = initEcho(token);
    if (!echo) return;

    const channelName = `user.${userId}`;

    console.log(`[Echo] Подписываемся на приватный канал: ${channelName}`);

    const channel = echo
      .private(channelName)
      .listen(
        '.photo.compressed',
        (event: { photo_id: string; compressed_url: string; compressed_size: number }) => {
          console.log('[Echo] Получено событие photo.compressed', event);

          if (
            photoId &&
            event.photo_id === photoId &&
            event.compressed_url &&
            event.compressed_size > 0
          ) {
            console.log(`[Echo] Совпадение photo_id → устанавливаем URL: ${event.compressed_url}`);
            onDone(event.compressed_url, event.compressed_size);
          } else {
            console.log('[Echo] photo_id не совпадает или ref пустой → игнорируем', {
              current: photoId,
              received: event.photo_id,
            });
          }
        }
      );

    channel.subscribed(() => {
      console.log(`[Echo] Успешно subscribed на ${channelName}`);
    });

    return () => {
      console.log(`[Echo] Отписываемся от ${channelName}`);
      channel.stopListening('.photo.compressed');
      echo.leave(channelName);
    };
  }, [userId, token, photoId, onDone]);
}
