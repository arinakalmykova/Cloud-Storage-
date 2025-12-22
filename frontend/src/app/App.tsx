import { useState, useEffect } from 'react';
import type { FC } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { fetchMe } from '../entities/user/api/auth.api.ts';
import type { User } from '../entities/user/model/types.ts';
import Auth from '../pages/auth/Auth.tsx';
import Profile from '../pages/profile/Profile.tsx';
import MePage from '../pages/profile/MePage.tsx';
import './styles/App.css';

const App: FC = () => {
  const [token, setToken] = useState<string>(localStorage.getItem('token') || '');
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    if (token) {
      fetchMe(token)
        .then(setUser)
        .catch(() => {
          localStorage.removeItem('token');
          setToken('');
        })
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, [token]);

  if (loading) return <div style={{ padding: '50px', textAlign: 'center' }}>Загрузка...</div>;

  return (
    <Router>
      <Routes>
        <Route
          path="/"
          element={
            !user ? <Auth setToken={setToken} setUser={setUser} /> : <Navigate to="/profile" />
          }
        />
        <Route
          path="/profile"
          element={user ? <Profile user={user} setUser={setUser} /> : <Navigate to="/" />}
        />
        <Route path="/me" element={user ? <MePage user={user} /> : <Navigate to="/" />} />
        <Route path="*" element={<Navigate to="/" />} />
      </Routes>
    </Router>
  );
};

export default App;
