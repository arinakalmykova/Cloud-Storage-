import { Cloud } from 'lucide-react';
import '../../../app/styles/Logo.css';
import { Link } from 'react-router-dom';
export function Logo() {
  return (
    <Link to={'/'} className='logo-link'>
      <div className="logo">
        <div className="logo-icon">
          <Cloud />
        </div>
        <div className="logo-text">PIXORY</div>
      </div>
    </Link>
  );
}
