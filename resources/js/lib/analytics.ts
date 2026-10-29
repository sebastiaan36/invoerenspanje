/**
 * Lightweight wrapper around Google Analytics (gtag.js).
 *
 * The gtag snippet is only injected on production (see app.blade.php), so on
 * local/staging `window.gtag` is undefined and these calls safely no-op.
 */

type GtagParams = Record<string, unknown>;

/**
 * Every custom GA4 event the site sends, so names stay consistent and can be
 * registered once in GA4 (Beheer → Gebeurtenissen):
 *
 * - kenteken_lookup: a licence plate was looked up in the kentekencheck.
 * - bpm_calculation: a licence plate was looked up in the BPM calculator.
 * - select_package: a service package was chosen.
 * - generate_lead: the quote form or the contact form was sent (GA4
 *   recommended event, mark it as a key event).
 */
export type AnalyticsEvent =
    | 'kenteken_lookup'
    | 'bpm_calculation'
    | 'select_package'
    | 'generate_lead';

declare global {
    interface Window {
        gtag?: (...args: unknown[]) => void;
    }
}

/**
 * Send a GA4 event. Does nothing when gtag is not loaded.
 */
export function trackEvent(
    name: AnalyticsEvent,
    params: GtagParams = {},
): void {
    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
        window.gtag('event', name, {
            page_path: window.location.pathname,
            ...params,
        });
    }
}
