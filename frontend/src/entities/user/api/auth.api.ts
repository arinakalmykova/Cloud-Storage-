import { API_AUTH_URL } from '../../../shared';
import type { User, ErrorResponse, AuthResponse } from '../../../entities';

export async function registerUser(
  name: string,
  email: string,
  password: string
): Promise<AuthResponse> {
  const res = await fetch(`${API_AUTH_URL}/register`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ name, email, password }),
  });

  if (!res.ok) {
    const err: ErrorResponse = await res.json();
    throw new Error(err.message || 'Ошибка регистрации');
  }

  return res.json();
}

export async function loginUser(email: string, password: string): Promise<AuthResponse> {
  const res = await fetch(`${API_AUTH_URL}/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });
  if (!res.ok) {
    const err: ErrorResponse = await res.json();
    throw new Error(err.message || 'Неверный логин или пароль');
  }

  return res.json();
}

export async function fetchMe(token: string): Promise<User> {
  const res = await fetch(`${API_AUTH_URL}/me`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return res.json();
}

export async function deleteUser(token: string) {
  const res = await fetch(`${API_AUTH_URL}/`, {
    method: 'DELETE',
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return res.json();
}

export async function updateUser(token: string, name: string, email: string) {
  const res = await fetch(`${API_AUTH_URL}/`, {
    method: 'PUT',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ name, email }),
  });
  return res.json();
}
