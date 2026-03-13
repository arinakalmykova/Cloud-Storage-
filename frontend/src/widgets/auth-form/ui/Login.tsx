import { useState } from 'react';
import { useLogin } from '../../../features';
import { Input, Button, Loader } from '../../../shared';
import styles from '../../../app/styles/AuthPage.module.css';

export function Login() {
  const { signIn, loading: apiLoading, loginError: apiError } = useLogin();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [localError, setLocalError] = useState<string | null>(null);

  if (apiLoading) return <Loader />;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!email.trim()) {
      setLocalError('Введите email');
      return;
    }
    if (!password.trim()) {
      setLocalError('Введите пароль');
      return;
    }
    
    setLocalError(null);
    
    try {
      await signIn(email, password);
    } catch (err: any) {
      console.error('Login error:', err);
    }
  };

  const errorMessage = localError || apiError;
  console.log(errorMessage);
  return (
    <form onSubmit={handleSubmit} className={styles.loginForm}>
      <Input
        label="Email:"
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Email"
        id="email"
        required 
      />
      <Input
        label="Пароль:"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        placeholder="Пароль"
        id="password"
        type="password"
        required 
      />

      <Button type="submit" disabled={apiLoading}>
        {apiLoading ? 'Вход...' : 'Войти'}
      </Button>

      {errorMessage && (
        <p style={{ color: 'red', marginTop: '10px', textAlign: 'center' }}>
          Ошибка входа
        </p>
      )}
    </form>
  );
}