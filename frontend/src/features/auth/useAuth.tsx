// app/hooks/useAuth.ts
import { useAppDispatch, useAppSelector, logout } from '../../app';

export const useAuth = () => {
  const dispatch = useAppDispatch();

  const { token, user, loading } = useAppSelector((state) => {
    return {
      token: state.auth.token,
      user: state.auth.user,
      loading: state.auth.loading,
    };
  });
  return {
    token,
    user,
    loading,
    isAuth: Boolean(token),
    logout: () => dispatch(logout()),
  };
};
