import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            // Self-hosted at build time (no third-party request at runtime).
            // Only the hero heading weight is preloaded: it is the LCP element.
            fonts: [
                bunny('Fraunces', {
                    weights: [400, 500, 600, 700],
                    subsets: ['latin'],
                    preload: [{ weight: 600, style: 'normal' }],
                }),
                bunny('Inter', {
                    weights: [400, 500, 600],
                    subsets: ['latin'],
                    preload: false,
                }),
            ],
        }),
        inertia(),
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
