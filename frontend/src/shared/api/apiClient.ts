export async function apiClient(
  url: string,
  token?: string,
  options: RequestInit = {}
) {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json', 
  };

  if (options.headers) {
    if (options.headers instanceof Headers) {
      options.headers.forEach((value, key) => {
        headers[key] = value;
      });
    } else if (Array.isArray(options.headers)) {
      options.headers.forEach(([key, value]) => {
        headers[key] = value;
      });
    } else {
      Object.assign(headers, options.headers);
    }
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  console.log('Making request to:', url);
  console.log('With method:', options.method || 'GET');

  const res = await fetch(url, {
    ...options,
    headers,
  });

  console.log('Response status:', res.status);

  if (!res.ok) {
    if (res.status === 302) {
      const location = res.headers.get('Location');
      throw new Error(`Редирект на ${location}. Возможно, неправильный URL или требуется аутентификация`);
    }

    if (res.status === 401) {
      window.location.href = '/auth';
      throw new Error('Требуется аутентификация');
    }
    
    let errorText;
    try {
      errorText = await res.text();
    } catch {
      errorText = 'Не удалось прочитать ответ';
    }
    
    throw new Error(errorText || `Ошибка запроса: ${res.status}`);
  }

  return res.json();
}