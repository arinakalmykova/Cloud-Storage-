import { Cloud } from 'lucide-react';
import { Link } from 'react-router-dom';
import styles from '../../../app/styles/Logo.module.css';

export function Logo() {
  return (
    <Link to={'/'} className={styles.logoLink}>
      <div className={styles.logo}>
        <div className={styles.logoIcon}>
          <Cloud />
        </div>
        <div className={styles.logoText}>PIXORY</div>
      </div>
    </Link>
  );
}
