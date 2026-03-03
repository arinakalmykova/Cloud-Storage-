import { Cloud } from 'lucide-react';
import { Link } from 'react-router-dom';
import '../../../app/styles/Logo.css';

export function Logo() {
  return (
    <Link to={'/'} className="logo-link">
      <div className="logo">
        <div className="logo-icon">
          <Cloud />
        </div>
        <div className="logo-text">PIXORY</div>
      </div>
    </Link>
  );
}
