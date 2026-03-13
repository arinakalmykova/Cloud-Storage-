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

  console.log('[Echo Init] 1. Запуск initEcho');

  const echo = new Echo({
    broadcaster: 'reverb',
    key: 'photokey123',
    wsHost: 'localhost',
    wsPort: 8081,
    wssPort: 8081,
    forceTLS: false,
    enabledTransports: ['ws'],
    authEndpoint: '/api/broadcasting/auth',
    authTransport: 'ajax',
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    },
  });

  window.Echo = echo;

  console.log('[Echo Init] 2. Echo создан');

  setTimeout(() => {
    console.log('[Echo Debug] connector существует?', !!echo.connector);
    console.log('[Echo Debug] pusher существует?', !!echo.connector?.pusher);
    console.log('[Echo Debug] connection существует?', !!echo.connector?.pusher?.connection);
    console.log(
      '[Echo Debug] текущее состояние:',
      echo.connector?.pusher?.connection?.state || 'нет состояния'
    );
  }, 1000);

  if (echo.connector?.pusher) {
    const pusher = echo.connector.pusher;

    pusher.bind('pusher:connection_established', (data: any) => {
      console.log('[Pusher] Соединение установлено полностью!', data);
    });

    pusher.bind('pusher:error', (err: any) => {
      console.error('[Pusher] Критическая ошибка:', err);
    });

    pusher.connection.bind('state_change', (states: { previous: string; current: string }) => {
      console.log('[Pusher State Change]', states.previous, '→', states.current);
    });
  } else {
    console.warn('[Echo Debug] pusher не инициализирован');
  }

  return echo;
}
