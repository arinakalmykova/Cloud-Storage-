import { useRegister } from '../../../features';
import { useState } from 'react'; 
import { Input, Button } from '../../../shared';
import styles from '../../../app/styles/AuthPage.module.css';

export function Register() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState(''); 
  const { register, loading: apiLoading, registerError: apiError } = useRegister();
  const [localError, setLocalError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const validateForm = () => {
    if (!name.trim()) {
      setLocalError('Введите имя');
      return false;
    }
    if (!email.trim()) {
      setLocalError('Введите email');
      return false;
    }
    if (!email.includes('@')) {
      setLocalError('Введите корректный email');
      return false;
    }
    if (!password.trim()) {
      setLocalError('Введите пароль');
      return false;
    }
    if (password.length < 6) {
      setLocalError('Пароль должен быть не менее 6 символов');
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
    } catch (err: any) {
      console.error('Register error:', err);
    }
  };

  const errorMessage = localError || apiError;
  console.log('errorMessage', errorMessage);
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
        placeholder="Пароль (мин. 6 символов)"
        id="password"
        type="password"
        required
        disabled={apiLoading}
      />
      
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
        <p style={{ 
          color: 'red', 
          marginTop: '10px',
          padding: '10px',
          backgroundColor: '#ffeeee',
          borderRadius: '4px',
          textAlign: 'center'
        }}>
          Ошибка регистрации
        </p>
      )}
      
      {success && (
        <p style={{ 
          color: 'green', 
          marginTop: '10px',
          padding: '10px',
          backgroundColor: '#eeffee',
          borderRadius: '4px'
        }}>
          Регистрация прошла успешно! Пожалуйста, войдите.
        </p>
      )}

      <Button type="submit" loading={apiLoading} disabled={apiLoading}>
        {apiLoading ? 'Регистрация...' : 'Зарегистрироваться'}
      </Button>
    </form>
  );
}