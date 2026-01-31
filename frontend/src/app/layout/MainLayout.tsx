import { Outlet } from 'react-router-dom';
import { Header, Footer, SideBar } from '../../widgets';
import '../styles/MainLayout.css';
export function MainLayout() {
  return (
    <div className="layout">
      <Header />
      <div className="content">
        <SideBar />
        <main className="main">
          <Outlet />
        </main>
      </div>
      <Footer />
    </div>
  );
}
