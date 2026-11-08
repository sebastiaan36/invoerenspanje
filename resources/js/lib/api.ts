import { cookie as csrfCookie } from '@/routes/csrf';

export const SESSION_EXPIRED_MESSAGE =
    'Je sessie is verlopen, herlaad de pagina.';
export const TOO_MANY_REQUESTS_MESSAGE =
    'Te veel aanvragen, probeer het over een minuut opnieuw.';

function xsrfTokenFromCookie(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

export interface ApiError<T = unknown> {
    status: number;
    data: T | null;
}

export interface NetworkError {
    network: true;
    message: string;
}

export function isNetworkError(e: unknown): e is NetworkError {
    return (
        typeof e === 'object' &&
        e !== null &&
        'network' in e &&
        (e as NetworkError).network === true
    );
}

export function isApiError<T = unknown>(e: unknown): e is ApiError<T> {
    return (
        typeof e === 'object' &&
        e !== null &&
        'status' in e &&
        typeof (e as ApiError).status === 'number'
    );
}

/**
 * Messages for failures that every API call shares: an expired session (419,
 * still failing after one token refresh) and rate limiting (429).
 */
export function sharedApiErrorMessage(e: unknown): string | null {
    if (!isApiError(e)) {
        return null;
    }

    if (e.status === 419) {
        return SESSION_EXPIRED_MESSAGE;
    }

    if (e.status === 429) {
        return TOO_MANY_REQUESTS_MESSAGE;
    }

    return null;
}

async function sendJson(url: string, body: unknown): Promise<Response> {
    try {
        return await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfTokenFromCookie(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });
    } catch (e) {
        const err: NetworkError = {
            network: true,
            message: e instanceof Error ? e.message : 'Onbekende netwerkfout',
        };

        throw err;
    }
}

/**
 * The response carries a fresh XSRF-TOKEN cookie (new session after expiry).
 * A failure here is ignored: the retry then simply fails with 419 again.
 */
async function refreshXsrfToken(): Promise<void> {
    try {
        await fetch(csrfCookie.url(), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
    } catch {
        // Handled by the retry.
    }
}

export async function postJson<TResponse, TBody = unknown>(
    url: string,
    body: TBody,
): Promise<TResponse> {
    let response = await sendJson(url, body);

    // 419: the session (and its CSRF token) expired while the page was open.
    // Fetch a new token and retry once before giving up.
    if (response.status === 419) {
        await refreshXsrfToken();
        response = await sendJson(url, body);
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        const err: ApiError = { status: response.status, data };

        throw err;
    }

    return data as TResponse;
}
