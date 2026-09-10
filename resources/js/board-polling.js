export function startBoardPolling({ url, interval = 15000 }) {
    let lastSignature = null;
    let inFlight = false;

    const isTyping = () => {
        const active = document.activeElement;

        return Boolean(active && active.matches?.('input, textarea, [contenteditable="true"]'));
    };

    const poll = async () => {
        if (inFlight) {
            return;
        }

        inFlight = true;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();

            if (payload.signature !== lastSignature) {
                lastSignature = payload.signature;

                if (!isTyping()) {
                    window.location.reload();
                }
            }
        } catch (error) {
            // transient network/server failure - retry on the next tick
        } finally {
            inFlight = false;
        }
    };

    window.setInterval(poll, interval);
    poll();
}