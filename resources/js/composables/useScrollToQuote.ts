/**
 * Scroll to the quote flow on the homepage without a page visit (which would
 * wipe the visitor's calculation). The quote form (#offerte) only exists after
 * a lookup; before that, go to the kenteken check (#kentekencheck) instead.
 * Focuses the first field so the visitor can type right away.
 */
export function scrollToQuote(): void {
    const target =
        document.getElementById('offerte') ??
        document.getElementById('kentekencheck');

    if (!target) {
        return;
    }

    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    target.scrollIntoView({
        behavior: reduceMotion ? 'auto' : 'smooth',
        block: 'start',
    });
    target
        .querySelector<HTMLElement>('input, textarea')
        ?.focus({ preventScroll: true });
}
