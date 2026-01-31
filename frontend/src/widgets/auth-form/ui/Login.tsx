import { useState } from 'react';
import { useLogin } from '../../../features';
import '../../../app/styles/AuthPage.css';
import {Input, Button} from '../../../widgets';

export function Login() {
  const { signIn, loading, error } = useLogin();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    signIn(email, password);
  };

  return (
    <form onSubmit={handleSubmit} className="login-form">
      <label htmlFor="email">Email</label>
      <Input value={email} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEmail(e.target.value)} placeholder="Email" id="email" />
      <label htmlFor="password">Пароль</label>
      <Input value={password} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.target.value)} placeholder="Пароль" id="password" type="password" />

      {error && <p style={{ color: 'red' }}>{error}</p>}

      <Button type="submit" loading={loading} > Войти </Button>
    </form>
  );
}
