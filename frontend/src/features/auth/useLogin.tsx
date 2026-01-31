import { useAppSelector, useAppDispatch  } from '../../app';
import { loginThunk } from '../../app';
import { useNavigate } from 'react-router-dom';

export function useLogin() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { loading, error } = useAppSelector(state => state.auth);

  const signIn = async (email: string, password: string) => {
    const result = await dispatch(loginThunk({ email, password }));
    if (loginThunk.fulfilled.match(result)) {
      navigate('/', { replace: true });
    }
  };

  return { signIn, loading, error };
}
