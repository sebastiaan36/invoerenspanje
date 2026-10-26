import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            // Self-hosted latin-only WOFF2 files (resources/fonts, OFL). The
            // bunny() provider emitted a separate WOFF rule after every WOFF2
            // rule, so browsers downloaded the larger WOFF and the preload was
            // wasted. Preloaded: the hero heading (Fraunces 600) and the hero
            // paragraph / body text (Inter 400), which is the LCP element and
            // caused the font-swap layout shift.
            fonts: [
                local('Fraunces', {
                    variants: [400, 500, 600, 700].map((weight) => ({
                        src: `resources/fonts/fraunces-${weight}-latin.woff2`,
                        weight,
                    })),
                    preload: [{ weight: 600, style: 'normal' }],
                }),
                local('Inter', {
                    variants: [400, 500, 600].map((weight) => ({
                        src: `resources/fonts/inter-${weight}-latin.woff2`,
                        weight,
                    })),
                    preload: [{ weight: 400, style: 'normal' }],
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
