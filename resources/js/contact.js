/**
 * Page Contact : en-tête et apparitions (communs), compteur de caractères du message,
 * focus sur le résumé des erreurs après un envoi refusé.
 */
import '../css/home.css';
import '../css/association.css';
import '../css/contact.css';
import { initHeader, initReveal } from './shared/site-sections';

initHeader();
initReveal();

const message = document.querySelector('[data-contact-message]');
const count = document.querySelector('[data-contact-count]');
if (message && count) {
    const max = Number(message.getAttribute('maxlength'));
    const update = () => {
        const remaining = max - message.value.length;
        count.textContent = `${remaining} caractère${remaining > 1 ? 's' : ''} restant${remaining > 1 ? 's' : ''}`;
    };
    message.addEventListener('input', update);
    update();
}

document.querySelector('[data-contact-errors]')?.focus();
