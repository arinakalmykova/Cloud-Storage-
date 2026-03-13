import { Outlet } from 'react-router-dom';
import { Header, SideBar } from '../../widgets';
import styles from '../styles/MainLayout.module.css';
export default function MainLayout() {
  return (
    <div className={styles.layout}>
      <Header />
      <div className={styles.content}>
        <SideBar />
        <main className={styles.main}>
          <Outlet />
        </main>
      </div>
    </div>
  );
}
