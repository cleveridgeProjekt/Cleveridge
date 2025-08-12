import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

import fs from 'fs'

export default defineConfig({
    base: '/build/',
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        https: {
            key: fs.readFileSync('./.cert/localhost-key.pem'),
            cert: fs.readFileSync('./.cert/localhost.pem'),
        },
        cors: true,
        host: 'localhost',
        port: 5173,
        strictPort: true
    },
});
