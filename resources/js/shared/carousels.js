/**
 * Carrousels Swiper des composants <x-home.carousel> (accueil, fiche événement…).
 */
import Swiper from 'swiper';
import { A11y, Autoplay, Keyboard, Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/a11y';

export function initCarousels() {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Carrousels : 1,2 carte sur mobile, 2,5 sur tablette, 3 à 3,5 sur ordinateur.
    document.querySelectorAll('[data-home-carousel]').forEach((carousel) => {
        const autoplay = carousel.hasAttribute('data-autoplay') && !reducedMotion;

        new Swiper(carousel.querySelector('.swiper'), {
            modules: [A11y, Autoplay, Keyboard, Navigation, Pagination],
            slidesPerView: 1.2,
            spaceBetween: 16,
            speed: reducedMotion ? 0 : 500,
            grabCursor: true,
            watchOverflow: true,
            keyboard: { enabled: true, onlyInViewport: true },
            navigation: {
                prevEl: carousel.querySelector('[data-carousel-prev]'),
                nextEl: carousel.querySelector('[data-carousel-next]'),
            },
            pagination: {
                el: carousel.querySelector('[data-carousel-pagination]'),
                type: 'progressbar',
            },
            a11y: {
                prevSlideMessage: 'Élément précédent',
                nextSlideMessage: 'Élément suivant',
                firstSlideMessage: 'Premier élément',
                lastSlideMessage: 'Dernier élément',
                slideLabelMessage: '{{index}} sur {{slidesLength}}',
                containerRoleDescriptionMessage: 'carrousel',
                itemRoleDescriptionMessage: 'diapositive',
            },
            // Défilement lent, en pause au survol et au clavier (carrousel principal uniquement).
            autoplay: autoplay ? { delay: 6000, pauseOnMouseEnter: true, disableOnInteraction: false } : false,
            breakpoints: {
                640: { slidesPerView: 2.2, spaceBetween: 20 },
                768: { slidesPerView: 2.5, spaceBetween: 24 },
                1024: { slidesPerView: 3, spaceBetween: 28 },
                1440: { slidesPerView: 3.5, spaceBetween: 28 },
            },
            on: {
                init(swiper) {
                    carousel.classList.add('is-ready');
                    // Pause de l'autoplay quand le focus clavier entre dans le carrousel.
                    if (autoplay) {
                        carousel.addEventListener('focusin', () => swiper.autoplay.stop());
                        carousel.addEventListener('focusout', () => swiper.autoplay.start());
                    }
                },
            },
        });
    });
}
