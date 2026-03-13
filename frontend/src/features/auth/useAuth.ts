import { useAppDispatch, useAppSelector, logout, update } from '../../app';
import { deleteUser, updateUser } from '../../entities';

export const useAuth = () => {
  const dispatch = useAppDispatch();
 const token = useAppSelector((state) => state.auth.token);
  const user = useAppSelector((state) => state.auth.user);
  const loading = useAppSelector((state) => state.auth.loading);
  const error = useAppSelector((state) => state.auth.loginError || state.auth.registerError);

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
    error,
    loading,
    deleteUserFunc,
    updateUserFunc,
    isAuth: Boolean(token),
    logout: () => dispatch(logout()),
  };
};
