import { createBrowserRouter } from 'react-router-dom';
import { MainLayout, AuthLayout,ProtectedLayout } from '../../app';
import { DashboardPage, ArchivePage, AuthPage, ProfilePage, UploadPage, SearchPage } from '../../pages';

export const router = createBrowserRouter([
  {
        element: <ProtectedLayout />,
        children: [
          { 
            element: <MainLayout />,
            children: [
              {
                path: '/',
                element: <DashboardPage />,
              },
              {
                path: '/archive',
                element: <ArchivePage />,
              },
              {
                path: '/profile',
                element: <ProfilePage />,
              },
              {
                path: '/upload',
                element: <UploadPage />,
              },
              {
                path: '/search',
                element: <SearchPage/>,
              }
          ],
          }],

        },
        {
          element: <AuthLayout />,
          children: [
            {
              path: '/auth',
              element: <AuthPage />,
            },
          ],
        },
]);
