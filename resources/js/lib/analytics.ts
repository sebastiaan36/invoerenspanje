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
 * - email_click / whatsapp_click: an e-mail or WhatsApp link was clicked.
 */
export type AnalyticsEvent =
    | 'kenteken_lookup'
    | 'bpm_calculation'
    | 'select_package'
    | 'generate_lead'
    | 'email_click'
    | 'whatsapp_click';

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

/**
 * Track clicks on e-mail and WhatsApp links anywhere on the site with one
 * delegated listener, so links in layouts, components, server-rendered
 * fallback HTML and blog posts are all covered without wiring each one.
 */
export function initializeContactClickTracking(): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.addEventListener(
        'click',
        (event) => {
            const link = (event.target as Element | null)?.closest?.('a[href]');

            if (!(link instanceof HTMLAnchorElement)) {
                return;
            }

            const href = link.href.toLowerCase();
            const name: AnalyticsEvent | null = href.startsWith('mailto:')
                ? 'email_click'
                : /^https:\/\/(wa\.me|api\.whatsapp\.com)\//.test(href)
                  ? 'whatsapp_click'
                  : null;

            if (name) {
                trackEvent(name, {
                    link_location: link.closest('header')
                        ? 'header'
                        : link.closest('footer')
                          ? 'footer'
                          : 'content',
                });
            }
        },
        { capture: true },
    );
}
