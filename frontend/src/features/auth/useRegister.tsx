import { useAppDispatch, useAppSelector } from '../../app';
import { registerThunk } from '../../app';

export function useRegister() {
  const dispatch = useAppDispatch();
  const { loading, error } = useAppSelector((state) => state.auth);

  const register = async (name: string, email: string, password: string) => {
    const result = await dispatch(registerThunk({ name, email, password }));

    if (registerThunk.fulfilled.match(result)) {
      return true;
    }

    return false;
  };

  return { register, loading, error };
}
