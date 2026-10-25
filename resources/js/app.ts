import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    // Titles are complete as sent by the server (config/seo.php, blog
    // controller), so the browser title matches the server-rendered <title>
    // that search engines read. Only pages without a title fall back.
    title: (title) => title || appName,
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'Diensten':
            case name === 'Tarieven':
            case name === 'BpmCalculator':
            case name === 'Contact':
            case name === 'OverOns':
            case name === 'Faq':
            case name === 'Gids':
            case name.startsWith('Blog/'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
