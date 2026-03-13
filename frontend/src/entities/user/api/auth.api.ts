import { API_AUTH_URL } from '../../../shared';
import type { User, AuthResponse } from '../../../entities';
import {apiClient} from '../../../shared';

export async function registerUser(
  name: string,
  email: string,
  password: string
): Promise<AuthResponse> {
  return apiClient(`${API_AUTH_URL}/register`, undefined, { method: 'POST', body: JSON.stringify({ name, email, password }) });
}

export async function loginUser(email: string, password: string): Promise<AuthResponse> {
  return apiClient(`${API_AUTH_URL}/login`, undefined, { method: 'POST', body: JSON.stringify({ email, password }) });
}

export async function fetchMe(token: string): Promise<User> {
  return apiClient(`${API_AUTH_URL}/me`, token);
}

export async function deleteUser(token: string) {
  return apiClient(`${API_AUTH_URL}/`, token, { method: 'DELETE' });
}

export async function updateUser(token: string, name: string, email: string) {
  return apiClient(`${API_AUTH_URL}/`, token, { method: 'PUT', body: JSON.stringify({ name, email }) });
}
