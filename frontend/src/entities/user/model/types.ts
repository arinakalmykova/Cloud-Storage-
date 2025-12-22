export interface User {
  id: number;
  name: string;
  email: string;
}

export interface AuthResponse {
  token: string;
  user: User;
  error?: string;
}

export interface ErrorResponse {
  message?: string;
}
