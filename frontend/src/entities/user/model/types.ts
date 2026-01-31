export interface User {
  id: string;
  name: string;
  email: string;
}

export interface AuthResponse {
  token: string;
  user: User;
  error?: string;
  userId?: string;
  name?: string;
  email?: string;
}

export interface ErrorResponse {
  message?: string;
}
