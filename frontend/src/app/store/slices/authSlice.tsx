import { createSlice, createAsyncThunk, type ActionReducerMapBuilder, type AsyncThunk } from '@reduxjs/toolkit';
import type { User } from '../../../entities';
import { loginUser, fetchMe, registerUser } from '../../../entities';

export interface AuthState {
  token: string | null;
  user: User | null;
  loading: boolean;
  loginError: string | null;      
  registerError: string | null;   
}

interface RejectValue {
  message: string;
  error?: string;
}

const initialState: AuthState = {
  token: null,
  user: null,
  loading: false,
  loginError: null,
  registerError: null,
};


function handleThunk<T>(
  builder: ActionReducerMapBuilder<AuthState>,
  thunk: AsyncThunk<T, any, { rejectValue: RejectValue }>,
  fulfilledHandler: (state: AuthState, action: { payload: T }) => void,
  errorField: 'loginError' | 'registerError'
) {
  builder
    .addCase(thunk.pending, (state) => {
      state.loading = true;
      state[errorField] = null;
    })
    .addCase(thunk.fulfilled, (state, action) => {
      state.loading = false;
      state[errorField] = null;
      fulfilledHandler(state, action);
    })
    .addCase(thunk.rejected, (state, action) => {
      state.loading = false;
      state[errorField] = action.payload?.message || 'Произошла ошибка';
    });
}

export const loginThunk = createAsyncThunk<
  { token: string; userId: string; user: User },
  { email: string; password: string },
  { rejectValue: RejectValue }
>('auth/login', async ({ email, password }, { rejectWithValue }) => {
  try {
    const response = await loginUser(email, password);
    const userId = String(response.userId);
    const user = await fetchMe(response.token);

    return {
      token: response.token,
      userId,
      user: {
        id: userId,
        name: user.name,
        email: user.email,
        createdAt: user.createdAt,
      },
    };
  } catch (err: any) {
    return rejectWithValue({
      message: err.message || 'Ошибка при входе',
      error: 'login_error',
    });
  }
});

export const registerThunk = createAsyncThunk<
  { user: User },
  { name: string; email: string; password: string },
  { rejectValue: RejectValue }
>('auth/register', async ({ name, email, password }, { rejectWithValue }) => {
  try {
    const response = await registerUser(name, email, password);

    if (!response || response.error) {
      return rejectWithValue({
        message: response?.message || 'Ошибка при регистрации',
        error: response?.error || 'registration_error',
      });
    }

    return {
      user: {
        id: response.userId || '',
        name: response.name || '',
        email: response.email || '',
        createdAt: response.createdAt || new Date().toISOString(),
      },
    };
  } catch (err: any) {
    return rejectWithValue({
      message: err.message || 'Ошибка при регистрации',
      error: 'unknown_error',
    });
  }
});

export const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    logout(state) {
      state.token = null;
      state.user = null;
      state.loginError = null;
      state.registerError = null;
      localStorage.removeItem('persist:root');
    },
    update(state, action) {
      state.user = action.payload;
    },
  },
  extraReducers: (builder) => {
    handleThunk(
      builder,
      loginThunk,
      (state, action) => {
        state.token = action.payload.token;
        state.user = action.payload.user;
      },
      'loginError'
    );

    handleThunk(
      builder,
      registerThunk,
      (state, action) => {
        state.user = action.payload.user;
      },
      'registerError'
    );
  },
});

export const { logout, update } = authSlice.actions;
export default authSlice.reducer;