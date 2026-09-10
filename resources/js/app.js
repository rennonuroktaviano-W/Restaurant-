import Alpine from 'alpinejs';
import { initEcho } from './echo';
import { startBoardPolling } from './board-polling';
import './cart';

window.Alpine = Alpine;
window.startBoardPolling = startBoardPolling;

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