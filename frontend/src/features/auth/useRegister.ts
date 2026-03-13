import { useAppDispatch, useAppSelector, registerThunk } from '../../app';

export function useRegister() {
  const dispatch = useAppDispatch();
  const registerError = useAppSelector((state) => state.auth.registerError);
  const loading = useAppSelector((state) => state.auth.loading);

  const register = async (name: string, email: string, password: string) => {
    const result = await dispatch(registerThunk({ name, email, password }));

    if (registerThunk.fulfilled.match(result)) {
      return true;
    }

    return false;
  };

  return { register, loading, registerError };
}
