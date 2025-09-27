import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
      '@neibrpay/ui': resolve(__dirname, '../../packages/ui/src'),
      '@neibrpay/api-client': resolve(
        __dirname,
        '../../packages/api-client/src'
      ),
      '@neibrpay/models': resolve(__dirname, '../../packages/models/src'),
      '@neibrpay/config': resolve(__dirname, '../../packages/config/src'),
    },
  },
  server: {
    port: 3001,
    host: true,
  },
  build: {
    outDir: 'dist',
    sourcemap: true,
  },
});
