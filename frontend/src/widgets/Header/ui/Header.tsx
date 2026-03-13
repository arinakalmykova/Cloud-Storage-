import { useState } from 'react';
import { LogOut, User } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Logo } from '../../../widgets';
import { useAuth } from '../../../features';
import styles from '../../../app/styles/Header.module.css';

export function Header() {
  const { logout } = useAuth();
  const [isOpen, setIsOpen] = useState(false);

  const handleLogout = () => {
    logout();
  };

  return (
    <header className={styles.header}>
      <Logo />
      <div className={styles.headerUserMenu}>
        <img
          src="https://cdn-icons-png.flaticon.com/512/149/149071.png"
          alt="user"
          className={styles.headerUserMenuImage}
          onClick={() => {
            setIsOpen(!isOpen);
          }}
        />
        {isOpen && (
          <nav className={styles.headerUserNav}>
            <Link to={'/profile'} className={styles.headerUserNavItem}>
              <User size={20} /> Профиль
            </Link>
            <Link to={'/auth'} onClick={handleLogout} className={styles.headerUserNavItem}>
              <LogOut size={20} /> Выйти
            </Link>
          </nav>
        )}
      </div>
    </header>
  );
}
