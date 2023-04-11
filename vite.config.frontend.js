import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    build: {
        rollupOptions: {
            input: 'src/frontend/frontend-main.js',
        },
    },
    server: {
        open: '/templates/frontend/index.html',
    },
});