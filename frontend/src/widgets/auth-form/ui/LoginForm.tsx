import { useState } from 'react';
import { loginUser, fetchMe } from '../../../entities/user/api/auth.api.ts';
import { useNavigate } from 'react-router-dom';
import type { FC } from 'react';
import type { AuthProps } from '../../../pages/auth/Auth.tsx';

const Login: FC<AuthProps> = ({ setToken, setUser }) => {
  const [email, setEmail] = useState<string>('');
  const [password, setPassword] = useState<string>('');
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    try {
      const data = await loginUser(email, password);

      if (data.token) {
        localStorage.setItem('token', data.token);
        setToken(data.token);

        const userData = await fetchMe(data.token);
        setUser(userData);

        navigate('/profile');
      }
    } catch (err: any) {
      alert(err.message || 'Произошла ошибка');
    }
  };

  return (
    <form onSubmit={handleSubmit} style={{ marginTop: '20px' }}>
      <input
        type="email"
        placeholder="Email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        required
        style={{ display: 'block', margin: '10px auto' }}
      />
      <input
        type="password"
        placeholder="Пароль"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        required
        style={{ display: 'block', margin: '10px auto' }}
      />
      <button type="submit" style={{ marginTop: '10px' }}>
        Войти
      </button>
    </form>
  );
};

export default Login;
