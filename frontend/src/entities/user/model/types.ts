export interface User {
  id: string;
  name: string;
  email: string;
  createdAt: string;
}

export interface AuthResponse {
  token: string;
  user: User;
  error?: string;
  userId?: string;
  name?: string;
  email?: string;
  createdAt?: string;
}

export interface ErrorResponse {
  message?: string;
}
