import { RouterProvider } from 'react-router-dom';
import { router } from '../app';
import { Suspense } from 'react';
import { Loader } from '../shared';
import './styles/App.css';

export default function App() {
  return (
    <Suspense fallback={<Loader/>}>
      <RouterProvider router={router} />
    </Suspense>
  );
}
