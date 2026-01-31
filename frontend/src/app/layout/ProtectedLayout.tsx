import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../../features';

export const ProtectedLayout = () => {
  const { isAuth, loading } = useAuth();

  if (loading) return null;

  return isAuth ? <Outlet /> : <Navigate to="/auth" replace />;
};
