function xsrfTokenFromCookie(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

export interface ApiError<T = unknown> {
    status: number;
    data: T | null;
}

export async function postJson<TResponse, TBody = unknown>(
    url: string,
    body: TBody,
): Promise<TResponse> {
    const response = await fetch(url, {
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

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        const err: ApiError = { status: response.status, data };
        throw err;
    }

    return data as TResponse;
}
