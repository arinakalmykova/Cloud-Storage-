const API_URL:string = import.meta.env.VITE_API_BASE || 'http://localhost/api/auth';

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

export async function registerUser(name:string, email:string, password:string):Promise<AuthResponse> {
    const res = await fetch(`${API_URL}/register`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ name, email, password }),
    });

    if (!res.ok) {
        const err:ErrorResponse = await res.json();
        throw new Error(err.message || 'Ошибка регистрации');
    }

    return res.json();
}

export async function loginUser(email:string, password:string):Promise<AuthResponse> {
   const res = await fetch(`${API_URL}/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
    });
    if (!res.ok) {
        const err:ErrorResponse = await res.json();
        throw new Error(err.message || 'Неверный логин или пароль');
    }

    return res.json();
}

export async function fetchMe(token:string):Promise<User> {
 const res = await fetch(`${API_URL}/me`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
    });
  return res.json();
}
