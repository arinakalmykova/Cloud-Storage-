import React from 'react';
import styles from '../../../app/styles/Button.module.css';

type ButtonProps = {
  children: React.ReactNode;
  onClick?: (e: React.MouseEvent) => void;
  loading?: boolean;
  disabled?: boolean;
  className?: string;
  type?: 'button' | 'submit' | 'reset';
};

export function Button({
  children,
  onClick,
  loading = false,
  disabled = false,
  className = '',
  type = 'button',
}: ButtonProps) {
  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled || loading}
      className={`${styles.customButton} ${className} ${loading ? 'loading' : ''}`}
    >
      {loading ? 'Загрузка...' : children}
    </button>
  );
}
