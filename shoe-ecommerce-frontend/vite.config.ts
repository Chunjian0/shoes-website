import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: '0.0.0.0', // Allow access from network
    port: 3000,
    proxy: {
      // Use environment variable for the backend URL
      '/api': {
        target: process.env.VITE_API_BASE_URL || 'http://localhost:8000', // Default for local dev
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/api/, '/api'), // Keep /api prefix for backend
      },
      // Proxy for storage files if needed during development
      '/storage': {
        target: process.env.VITE_API_BASE_URL || 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
}); 