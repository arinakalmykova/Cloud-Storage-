import { Outlet } from 'react-router-dom';
import styles from '../../app/styles/AuthLayout.module.css';
export default function AuthLayout() {
  return (
    <div className={styles.authContent}>
      <Outlet />
    </div>
  );
}
