import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    build: {
        rollupOptions: {
            input: 'src/admin/admin-main.js',
        },
    },
    server: {
        open: '/src/admin/index.html',
    },
});
