import {lazy} from 'react';
import { createBrowserRouter } from 'react-router-dom';
import {
  DashboardPage,
  ArchivePage,
  AuthPage,
  ProfilePage,
  UploadPage,
  SearchPage,
} from '../../pages';

const ProtectedLayout = lazy(() => import('../layout/ProtectedLayout'));
const MainLayout = lazy(() => import('../layout/MainLayout'));
const AuthLayout = lazy(() => import('../layout/AuthLayout'));

export const privateRoutes = [
  { path: '/', element: <DashboardPage /> },
  { path: '/archive', element: <ArchivePage /> },
  { path: '/profile', element: <ProfilePage /> },
  { path: '/upload', element: <UploadPage /> },
  { path: '/search', element: <SearchPage /> },
];

export const publicRoutes = [
  { path: '/auth', element: <AuthPage /> },
];

export const router = createBrowserRouter([
  {
    element: <ProtectedLayout />,
    children: [
      {
        element: <MainLayout />,
        children: privateRoutes,
      },
    ],
  },
  {
    element: <AuthLayout />,
    children: publicRoutes,
  },
]);
