import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../../features';
import { Loader } from '../../shared';

export default function ProtectedLayout () {
  const { isAuth, loading } = useAuth();
  
  if (loading) return <Loader/>;

  return isAuth ? <Outlet /> : <Navigate to="/auth" replace />;
};
