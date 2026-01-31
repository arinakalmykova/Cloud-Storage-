import { Logo } from '../../../widgets';
import '../../../app/styles/Header.css';
import { LogOut, User } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../../features';
import {useState} from 'react';

export function Header() {
  const { logout } = useAuth();
  const [isOpen, setIsOpen] = useState(false);

  const handleLogout = () => {
    logout();          
  };

  return (
    <header className="header">
      <Logo />
      <div className="header__user-menu">
        <img
          src="https://cdn-icons-png.flaticon.com/512/149/149071.png"
          alt="user"
          className="header__user-menu__img" onClick={()=> {setIsOpen(!isOpen);}}
        />
        {isOpen && (<nav className="header__user-nav">
          <Link to={'/profile'} className="header__user-nav__item">
            <User size={20} /> Профиль
          </Link>
          <Link to={'/auth'} onClick= {handleLogout} className="header__user-nav__item">
            <LogOut size={20} /> Выйти
          </Link>
        </nav>
      )}
      </div>
    </header>
  );
}
