import { useState } from 'react';
import { Login, Register, Logo } from '../../widgets';
import { motion } from 'framer-motion';
import styles from '../../app/styles/AuthPage.module.css';

export function AuthPage() {
  const [tab, setTab] = useState<'login' | 'register'>('login');

  return (
    <div className={styles.authPage}>
      <motion.div
        initial={{ opacity: 0, x: -30 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.6 }}
      >
        <div className={styles.authContentContainer}>
          <Logo />
          <div className={styles.textContent}>
            <h2 className={styles.textTitle}>Современное облачное хранилище ваших фотографий</h2>

            <p className={styles.textDescription}>
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
        <div className={styles.authTabsContainer}>
          <h1>Добро пожаловать в PIXORY</h1>
          <p>Пожалуйста, войдите или зарегистрируйтесь</p>
          <div className={styles.authTabs}>
            <button
              onClick={() => setTab('login')}
              className={`${styles.tabs} ${tab === 'login' ? styles.active : ''} `}
            >
              Вход
            </button>
            <button
              onClick={() => setTab('register')}
               className={`${styles.tabs} ${tab === 'register' ? styles.active : ''} `}
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
