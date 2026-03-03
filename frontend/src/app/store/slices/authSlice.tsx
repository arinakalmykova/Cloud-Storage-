import { createSlice } from '@reduxjs/toolkit';
import type { User } from '../../../entities';
import { createAsyncThunk } from '@reduxjs/toolkit';
import { loginUser, fetchMe, registerUser } from '../../../entities';

interface AuthState {
  token: string | null;
  loading: boolean;
  error: string | null;
  user: User | null;
}

const savedUser = localStorage.getItem('user_data');
const initialState: AuthState = {
  token: localStorage.getItem('token'),
  user: savedUser ? JSON.parse(savedUser) : null,
  loading: false,
  error: null,
};

export const loginThunk = createAsyncThunk<
  { token: string; userId: string; user: User },
  { email: string; password: string },
  { rejectValue: string }
>('auth/login', async ({ email, password }, { rejectWithValue }) => {
  try {
    const response = await loginUser(email, password);
    const userId = String(response.userId);
    localStorage.setItem('token', response.token);
    const user = await fetchMe(response.token);
    localStorage.setItem(
      'user_data',
      JSON.stringify({
        id: userId,
        name: user.name,
        email: user.email,
        createdAt: user.createdAt,
      })
    );
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
    return rejectWithValue(err.message || 'Произошла ошибка');
  }
});

export const registerThunk = createAsyncThunk<
  { user: User },
  { name: string; email: string; password: string },
  { rejectValue: string }
>('auth/register', async ({ name, email, password }, { rejectWithValue }) => {
  try {
    const response = await registerUser(name, email, password);

    return {
      user: {
        id: response.userId || '',
        name: response.name || '',
        email: response.email || '',
        createdAt: response.createdAt || new Date().toISOString(),
      },
    };
  } catch (err: any) {
    return rejectWithValue(err.message || 'Произошла ошибка');
  }
});

export const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    logout(state) {
      state.token = null;
      state.user = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user_data');
    },
    update(state, action) {
      state.user = action.payload;
      localStorage.setItem('user_data', JSON.stringify(action.payload));
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(loginThunk.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(loginThunk.fulfilled, (state, action) => {
        state.loading = false;
        state.token = action.payload.token;
        state.user = action.payload.user;
      })
      .addCase(loginThunk.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload || 'Ошибка';
      })
      .addCase(registerThunk.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(registerThunk.fulfilled, (state, action) => {
        state.loading = false;
        state.user = action.payload.user;
      })
      .addCase(registerThunk.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload || 'Ошибка';
      });
  },
});

export const { logout, update } = authSlice.actions;

export default authSlice.reducer;
