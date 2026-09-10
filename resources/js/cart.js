const formatNumber = (value) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value || 0);

const formatRupiah = (value) => `Rp ${formatNumber(value)}`;

const csrfToken = () => (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

function showToast(message, type = 'error') {
    const icons = {
        error: 'M12 9v3m0 4h.01M12 21a9 9 0 100-18 9 9 0 000 18z',
        success: 'M5 13l4 4L19 7',
        info: 'M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z',
    };
    const toast = document.createElement('div');
    toast.setAttribute('role', 'alert');
    toast.className = `toast toast-${type} fixed bottom-4 right-4 z-[80] max-w-sm`;
    toast.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="${icons[type] || icons.error}"/>
        </svg>
        <div>${message}</div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

function patchLine(line) {
    const id = line.product_id;

    const drawerQty = document.querySelector(`[data-cart-line-qty="${id}"]`);
    if (drawerQty) drawerQty.textContent = line.quantity;

    const drawerTotal = document.querySelector(`[data-cart-line-total="${id}"]`);
    if (drawerTotal) drawerTotal.textContent = formatRupiah(line.price * line.quantity);

    const drawerMinus = document.querySelector(`[data-cart-drawer-minus="${id}"]`);
    if (drawerMinus) drawerMinus.value = Math.max(0, line.quantity - 1);

    const drawerPlus = document.querySelector(`[data-cart-drawer-plus="${id}"]`);
    if (drawerPlus) {
        drawerPlus.value = line.quantity + 1;
        drawerPlus.disabled = line.limited && line.quantity >= line.stock;
    }

    const rowTotal = document.querySelector(`[data-cart-row-total="${id}"]`);
    if (rowTotal) rowTotal.textContent = formatRupiah(line.price * line.quantity);

    const pageQty = document.querySelector(`[data-cart-page-qty="${id}"]`);
    if (pageQty) pageQty.value = line.quantity;

    const minus = document.querySelector(`[data-cart-minus="${id}"]`);
    if (minus) minus.value = Math.max(0, line.quantity - 1);

    const plus = document.querySelector(`[data-cart-plus="${id}"]`);
    if (plus) {
        plus.value = line.quantity + 1;
        plus.disabled = line.limited && line.quantity >= line.stock;
    }

    const stepper = document.querySelector(`[data-menu-stepper="${id}"]`);
    const addForm = document.querySelector(`[data-menu-add="${id}"]`);
    if (stepper && addForm) {
        stepper.hidden = line.quantity === 0;
        addForm.hidden = line.quantity > 0;

        const qtySpan = stepper.querySelector(`[data-menu-qty="${id}"]`);
        if (qtySpan) qtySpan.textContent = line.quantity;

        const stepperMinus = stepper.querySelector('[data-menu-minus]');
        if (stepperMinus) stepperMinus.value = Math.max(0, line.quantity - 1);

        const stepperPlus = stepper.querySelector('[data-menu-plus]');
        if (stepperPlus) {
            stepperPlus.value = line.quantity + 1;
            stepperPlus.disabled = line.limited && line.quantity >= line.stock;
        }
    }
}

function removeMissingRows(ids) {
    document.querySelectorAll('[data-cart-line-row]').forEach((row) => {
        const id = Number(row.dataset.cartLineRow);
        if (!ids.includes(id)) row.remove();
    });
}

function updateSummary(pricing) {
    const container = document.querySelector('[data-cart-summary]');
    if (!container) return;

    const row = (label, value, extraClass = '') => {
        const div = document.createElement('div');
        div.className = `flex justify-between ${extraClass}`;
        const dt = document.createElement('dt');
        dt.className = extraClass.includes('forest') ? '' : 'text-ink-500';
        dt.textContent = label;
        const dd = document.createElement('dd');
        dd.className = extraClass.includes('font-') ? '' : 'font-medium';
        dd.textContent = value;
        div.append(dt, dd);
        return div;
    };

    container.innerHTML = '';
    container.appendChild(row('Subtotal', formatRupiah(pricing.subtotal)));

    if (Number(pricing.discount_amount) > 0) {
        container.appendChild(row('Diskon', `− ${formatRupiah(pricing.discount_amount)}`, 'text-forest-700'));
    }
    if (Number(pricing.tax_amount) > 0) {
        container.appendChild(row('Pajak', formatRupiah(pricing.tax_amount)));
    }
    if (Number(pricing.service_charge_amount) > 0) {
        container.appendChild(row('Service Charge', formatRupiah(pricing.service_charge_amount)));
    }

    const totalRow = document.createElement('div');
    totalRow.className = 'flex justify-between border-t border-ink-900/10 pt-3 font-display text-base font-bold';
    const dt = document.createElement('dt');
    dt.textContent = 'Total';
    const dd = document.createElement('dd');
    dd.className = 'text-forest-700';
    dd.textContent = formatRupiah(pricing.grand_total);
    totalRow.append(dt, dd);
    container.appendChild(totalRow);
}

function applyCartState(state) {
    if (window.Alpine?.store) {
        Alpine.store('cartCount', state.count);
        Alpine.store('cart').items = state.lines;
    }

    if (state.subtotal !== undefined) {
        document.querySelectorAll('[data-cart-subtotal]').forEach((el) => {
            el.textContent = formatRupiah(state.subtotal);
        });
    }

    state.lines.forEach(patchLine);
    removeMissingRows(state.lines.map((line) => line.product_id));
    updateSummary(state.pricing);

    if (state.count === 0) {
        window.location.reload();
    }
}

async function submitCartForm(form, forcedBody = null, submitter = null) {
    const button = submitter && submitter.name ? submitter : form.querySelector('button[type="submit"]');
    if (button) button.disabled = true;

    try {
        let body = forcedBody;

        if (!body) {
            const formData = new FormData(form);
            if (submitter && submitter.name) formData.set(submitter.name, submitter.value);
            body = new URLSearchParams(formData);
        }

        const response = await fetch(form.action, {
            method: form.method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body,
            credentials: 'same-origin',
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            showToast(data.message || 'Terjadi kesalahan saat memperbarui keranjang.', 'error');
            return;
        }

        applyCartState(data);
    } catch (error) {
        console.error('Cart update failed:', error);
        showToast('Gagal terhubung ke server. Coba lagi.', 'error');
    } finally {
        if (button) button.disabled = false;
    }
}

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form');
    if (!form || !form.matches('[data-cart-ajax]')) return;

    event.preventDefault();
    submitCartForm(form, null, event.submitter);
});

document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-cart-quantity-input]');
    if (!input) return;

    const form = input.closest('form');
    if (!form) return;

    const body = new URLSearchParams(new FormData(form));
    body.set('quantity', input.value);
    submitCartForm(form, body);
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;

    const input = event.target.closest && event.target.closest('[data-cart-quantity-input]');
    if (!input) return;

    event.preventDefault();
    const form = input.closest('form');
    if (!form) return;

    const body = new URLSearchParams(new FormData(form));
    body.set('quantity', input.value);
    submitCartForm(form, body);
});

