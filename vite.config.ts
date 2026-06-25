import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import legacy from '@vitejs/plugin-legacy';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    css: {
        transformer: 'lightningcss',
        lightningcss: {
            targets: {
                chrome: 51 << 16,
            },
        },
    },
    build: {
        cssMinify: 'lightningcss',
        cssTarget: 'chrome51',
    },
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
            input: ['resources/js/app.ts', 'resources/css/filament/admin/theme.css'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss({
            optimize: false,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        legacy({
            targets: ['Chrome >= 51', 'Android >= 7'],
        }),
        wayfinder({
            formVariants: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                navigateFallback: null,
            },
            manifest: {
                id: '/',
                name: 'Thương Óc',
                short_name: 'ThuongOc',
                description: 'Thương Óc - Ứng dụng đặt món',
                theme_color: '#ffffff',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/pwa-64x64.png',
                        sizes: '64x64',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/maskable-icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
                screenshots: [
                    {
                        src: '/images/screenshot-mobile.png',
                        sizes: '390x844',
                        type: 'image/png',
                        form_factor: 'narrow',
                        label: 'Thương Óc - Ứng dụng đặt món',
                    },
                    {
                        src: '/images/screenshot-tablet.png',
                        sizes: '1024x1366',
                        type: 'image/png',
                        form_factor: 'wide',
                        label: 'Thương Óc - Ứng dụng đặt món',
                    },
                ],
            },
        }),
    ],
});
