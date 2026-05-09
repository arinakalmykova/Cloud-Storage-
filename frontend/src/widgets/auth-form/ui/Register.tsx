import { useState } from 'react';
import { useRegister } from '../../../features';
import { Button, Input } from '../../../shared';
import styles from '../../../app/styles/AuthPage.module.css';

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PASSWORD_RULES_MESSAGE =
  'Пароль должен содержать минимум 6 символов, одну заглавную букву, одну строчную букву, одну цифру и один специальный символ.';

function getPasswordValidationError(password: string): string | null {
  if (!password.trim()) {
    return 'Введите пароль';
  }

  if (password.length < 6) {
    return PASSWORD_RULES_MESSAGE;
  }

  if (!/[A-Z]/.test(password)) {
    return PASSWORD_RULES_MESSAGE;
  }

  if (!/[a-z]/.test(password)) {
    return PASSWORD_RULES_MESSAGE;
  }

  if (!/\d/.test(password)) {
    return PASSWORD_RULES_MESSAGE;
  }

  if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    return PASSWORD_RULES_MESSAGE;
  }

  return null;
}

export function Register() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [localError, setLocalError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const { register, loading: apiLoading, registerError: apiError } = useRegister();

  const validateForm = () => {
    if (!name.trim()) {
      setLocalError('Введите имя');
      return false;
    }

    if (!email.trim()) {
      setLocalError('Введите email');
      return false;
    }

    if (!EMAIL_REGEX.test(email)) {
      setLocalError('Введите корректный email');
      return false;
    }

    const passwordError = getPasswordValidationError(password);

    if (passwordError) {
      setLocalError(passwordError);
      return false;
    }

    if (!confirmPassword.trim()) {
      setLocalError('Подтвердите пароль');
      return false;
    }

    if (password !== confirmPassword) {
      setLocalError('Пароли не совпадают');
      return false;
    }

    return true;
  };

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    setLocalError(null);
    setSuccess(false);

    if (!validateForm()) {
      return;
    }

    try {
      const result = await register(name, email, password);

      if (result) {
        setSuccess(true);
        setName('');
        setEmail('');
        setPassword('');
        setConfirmPassword('');
      }
    } catch (err) {
      console.error('Register error:', err);
    }
  };

  const errorMessage = localError || apiError;

  return (
    <form onSubmit={handleSubmit} className={styles.registerForm}>
      <Input
        label="Имя:"
        type="text"
        value={name}
        onChange={(e) => setName(e.target.value)}
        placeholder="Имя"
        id="name"
        required
        disabled={apiLoading}
      />

      <Input
        label="Email:"
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Email"
        id="email"
        required
        disabled={apiLoading}
      />

      <Input
        label="Пароль:"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        placeholder="Пароль"
        id="password"
        type="password"
        required
        disabled={apiLoading}
      />

      <p style={{ marginTop: '-4px', marginBottom: '8px', fontSize: '12px', color: '#666' }}>
        {PASSWORD_RULES_MESSAGE}
      </p>

      <Input
        label="Подтверждение пароля:"
        value={confirmPassword}
        onChange={(e) => setConfirmPassword(e.target.value)}
        placeholder="Повторите пароль"
        id="confirmPassword"
        type="password"
        required
        disabled={apiLoading}
      />

      {errorMessage && (
        <p
          style={{
            color: 'red',
            marginTop: '10px',
            padding: '10px',
            backgroundColor: '#ffeeee',
            borderRadius: '4px',
            textAlign: 'center',
          }}
        >
          {errorMessage}
        </p>
      )}

      {success && (
        <p
          style={{
            color: 'green',
            marginTop: '10px',
            padding: '10px',
            backgroundColor: '#eeffee',
            borderRadius: '4px',
          }}
        >
          Регистрация прошла успешно! Пожалуйста, войдите.
        </p>
      )}

      <Button type="submit" loading={apiLoading} disabled={apiLoading}>
        {apiLoading ? 'Регистрация...' : 'Зарегистрироваться'}
      </Button>
    </form>
  );
}
