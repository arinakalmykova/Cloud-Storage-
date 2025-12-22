import { useEffect } from 'react';
import { initEcho } from '../../websocket/lib/echo';

interface Props {
  userId: string | null;
  token: string | null;
  photoIdRef: React.MutableRefObject<string | null>;
  onDone: (url: string) => void;
}

export function usePhotoCompressionEcho({ userId, token, photoIdRef, onDone }: Props) {
  useEffect(() => {
    if (!userId || !token) return;

    const echo = initEcho(token);
    const channel = echo.private(`user.${userId}`);

    channel.listen('photo.compressed', (e: any) => {
      if (e.photoId === photoIdRef.current) {
        onDone(e.url);
      }
    });

    return () => {
      echo.leave(`user.${userId}`);
    };
  }, [userId, token]);
}
