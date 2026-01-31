import {createSlice} from '@reduxjs/toolkit';
import type { User } from "../../../entities";
import { createAsyncThunk } from '@reduxjs/toolkit';
import { loginUser, fetchMe, registerUser } from '../../../entities';

interface AuthState{
    token:string | null
    loading:boolean
    error:string | null,
    user:User | null
}

const initialState: AuthState = {
  token: localStorage.getItem('token'),
  user:  localStorage.getItem('user_id')
    ? { id: String(localStorage.getItem('user_id')), name: '', email: '' } 
    : null,
  loading: false,
  error: null,
};


export const loginThunk = createAsyncThunk<
  { token: string; userId: string; user: User },
  { email: string; password: string },
  { rejectValue: string }
>(
  'auth/login',
  async ({ email, password }, { rejectWithValue }) => {
    try {
      console.log('💡 loginThunk: вызываем loginUser', { email });
      const response = await loginUser(email, password);
      console.log('💡 loginThunk: ответ loginUser', response);

      const userId = String(response.userId);
      localStorage.setItem('token', response.token);
      localStorage.setItem('user_id', userId);
      console.log('💡 loginThunk: userId сохранён в localStorage', userId);

      const user = await fetchMe(response.token);
      console.log('💡 loginThunk: ответ fetchMe', user);

      return {
        token: response.token,
        userId,
        user: {
          id: userId,      // string
          name: user.name,
          email: user.email
        }
      };
    } catch (err: any) {
      console.error('❌ loginThunk: ошибка', err);
      return rejectWithValue(err.message || 'Произошла ошибка');
    }
  }
);



export const registerThunk = createAsyncThunk<
  { user: User },
  { name: string; email: string; password: string },
  { rejectValue: string }
>(
  'auth/register',
  async ({ name, email, password }, { rejectWithValue }) => {
    try {
      const response = await registerUser(name, email, password);

      return {
        user: {
          id: response.userId || '',     
          name: response.name || '',     
          email: response.email || '',   
        }
      };
    } catch (err: any) {
      return rejectWithValue(err.message || 'Произошла ошибка'); 
    }
  }
);


export const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    logout(state) {
      state.token = null;
      state.user = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user_id');
    },
  },
  extraReducers:(builder) => {
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
  }
});

export const { logout } = authSlice.actions;


export default authSlice.reducer;
