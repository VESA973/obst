/**
 * Page « L'association » : en-tête et apparitions (communs avec l'accueil),
 * compteurs animés des chiffres clés, visionneuse de la galerie (GLightbox).
 */
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';
import '../css/home.css';
import '../css/association.css';
import { initHeader, initReveal } from './shared/site-sections';

initHeader();

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Compteur : de 0 à la valeur finale (déjà écrite dans la page) en ~1,2 s.
const animateCounter = (element) => {
    const target = Number(element.dataset.countTo);
    if (reducedMotion || !Number.isFinite(target) || target <= 1) {
        return;
    }

    const duration = 1200;
    const start = performance.now();
    const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        // Décélération douce en fin de course.
        element.textContent = String(Math.round(target * (1 - (1 - progress) ** 3)));
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };
    element.textContent = '0';
    requestAnimationFrame(step);
};

initReveal('.home-reveal', (section) => section.querySelectorAll('[data-count-to]').forEach(animateCounter));

// Visionneuse de la galerie : légendes, clavier, balayage tactile.
if (document.querySelector('.glightbox')) {
    GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        openEffect: reducedMotion ? 'none' : 'fade',
        closeEffect: reducedMotion ? 'none' : 'fade',
        descPosition: 'bottom',
    });
}
