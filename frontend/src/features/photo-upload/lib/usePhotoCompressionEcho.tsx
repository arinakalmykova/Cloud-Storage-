// features/photo-upload/lib/usePhotoCompressionEcho.tsx
import { useEffect } from 'react';
import { initEcho } from '../../websocket/lib/echo'; // или откуда у тебя путь

type Props = {
  userId: string | null;
  token: string | null;
  photoIdRef: React.MutableRefObject<string | null>;
  onDone: (url: string) => void;
};

export function usePhotoCompressionEcho({ userId, token, photoIdRef, onDone }: Props) {
  useEffect(() => {
    if (!userId || !token) {
      console.warn('[usePhotoCompressionEcho] Нет userId или token → пропускаем подписку');
      return;
    }

    // Инициализируем Echo один раз (если ещё не сделано где-то выше)
    const echo = initEcho(token);
    if (!echo) return;

    const channelName = `user.${userId}`;

    console.log(`[Echo] Подписываемся на приватный канал: ${channelName}`);

    // Важно: .photo.compressed с точкой в начале!
    const channel = echo.private(channelName).listen(
      '.photo.compressed',
      (event: { photo_id: string; compressed_url: string }) => {
        console.log('[Echo] Получено событие photo.compressed', event);

        if (photoIdRef.current && event.photo_id === photoIdRef.current) {
          console.log(`[Echo] Совпадение photo_id → устанавливаем URL: ${event.compressed_url}`);
          onDone(event.compressed_url);
        } else {
          console.log('[Echo] photo_id не совпадает или ref пустой → игнорируем', {
            current: photoIdRef.current,
            received: event.photo_id,
          });
        }
      }
    );

    // Отладка: проверяем, что канал действительно subscribed
    channel.subscribed(() => {
      console.log(`[Echo] Успешно subscribed на ${channelName}`);
    });

    return () => {
      console.log(`[Echo] Отписываемся от ${channelName}`);
      channel.stopListening('.photo.compressed');
      echo.leave(channelName);
    };
  }, [userId, token, photoIdRef, onDone]); // ← token тоже в зависимостях, если может меняться
}