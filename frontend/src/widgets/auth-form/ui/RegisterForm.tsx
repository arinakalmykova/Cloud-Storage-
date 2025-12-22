import { useState } from 'react';
import { registerUser, fetchMe } from '../../../entities/user/api/auth.api.ts';
import { useNavigate } from 'react-router-dom';
import type { AuthProps } from '../../../pages/auth/Auth.tsx';
import type { FC } from 'react';

const Register: FC<AuthProps> = ({ setToken, setUser }) => {
  const [name, setName] = useState<string>('');
  const [email, setEmail] = useState<string>('');
  const [password, setPassword] = useState<string>('');
  const [success, setSuccess] = useState<boolean>(false);
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    try {
      const data = await registerUser(name, email, password);

      if (data.token) {
        localStorage.setItem('token', data.token);
        setToken(data.token);
        const userData = await fetchMe(data.token);
        setUser(userData);
        navigate('/profile');
      } else {
        setSuccess(true);
        setTimeout(() => navigate('/'), 2000);
      }
    } catch (err: any) {
      alert(err.message || 'Ошибка регистрации');
    }
  };

  return (
    <div style={{ marginTop: '20px' }}>
      {success && (
        <p style={{ color: 'green' }}>Регистрация прошла успешно! Пожалуйста, войдите.</p>
      )}
      <form onSubmit={handleSubmit}>
        <input
          type="text"
          placeholder="Имя"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
          style={{ display: 'block', margin: '10px auto' }}
        />
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
          Зарегистрироваться
        </button>
      </form>
    </div>
  );
};

export default Register;
