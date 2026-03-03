import { useRegister } from '../../../features';
import { useState } from 'react';
import { Input, Button } from '../../../shared';
import '../../../app/styles/AuthPage.css';

export function Register() {
  const [name, setName] = useState<string>('');
  const [email, setEmail] = useState<string>('');
  const [password, setPassword] = useState<string>('');
  const { register, loading, error } = useRegister();
  const [success, setSuccess] = useState<boolean>(false);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const result = await register(name, email, password);
    if (result) {
      setSuccess(true);
    }
  };

  return (
    <>
      <form onSubmit={handleSubmit} className="register-form">
        <Input
          label="Имя:"
          value={name}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) => setName(e.target.value)}
          placeholder="Имя"
          id="name"
        />
        <Input
          label="Email:"
          value={email}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEmail(e.target.value)}
          placeholder="Email"
          id="email"
        />
        <Input
          label="Пароль:"
          value={password}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.target.value)}
          placeholder="Пароль"
          id="password"
          type="password"
        />
        <Button type="submit" loading={loading}>
          {' '}
          Зарегистрироваться{' '}
        </Button>
      </form>
      {loading && <p>Loading...</p>}
      {error && <p style={{ color: 'red' }}>{error}</p>}
      {success && (
        <p style={{ color: 'green' }}>Регистрация прошла успешно! Пожалуйста, войдите.</p>
      )}
    </>
  );
}
