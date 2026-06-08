/**
 * Lightweight wrapper around Google Analytics (gtag.js).
 *
 * The gtag snippet is only injected on production (see app.blade.php), so on
 * local/staging `window.gtag` is undefined and these calls safely no-op.
 */

type GtagParams = Record<string, unknown>;

declare global {
    interface Window {
        gtag?: (...args: unknown[]) => void;
    }
}

/**
 * Send a GA4 event. Does nothing when gtag is not loaded.
 */
export function trackEvent(name: string, params: GtagParams = {}): void {
    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
        window.gtag('event', name, params);
    }
}
