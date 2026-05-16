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
    return typeof e === 'object' && e !== null && 'network' in e && (e as NetworkError).network === true;
}

export function isApiError<T = unknown>(e: unknown): e is ApiError<T> {
    return typeof e === 'object' && e !== null && 'status' in e && typeof (e as ApiError).status === 'number';
}

export async function postJson<TResponse, TBody = unknown>(
    url: string,
    body: TBody,
): Promise<TResponse> {
    let response: Response;

    try {
        response = await fetch(url, {
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

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        const err: ApiError = { status: response.status, data };

        throw err;
    }

    return data as TResponse;
}
