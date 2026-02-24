import { Outlet } from 'react-router-dom';
import '../../app/styles/AuthLayout.css';
export function AuthLayout() {
  return (
    <div className="auth-content">
      <Outlet />
    </div>
  );
}
