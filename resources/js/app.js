import Alpine from 'alpinejs';
import { initEcho } from './echo';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('cartCount', 0);
    Alpine.store('cart', {
        items: [],
    });
});

Alpine.start();

if (window.EchoEnabled) {
    initEcho();
}