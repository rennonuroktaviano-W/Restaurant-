import Echo from 'laravel-echo';

export function initEcho() {
    const client = window.Pusher;

    if (! client) {
        return;
    }

    window.Echo = new Echo({
        broadcaster: 'reverb',
        client,
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT || 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}