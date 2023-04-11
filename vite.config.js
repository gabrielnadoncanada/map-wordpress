import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'


function createOutput(entryName) {
  return {
    format: 'es',
    entryFileNames: `${entryName}.js`,
    chunkFileNames: `${entryName}.[name].js`,
    assetFileNames: `${entryName}.[name].[ext]`,
    dir: `dist/${entryName}`,
  };
}

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src/front', import.meta.url))
    }
  },
  build: {
    rollupOptions: {
      input: {
        frontend: 'src/frontend/frontend-main.js',
        admin: 'src/admin/admin-main.js',
      },
      output: [
        createOutput('frontend'),
        createOutput('admin'),
      ],
    },
  }
})
