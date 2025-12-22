import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Echo: any;
    Pusher: any;
  }
}

window.Pusher = Pusher;

export function initEcho(token: string) {
  if (window.Echo) return window.Echo;

  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'photokey123',
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: 'http://localhost/api/broadcasting/auth',
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    },
  });

  // Отладка - добавьте типы
  window.Echo.connector.socket.connection.bind('connected', () => {
    console.log('✅ Connected to Reverb');
  });

  window.Echo.connector.socket.connection.bind('error', (error: any) => {
    // ← Добавьте тип
    console.error('❌ Reverb connection error:', error);
  });

  return window.Echo;
}
