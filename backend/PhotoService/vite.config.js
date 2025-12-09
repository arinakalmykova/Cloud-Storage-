// vite.config.js
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// Полностью убираем laravel-vite-plugin — он больше не нужен
// import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [react()],

  root: 'resources/js',                    // ← важно! откуда берём index.html
  build: {
    outDir: '../dist',                     // ← собираем в корневую папку dist
    emptyOutDir: true,
    rollupOptions: {
      input: 'resources/js/index.html',    // ← явно указываем входную точку
    },
  },

  server: {
    port: 5173,
    open: true,                            // автоматически откроет браузер
    host: true,
  },
});