import { useState } from 'react';
import { Login, Register } from '../../widgets';
import '../../app/styles/AuthPage.css';
import { motion } from 'framer-motion';
import { Logo } from '../../widgets';

export function AuthPage() {
  const [tab, setTab] = useState<'login' | 'register'>('login');

  return (
    <div className="auth-page">
      <motion.div
        initial={{ opacity: 0, x: -30 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.6 }}
      >
        <div className="auth-content-container">
          <Logo />
          <div className="text-content">
            <h2 className="text-title">
              Современное облачное хранилище ваших фотографий
            </h2>

            <p className="text-description">
              Безопасное, интеллектуальное и красиво организованное. Храните и оптимизируйте свои
              фотографии с использованием передовых технологий.
            </p>
          </div>
        </div>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, x: 30 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.6 }}
      >
        <div className="auth-tabs-container">
          <h1>Добро пожаловать в PIXORY</h1>
          <p>Пожалуйста, войдите или зарегистрируйтесь</p>
          <div className="auth-tabs">
            <button
              onClick={() => setTab('login')}
              className={`tabs ${tab === 'login' ? 'active' : ''} `}
            >
              Вход
            </button>
            <button
              onClick={() => setTab('register')}
              className={`tabs ${tab === 'register' ? 'active' : ''} `}
            >
              Регистрация
            </button>
          </div>
          {tab === 'login' ? <Login /> : <Register />}
        </div>
      </motion.div>
    </div>
  );
}
