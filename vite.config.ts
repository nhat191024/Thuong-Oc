import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: true,
        allowedHosts: ['thuongoc.test'],
        hmr: {
            host: 'thuongoc.test',
            protocol: 'wss',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/app.ts',
                'resources/css/filament/admin/theme.css',
            ],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
});
