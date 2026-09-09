import Alpine from 'alpinejs';
import { initEcho } from './echo';
import './cart';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('cartCount', 0);
    Alpine.store('cart', {
        items: [],
    });
});

Alpine.start();

if (window.EchoEnabled) {
    try {
        initEcho();
    } catch (error) {
        console.warn('Echo not available:', error);
    }
}