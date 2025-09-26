import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'

export default defineConfig({

    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        https: false,
        hmr: { host: '127.0.0.1' },
    },

    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss', 'resources/js/app.js',
            ],
            refresh: true,
        }),
            vue(),
    ],
});
