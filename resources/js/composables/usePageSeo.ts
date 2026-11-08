import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';

/**
 * The shared `seo` page prop (HandleInertiaRequests::resolveSeo). The hero
 * copy comes from config/seo.php, the same source as the crawler HTML in
 * resources/views/seo/fallback.blade.php, so the two can't drift apart.
 */
export interface PageSeo {
    title: string;
    description: string;
    image: string | null;
    h1?: string | null;
    h1_accent?: string | null;
    intro?: string | null;
    intro_short?: string | null;
}

export function usePageSeo(): ComputedRef<PageSeo> {
    const page = usePage();

    return computed(() => page.props.seo as PageSeo);
}
