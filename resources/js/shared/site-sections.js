/**
 * Comportements communs aux pages au design « accueil » (accueil, L'association) :
 * en-tête transparent puis compact au défilement, apparitions au défilement.
 */

export function initHeader() {
    // En-tête : transparent sur la photo, puis compact avec fond flouté après défilement (≥ 1024 px, voir CSS).
    const onScroll = () => document.body.classList.toggle('home-scrolled', window.scrollY > 40);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Hauteur de l'en-tête, pour que la photo commence sous lui.
    const header = document.querySelector('.site-header');
    const syncHeaderHeight = () => {
        if (header && !document.body.classList.contains('home-scrolled')) {
            document.documentElement.style.setProperty('--home-header-h', `${header.offsetHeight}px`);
        }
    };
    syncHeaderHeight();
    window.addEventListener('resize', syncHeaderHeight);
}

/**
 * Apparitions au défilement (la classe home-has-reveal est posée dans le <head> ; sans JS tout reste visible).
 * `onVisible` est appelé une fois par élément devenu visible.
 */
export function initReveal(selector = '.home-reveal', onVisible = () => {}) {
    if (!document.documentElement.classList.contains('home-has-reveal')) {
        document.querySelectorAll(selector).forEach(onVisible);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                onVisible(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll(selector).forEach((element) => observer.observe(element));
}