document.addEventListener('DOMContentLoaded', () => {
    const node = document.getElementById('cart-state');
    if (!node) return;

    let state = null;
    try {
        state = JSON.parse(node.textContent);
    } catch (error) {
        return;
    }

    if (!state) return;

    if (window.Alpine?.store) {
        Alpine.store('cartCount', state.count);
        Alpine.store('cart').items = state.lines;
    }

    // Sync localStorage with server state on load
    syncLocalStorage(state);
});

// LocalStorage persistence for cart
const STORAGE_KEY = 'pos_customer_cart';
const STORAGE_VERSION = 1;

function getLocalCart() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (!stored) return null;
        const data = JSON.parse(stored);
        if (data.version !== STORAGE_VERSION) return null;
        return data.cart;
    } catch {
        return null;
    }
}

function setLocalCart(cartState) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            version: STORAGE_VERSION,
            cart: cartState,
            timestamp: Date.now(),
        }));
    } catch {
        // Ignore quota exceeded, private browsing, etc.
    }
}

function clearLocalCart() {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
        // Ignore
    }
}

function syncLocalStorage(state) {
    if (!state || state.count === 0) {
        clearLocalCart();
        return;
    }
    setLocalCart({
        count: state.count,
        lines: state.lines,
        subtotal: state.subtotal,
        pricing: state.pricing,
    });
}

// Override applyCartState to also sync localStorage
const originalApplyCartState = applyCartState;
window.applyCartState = function(state) {
    originalApplyCartState(state);
    syncLocalStorage(state);
};

// Restore cart from localStorage on pages without server state (e.g., direct navigation)
document.addEventListener('DOMContentLoaded', () => {
    // Only restore if no server state is present
    if (document.getElementById('cart-state')) return;

    const localCart = getLocalCart();
    if (!localCart || !localCart.lines?.length) return;

    // Check if cart is not too old (7 days)
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
        try {
            const data = JSON.parse(stored);
            if (Date.now() - data.timestamp > 7 * 24 * 60 * 60 * 1000) {
                clearLocalCart();
                return;
            }
        } catch {
            clearLocalCart();
            return;
        }
    }

    // Restore to Alpine store for UI
    if (window.Alpine?.store) {
        Alpine.store('cartCount', localCart.count);
        Alpine.store('cart').items = localCart.lines;
    }

    // Update UI elements
    document.querySelectorAll('[data-cart-subtotal]').forEach((el) => {
        el.textContent = formatRupiah(localCart.subtotal);
    });
    localCart.lines.forEach(patchLine);
    if (localCart.pricing) {
        updateSummary(localCart.pricing);
    }

    // Show toast notification
    showToast('Keranjang dipulihkan dari penyimpanan lokal', 'info');
});

// Export functions for external use (e.g., discount code apply from cart.blade.php)
window.updateSummary = updateSummary;
window.showToast = showToast;
window.clearLocalCart = clearLocalCart;