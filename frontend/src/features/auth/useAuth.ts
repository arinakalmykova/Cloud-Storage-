import { useAppDispatch, useAppSelector, logout, update } from '../../app';
import { deleteUser, updateUser } from '../../entities';

export const useAuth = () => {
  const dispatch = useAppDispatch();
  const { token, user, loading } = useAppSelector((state) => ({
    token: state.auth.token,
    user: state.auth.user,
    loading: state.auth.loading,
  }));

  const deleteUserFunc = async () => {
    if (!token) return { success: false, message: 'Нет токена' };
    const result = await deleteUser(token);
    if (result.success) {
      dispatch(logout());
    }
    return result;
  };

  const updateUserFunc = async (data: { name: string; email: string }) => {
    if (!token) return { success: false, message: 'Нет токена' };
    const result = await updateUser(token, data.name, data.email);
    if (result.success && result.user) {
      dispatch(update(result.user));
    }
    return result;
  };

  return {
    token,
    user,
    loading,
    deleteUserFunc,
    updateUserFunc,
    isAuth: Boolean(token),
    logout: () => dispatch(logout()),
  };
};
