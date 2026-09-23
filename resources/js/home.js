/**
 * Page d'accueil : carrousels Swiper, en-tête transparent puis compact, apparitions au défilement.
 * Chargé uniquement sur l'accueil (voir pages/home.blade.php).
 */
import '../css/home.css';
import { initCarousels } from './shared/carousels';
import { initHeader, initReveal } from './shared/site-sections';

initCarousels();
initHeader();
initReveal();
